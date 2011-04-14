<?php
// ReleaseLog UMSP plugin by Rezmus
// http://forum.wdlxtv.com/viewtopic.php?f=49&t=734 

function _pluginMain($prmQuery) {
  $queryData = array();
  parse_str($prmQuery, $queryData);
  if ($queryData['url'] !='') {
    $items = _pluginCreateMovieList($queryData['url'],14);
    return $items;
  } else if ($queryData['movlink'] !='') {
    $items = _pluginCreateVideoItems($queryData['movlink']);
    return $items;
  }
  else {
  $items = _pluginCreateCategoryList();
  return $items;
  }
}

function _pluginCreateCategoryList() {
  $categories = array(
    'All Movies' => 'movies',
    'TV Shows' => 'tv-shows',
    'TV Packs' => 'tv-shows/tv-packs',
    'BDRip Movies' => 'movies/bdrip',
    'BDSCR Movies' => 'movies/bdscr',
    'Cam Movies' => 'movies/cam',
    'DVDRiP Movies' => 'movies/dvdrip',
    'DVDRiP Old Movies' => 'movies/dvdrip-old',
    'DVDSCR Movies' => 'movies/dvdscr',
    'HDRiP Movies' => 'movies/hdrip',
    'R5 Movies' => 'movies/r5',
    'RC Movies' => 'movies/rc',
    'SCR Movies' => 'movies/scr',
    'Staff Picks' => 'movies/staff-picks',
    'Telecine Movies' => 'movies/telecine',
    'Telesync Movies' => 'movies/telesync',
    'Workprint Movies' => 'movies/workprint'
  );

  foreach ($categories as $name => $id) {
    $url = "http://www.rlslog.net/category/" . $id;
    $data = array(
      'url' => $url
    );
    $dataString = http_build_query($data, 'pluginvar_');
    $retMediaItems[] = array (
      'id' => 'umsp://plugins/rlslog/rlslog?' . $dataString,
      'dc:title' => $name,
      'upnp:class' => 'object.container',
    );
  }
  return $retMediaItems;
}

function _pluginCreateMovieList($url,$pageCount) {
  for ($i = 1; $i <= $pageCount; $i++) {
    if ($i == 1) {
      $html = file_get_contents($url);
    } else {
      $html = file_get_contents($url . "/page/" . $i);
    }
    preg_match_all('/rel="bookmark">\x0d\x0a\x20\x20\x20\x20(.*?)\x20\x20\x20\x20<\/a> <\/h3>/',$html,$title);
    preg_match_all('/<a href="(.*?)" rel="bookmark">/',$html,$link);
    preg_match_all('/Posted on (\d{2}\.\d{2}\.\d{4}?) at/',$html,$date);
    preg_match_all('/<img class="alignleft" src="(.*?)" alt=""/',$html,$poster);
    for ($z = 0; $z < sizeof($title[1]); $z++) {
      $data = array(
        'movlink' => $link[1][$z]
      );
      $dataString = http_build_query($data, 'pluginvar_');
      $retMediaItems[] = array (
        'id' => 'umsp://plugins/rlslog/rlslog?' . $dataString,
        'dc:title' => str_replace(" &#8211; ","-",urldecode($title[1][$z] . "(" . str_replace(".","-",$date[1][$z]) . ")")),
        'upnp:album_art'=> $poster[1][$z],
        'upnp:class' => 'object.container'
      );
    }
  }
  return $retMediaItems;
}

function _pluginCreateVideoItems($url) {
 
  $html = file_get_contents($url);
  preg_match_all('/"(http:\/\/hotfile.com\/dl\/.*(avi|mkv).html?)"/',$html,$links);
  $urls = array_unique($links[1]);
  $opts = array(
     'http' => array(
        'method' => "HEAD",
       'max_redirects' => '0',
       'header' => 'Cookie: auth=' . getCookie() . "\r\n"
     )
  );
  $context = stream_context_create($opts);
 
  foreach ($urls as $link) {
    $html = @file_get_contents($link, false, $context);
    foreach ($http_response_header as $header) {
      if(preg_match('/Location: (.+)/',$header,$res)) {
        $retMediaItems[] = array (
        'id' => 'umsp://plugins/rlslog/rlslog?' . $res[1],
        'dc:title' => urldecode(basename($res[1])),
        'res' => $res[1],
        'upnp:class' => 'object.item.videoitem',
        'protocolInfo' => '*:*:*:*'
        );
      }
    }
  }
  return $retMediaItems;
}

function getCookie() {
    $config = file_get_contents('/conf/config');

    if(preg_match('/HOTFILE_AUTH=\'(.+)\'/', $config, $config_cookie)) {
        $cookieVal = $config_cookie[1];
        return $cookieVal;
    }
    return '';
}

?>

