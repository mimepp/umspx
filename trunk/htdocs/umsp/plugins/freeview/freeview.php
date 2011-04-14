<?php
// Parnas Freeview UMSP plugin
//http://forum.wdlxtv.com/viewtopic.php?f=49&t=431#p2979 

function _pluginMain($prmQuery) {
  $items = _pluginCreateChannelList();
  return $items;
}

function _pluginCreateChannelList() {
  $videoItems = array (
     'BBC1' => 'http://cctv.ws/7/BBC1',
     'BBC2' => 'http://cctv.ws/7/BBC2',
     'ITV1' => 'http://cctv.ws/2/ITV1',
     'Channel Four' => 'http://cctv.ws/5/ChannelFour',
     'Five' => 'http://cctv.ws/0/s3byz/Five',
     'BBC3' => 'http://cctv.ws/9/BBC3',
     'BBC4' => 'http://cctv.ws/7/CBeebies/BBC4',
     'ITV2' => 'http://cctv.ws/0/NhYpi4/ITV2',
     'ITV3' => 'http://cctv.ws/0/ITV3',
     'ITV4' => 'http://cctv.ws/3/rrU5j/ITV4',
     'E4' => 'http://cctv.ws/5/E4Channel',
     'More4' => 'http://cctv.ws/3/More4',
     'CBS Reality' => 'http://cctv.ws/6/CBSReality',
     '4Music' => 'http://cctv.ws/3/4Music',
     'Zone Horror' => 'http://cctv.ws/1/ZoneHorror',
     'Film4' => 'http://cctv.ws/1/3eZ1R1/Film4',
     'Classic Movies2' => 'http://cctv.ws/4/classicmovies2',
     'James Bond TV' => 'http://cctv.ws/2/JamesBondTV',
     'QVC' => 'http://cctv.ws/2/qvcuk',
     'Fashion TV' => 'http://cctv.ws/0/FashionTV',
     'ClassicFM TV' => 'http://cctv.ws/6/cfmtv',
     'History' => 'http://cctv.ws/9/1wbeA4/History',
     'Discovery' => 'http://cctv.ws/9/DiscoveryChannel',
     'Movies 1' => 'http://cctv.ws/7/somethingmovies1',
     'Movies 2' => 'http://cctv.ws/9/somethingmovies2',
     'Movies 3' => 'http://cctv.ws/1/somethingmovies3',
     'Movies 4' => 'http://cctv.ws/2/ovies3',
     'Eurosport' => 'http://cctv.ws/1/BritishEurosport',
     'BBC News24' => 'http://cctv.ws/8/BBCNews',
     'Sky News' => 'http://cctv.ws/2/SkyNews',
     'BBC Parliament' => 'http://cctv.ws/5/bbcpar',
     'Bloomberg TV' => 'http://cctv.ws/6/BloombergUK',
     'Russia Today' => 'http://cctv.ws/9/RussiaToday',
     'Scuzz' => 'http://cctv.ws/1/Scuzz',
     'Flaunt' => 'http://cctv.ws/3/Flaunt',
     'France 24' => 'http://cctv.ws/6/France24En',
     'BBC1 (LQ)' => 'http://cctv.ws/6/BBC1LQ',
     'BBC2 (LQ)' => 'http://cctv.ws/1/BBC2LQ',
     'BBC3 (LQ)' => 'http://cctv.ws/5/BBC3lq',
     'Channel Four (LQ)' => 'http://cctv.ws/3/C4LQ',
     'E4 (LQ)' => 'http://cctv.ws/1/e4lQ',
     'Five (LQ)' => 'http://cctv.ws/1/FivelQ',
     'ITV1 (LQ)' => 'http://cctv.ws/3/ITV1LQ',
     'ITV2 (LQ)' => 'http://cctv.ws/8/TV2lq/ITV2LQ',
     'ITV3 (LQ)' => 'http://cctv.ws/4/ITV3LQ',
     'CBS Reality (LQ)' => 'http://cctv.ws/4/CBSRealityLQ',
     'BBC News24 (LQ)' => 'http://cctv.ws/3/BBCnewsLQ',
     'Bloomberg (LQ)' => 'http://cctv.ws/8/BloombergLQ',
     'CNN International' => 'http://cctv.ws/7/CNNint',
     'CNN International (LQ)' => 'http://cctv.ws/5/xvtmX3/CNNlq',
  );

  foreach ($videoItems as $name => $url) {
    $url_data = array('itemurl' => $url);
    $url_data_string = http_build_query($url_data);

    $retMediaItems[] = array (
      'id' => 'umsp://plugins/freeview/freeview?' . $url,
      'dc:title' => $name,
      'upnp:class' => 'object.item.videoitem',
      'res' => 'http://127.0.0.1/umsp/plugins/freeview/freeview-proxy.php?'.$url_data_string,
      'protocolInfo' => 'http-get:*:*:*',
    );
  }
  return $retMediaItems;
}

?>

