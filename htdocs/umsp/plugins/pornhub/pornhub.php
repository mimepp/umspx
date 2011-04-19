<?php

	include_once(includeWorkaround());
	$logLevel=L_WARNING;
	include_once('pornhub-helper.php');


	function _pluginMain($prmQuery)
	{
		$items = array();
		$queryData = array();
		parse_str($prmQuery, $queryData);
		$configData = getConfigData();
		if ($queryData['url'] !='')
			$items = _pluginCreateVideoItems($queryData,$configData);
		else
			$items = _pluginCreateCategoryList($configData);
		return $items;
	}

	function _pluginSearch($prmQuery)
	{
		$configData = getConfigData();
		// get the search term. $prmQuery is (upnp:class derived from "object.item.videoItem") and dc:title contains "search term" plus variations
		// depending on the menu that the search function was called from
		if (preg_match('/and dc:(title|album|genre) contains "(.*?)"/', $prmQuery, $searchterm)){
			//rebuild the queryData array
			$qData = array();
			$qData['url'] = "http://www.pornhub.com/video/search?search=".rawurlencode($searchterm[2])."&x=0&y=0";
			return _pluginCreateVideoItems($qData, $configData['maxpages']);
		}
		else
			return null;
	}

	function _pluginCreateCategoryList($configData)
	{
		$retMediaItems = array();
		// need to use curl, we have a redirect in there and simple file_get_ gets a 401 authorization error
		if(_pluginGetPHShowData($cat_title,$html))
		{
			preg_match_all('/<a href="(?P<category>.+)"><img src="(?P<thumb>.+)" alt="" \/><span class="cat_overlay png"><\/span><\/a>/',$html,$cat_thumbs);
			pushMediaItems ('Latest','/video?c=0',$configData['badge'],'',$retMediaItems);
			for ($z = 0; $z < count($cat_thumbs[0]); $z++)
			{
				//pushMediaItems ($cat_title[2][$z],$cat_title[1][$z],$cat_thumbs[2][$z],$configData['excludeshows'],$retMediaItems);
				pushMediaItems ($cat_title['title'][$z],$cat_title['category'][$z],$cat_thumbs['thumb'][$z],$configData['excludeshows'],$retMediaItems);
			}
		}
		return $retMediaItems;
	}

	function pushMediaItems ($title,$upart,$badge,$excludeshows,&$retMediaItems)
	{
		if (strpos($excludeshows,"|$title|") === false) 
		{
			$data = array (
				'url'	=> "http://www.pornhub.com$upart",
			);
			$dataStr = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/pornhub/pornhub?'.$dataStr,
				'dc:title'	=> $title,
				'upnp:album_art'=> $badge,
				'upnp:class'	=> 'object.container',
			);
		}
	}

	function _pluginCreateVideoItems($queryData,$configData)
	{
		$pageCount = $configData['maxpages'];
		$elements = array ('title','link');
		$retMediaItems=array();
		for ($i = 1; $i <= $pageCount; $i++)
		{
			
			$uri = "{$queryData['url']}&page=$i";
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
			if (preg_match_all('/\<a href=\"http\:\/\/www\.pornhub\.com\/view_video\.php\?viewkey=([0-9]+)\"\s+title=\"([^\"]*)\"\s+class=\"title\"/', $html, $clips, PREG_SET_ORDER)){
				//we have some results
				//_logDebug(serialize($clips));
				foreach ($clips as $val){
					$clipid = $val[1];
					$title = $val[2];
					_logDebug("Found clip $clipid with title $title");
					
					//get the thumbnail (best effort): <img src="http://cdn1.image.pornhub.phncdn.com/thumbs/003/046/304/small.jpg?cache=9578166" alt="Blonde Latina Gets An Anal" 
					$quotedTitle = preg_quote($title);
					_logDebug("Quoted title is: $quotedTitle");
					$thumb="";
					if(@preg_match("/img src=\"([^\"]+)\" alt=\"$quotedTitle/", $html, $match)){
						$thumb = $match[1];
						_logDebug("Found thumbnail $thumb");
					}
					
					//get the clip duration (best effort) from adjacent <var class="duration">5:54</var>
					$time ="";
					if(@preg_match("/$quotedTitle.*var class=\"duration\"\>\s*([0-9\:]+)\<\/var\>/msU", $html, $match)){
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
						'id'		=> 'umsp://plugins/pornhub/pornhub?'.$dataStr,
						'dc:title'	=> str_replace('[] ','',$title),
						'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/pornhub/pornhub.php?'.$dataStr,
						'upnp:album_art'=> (($thumb!='')?$thumb:$configData['badge']),
						'upnp:class'	=> 'object.item.videoItem',
						'protocolInfo'	=> $type
					);
				}
			}
			else{
				//no results - either a bad url/search or the format has changed
				_logWarning("No parsable results for ".$queryData['url'].". It's either due to a bad search/url, or the page format is changed!");
			}
		}
		
		#_logDebug(serialize($retMediaItems));	
		return $retMediaItems;
		
	}

	function getConfigData()
	{
		$config = file_get_contents('/conf/config');
		preg_match('/PORNHUB_MAX_PAGE_DEPTH=\'(.+)\'/', $config, $matches);
		$maxpages = trim($matches[1]);
		preg_match('/PORNHUB_EXCLUDE_SHOWS=\'(.+)\'/', $config, $matches);
		$exclude_shows = trim($matches[1]);
		preg_match('/PROXY_LED=\'(.+)\'/', $config, $matches);
		$proxy_led = trim($matches[1])||"OFF";
		return array (
			'proxy_led' => $proxy_led,
			'maxpages'	=> (($maxpages!='')?$maxpages:4),
			'excludeshows'	=> '|'.$exclude_shows.'|',
			'badge'		=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TWaqqZ2PpKI/AAAAAAAAAZ8/DDSN49dCaqs/s200/ph-logo-tall-mirror.png',
			'stopbadge'	=> 'http://lh6.googleusercontent.com/_xJcSFBlLg_Y/TWOoLmVMr6I/AAAAAAAAAYE/_-d60WDF-rQ/stop-a-cop8.png',
		);
	}

	function includeWorkaround()
	{
		if((file_exists('/tmp/funcs-log.php'))||(copy('/usr/share/umsp/funcs-log.php', '/tmp/funcs-log.php')))
		{
			//strip all blank lines in place
			system("sed -i '/^$/d' /tmp/funcs-log.php");
			system('chmod 666 /tmp/funcs-log.php');
			return '/tmp/funcs-log.php';
		}
		return '/usr/share/umsp/funcs-log.php';
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
			elseif (($resp['Content-Type'] == 'video/3gp') || ($resp['Content-Type'] == 'video/3gpp'))
				$resp['Content-Disposition'] = 'attachment; filename="video.3gp"';
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
		$uri = "http://www.pornhub.com/view_video.php?viewkey=$id";
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
		//to.addVariable("video_url","http%3A%2F%2Fams-v48.pornhub.com%2Fdl%2Ffc609a1afb1e697b34cded80f47be0fa%2F4d9340db%2Fvideos%2F002%2F074%2F568%2F2074568.flv%3Fr%3D150");
		if(preg_match("/video_url\",\"([^\"]+)\"/", $html, $match)){
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
