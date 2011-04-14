<?php

	/*
	(C) 2010 shunte

	TWiT.tv: pull contents from TWiT.tv listings and aggregates content inclusive of show art

	This TWiT.tv plugin is designed for Zoster's UMSP server which runs (amongst others) inside 
	the EM7075 and DTS variant.
	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	Thank you, and enjoy this plugin.
	*/

	function _pluginMain($prmQuery) {
        
		$items = array();
		$queryData = array();
		if (strpos($prmQuery,'&amp;')!==false) $prmQuery=str_replace('&amp;','&',$prmQuery);
		parse_str($prmQuery, $queryData);
		if ($queryData['feed'] !='') 
		{
			$items = _pluginFeedVersions(
					$queryData['feed'],$queryData['title'],$queryData['showpage'],$queryData['hashkey']
				);
		}
		else 
		{
			if ($queryData['itemurl'] !='') 
			{
				$items = _pluginCreateItems($queryData['itemurl'],$queryData['hashkey']);
			} 
			else 
			{
				$items = _pluginCreateFeedList();
			} // if
		} // if

		return $items;

	} // function

	function getConfigData()
	{
		$stick = getStick();
		$config = file_get_contents('/conf/config');
		// exclude anything we have no interest in via config
		preg_match('/TWITTV_EXCLUDE_SHOWS=\'(.+)\'/', $config, $m);
		$exclude_shows = trim(strtolower($m[1].'|'));
		preg_match('/TWITTV_CACHE_FOLDER=\'(.+)\'/', $config, $m);
		$cache_folder = ((trim($m[1])!='')?trim($m[1]):$stick.'twittv-cache');
		preg_match('/TWITTV_MEDIA_HASH=\'(.+)\'/', $config, $m);
		$hashfile = trim($m[1]);
		return array (
			'cachefolder'	=> $cache_folder,
			'excludeshows'	=> '|'.$exclude_shows.'|',
			'hashfile'	=> (($hashfile!='')?$hashfile:$cache_folder.'/linkshash.txt'),
			'badge'		=> $cache_folder.'twitv-logo.png',
			'stick'		=> $stick,
		);
	}

	function testCache($hash,$configData,&$art='',&$feeds='') 
	{
		$cached = $configData['cachefolder'].'/'.$hash.'.png';
		$art='';
		$feeds='';
		if (file_exists($cached)) 
		{
			// already cached, we're all set
			$art = $cached;
		}
		if (file_exists($configData['hashfile']))
		{
			$hashed = file_get_contents($configData['hashfile']);
			preg_match('/'.$hash.'=(.+)/', $hashed, $h);
			$feeds = $h[1];
		}
		return ((trim($art)!='')&&(trim($feeds)!=''));
	}

	function getStick()
	{
		$stick='';
		$tests =array('.wd_tv','.wdtvext-plugins','wdtvlive.bin','root.bin');
		foreach ($tests as $test) 
		{
			$res = shell_exec("find '/tmp/mnt/' -name '$test'");
			if(trim($res)!='')
			{
				$stick = str_replace($test,'',trim($res));
				break;
			}
		}
		// defaults to /tmp so is volatile!
		return($stick=='')?'/tmp/':$stick;
	}

	function testAndCacheDetails($hash,$configData,$feeds)
	{
		$fh = fopen($configData['hashfile'], 'a') or die ("can't open file {$configData['hashfile']}");
		fwrite($fh, "$hash=".((is_array($feeds))?implode('|',$feeds):$feeds)."\n");
		fclose($fh);
	}

	function testAndCacheImage($hash,$imgurl,$configData) 
	{
		$imgcache = $imgurl;
		$cached = $configData['cachefolder'].'/'.$hash.'.png';
		if (file_exists($cached)) 
		{
			// already cached, we're all set
			$imgcache = $cached;
		}
		else
		{
			// get the file and store, used for show art as well as menu badges
			$size = getimagesize($imgurl);
			switch($size['mime'])
			{
				case 'image/jpeg': $im = imagecreatefromjpeg($imgurl); break;
				case 'image/gif': $im = imagecreatefromgif($imgurl); break;
				case 'image/png': $im = imagecreatefrompng($imgurl); break;
				default: $im = false; break;
			} // switch
			if ($im)
				imagepng($im,$cached,5); // save local, and for simplicity always a .png
			imagedestroy($im);
			$imgcache = $cached;
		}

		return $imgcache;
	}

	function makeCacheFolder($folder)
	{
		if (!file_exists($folder)) 
		{
			// make cache folder to store cached images and hash table (assuming in same directory)
			$oldumask = umask(0);
			@mkdir($folder, 0777);
			umask($oldumask);
		} // if
	}

	function cacheSomeBadge($configData)
	{
		// badge images stored in PicasaWeb, save to cache for speed, Google resize to 200pix
		// these are the default badges should cached images be missing for whatever reason
		// if it's a cache folder issue then obviousl all of this is for naugt too ;)
		testAndCacheImage('twit-logo','http://lh5.ggpht.com/_xJcSFBlLg_Y/TQtpX9fuqqI/AAAAAAAAAC4/citAdIhoOVA/s200/twit-logo.png',$configData);
		testAndCacheImage('twit-video','http://lh4.ggpht.com/_xJcSFBlLg_Y/TQtpYPuslbI/AAAAAAAAAC8/6WT48annmGw/s200/twit-video.png',$configData);
		testAndCacheImage('twit-audio','http://lh3.ggpht.com/_xJcSFBlLg_Y/TQtpYFuLLbI/AAAAAAAAADA/3SNhzX56xxc/s200/twit-audio.png',$configData);
	}

	function _pluginCreateFeedList()
	{

		$configData = getConfigData();
		makeCacheFolder($configData['cachefolder']);
		cacheSomeBadge($configData);
		$baseurl = 'http://twit.tv';
		$html = file_get_contents($baseurl.'/');
		// position 2 = url, 4 = title, 3 can contain long desc but we'll discard
		// included test that should not occur on itial page - completion only
		if(preg_match_all('/<li class="(leaf|leaf first|leaf last|leaf first active-trail|leaf last active-trail|leaf active-trail)"><a href="(.*?)"(.*?)>(.*?)<\/a><\/li>/',$html,$shows))
		{
			for ($z = 0; $z < count($shows[1]); $z++) 
			{
				$upart = trim($shows[2][$z]);
				if (strpos($configData['excludeshows'], '|'.str_replace('/','',$upart).'|') === false) 
				{

					$title = trim($shows[4][$z]);
					$hash = md5($upart);
					// need to grab the show art (and details) from
					// a sperate page we'll cache this so is not such
					// an expensive exercise on subsequent iterations
					if(testCache($hash,$configData,$badge,$feedStr)==false)
					{
						$detail = file_get_contents($baseurl.$upart);
						if(trim($badge)=='')
						{
							preg_match_all('/<img src="(.+)" alt="" title=""  class="imagecache imagecache-coverart" width="200" height="200" \/>/',$detail,$art);
							$badge =  testAndCacheImage($hash,(string)$art[1][0],$configData);
						}
						if($feedStr=='')
						{
							// get feeds, need to differentiate between audio, large small etc
							preg_match_all('/<option value="(.*?)">RSS<\/option>/',$detail,$feeds);
							$feedStr = implode('|',$feeds[1]);
							testAndCacheDetails($hash,$configData,$feedStr);
						}
					}
					$data = array (
						'showpage'	=>	$baseurl.$upart,
						'feed'		=>	$feedStr,
						'title'		=>	$title,
						'hashkey'	=>	$hash,
					);
					$dataStr = http_build_query($data,'','&amp;');
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/twittv/twittv?'.$dataStr,
						'dc:title'	=> str_replace('&#039;',"'",$title),
						'res'		=> 'umsp://plugins/twittv/twittv?'.$dataStr,
						'upnp:class'	=> 'object.container',
						'upnp:album_art'=> $badge,
					);
				} // exclude ?
			}
		}

		return $retMediaItems;

	} // function

	function _pluginFeedVersions($feedStr,$title,$url='',$hash='') 
	{
		// Blow-out the feed items to source an item from XML
		$configData=getConfigData();
		$feeds=explode('|',$feedStr);
		$retMediaItems = array();
		if(count($feeds)==1)
		{
			$retMediaItems = _pluginCreateItems($feeds[0],$hash);
		}
		else
		{
			$type_map = array (
				'podcasts'	=>	array('title'=>'Audio Podcast','badge'=>str_replace('-logo','-audio',$configData['badge'])),
				'video_small'	=>	array('title'=>'Small Vodcast','badge'=>str_replace('-logo','-video',$configData['badge'])),
				'video_large'	=>	array('title'=>'Large Vodcast','badge'=>str_replace('-logo','-video',$configData['badge'])),
			);
			foreach ($feeds as $feed) 
			{
				if(preg_match('/(podcasts|video_large|video_small)/',$feed,$types))
				{
					$data = array(
						'itemurl'	=> $feed,
						'hashkey'	=> $hash,
					);
					$dataStr = http_build_query($data, '', '&amp;');
					$cached = $configData['cachefolder'].'/'.$hash.'.png';
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/twittv/twittv?'.$dataStr,
						'dc:title'	=> str_replace('&#039;',"'","$title ({$type_map[$types[1]]['title']})"),
						'res'		=> 'umsp://plugins/twittv/twittv?'.$dataStr,
						'upnp:album_art'=> ((file_exists($cached))?$cached:$type_map[$types[1]]['badge']),
						'upnp:class'	=> 'object.container',
        	                       );
				}
			}
		}
		return $retMediaItems;
	}

	function _pluginCreateItems($url,$hash='') 
	{
		$retMediaItems = array();
		$configData=getConfigData();
		$xml_shows = simplexml_load_file($url);
		foreach($xml_shows->channel->item as $item)
		{
			$title = (string)$item->title;
			$attr = $item->enclosure->attributes();
			$uri = (string)$attr->url;
			$type = (string)$attr->type;
			$type = (($type=='')?'video/mp4':$type);
			$data = array(
				'url'	=> $uri,
			);
			$dataStr = http_build_query($data, 'pluginvar_');
			$cached = $configData['cachefolder'].'/'.$hash.'.png';
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/twittv/twittv?'.$dataStr,
				'dc:title'	=> $title,
				'res'		=> $uri,
				'upnp:class'	=> ((strpos(strtolower($type),'video')===false)?'object.item.audioItem':'object.item.videoItem'),
				'upnp:album_art'=> ((file_exists($cached))?$cached:$configData['badge']),
				'upnp:artist'	=> (string)$item->author,
				'upnp:genre'	=> (string)$item->category,
				'upnp:album'	=> $title,
				'size'		=> (string)$attr->length,
				'dc:date'	=> (string)$item->pubDate,
				'protocolInfo'	=> "http-get:*:$type:*",
			);
		}

		return $retMediaItems;
	}

?>
