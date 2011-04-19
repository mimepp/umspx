<?php

include('info.php');
include_once($_SERVER[DOCUMENT_ROOT] . '/umsp/funcs-config.php');

# _DONT_RUN_CONFIG_ gets set by external scripts that just want to get the pluginInfo array via include() without running any code. Better solution?

if ( !defined('_DONT_RUN_CONFIG_') ) 
{

	include_once(_getUMSPWorkPath() . '/funcs-config.php');

	# Check for a form submit that changes the plugin status:
	if ( isset($_GET['pluginStatus']) )
		$writeResult = _writePluginStatus($pluginInfo['id'], $_GET['pluginStatus']);

	# Read the current status of the plugin ('on'/'off') from conf
	$pluginStatus = _readPluginStatus($pluginInfo['id']);

	# Plugin enabled. Check for XML file
	if( $pluginStatus === 'on')
	{
		$config = file_get_contents('/conf/config');
		preg_match_all('/DAILY_PODCASTS_XML=\'(.*?)\'\n/', $config, $matches, PREG_PATTERN_ORDER);
		$xml_file = $matches[1][0];
		if($xml_file == '')
		{
			echo '*** config DAILY_PODCASTS_XML not set.';
			echo '*** Do: config_tool -c DAILY_PODCASTS_XML=<xml file path>';
			$writeResult = _writePluginStatus($pluginInfo['id'], 'off');
			return;
		} 
		else if(!file_exists($xml_file))
		{
			$writeResult = _writePluginStatus($pluginInfo['id'], 'off');
			echo "*** DAILY_PODCASTS_XML file $xml_file not found.";
			return;
		}
	}

	# New or unknown plugins return null. Add special handling here:
	if ( $pluginStatus === null )
		$pluginStatus = 'off';

	# _configMainHTML generates a standard plugin dialog based on the pluginInfo array:
	$retHTML = _configMainHTML($pluginInfo, $pluginStatus);
	echo $retHTML;

	# Add additonal HTML or code here

	# _configMainHTML doesn't return end tags so add them here:
	echo '<p>This plugin needs an xml file with a config item pointing to the file "config_tool -c DAILY_PODCASTS_XML=xml file path"';
	echo '<p>See web link above and the <a href=http://wdtvtools.jakereichert.com>iTunes subscription importer</a> by chameleon_skin';
	echo '</body>';
	echo '</html>';
}

?>
