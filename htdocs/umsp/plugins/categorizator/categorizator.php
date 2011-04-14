<?php
# Categorizator
# (c) avkiev 07.04.2011
# English - http://forum.wdlxtv.com/viewtopic.php?f=53&t=4062
# Russian - http://wdhd.ru/board/index.php?a=vtopic&t=3097

function _pluginMain($arg)
{ parse_str($arg);
  $u = $retD = $retF = $cat = array();
  $n = strPar("PLUGINS");
  for ($i=1; $i<=$n; $i++)
  { $v = explode(" ", strPar("PLUGIN$i"), 2);
    $u[$v[0]] = $v[1];
  }
  if ($category=="Uncategorized" && strtolower(strPar("USE_UNCAT"))=="on") $category="";

  define("_DONT_RUN_CONFIG_", 1);
  define("_DONT_DO_CATEGORIZE_", 1);
  include ("/var/www/umsp/media-items.php");
  foreach($myMediaItems as $p)
  { $id = substr($p["id"], 15);
    $plcat = $u[$id];
    while (! $plcat)
    { $config = "/tmp/umsp-plugins/$id/config.php";
      if (! is_file($config) ) break;
      unset($pluginInfo);
      include($config);
      if ( is_array($pluginInfo) ) $plcat = trim($pluginInfo["category"]);
      break;
    }
    if ($plcat == $category) { $retF[] = $p; continue; }
    $l = strlen($category)+1;
    if ( strncmp($plcat, $category."/", $l) ) continue;
    $v = explode("/", substr($plcat, $l), 2);
    $cat[] = trim($v[0]);
  }

  usort($retF, "cmp");
  array_unique($cat); natcasesort($cat);
  foreach ($cat as $v)
    $retD[] = array
    ( "id" => "umsp://plugins/categorizator?category=" . urlencode("$category/$v"),
      "dc:title" => "[ $v ]",
      "upnp:class" => "object.container"
    );
  return array_merge($retD, $retF);
}

function strPar($par)
{ static $config;
  if (! $config) $config = file_get_contents("/conf/config");
  preg_match("/CATEGORIZATOR_$par='(.+)'/", $config, $matches);
  return trim($matches[1]);
}

function cmp($a,$b) { return strcasecmp($a["dc:title"], $b["dc:title"]); }
?>