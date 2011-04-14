<?php
  include ("info.php");
  if (defined("WECVERSION") && WECVERSION >= 3)
  { include_once("/usr/share/umsp/funcs-config.php");
    $descr = $thumb = $art = $name = $version = $date = $author = $desc = $url = $id = "";
    extract($pluginInfo);
    if ($thumb || $art)
      $descr = "<div style='float: left; padding: 4px 10px 4px 4px;'><img src='".($thumb ? $thumb : $art)."' width='60' height='60' alt='logo'></div>";
    $descr  .= "<div>$name v$version ($date) by $author.<br>$desc<br>Information: <a href='$url'>$url</a></div>";

    $key = strtoupper("{$id}_DESC");
    categorizator($key, $descr, NULL, WECT_DESC);
    categorizator($id, "Enable $name UMSP plugin", NULL, WECT_BOOL, array("off", "on"));
    $wec_options[$id]["readhook"]  = wec_umspwrap_read;
    $wec_options[$id]["writehook"] = wec_umspwrap_write;

    categorizator("USE_UNCAT", "Use UNCATEGORIZED folder", "off", WECT_BOOL);
    categorizator("PLUGINS", "Count of categorized plugins", "1", WECT_INT);
    $wec_options["CATEGORIZATOR_PLUGINS"]["longdesc"] = "You can set your own categories for plugins.<br>"
                                                       ."Format: PLUGIN CATEGORY/SUBCATEGORY1/SUBCATEGORY2...<br>"
                                                       ."F.e.: weather System/Tools";
    $n = CategorizatorStrPar("PLUGINS");
    for ($i=1; $i<=$n; $i++) categorizator("PLUGIN$i", "Plugin $i", "", WECT_TEXT);
  }

function categorizator($key, $desc, $def, $typ, $avv=NULL, $avn=NULL)
{ global $wec_options, $name, $pri;
  if (! is_null($def)) $key = "CATEGORIZATOR_$key";
  $wec_options["$key"] = array
  ( "configname"   => "$key",
    "configdesc"   => $desc,
    "group"	       => $name,
    "type"	       => $typ,
    "page"	       => WECP_UMSP,
    "displaypri"   => $pri++,
    "defaultval"   => $def,
    "availval"     => $avv,
    "availvalname" => $avn
  );
}

function CategorizatorStrPar($par)
{ static $config;
  if (! $config) $config = file_get_contents("/conf/config");
  preg_match("/CATEGORIZATOR_$par='(.+)'/", $config, $matches);
  return trim($matches[1]);
}
?>
