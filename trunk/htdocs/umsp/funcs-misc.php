<?php
	error_reporting(E_ALL ^ E_NOTICE);	// avoid the notice message.
	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
	function _callPlugin($prmPath, $prmQuery = null) {
		# TODO: make plugins config Ini based
		# prmPath is expected as '/pluginName'
		$tmpArr = explode('/', $prmPath, 3);
		if ($tmpArr[1] != '') {
			$pluginFile = _getPluginFile($tmpArr[1]);
			if (is_null($pluginFile)) {
				return NULL;
			};
		};
		# Save the plugin path to tmp to know our location in case of a search query
		_writeTmpVar('lastUsedPlugin', $pluginFile);
		
		# Load plugin file and call its _pluginMain function with query string as parameter
		# The plugin is expected to return an array of media items
		require $pluginFile;
		$pluginItems = _pluginMain($prmQuery);
		return $pluginItems;
	} # end function

	function _getPluginFile($pluginName) {
		$tmpPluginPath = Array(
			dirname(__FILE__).'/user-plugins/'. $pluginName . '.php',
			dirname(__FILE__).'/plugins/'. $pluginName . '.php',
			dirname(__FILE__).'/plugins/'. $pluginName . '/' . $tmpArr[2] . '.php',
			dirname(__FILE__).'/plugins/'. $pluginName . '/' . $pluginName . '.php'
		);
		foreach ($tmpPluginPath as $key=>$val) {
			if (is_file($val)) {
				return $val;
			};
		};
		return NULL;
	};

	function _callPluginSearch($prmQuery) {
		$lastPlugin = _readTmpVar('lastUsedPlugin');
		if (!is_readable($lastPlugin)){
			return NULL;
		} else {
			require $lastPlugin;
			if (function_exists('_pluginSearch')) {
				$searchItems = _pluginSearch($prmQuery);
				return $searchItems;
			} else {
				return NULL;
			};
		};
	};

	function _createMessageItems($prmText) {
		# $prmText can be a single string or an array of multiple strings (lines)
		if (is_string($prmText)) { $prmText = array($prmText); }
		foreach ($prmText as $line) {
			if (trim($line) != '') {
			$retItems[] = array('id'		=> 'msg_' . count($retItems),
								'dc:title'	=> $line,
								'upnp:class'=> 'object.container',
							);
			} # end if
		} # end foreach
		return $retItems;
	} # end function

	function _readCache($prmId) {
		$filename = _getUMSPTmpPath() . '/umsp-cache';
		if ($prmId == '0') {
			# don't cache root and delete old cache
			if (is_file($filename)) {
				unlink($filename);
			}
			return NULL;
		} # end if
		if (!is_readable($filename)) {
			return NULL;
		}
		$rawContent = file_get_contents($filename);
		$cacheData = unserialize($rawContent);
		if ((is_array($cacheData['data'])) && ($cacheData['id'] == $prmId)) {
			return $cacheData['data'];
		} else {
			return NULL;
		} # end if
	} # end function

	function _writeCache($prmId, $prmDataArray) {
		$filename = _getUMSPTmpPath() . '/umsp-cache';
		if (!is_array($prmDataArray)) {
			return FALSE;
		};
		if ($prmId == '0') {  # don't cache root
			return FALSE;
		};
		$rawContent = serialize(array('id' => $prmId, 'data' => $prmDataArray));
		return file_put_contents($filename, $rawContent);
	} # end function

	function _clearCache() {
		$filename = _getUMSPTmpPath() . '/umsp-cache';
		return file_put_contents($filename, "");
	} # end function

	function _readTmpVar($prmVarName) {
		$filename = _getUMSPTmpPath() . '/umsp-tmpvars';
		if (!is_readable($filename)) {
			return NULL;
		};
		$rawContent = file_get_contents($filename);
		$varData = unserialize($rawContent);
		if (is_array($varData) && isset($varData[$prmVarName])) {
			return $varData[$prmVarName];
		} else {
			return NULL;
		} # end if
	} # end function

	function _writeTmpVar($prmVarName, $prmVarValue) {
		$filename = _getUMSPTmpPath() . '/umsp-tmpvars';
		if (is_readable($filename)) {
			$rawContent = file_get_contents($filename);
			$prevData = unserialize($rawContent);
			if ($prevData === FALSE || !is_array($prevData)) {
				$newData = Array();
			} else {
				$newData = $prevData;
			}
		} else {
			$newData = Array();
		}
		$newData[$prmVarName] = $prmVarValue;
		$rawContent = serialize($newData);
		return file_put_contents($filename, $rawContent);
	} # end function

	function _var_dump_pre($mixed = NULL) {
		echo '<pre>';
		var_dump($mixed);
		echo '</pre>';
		return NULL;
	}

	function _var_dump_ret($mixed = NULL) {
		ob_start();
		var_dump($mixed);
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	function _var_dump_to_file($mixed = NULL, $prmFile = '/tmp/umsp-log.txt') {
		ob_start();
		var_dump($mixed);
		$content = ob_get_contents();
		ob_end_clean();

		$prmFile = _getUMSPTmpPath() . '/umsp-log.txt';
		$myFile = $prmFile;
		$fh = fopen($myFile, 'a') or die();
		fwrite($fh, $content . "\r\n");
		fclose($fh);
	}

	function _my_parse_ini_string($prmIniString) {
		$lines = explode("\n", $prmIniString);
		$currentSection = NULL;
		foreach ($lines as $line) {
			$line = trim($line);
			$firstChar = substr($line, 0, 1);
			switch ($firstChar) {
				case ';':
				case '#':
					# is comment
					break;
				case '[':
					$currentSection = substr($line, 1, -1);
					$data[$currentSection] = array();
					break;
				default:
					//skip line feeds in INI file  
					if (empty($line)) {
						continue;
					}
					//if $currentsection is still null,
					//there was missing a "[<sectionName>]"
					//before the first key/value pair
					if (null === $currentSection) {
						return FALSE;
					}
					//get key and value
					list($key, $val) = explode('=', $line, 2);
					$data[$currentSection][$key] = $val;
			} # end switch
		} # end foreach
		return $data;
	} # end function
?>