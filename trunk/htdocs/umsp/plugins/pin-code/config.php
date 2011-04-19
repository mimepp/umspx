<?php
	// info contains meta data and plug-in configuration attributes
	include('info.php');

    // Disable all pin-coded plugins
    $config = file_get_contents("/conf/config");
    preg_match("/PIN_PLUGINS='(.+)'/", $config, $matches);
    $n = intval($matches[1]);
    for ($i=1; $i<=$n; $i++)
    { preg_match("/PIN_PLUGIN$i='(.+)'/", $config, $matches);
      $s = trim($matches[1]);
      if (! $s) continue;
      $a = explode(" ", $s);
      $s = trim($a[1]);
      if (_readPluginStatus($s) != "off") _writePluginStatus($s, "off");
    }

	# _DONT_RUN_CONFIG_ gets set by external scripts that just want to get the pluginInfo array via include() without running any code. Better solution?
	if ( !defined('_DONT_RUN_CONFIG_') )
	{
		include_once(_getUMSPWorkPath() . '/funcs-config.php');
		# Check for a form submit that changes the plugin status:
		if ( isset($_GET['pluginStatus']) )
			$writeResult = _writePluginStatus($pluginInfo['id'], $_GET['pluginStatus']);

		# Read the current status of the plugin ('on'/'off') from conf
		$pluginStatus = _readPluginStatus($pluginInfo['id']);

		# New or unknown plugins return null. Add special handling here:
		if ( $pluginStatus === null )
			$pluginStatus = 'off';

		# _configMainHTML generates a standard plugin dialog based on the pluginInfo array:
		$retHTML = _configMainHTML($pluginInfo, $pluginStatus);
		echo $retHTML;

		# Add additonal HTML or code here

		# _configMainHTML doesn't return end tags so add them here:
		echo '</body>';
		echo '</html>';
	}
?>
