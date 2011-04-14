<?php
// Freeview UMSP plugin by Parnas
// http://forum.wdlxtv.com/viewtopic.php?f=49&t=431#p2979 


$rawURL = $_GET['itemurl'];
$parsedURL = parse_url($rawURL);
$itemHost = $parsedURL['host'];

if(isset($parsedURL['query'])) {
    $itemPath = $parsedURL['path'].'?'.$parsedURL['query'];
} else {
    $itemPath = $parsedURL['path'];
}

$itemPort = $parsedURL['port'];
_freeviewGet($itemHost, ($itemPath ? $itemPath : "/"), ($itemPort ? $itemPort : 80));


function _freeviewGet($prmHost, $prmPath, $prmPort) {
   # print "host: ".$prmHost."\n";
   # print "port: ".$prmPort."\n";
   # print "path: ".$prmPath."\n";
   
   $fp = fsockopen($prmHost, $prmPort, $errno, $errstr, 30);
   if (!$fp) {
      echo "$errstr ($errno)<br />\n";
   } else {
      $out  = "GET ". $prmPath ." HTTP/1.1" ."\r\n";
      $out .= "User-Agent: Wget/1.12 (elf)" ."\r\n";
      $out .= "Host: " . $prmHost . "\r\n";
      $out .= "Cache-Control: no-cache" ."\r\n";
      $out .= "Connection: Close"."\r\n"."\r\n";
      fwrite($fp, $out);     
      $headerpassed = false;
      while ($headerpassed == false) {
         $line = fgets( $fp);
         list($tag, $value) = explode(": ", $line, 2);
         
         if (stristr($tag, 'Location')) {
            $target_url = trim($value);
            $url_data_string = http_build_query(array('itemurl' => $target_url));
            header("Location: http://127.0.0.1/umsp/plugins/freeview/freeview-proxy.php?".$url_data_string."\r\n");
            continue;
         }
         if (stristr($tag, 'Content-Type'))
         {
            if (strstr($value, '/octet-stream'))
                 header("Content-Type: video/h264"."\r\n");
            else header($line);
            continue;
         }
         
         header($line);
         if ($line == "\r\n") {
            $headerpassed = true;
         }
      }

      fpassthru($fp);
      fclose($fp);
   }
}

?>

