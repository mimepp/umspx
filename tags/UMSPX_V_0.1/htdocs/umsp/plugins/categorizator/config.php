<?php
if (! function_exists("CategorizatorStrPar"))
{
function CategorizatorStrPar($par)
{ static $config;
  if (! $config) $config = file_get_contents("/conf/config");
  preg_match("/CATEGORIZATOR_$par='(.+)'/", $config, $matches);
  return trim($matches[1]);
}

function cmpS($a,$b) { return strcasecmp($a["dc:title"], $b["dc:title"]); }

function MoveToEnd()
{ $dat = _readPluginStatusAll();
  unset($dat["categorizator"]);
  $dat["categorizator"] = "on";
  $fileConf = "/conf/umsp-plugins-status";
  $fileTmp =   "/tmp/umsp-plugins-status";
  $h = fopen($fileTmp, "w");
  fwrite($h, serialize($dat));
  fclose($h);
  exec("sudo cp $fileTmp $fileConf");
}
}

  if (! defined("_DONT_DO_CATEGORIZE_"))
  { if ( array_pop(array_keys($pluginStatusAll)) != $pluginId ) MoveToEnd();
    global $myMediaItems;
    if ( is_array($pluginRootItems) ) $myMediaItems = array_merge((array) $myMediaItems, $pluginRootItems);
    $pluginRootItems = NULL;
    $u = array();
    $n = CategorizatorStrPar("PLUGINS");
    for ($i=1; $i<=$n; $i++)
    { $v = explode(" ", CategorizatorStrPar("PLUGIN$i"), 2);
      $u[$v[0]] = $v[1];
    }

    $use_uncat = (strtolower(CategorizatorStrPar("USE_UNCAT")) == "on");
    define("_DONT_RUN_CONFIG_", 1);
    foreach($myMediaItems as $kp => $p)
    { $id = substr($p["id"], 15);
      $plcat = $u[$id];
      while (! $plcat)
      { $config = "/tmp/umsp-plugins/$id/config.php";
        if (! is_file($config) ) break;
        unset($pluginInfo);
        include($config);
        if ( is_array($pluginInfo) ) $plcat = $pluginInfo["category"];
        break;
      }
      $v = explode("/", $plcat, 2);
      $plcat = trim($v[0]);
      if ($use_uncat && ! $plcat) $plcat = "Uncategorized";
      if ($plcat) { $cat[]=$plcat; unset($myMediaItems[$kp]); }
    }
    unset($pluginInfo);

    usort($myMediaItems, "cmpS");
    $cat = array_unique($cat); natcasesort($cat); $cat = array_reverse($cat);
    foreach ($cat as $v)
      array_unshift($myMediaItems, array
      ( "id"             => "umsp://plugins/categorizator?category=$v",
        "parentID"       => "0",
        "dc:title"       => "[ $v ]",
        "desc"           => $v,
        "upnp:class"     => "object.container",
        "upnp:album_art" => ""
      ) );
  }

	// info contains meta data and plug-in configuration attributes
	//include('info.php');
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
