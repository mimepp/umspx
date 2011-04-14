<?php

# itemUrl is like
# itemUrl=http://dm7025:8001/1:0:1:11:1001:20F6:EEEE0000:0:0:0:

$rawURL = $_GET['itemUrl'];
$parsedURL = parse_url($rawURL);

# Remove the resume point from the /tmp/video_resume_point_table file
# It's of no use to try to resume a live stream...

#exec('perl -0135 -i.bak -ne "print unless /$rawURL/;" /tmp/video_resume_point_table');

error_log('URL: ' . $rawURL);

# Immediately respond to 'HEAD' requests
if ( $_SERVER['REQUEST_METHOD'] == 'HEAD' ) {
  header('HTTP/1.0 200 OK');
  header('Connection: Close');
  header('ContentFeatures.DLNA.ORG: DLNA.ORG_OP=00;DLNA.ORG_CI=1');
  header('Content-Type: video/mpeg');
  header('Server: stream_enigma2');

  return;
}

$itemHost = $parsedURL['host'];
$itemPort = $parsedURL['port'];
$itemPath = $parsedURL['path'];

_dreamboxGet($itemHost, $itemPort, $itemPath);

    
function _dreamboxGet($prmHost, $prmPort, $prmPath) {
  $fp = fsockopen($prmHost, $prmPort, $errno, $errstr);
  if (!$fp) {
    echo "$errstr ($errno)<br />\n";
  } else {
    # Create the HTTP GET request for Dreambox
    
    $out  = "GET $prmPath HTTP/1.0\r\n";
    $out .= "User-Agent: Wget/1.12\r\n";
    $out .= "Accept: */*\r\n";
    $out .= "Host: $prmHost:$prmPort\r\n";
    $out .= "Connection: Keep-Alive\r\n";
    $out .= "\r\n";
    
    fwrite($fp, $out);
    
    # Add the ContentFeatures.DLNA.ORG to the original headers
    
    $headerpassed = false;
    while ($headerpassed == false) {
      $line = fgets($fp);
                   
      if ( $line == "\r\n" ) {
        header('ContentFeatures.DLNA.ORG: DLNA.ORG_OP=00;DLNA.ORG_CI=1'); 
        $headerpassed = true;
      } else {
        header($line);
      }
    }
    
    # Pass thru the DVB transport stream
    # It's important to disable the time limit
    
    set_time_limit(0);
    fpassthru($fp);
    set_time_limit(30);
    
    fclose($fp);
  }
}
?>