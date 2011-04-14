<?php
include_once( includeWorkaround() );

// set the logging level, one of L_ALL, L_DEBUG, L_INFO, L_WARNING, L_ERROR, L_OFF
global $logLevel;
$logLevel = L_WARNING;
global $logIdent;
$logIdent = 'YTSubscriptions-proxy';

//Turn the power led on if desired
if(getConfigValue('PROXY_LED') == 'ON'){
	//turn the power led on while the proxy is doing something
	system("sudo su -c 'echo power led on >> /proc/led'");
}

/* Extract the header from $content. Save header elements as key/value pairs */
function parse_header($content)
{
    $newline = "\r\n";
    $parts = preg_split("/$newline . $newline/", $content);

    $header = array_shift($parts);
    $content = implode($parts, $newline . $newline);

    $parts = preg_split("/$newline/", $header);
    foreach ($parts as $part)
    {
        if (preg_match("/(.*)\: (.*)/", $part, $matches))
        {
            $headers[$matches[1]] = $matches[2];
        }
    }
    _logDebug("parse_header -> returning \$headers: ".serialize($headers));
    return $headers;
}

_logInfo("Starting execution. \$_SERVER is:".serialize($_SERVER));
_logInfo("Getting url (global) for video id ".$_GET['video_id']);

$url = _getYTVideo($_GET['video_id']);

_logInfo("Downloading through $url");

_DownloadThru($url);

function _DownloadThru($url)
{
	
  	foreach (array (' ',"\t","\n") as $char)
    	$url = preg_replace("/$char/",urlencode($char),$url);

	$parsedURL = parse_url($url);

	$itemHost = $parsedURL['host'];
	$itemPath  = array_key_exists('path', $parsedURL) ? $parsedURL['path'] : "/";
	$itemPort  = array_key_exists('port', $parsedURL) ? (int)$parsedURL['port'] : 80;
	$itemPath  .= array_key_exists('query', $parsedURL) ? "?" . $parsedURL['query'] : "";

	$itemPath = urldecode($itemPath);
	_logDebug("_DownloadThrough -> calling _GetFile($itemHost, $itemPath, $itemPort)");
	_GetFile($itemHost, $itemPath, $itemPort);
}

function _GetFile($prmHost, $prmPath, $prmPort) {
	$fp = fsockopen($prmHost, $prmPort, $errno, $errstr, 30);
	if (!$fp) {
		_logError("_GetFile -> $errstr ($errno)");
		echo "$errstr ($errno)<br />\n";
	} else {
		// prepare the header
		$method = $_SERVER['REQUEST_METHOD']; //MediaPlayer knows what to request
		$out  = "$method ". $prmPath .' HTTP/1.1' ."\r\n"; 
		$out .= 'Host: ' . $prmHost . "\r\n";
		$out .= "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13\r\n";
		$out .= "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8\r\n";
		$out .= "Accept-Language: en-us;q=0.7,en;q=0.3\r\n";
		$out .= "Accept-Encoding: gzip,deflate\r\n";
		$out .= "Accept-Charset: windows-1251,utf-8;q=0.7,*;q=0.7\r\n";
		if(isset ($_SERVER['CONTENT_LENGTH'])){ //add content-length if the MediaPlayer specifies it
			$out .= "Content-Length: ".$_SERVER['CONTENT_LENGTH']."\r\n";
		}
		if(isset ($_SERVER['HTTP_RANGE'])){ //jump to the specific range if the MediaPlayer specifies it (when navigating)
			$out .= "Range: ".$_SERVER['HTTP_RANGE']."\r\n";
		}

		$out .= "\r\n";

		fwrite($fp, $out);
		_logDebug("_GetFile -> Sent header $out");
		$headerpassed = false;
		$response_text = "";

		//HTTP/1.1 200 OK
		//HTTP/1.1 302 Found
		$http_code = "";
				
		//read back the response
		while ($headerpassed == false) {
			$line = fgets( $fp);
			if( $line == "\r\n" )
				$headerpassed = true; //break the loop - we have the header
			else
				if ($http_code == "")
					$http_code = $line; //it's the first line the server sends back
				else
					$response_text .= $line; //save the rest of the lines in $response_text
		}
		_logDebug("_GetFile -> Received header $response_text");

		//get an associative array of the response
		$response = parse_header($response_text);
		
  		if ($response['Content-Type'] == 'video/x-flv'){
      		$response['Content-Disposition'] = 'attachment; filename="video.flv"';  //remember to ask it as an attachment
        }
  		if ($response['Content-Type'] == 'video/mp4'){
      		$response['Content-Disposition'] = 'attachment; filename="video.mp4"';  //remember to ask it as an attachment
        }
		
		//I'm not getting the file - I'm getting redirected somewhere else
		if ($http_code == "HTTP/1.1 302 Found\r\n" || $http_code == "HTTP/1.1 303 See Other\r\n"){
		    fclose($fp);
			_logInfo("_GetFile -> Downloading through ". $response['Location']. " because we received a HTTP 302 or 303");
		    _DownloadThru($response['Location']);  //repeat the download process
		}
		else
			if ($http_code == "HTTP/1.1 200 OK\r\n" || $http_code == "HTTP/1.1 206 Partial Content\r\n"){
				//extra headers have been set above for video files and will be sent	
				foreach (array_keys($response) as $header){
					//do a redirect and re-read the file/url
					_logInfo("_GetFile -> Received 200 or 206. Asking for the content with header ". "$header: " . $response[$header]);
				    header("$header: " . $response[$header]);
				}
				_logInfo("_GetFile -> Flushing the socket and exiting...");
				//Turn the power led off if desired
				if(getConfigValue('PROXY_LED') == 'ON'){
					//turn the power led off when the proxy finished
					system("sudo su -c 'echo power led off >> /proc/led'");
				}
				fpassthru($fp); //flush the socket and exit
				exit;
			}
			else{
				//the HTTP code is not supported
				fclose($fp);
				_logError("_GetFile -> HTTP code $http_code is not supported. Out: $out. Response Text: $response_text");
			}

   } //from else socket
}

function _getYTVideo($id)
{
    //decide what video quality to request
    $quality_map = array('1080P' => 37, '720P' => 22, '480P' => 35, '360P' => 34, '240P' => 18);
	//keep the same array, but without the P's
	$numeric_quality_map = array();
    foreach($quality_map as $key => $value){
    	if(preg_match("/([0-9]+)P/", $key, $m)){
    		$numeric_quality_map[ $m[1] ] = $value;
    	}
    }
   
    //set a default quality setting -> 270P by default
    $fmt = 18;
   
    $resolution = getConfigValue('YOUTUBE_QUALITY');
    if(preg_match("/([0-9]+)P/", $resolution, $m)){
        $numeric_resolution = $m[1]; //keep the numerical part
    }

    if(isset($resolution)){
        if(array_key_exists($resolution, $quality_map)){
                $fmt = $quality_map[$resolution];
        }
    }
 	_logInfo("_getYTVideo -> Asking for quality $quality_map[$resolution]");
    
    _logDebug("_getYTVideo -> Asking for file_get_contents(http://www.youtube.com/watch?v={$id})");

    $html = file_get_contents("http://www.youtube.com/watch?v={$id}");

	//from the whole page, extract the ticket
    preg_match("/\&t=([^(\&|$)]*)/", $html, $m);
    $ticket = $m[1];

	_logDebug("_getYTVideo -> extracted ticket $ticket");

    preg_match("/fmt_url_map=([^&$]*)/", $html, $fmt_url_map);
    foreach(explode(',',urldecode($fmt_url_map[1])) as $var_fmt_url_map) {
          list($yt_qlty,$yt_url) = explode('|',$var_fmt_url_map);
          $hash_qlty_url[$yt_qlty] = $yt_url ;
          _logDebug("_getYTVideo -> Quality $yt_qlty is available");
    }

	if(array_key_exists($fmt, $hash_qlty_url)){
		//we found the quality we were looking for, so we can return the decoded URL
		return urldecode($hash_qlty_url[$fmt]);
	}
	else{
		_logWarning("_getYTVideo -> Unable to find url map for quality $fmt ($resolution)");
		//select a different quality - prefer lower quality than desired
		krsort($numeric_quality_map, SORT_NUMERIC); //sort key high to low
		foreach($numeric_quality_map as $key => $value){
			if($key >= $numeric_resolution)
				continue; //skip qualities that are higher than the user requested
			if(array_key_exists($value, $hash_qlty_url)){
				//this is the winning resolution
				_logWarning("_getYTVideo -> Selected quality $value ({$key}P) instead");
				return urldecode($hash_qlty_url[$value]);
			}
		}
		
		//if no lower quality is found, prefer a higher quality than desired
		ksort($numeric_quality_map, SORT_NUMERIC); //sort key low to high
		foreach($numeric_quality_map as $key => $value){
			if($key <= $numeric_resolution)
				continue; //skip qualities that are lower than the user requested
			if(array_key_exists($value, $hash_qlty_url)){
				//this is the winning resolution
				_logWarning("_getYTVideo -> Selected quality $value ({$key}P) instead");
				return urldecode($hash_qlty_url[$value]);
			}
		}
		
		//the code should return something by now. We shouldn't get here. If we do anyway (because of a bug), select a random quality
		_logWarning("_getYTVideo -> Couldn't find any suitable qualities. Selecting a random one and hoping for the best...");
		foreach($hash_qlty_url as $key => $value){
			return urldecode($value); //send the first value
		}
	}

}

function getConfigValue($key){
        $configFile = '/conf/config';
        $fh = fopen($configFile, 'r');
        while(!feof($fh)){
                //read line by line
                $line = fgets($fh);
                //look for the variable we're searching
                preg_match("/^$key=(?:\'|\")?(.*)(?:\'|\")$/", $line, $result);
                if(isset($result[1])){
                        fclose($fh);
                        return $result[1]; //we have a match;
                }
        }
        fclose($fh);
        return null;
}

/* When including funcs-log.php, the proxy will stop working because currently (0.4.5.3) there is an
extra newline at the end of the file witch screws up the headers (they can't be rewritten. We need to
cleanup the empty line and import the new file as a workaround. This can be dropped once the firmware
has the correct version (and enough time has passed so that the users have had time to upgrade)
*/
function includeWorkaround(){
   if(copy("/usr/share/umsp/funcs-log.php", "/tmp/funcs-log.php")){
		//strip all blank lines in place
		system("sed -i '/^$/d' /tmp/funcs-log.php");
		return "/tmp/funcs-log.php";
   }
   return "/usr/share/umsp/funcs-log.php";
}

//Turn the power led off if desired
if(getConfigValue('PROXY_LED') == 'ON'){
	//turn the power led off when the proxy finished
	system("sudo su -c 'echo power led off >> /proc/led'");
}
?>
