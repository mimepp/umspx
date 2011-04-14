<?php

include('info.php');
# _DONT_RUN_CONFIG_ gets set by external scripts that just want to get the pluginInfo array via include() without running any code. Better solution?

if ( !defined('_DONT_RUN_CONFIG_') )
{

	include_once('/usr/share/umsp/funcs-config.php');

	# Check for a form submit that changes the plugin status:
	if ( isset($_GET['pluginStatus']) ) {
		$writeResult = _writePluginStatus($pluginInfo['id'], $_GET['pluginStatus']);
	}

	# Read the current status of the plugin ('on'/'off') from conf
	$pluginStatus = _readPluginStatus($pluginInfo['id']);

	# New or unknown plugins return null. Add special handling here:
	if ( $pluginStatus === null ) {
		$pluginStatus = 'off';
	}

	# _configMainHTML generates a standard plugin dialog based on the pluginInfo array:
	//$retHTML = _configMainHTML($pluginInfo, $pluginStatus);
	//echo $retHTML;

	# Add additonal HTML or code here
	$config_path = '/tmp/conf/grooveshark.conf';
	if ( (array_key_exists('groovesharkUsername', $_GET))and(array_key_exists('groovesharkPassword', $_GET)) )  {
		$username = trim($_GET['groovesharkUsername']);
		if( file_exists($config_path) ) {
			$config = new SimpleXMLElement(file_get_contents($config_path));
		} else {
			$config = new SimpleXMLElement('<config></config>');
		}
		$config->username = $username;
		if( strlen(trim($_GET['groovesharkPassword'])) > 0 ) {
			$config->password = trim($_GET['groovesharkPassword']);
		}
		$config->asXML('/tmp/grooveshark.conf');
		# TODO: Better solution?
		exec('sudo cp /tmp/grooveshark.conf ' . $config_path);
		exec('sudo rm /tmp/grooveshark.conf');
	} else {
		$username = '';
		if( file_exists($config_path) ) {
			$config = new SimpleXMLElement(file_get_contents($config_path));
			if( !is_null($config) ) {
				$username = $config->username[0];
			}
		}
	}

	# _configMainHTML replacement for extended configuration
	$html  = '<html xmlns="http://www.w3.org/1999/xhtml">';
	$html .= '<head>';
	$html .= '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />';
	$html .= '<title>' . $pluginInfo['name'] . '</title>';
	$html .= '</head>';
	$html .= '<body>';
	$html .= '<p align="center"><strong>' . $pluginInfo['name'] . '</strong><br>';
	$html .= $luginInfo['desc'] . '</p>';
	$html .= '<table border="0" align="center" cellspacing="10">';
	$html .= '  <tr>';
	$html .= '    <td>Version: ' . $pluginInfo['version'] . '</td>';
	$html .= '    <td>Date: ' . $pluginInfo['date'] . '</td>';
	$html .= '  </tr>';
	$html .= '  <tr>';
	$html .= '    <td>Author: ' . $pluginInfo['author'] . '</td>';
	$html .= '    <td>ID: ' . $pluginInfo['id'] . '</td>';
	$html .= '  </tr>';
	$html .= '  <tr>';
	$html .= '          <td colspan="2" align="center"><a href="' . $pluginInfo['url'] . '">Web Link</a></td>';
	$html .= '</table>';
	$html .= '<form method="get">';
	$html .= '<p align="center">';
	$html .= '<input type="radio" name="pluginStatus" value="on"' . ( ($pluginStatus == 'on') ? ' checked ' : '' ) . '> Enabled';
	$html .= '<input type="radio" name="pluginStatus" value="off"' . ( ($pluginStatus == 'off') ? ' checked ' : '' ) . '> Disabled<br>';
	// Start: Additional configuration
	$html .= '<br /><b>Grooveshark Credentials</b><br />';
	$html .= '<i>Grooveshark credentials allow you to have a better experience of this plugin but are not mandatory.</i><br /><br />';
	$html .= '<table border="0" align="center" cellspacing="10">';
	$html .= '  <tr>';
	$html .= '    <td>Username</td>';
	$html .= '    <td><input type="text" name="groovesharkUsername" value="'.$username.'" /></td>';
	$html .= '  </tr>';
	$html .= '  <tr>';
	$html .= '    <td>Password:</td>';
	$html .= '    <td><input type="password" name="groovesharkPassword"></td>';
	$html .= '  </tr>';
	$html .= '</table>';
	$html .= '<br />';
	// End: Additional configuration
	$html .= '<!--<input type="submit" name="buttonSubmit" id="buttonSubmit" value="Submit" />-->';
	$html .= '<input type="submit" value="Submit" />';
	$html .= '</p>';
	$html .= '</form>';
	$html .= '<hr />';
	echo $html;

	# _configMainHTML doesn't return end tags so add them here:
	echo '</body>';
	echo '</html>';
}

?>
