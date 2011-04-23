<?php
  include ("info.php");
  if (defined("WECVERSION") && WECVERSION >= 3)
  { include_once(_getUMSPWorkPath() . "/funcs-config.php");
    $descr = $thumb = $art = $name = $version = $date = $author = $desc = $url = $id = "";
    extract($pluginInfo);
    if ($thumb || $art)
      $descr = "<div style='float: left; padding: 4px 10px 4px 4px;'><img src='".($thumb ? $thumb : $art)."' width='60' height='60' alt='logo'></div>";
    $descr  .= "<div>$name v$version ($date) by $author.<br>$desc<br>Information: <a href='$url'>$url</a></div>";

    $key = strtoupper("{$id}_DESC");
    weather($key, $descr, "", WECT_DESC);
    weather($id, "Enable $name UMSP plugin", "", WECT_BOOL);
    $wec_options[$id]["availval"]  = array("off", "on");
    $wec_options[$id]["readhook"]  = wec_umspwrap_read;
    $wec_options[$id]["writehook"] = wec_umspwrap_write;

    weather("LOCATION",               "Your language and location", "ru/ua/kyev-city/kyiv");
    weather("2_WEEK",                 "Forecast for 2 week", "ON", WECT_BOOL);
    weather("SCREEN",                 "Resolution of screen with detailed forecast (W*H)", "1300*740");
    weather("SCREEN_BORDER",          "Screen border (Left,Top,Right,Bottom)", "10 10 10 10");
    weather("SCREEN_TRUECOLOR",       "In case of Resizing - use TrueColor image (slow)", "OFF", WECT_BOOL);
    weather("SCREEN_BASE",            "Resolution of base-screen (W*H)", "1280*720");
    weather("SCREEN_FONT_BACKGROUND", "Background font (Size,Red,Green,Blue)", "00 00 80 80");
    weather("SCREEN_FONT_TITLE",      "Title font",    "34 00 00 FF");
    weather("SCREEN_FONT_HEADER",     "Header font",   "26 FF FF 00");
    weather("SCREEN_FONT_KEY",        "Key font",      "22 FF FF FF");
    weather("SCREEN_FONT_VALUE",      "Value font",    "22 FF FF FF");
    weather("SCREEN_FONT_SUNMOON",    "Sun&Moon font", "18 00 00 00");
    weather("SCREEN_FONT_BORDER",     "Border font",   "00 80 00 80");
  }

function weather($key, $desc, $def, $typ=WECT_TEXT)
{ global $wec_options, $name, $pri;
  if ($def) $key = "WEATHER_$key";
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
?>
