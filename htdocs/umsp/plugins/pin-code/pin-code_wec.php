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
    pincode($key, $descr, NULL, WECT_DESC);
    pincode($id, "Enable $name UMSP plugin", NULL, WECT_BOOL);
    $wec_options[$id]["availval"]  = array("off", "on");
    $wec_options[$id]["readhook"]  = wec_umspwrap_read;
    $wec_options[$id]["writehook"] = wec_umspwrap_write;

    pincode("SEPARATOR", "Separator between digits", " - ");
    pincode("PLUGINS", "Count of pin-coded plugins", "1", WECT_INT);
    $wec_options["PIN_PLUGINS"]["longdesc"] = "Format: PIN PLUGIN<br>F.e.: 1234 tube8";
    $n = pinStrPar("PLUGINS");
    for ($i=1; $i<=$n; $i++) pincode("PLUGIN$i", "Plugin $i", "");
  }

function pincode($key, $desc, $def, $typ=WECT_TEXT)
{ global $wec_options, $name, $pri;
  if (! is_null($def)) $key = "PIN_$key";
  $wec_options["$key"] = array
  ( "configname" => "$key",
    "configdesc" => $desc,
    "group"	     => $name,
    "type"	     => $typ,
    "page"	     => WECP_UMSP,
    "displaypri" => $pri++,
    "defaultval" => $def
  );
}

function pinStrPar($par)
{ static $config;
  if (! $config) $config = file_get_contents("/conf/config");
  preg_match("/PIN_$par='(.+)'/", $config, $matches);
  return trim($matches[1]);
}
?>
