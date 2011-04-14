<?php
function _var_dump_pre($mixed = null) {
  echo '<pre>';
  var_dump($mixed);
  echo '</pre>';
  return null;
}

function _var_dump_ret($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

function _var_dump_to_file($mixed = null, $prmFile = '/tmp/umsp-log.txt') {
	ob_start();
	var_dump($mixed);
	$content = ob_get_contents();
	ob_end_clean();

	$myFile = $prmFile;
	$fh = fopen($myFile, 'a') or die();
	fwrite($fh, $content . "\r\n");
	fclose($fh);
}

function _my_parse_ini_string($prmIniString) {
	$lines = explode("\n", $prmIniString);
	$currentSection = null;  
	foreach ($lines as $line) {
		$line = trim($line);
		$firstChar = substr($line, 0, 1);
		switch ($firstChar) {
			case ';':
				# is comment
				break;
			case '#':
				# is comment
				break;
			case '[':
				$currentSection = substr($line, 1, -1);  
				$data[$currentSection] = array();
				break;
			default:
				//skip line feeds in INI file  
				if (empty($line)) { continue;
				}  
				//if $currentsection is still null,  
				//there was missing a "[<sectionName>]"  
				//before the first key/value pair  
				if (null === $currentSection) { return false;
				}  
				//get key and value  
				list($key, $val) = explode('=', $line, 2);  
				$data[$currentSection][$key] = $val;
		} # end switch
	} # end foreach
	return $data;  
} # end function	

?>