<?php

/*************************************************************
    Proxy script for VDR live TV streaming to UMSP
    Version 0.12
    History
    When        Who What
    16.05.10    cd  initial released version 0.1
    16.05.10    cd  do set_time_limit() once per $logInterval
                    not once per fread(), remove header "200 OK"
    16.05.10    cd  removed unfunctional DLNA request handling,
                    implemented header proxy like mmikel
   URL
   http://forum.wdlxtv.com/viewtopic.php?f=49&t=517 
*************************************************************/

set_include_path (get_include_path() . PATH_SEPARATOR . _getUMSPWorkPath() . PATH_SEPARATOR .'/var/www/umsp');
include_once('funcs_log.php');

// ********************************************************
// Settings, please adjust to your needs

// Logging see log_func.php
$logLevel = L_INFO;

// chunksize and log interval
$chunkSize = 8192*8; // 64K
$logInterval = 10; // 10 secs
$timeLimit = 30; // 30 secs, must be greater than $logInterval

// ********************************************************

_logInfo ('================= start ===================');

_logInfo ('$_SERVER:', $_SERVER);

_logDebug ('$_GET: ', $_GET);

$rawURL = $_GET['itemUrl'];
if (!$rawURL) {
    header("HTTP/1.1 404 Not Found");
    echo "<br>please give itemUrl</br>\n";
    _logError ('ERROR: no itemUrl given, exiting');
    exit;
}
_logDebug ('called with itemUrl: ', $rawURL);

$parsedURL = parse_url($rawURL);
if (!$parsedURL) {
    header("HTTP/1.1 404 Not Found");
    echo "<br>can't parse itemUrl</br>\n";
    _logError ('ERROR: itemUrl parse problem, exiting');
    exit;
}
_logDebug ('parsed URL: ', $parsedURL);

_vdrGet($parsedURL);

function _vdrGet($URL) {
    $fp = fsockopen($URL['host'], $URL['port'], $errno, $errstr, 30);
    if (!$fp) {
        header("HTTP/1.1 404 Not Found");
        echo "$errstr ($errno)<br />\n";
        _logError ('ERROR: problem opening socket, exiting', "$errstr ($errno)");
        exit;
    }

    $out  = 'GET '. $URL['path'] .' HTTP/1.1' ."\r\n";
    $out .= "User-Agent: Wget/1.12\r\n";
    $out .= 'Host: ' . $URL['host'] . "\r\n";
    $out .= 'Cache-Control: no-cache' ."\r\n";
    $out .= "Connection: Close"."\r\n"."\r\n";
    fwrite($fp, $out);

    # Create HTTP headers for WDTV from original headers
    $headerpassed = false;
    while ($headerpassed == false) {
        $line = fgets( $fp);
        list($tag, $value) = explode(": ", $line, 2);

        if (stristr($tag, 'Location')) {
            header('Location: http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
            continue;
        }
      if (trim($line) == "") {
            $headerpassed = true;
         header('Content-Type: video/mpeg2ts');
            header("Content-Size: 65535");
            header("Content-Length: 65535");
        }
      header($line);
    }
   
    _logInfo ('start actual streaming...');
    _logDebug ('meta data: ', stream_get_meta_data($fp));

    global $chunkSize, $logInterval, $timeLimit;
    $fSize = 0;
    $startTime = time();
    $timer = time() + $logInterval;

    while(!feof($fp)) {
        $buf = fread($fp, $chunkSize);
        echo $buf;
        $fSize += strlen($buf);
        if (time() >= $timer) {
            // each call to set_time_limit() will reset the timer, so run indefinitely,
            // but in case of hang, stop script after 30 secs
            set_time_limit($timeLimit);
            $timer = time() + $logInterval;
            _logInfo (sprintf ('%0.2f MB streamed in %d:%02d minutes',$fSize / (1024*1024), (time() - $startTime) / 60, (time() - $startTime) % 60));
        }
    }

    /* alternative code, maybe less cpu consuming, but also less save and less informative
    // completely switch off timelimit, and hope nothing will go wrong...
    set_time_limit(0);
    while(!feof($fp)) {
        $fSize += fpassthru($fp);
        _logInfo ('read junk of size: ' . $fSize);
        sleep(1);
    }
    */

    _logInfo ('encountered eof, meta data: ', stream_get_meta_data($fp));

    fclose($fp);
}
?>

