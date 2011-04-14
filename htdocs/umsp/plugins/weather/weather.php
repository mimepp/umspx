<?php
# Weather - UMSP-plugin for weather forecast
# (c) avkiev 20.03.2011
# English - http://forum.wdlxtv.com/viewtopic.php?f=53&t=3798
# Russian - http://wdhd.ru/board/index.php?a=vtopic&t=2993

error_reporting(E_ALL ^ E_NOTICE);	// avoid the notice message.
include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
function _pluginMain($arg)
{ $loc = strtolower(strPar("LOCATION"));
  $ret = ForecastWeek($loc, "");  if (boolPar("2_WEEK")) $ret = array_merge($ret, ForecastWeek($loc, "2"));
  return $ret;
}


function ForecastWeek($location, $w)
{ $url = "http://www.accuweather.com/$location/forecast$w.aspx";
  $b = html2array($url, "ForecastDescription");
  $n = count($b)/2;
  for ($i=0; $i<$n; $i++)
  { $j=$i+1; $k=$i+$n;
    $url = $b[$i]["Details_href"];
    if ($w) $url = str_replace($j, $j+$n, $url);
    $date = $b[$i]["Date"]; $dt = $b[$i]["High"]; $dr = $b[$i]["RealFeelValue"];
    $desc = $b[$i]["Desc"]; $nt = $b[$k]["High"]; $nr = $b[$k]["RealFeelValue"];
  	$ret[] = array
    ( "id"           => "umsp://plugins/weather?$i",
      "dc:title"     => "$date. $desc. D: $dt ($dr), N: $nt ($nr)",
      "upnp:class"   => "object.item.imageitem",
      "protocolInfo" => "http-get:*:image/jpeg:DLNA.ORG_PN=JPEG_SM",
      "res"          => "http://127.0.0.1/umsp/proxy.php?plugin=weather&url=$url&date=" . urlencode($date)
    );
  }
  return $ret;
}


function _pluginProxy($arg)
{ # Destination image
  list($WIDTH, $HEIGHT) = explode("*", strPar("SCREEN"));
  list($BL, $BT, $BR, $BB) = explode(" ", strPar("SCREEN_BORDER"));
  $TRUECOLOR = boolPar("SCREEN_TRUECOLOR");
  # Source image
  list($WIDTH1, $HEIGHT1) = explode("*", strPar("SCREEN_BASE"));
  $fb = fontPar("SCREEN_FONT_BACKGROUND");
  $ft = fontPar("SCREEN_FONT_TITLE");
  $fh = fontPar("SCREEN_FONT_HEADER");
  $fk = fontPar("SCREEN_FONT_KEY");
  $fv = fontPar("SCREEN_FONT_VALUE");
  $fs = fontPar("SCREEN_FONT_SUNMOON");
  $fr = fontPar("SCREEN_FONT_BORDER");

  $regu = _getUMSPFont();	//"/tmp/umsp-plugins/reader/fonts/arial.ttf";
  $bold = _getUMSPFontBD();	//"/tmp/umsp-plugins/reader/fonts/arialbd.ttf";

  $img = imagecreate($w1=$WIDTH1, $h1=$HEIGHT1);
  foreach (array($fb,$ft,$fh,$fk,$fv,$fs) as $v) $cl[] = imagecolorallocate($img, $v[1], $v[2], $v[3]);
  $w2 = $w1/2; $w245 = $w2*4/5; $dx = -$w2; $dy = $fk[0] * 2;
  extract($arg);
  $d = html2array($url, "detailsDayBox_300");
  $n = html2array($url, "detailsNightBox_300");
  $b = imagettfbbox($ft[0], 0, $bold, $date);
  imagettftext($img, $ft[0], 0, ($w1-$b[2]+$b[0])/2, 50 + $ft[0]/2, $cl[1], $bold, $date);

  foreach(array($d[0],$n[0]) as $dn)
  { $dx += $w2; $y = $fh[0] + 110;
    foreach($dn as $k => $v)
      if (substr($k,-4) == "_img")
      {	$src = imagecreatefromjpeg($v);
        $i = imagesx($src);
      	imagecopy($img, $src, $dx+($w2-$i)/2, 10, 0, 0, $i, imagesy($src));
      	imagedestroy($src);      }
      elseif (isset($dn[$k."Value"]))
        imagettftext($img, $fk[0], 0, $dx+10,    $y, $cl[3], $regu, $v);
      elseif (substr($k,-5) == "Value")
      { imagettftext($img, $fv[0], 0, $dx+$w245, $y, $cl[4], $regu, $v); $y += $dy; }
      else
      { while (true)
        { $b = imagettfbbox($fh[0], 0, $bold, $v); $i = $w2-$b[2]+$b[0];
          if ($i>0) break;
          $v = mb_substr($v, 0, -1, "UTF-8");
        }
        imagettftext($img, $fh[0], 0, $dx+$i/2,  $y, $cl[2], $bold, $v); $y += $dy;
      }
  }

  $m = html2array($url, "", "sunMoon"); $m = $m[0]; array_shift($m);
  $w3 = $w1/3; $dx=-$w3; $dy=$fs[0]*3/2; $y0 = $y + $dy*3/2;
  foreach($m as $k => $v)
  	if (substr($k,-4) == "_img")
  	{ $src = imagecreatefromgif($v);
      $i = imagesx($src); $j = imagesy($src);
      $dx+=$w3; $y=$y0+$j;
      imagecopy($img, $src, $dx+($w3-$i)/2, $y0, 0, 0, $i, $j);
      imagedestroy($src);    }
    else
    { $b = imagettfbbox($fs[0], 0, $regu, $v);
      imagettftext($img, $fs[0], 0, $dx+($w3-$b[2]+$b[0])/2, $y+=$dy, $cl[5], $regu, $v);
    }
  header('content-type: image/png');
  if ($w1==$WIDTH && $h1==$HEIGHT && !$BL && !$BT && !$BR && !$BB) imagepng($img);
  else
  { if ($TRUECOLOR) $img2 = imagecreatetruecolor($WIDTH, $HEIGHT);
               else $img2 = imagecreate         ($WIDTH, $HEIGHT);
    if ($BL || $BT || $BR || $BB) imagefilledrectangle($img2, 0, 0, $WIDTH, $HEIGHT,
                                  imagecolorallocate($img2, $fr[1], $fr[2], $fr[3]));
    if ($w1 == $WIDTH-$BL-$BR && $h1 == $HEIGHT-$BT-$BB)
      imagecopy       ($img2, $img, $BL, $BT, 0, 0, $w1, $h1);
    else
      imagecopyresized($img2, $img, $BL, $BT, 0, 0, $WIDTH-$BL-$BR, $HEIGHT-$BT-$BB, $w1, $h1);
    //imagepng($img2);
    imagejpeg($img2);
    imagedestroy($img2);
  }
  imagedestroy($img);
}


function html2array($url, $class, $idd="")
{ if ($class) { $atr = "class"; $vatr = $class; }  else        { $atr = "id";    $vatr = $idd;   }
  $query = "select * from html where url='$url' and xpath='//div[@$atr=\"$vatr\"]'";
  $url = "http://query.yahooapis.com/v1/public/yql?q=" . urlencode($query);
  $reader = new XMLReader();
  $reader->XML(file_get_contents($url));
  while ($reader->read())
  { if ($reader->getAttribute($atr) == $vatr && $a) { $ret[]=$a; $a=""; continue; }
    $id = $reader->getAttribute("id"); if (! $id) continue;
    $id = strrchr($id, "_"); $s = substr($id,0,4); $id = substr($id,4);
    if ($s == "_img") { $a[$id."_img"] = $reader->getAttribute("src"); continue; }
    if ($s != "_lbl" && $s != "_lnk") continue;
    $href = $reader->getAttribute("href");
    $reader->read();
    $s = str_replace("\n", " ", $reader->value);
    $a[$id] = mb_strtoupper(mb_substr($s,0,1,"UTF-8"), "UTF-8") . mb_substr($s,1,99,"UTF-8");
    if ($href) $a[$id."_href"] = $href;
    $reader->read();
  }
  return $ret;
}


function strPar($par)
{ static $config;
  if (! $config) $config = file_get_contents(_getUMSPConfPath() . "/config");
  preg_match("/WEATHER_$par='(.+)'/", $config, $matches);
  return trim($matches[1]);
}
function boolPar($par)
{ return strtolower(strPar($par))=="on";
}
function fontPar($par)
{ return explode(" ", str_replace(" ", " 0x", strPar($par)));
}
?>