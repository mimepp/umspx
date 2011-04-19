<?php
	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');	
	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-log.php');
	//include_once(includeWorkaround());
	$logLevel=L_WARNING;
	include_once('tube8-helper.php');

	function _pluginMain($prmQuery)
	{
		$items = array();
		$queryData = array();
		parse_str($prmQuery, $queryData);
		$configData = getConfigData();
		if ($queryData['url'] !='')
			$items = _pluginCreateVideoItems($queryData['url'],$configData);
		else
			$items = _pluginCreateCategoryList($configData);
		return $items;
	}

	function _pluginCreateCategoryList($configData)
	{
		$retMediaItems = array();
		pushMediaItems ('Latest','/latest/',$configData['badge'],'',$retMediaItems);
		pushMediaItems ('Top','/top/',$configData['badge'],'',$retMediaItems);
		if(_pluginGetT8ShowData($cat_num_title,$html))
		{
			$testTitle = array();
			for ($z = 0; $z < count($cat_num_title[0]); $z++)
			{

				$regex = '/<a href="http:\/\/www.tube8.com\/'.mb_strtolower($cat_num_title['title'][$z])
					.'\/(.*?)category="'.trim(dechex($cat_num_title['id'][$z]))
					.'" thumb_src="(.*?)" src="(.*?)"/mis';
//				_logDebug("regex=$regex");
//				_logDebug("html=$html");
				if(preg_match($regex,$html,$badge))
					$thumb = $badge[3];
				else
					$thumb = $configData['badge'];
				pushMediaItems ($cat_num_title['title'][$z],$cat_num_title['category'][$z],$thumb,$configData['excludeshows'],$retMediaItems);
				$testTitle[] = $cat_num_title['title'][$z];
			}
			if(in_array('Gay',$testTitle)==false)pushMediaItems ('Gay','/cat/gay/9/',$configData['badge'],$configData['excludeshows'],$retMediaItems);
			if(in_array('Shemale',$testTitle)==false)pushMediaItems ('Shemale','/cat/shemale/15/',$configData['badge'],$configData['excludeshows'],$retMediaItems);
		}
		return $retMediaItems;
	}

	function _pluginSearch($prmQuery)
	{
		$configData = getConfigData();
		// get the search term. $prmQuery is (upnp:class derived from "object.item.videoItem") and dc:title contains "search term" plus variations
		// depending on the menu that the search function was called from
		if (preg_match('/and dc:(title|album|genre) contains "(.*?)"/', $prmQuery, $searchterm))
			return _pluginCreateVideoItems("http://www.tube8.com/search.html?q=".rawurlencode($searchterm[2]),$configData,false);
		else
			return array (
				'id'		=> 'umsp://plugins/tube8/tube8?no=data',
				'dc:title'	=> 'No data found',
				'upnp:album_art'=> $configData['stopbadge'],
				'upnp:class'	=> 'object.container',
			);
	}

	function pushMediaItems ($title,$upart,$badge,$excludeshows,&$retMediaItems)
	{
		if (strpos($excludeshows,"|$title|") === false) 
		{
			$data = array (
				'url'	=> 'http://www.tube8.com'.$upart.'page/',
			);
			$dataStr = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/tube8/tube8?'.$dataStr,
				'dc:title'	=> $title,
				'upnp:album_art'=> $badge,
				'upnp:class'	=> 'object.container',
			);
		}
	}

	function _pluginCreateVideoItems($url,$configData,$pagemode=true)
	{
		if($pagemode)	// "normal" mode
			$pageCount = $configData['maxpages'];
		else		// "search" mode
			$pageCount = 1;
		$elements = array ('title','link');
		
		$retMediaItems=array();
		for ($i = 1; $i <= $pageCount; $i++)
		{
			
			$uri = "$url".$i;
//			$uri = $queryData['url'];
			_logDebug("Downloading $uri");
			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'header'=>"Accept-language: en\r\n" .
						  "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13\r\n"
			  )
			);

			$context = stream_context_create($opts);
			$html = file_get_contents("$uri", false, $context);
			
			
			//_logDebug("Page is $html");
			//look for all instances of this regular expression
			//looking for <a href="http://www.tube8.com/asian/kianna-dior-looking-gorgeous-ass-she-gets-fucked/666031/" title="Kianna Dior looking gorgeous ass she gets fucked">
			if (preg_match_all('/\<a href=\"http\:\/\/www\.tube8\.com\/([^\"]+)\"\s+title=\"([^\"]*)\"/', $html, $clips, PREG_SET_ORDER)){
				//we have some results
				//_logDebug(serialize($clips));
				foreach ($clips as $val){
					$clipid = $val[1];
					$title = $val[2];
					//carve out the numeric id
					$numeric_id=0;
					if(preg_match("/\/([0-9]+)\/$/", $clipid, $match)){
						$numeric_id = $match[1];
					}
					else{
						_logWarning("Unable to extract numeric id for clip $clipid");
					}
					_logDebug("Found clip $clipid with title $title");
					
					//get the thumbnail (best effort): <img width="190" height="143" class="videoThumbs" id="i695091" category="2" thumb_src="http://cdn1.image.tube8.phncdn.com/1103/02/4d6e970eafd2e/190x143/" src="http://cdn1.image.tube8.phncdn.com/1103/02/4d6e970eafd2e/190x143/9.jpg"

					$thumb="";
					if(@preg_match("/img [^\>]* id=\"i${numeric_id}\" [^\>]* src=\"([^\"]+)\"/", $html, $match)){
						$thumb = $match[1];
						_logDebug("Found thumbnail $thumb");
					}
					
					//get the clip duration (best effort) from adjacent <strong>26:27</strong>
					$quotedTitle = preg_quote($title);
					_logDebug("Quoted title is: $quotedTitle");
					$time ="";
					if(@preg_match("/title=\"$quotedTitle.*\<strong\>\s*([0-9\:]+)\<\/strong\>/msU", $html, $match)){
						#regex modifiers: m-> multiline, U -> ungreedy, s -> dotall
						#ungreedy -> make sure the regex stops (.*) when running into the first duration section to get the propper time.
						_logDebug("Matched duration $match[1]");
						$time = "[".$match[1]."] ";
					}
					$title = $time.$title;
					
					
					$data = array (
						'mov_id'	=> rawurlencode($clipid),
						'ClipName'	=> $title,
					);
					$dataStr = "mov_id={$data['mov_id']}&ClipName=".rawurlencode($data['ClipName']);
					$type = 'http-get:*:video/flv:*';
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/tube8/tube8?'.$dataStr,
						'dc:title'	=> str_replace('[] ','',$title),
						'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/tube8/tube8.php?'.$dataStr,
						'upnp:album_art'=> (($thumb!='')?$thumb:$configData['badge']),
						'upnp:class'	=> 'object.item.videoItem',
						'protocolInfo'	=> $type
					);
				}
			}
			else{
				//no results - either a bad url/search or the format has changed
				_logWarning("No parsable results for $url. It's either due to a bad search/url, or the page format is changed!");
			}
		}
		
		#_logDebug(serialize($retMediaItems));	
		return $retMediaItems;

	}


	function getConfigData()
	{
		$config = file_get_contents(_getUMSPConfPath() . '/config');
		preg_match('/TUBE8_MAX_PAGE_DEPTH=\'(.+)\'/', $config, $matches);
		$maxpages = trim($matches[1]);
		preg_match('/TUBE8_EXCLUDE_SHOWS=\'(.+)\'/', $config, $matches);
		$exclude_shows = trim($matches[1]);
		preg_match('/PROXY_LED=\'(.+)\'/', $config, $matches);
		$proxy_led = trim($matches[1])||"OFF";
		return array (
			'proxy_led' => $proxy_led,
			'maxpages'	=> (($maxpages!='')?$maxpages:1),
			'excludeshows'	=> '|'.$exclude_shows.'|',
			'badge'		=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TWM_L7Vo2nI/AAAAAAAAAXM/74F1vbmw7yE/s200/t8logo2.png',
			'stopbadge'	=> 'http://lh6.googleusercontent.com/_xJcSFBlLg_Y/TWOoLmVMr6I/AAAAAAAAAYE/_-d60WDF-rQ/stop-a-cop8.png',
		);
	}

	function includeWorkaround()
	{
		if((file_exists('/tmp/funcs-log.php'))||(copy(_getUMSPWorkPath() . '/funcs-log.php', '/tmp/funcs-log.php')))
		{
			//strip all blank lines in place
			system("sed -i '/^$/d' /tmp/funcs-log.php");
			system('chmod 666 /tmp/funcs-log.php');
			return '/tmp/funcs-log.php';
		}
		return _getUMSPWorkPath() . '/funcs-log.php';
	}

	function proxyGet($host, $path, $query='')
	{
		$config_data = getConfigData();
		if($query!='')
			$pathq = "$path?$query";
		else
			$pathq = $path;
		$fp = fsockopen($host, 80, $errno, $errstr, 30);
		if (!$fp)
			echo "$errstr ($errno)<br />\n";
		else
		{
			$out  = "{$_SERVER['REQUEST_METHOD']} $pathq HTTP/1.1\r\n";
			$out .= 'Host: ' . $host . ":80\r\n";
			$out .= "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13\r\n";
			if(isset ($_SERVER['CONTENT_LENGTH']))
			{
				//add content-length if the MediaPlayer specifies it
				$out .= "Content-Length: {$_SERVER['CONTENT_LENGTH']}\r\n";
			}
			if(isset ($_SERVER['HTTP_RANGE']))
			{
				//jump to the specific range if the MediaPlayer specifies it (when navigating)
				$out .= "Range: {$_SERVER['HTTP_RANGE']}\r\n";
			}
			$out .= "\r\n";
			fwrite($fp, $out);
			$headerpassed = false;
			$response = '';
			$ret = '';
			while ($headerpassed == false)
			{
				$line = fgets($fp);
				if( $line == "\r\n" )
					$headerpassed = true;
				else
				{
					if($ret=='')$ret=$line;
					else
					{
						list($key,$value)=explode(':',trim($line));
						$resp[$key]=trim($value);
					}
				}
			}
			// process as an attachment; note only the 3gp currently requires transcode
			if ($resp['Content-Type'] == 'video/x-flv')
				$resp['Content-Disposition'] = 'attachment; filename="video.flv"';
			elseif ($resp['Content-Type'] == 'video/mp4')
				$resp['Content-Disposition'] = 'attachment; filename="video.mp4"';
			elseif ($resp['Content-Type'] == 'application/octet-stream'){
				//tube8 server sends data as application/octet-stream
				$resp['Content-Type'] = 'video/x-flv';
				$resp['Content-Disposition'] = 'attachment; filename="video.flv"';
			}	
			elseif (($resp['Content-Type'] == 'video/3gp') || ($resp['Content-Type'] == 'video/3gpp'))
				$resp['Content-Disposition'] = 'attachment; filename="video.3gp"';
			else{
				//if it's none of the above - it's unlikely to work, complain
				_logWarning("Unsupported content-type ".$resp['Content-Type'].". Video clip may not be supported.");
			}
			
			if ((strpos($ret, '200 OK') !== false) || (strpos($ret, '206 Partial Content') !== false))
			{
				//extra headers have been set above for video files and will be sent   
				foreach (array_keys($resp) as $header)
				{
					_logDebug("Sending header $header: {$resp[$header]}");
					header("$header: {$resp[$header]}");
				}
				//Turn the power led off if desired
				if($config_data['proxy_led'] == 'ON'){
					//turn the power led off when the proxy finished
					system("sudo su -c 'echo power led off >> /proc/led'");
				}
				fpassthru($fp);
				exit;
			}
			else
			{
				_logError("Received unsupported response while getting the video: $ret");
				fclose($fp);
				//Turn the power led off if desired
				if($config_data['proxy_led'] == 'ON'){
					//turn the power led off when the proxy finished
					system("sudo su -c 'echo power led off >> /proc/led'");
				}
				exit;
			}
		}
	}

// movie magic happens here - this is executed when called in proxy mode
if(isset($_GET['mov_id']))
{
	$config_data = getConfigData();
	//Turn the power led on if desired
	if($config_data['proxy_led'] == 'ON'){
		//turn the power led on while the proxy is doing something
		system("sudo su -c 'echo power led on >> /proc/led'");
	}
	if(''!=$_GET['mov_id'])
	{
		$id = $_GET['mov_id'];
		$uri = "http://www.tube8.com/$id";
		_logDebug("Downloading video $uri");
		$opts = array(
		  	'http'=>array(
			'method'=>"GET",
			'header'=>"Accept-language: en\r\n" .
					  "User-Agent: Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13\r\n"
		  )
		);

		$context = stream_context_create($opts);
		$html = file_get_contents("$uri", false, $context);
		
		//get the video URL from the video page
		//flashvars.video_url = "http%3A%2F%2Fcdn1.public.tube8.com%2F1102%2F20%2F4d612c2526c0a%2F4d612c2526c0a.flv%3Fsr%3D556%26int%3D307200b%26nvb%3D20110402093242%26nva%3D20110402113242%26hash%3D011f673d27fe059e46594";
		if(preg_match("/flashvars\.video_url\s*=\s*\"([^\"]+)\"/", $html, $match)){
			$video_url = urldecode($match[1]);
			_logDebug("Got video url $video_url");
			$parsed = parse_url($video_url);
            proxyGet($parsed['host'], $parsed['path'],$parsed['query']);
		}
		else{
			//couldn't find video
			_logError("Couldn't find video_url in $uri. Maybe the page format has changed");
		}
		
	}
	//Turn the power led off if desired
	if($config_data['proxy_led'] == 'ON'){
		//turn the power led off when the proxy finished
		system("sudo su -c 'echo power led off >> /proc/led'");
	}
}
?>
