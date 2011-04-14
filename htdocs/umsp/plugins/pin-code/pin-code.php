<?php
# PIN-code - UMSP-plugin for PIN-access to another UMSP-plugins
# (c) avkiev 08.04.2011
# English - http://forum.wdlxtv.com/viewtopic.php?f=53&t=3892
# Russian - http://wdhd.ru/board/index.php?a=vtopic&t=3045

function _pluginMain($arg)
{ parse_str($arg);
  if (! isset($pin)) $pin="";
  $n = intval(strPar("PLUGINS"));
  for ($i=1; $i<=$n; $i++)
  { $s = strPar("PLUGIN$i");
    if (! $s) continue;
    $a = explode(" ", $s);
    $pl[$a[0]] = $a[1];
  }
  $plugin = isset($pl[$pin]) ? $pl[$pin] : null;

  $digits = array
  ( "http://lh5.googleusercontent.com/_CsOEwmjx9p8/TZ66z1g_faI/AAAAAAAAO8Q/I-trh0Nw5hU/0.png",
    "http://lh6.googleusercontent.com/_CsOEwmjx9p8/TZ66zpfRu7I/AAAAAAAAO8M/et_eE-qeO4Q/1.png",
    "http://lh5.googleusercontent.com/_CsOEwmjx9p8/TZ66zmdknoI/AAAAAAAAO8I/0CRFOCWqUi8/2.png",
    "http://lh6.googleusercontent.com/_CsOEwmjx9p8/TZ66zqqRoQI/AAAAAAAAO8E/tlgc-cPFVQQ/3.png",
    "http://lh5.googleusercontent.com/_CsOEwmjx9p8/TZ66zTFV35I/AAAAAAAAO8A/WoOIVYO5o8c/4.png",
    "http://lh5.googleusercontent.com/_CsOEwmjx9p8/TZ66zUjVijI/AAAAAAAAO78/uuWm4mchU8c/5.png",
    "http://lh4.googleusercontent.com/_CsOEwmjx9p8/TZ66zbec24I/AAAAAAAAO74/s5lj5rjigIw/6.png",
    "http://lh3.googleusercontent.com/_CsOEwmjx9p8/TZ66zGH8wfI/AAAAAAAAO70/AMJL6AajYMY/7.png",
    "http://lh6.googleusercontent.com/_CsOEwmjx9p8/TZ66zESn4nI/AAAAAAAAO7s/0KzdnFMn810/8.png",
    "http://lh4.googleusercontent.com/_CsOEwmjx9p8/TZ66zOmxHYI/AAAAAAAAO7w/EFvZHjLvT90/9.png"
  );

  if ($plugin)
    $ret[] = array
    ( "id"         => "umsp://plugins/$plugin?pin=$pin",
      "dc:title"   => $plugin,
      "upnp:class" => "object.container"
    );
  else
   for ($sep=strPar("SEPARATOR"), $i=0; $i<=9; $i++)
    $ret[] = array
    ( "id"             => "umsp://plugins/pin-code?pin=" . ($q = $pin.$i),
      "dc:title"       => substr(chunk_split($q,1,$sep)." ", 0, -strlen($sep)-1),
      "upnp:class"     => "object.container",
      "upnp:album_art" => $digits[$i]
    );
  return $ret;
}


function strPar($par)
{ static $config;
  if (! $config) $config = file_get_contents("/conf/config");
  if ( preg_match("/PIN_$par='(.+)'/", $config, $matches) ) return $matches[1];
  return "";
}
