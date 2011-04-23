<?php

	/*

	(C) 2010 Alex Meijer for Eminent Europe B.V.

	Revision: modified to pull contents from rev3 shows listings inclusive of show art - shunte
	Revision: top down re-tooling - shunte 2010.12.21
	Revision: exposed show thumbnails - shunte 2011.02.19
	Revision: menu-fast track and R3 today feature - shunte 2011.03.07
	Revision: search and additional fast-tracking - shunte 2011.03.10

	This revision3 plugin is designed for Zoster's USMP server which runs (amongst others) inside the EM7075 and DTS variant.
	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	Thank you, and enjoy this plugin.

	*/

	function _pluginMain($prmQuery)
	{

		// This function is started if user clicks on the 'Revision3' option in the menu
		// It has no parameters and will run 'PluginCreateFeedlist' that returns an array with feeds.
		// If run with a feed (/revision3.php?feed=animetv) it will run the function _pluginFeedResolution
		// and if run with both present, it will return a list of items in the respective feed.

		if (strpos($prmQuery,'&amp;')!==false) $prmQuery=str_replace('&amp;','&',$prmQuery);
		parse_str($prmQuery, $queryData);
		$items = array();
		if ($queryData['today'] !='')
			$items = _pluginTodayFeedList($queryData);
		elseif ($queryData['feed'] !='')
			$items = _pluginFeedResolution($queryData);
		elseif ($queryData['episode'] !='')
			$items = _pluginEpisodeItem($queryData);
		elseif ($queryData['itemurl'] !='')
			$items = _pluginCreateVideoItems($queryData);
		elseif ($queryData['search']==true)
		{
			$items = _pluginCreateFeedList();
			exec("sudo chmod 666 /tmp/ir_injection && sleep 2 && sudo echo E > /tmp/ir_injection &");
		}
		else
			$items = _pluginCreateFeedList();
		return $items;
	}

	function scrapeShowsHTML($url,&$shows,&$image_url)
	{
		// we can get shows from the following URL, won't give us archived shows though!
		// page scrape method gives show name, URL, desc, thumb and a level of dynamacicity (sp?)
		//http://revision3.com/shows
		// archived (no longer associated or in production) can be found here
		//http://revision3.com/shows/archive
		$html = file_get_contents($url);
		preg_match_all('/<h3><a href="\/(?P<show>.*?)">(?P<title>.*?)<\/a><\/h3>/',$html,$shows);
		preg_match_all('/" class="thumbnail"><img src="(?P<thumb>.*?)" \/><\/a>/',$html,$image_url);
	}

	function pushMediaItems($shows,$image_url,$configData,$boiler,&$retMediaItems)
	{
		if(sizeof($shows[1])!=0)
		{
			$exclude_shows = $configData['exclude'];
			for ($z = 0; $z < count($shows[1]); $z++)
			{
				$upart = trim($shows['show'][$z]);
				$title = trim($shows['title'][$z]);

				// [to]day sourced show does not have the tekzilla branding
				if(('tzdaily'==$upart)&&('ON'!=$configData['dailyshows']))$exclude_shows .= "|$upart|";

				if((strpos($exclude_shows,'|'.$upart.'|')) === false)
				{
					if($configData['defaultres']!='')
					{
						if ($upart == 'tekzilla')
							$subshow = (($configData['dailyshows']=='ON')?'?subshow=true':'?subshow=false');
						else
							$subshow = '';
						$data = array (
							'itemurl'	=> 'http://revision3.com/'.$upart.$configData['defaultres'].$subshow,
						);
					}
					else
					{
						$data = array (
							'feed'		=> $upart,
						);
					}
					$dataStr = http_build_query($data, 'pluginvar_');
					$thumb = ((''!=$image_url['thumb'][$z])?$image_url['thumb'][$z]:
							"http://videos.revision3.com/revision3/images/shows/{$upart}/{$upart}.jpg"
							///"http://bitcast-a.bitgravity.com/revision3/docs/show/{$upart}/iphone/logo.jpg" // <--- content here is excellent but not active for all shows!
						);
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/revision3/revision3?'.$dataStr,
						'dc:title'	=> $boiler.' ('.$title.')',
						'upnp:album_art'=> $thumb,
						'upnp:class'	=> 'object.container',
                                      );
				}
			}
		}
	}

	function _pluginSearch($prmQuery)
	{
		if (preg_match('/and dc:(title|album|genre) contains "(.*?)"/', $prmQuery, $searchterm))
		{
			$retMediaItems = array();		
			$configData = getConfigData();
			for ($page = 1; $page < 3; $page++)	// 10 items per page limit!!
			{
				$html = file_get_contents('http://revision3.com/search/page?type=video&q='.urlencode($searchterm[2]).'&limit=10&page='.$page);
				if(preg_match_all(
					'/&client_id=revision3"><img src="(?P<thumb>.*?)" \/><div class="playLine">Play Video<\/div><\/a><a class="title" href='
					.'"http:\/\/www.videosurf.com\/webui\/inc\/go.php\?redirect=(?P<episode>.*?)&client_id=revision3">'
					.'(?P<title>.*?)<\/a><div class="description"><b>(?P<added>.*?)<\/b>/',$html,$episodes))
				{
					$exclude_shows = $configData['exclude'];
					for ($z = 0; $z < count($episodes[1]); $z++)
					{
						$title = $episodes['title'][$z].' '.strtolower($episodes['added'][$z]);
						$data = array(
							'episode'	=> urldecode($episodes['episode'][$z]),
							'thumb'		=> $episodes['thumb'][$z],
							'title'		=> $title,
						);
						$dataStr = http_build_query($data, '', '&amp;');
						$retMediaItems[] = array (
							'id'		=> 'umsp://plugins/revision3/revision3?'.$dataStr,
							'dc:title'	=> $title,
							'upnp:album_art'=> $episodes['thumb'][$z],
							'upnp:class'	=> 'object.container',
						);
					}
				}
			}
			return $retMediaItems;
		}
		else
			return null;
	}

	function _pluginCreateFeedList()
	{
		$configData = getConfigData();
		if($configData['showtoday']=='ON')
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/revision3/revision3?today=yes',
				'dc:title'	=> 'List shows that premiere on Revision3 today',
				//'upnp:album_art'=> $configData['today2'],
				'upnp:album_art'=> $configData['today'],
				'upnp:class'	=> 'object.container',
			);
		$retMediaItems[] = array (
			'id'		=> 'umsp://plugins/revision3/revision3?search=1',
			'dc:title'	=> 'Search Revision3 Past & Present',
			'upnp:album_art'=> $configData['search'],
			'upnp:class'	=> 'object.container',
		);
		// parse shows via revision3 shows html
		scrapeShowsHTML('http://revision3.com/shows',$shows,$image_url);
		pushMediaItems($shows,$image_url,$configData,'Revision3',$retMediaItems);
		if($configData['archives']=='ON')
		{
			scrapeShowsHTML('http://revision3.com/shows/archive',$shows,$image_url);
			pushMediaItems($shows,$image_url,$configData,'Revision3 Archive',$retMediaItems);
		}
		return $retMediaItems;
	}

	function _pluginEpisodeItem($queryData)
	{
		// we have an episode page from which we can extract playable uri information
		// fast-tack is broken via this mechanism but fills the need or search
		$configData = getConfigData();
		$retMediaItems = array();
		$html = file_get_contents($queryData['episode']);
		if(preg_match_all('/<a class="sizename" href="(?P<show>.*?).mp4">(?P<title>.*?)<\/a>/',$html,$playable))
		{
			for ($z = 0; $z < count($playable[1]); $z++)
			{
				$data = array(
					'url'	=> $playable['show'][$z].'.mp4',
				);
				$dataStr = http_build_query($data, 'pluginvar_');
				$retMediaItems[] = array (
					'id'		=> 'umsp://plugins/revision3/revision3?'.$dataStr,
					'dc:title'	=> $queryData['title']." ({$playable['title'][$z]})",
					'res'		=> $data['url'],
					'upnp:album_art'=> $queryData['thumb'],
					'protocolInfo'	=> 'http-get:*:*:*',
					'upnp:class'	=> 'object.item.videoItem',
				);

			}
		}
		if(empty($retMediaItems))
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/revision3/revision3?nothing=',
				'dc:title'	=> 'Apologies, unable to find playable content for this episode',
				'upnp:album_art'=> $configData['stopbadge'],
				'upnp:class'	=> 'object.container',
			);
		return $retMediaItems;
	}

	function _pluginFeedResolution($queryData)
	{

		// Create the actual XML feed url with resolution selection added at the end.
		// Create variants from $feed: ($feed holds the name, hak5 for ex.)
		$feed = $queryData['feed'];
		$configData = getConfigData();

		// if we want tekzilla daily then subshow arg should be passed (actually default is on)
		//http://revision3.com/tekzilla/feed/Xvid-Large?subshow=false
		if ($feed == 'tekzilla')
			$subshow = (($configData['dailyshows']=='ON')?'?subshow=true':'?subshow=false');
		else
			$subshow = '';

		$variants = array (
			array (
				'desc'	=> 'Small',
				'ext'	=> '/feed/MP4-Small',
				'badge'	=> $configData['small'],
			),
			array (
				'desc'	=> 'Large',
				'ext'	=> '/feed/MP4-Large',
				'badge'	=> $configData['medium'],
			),
			array (
				'desc'	=> 'HD 720p',
				'ext'	=> '/feed/MP4-High-Definition',
				'badge'	=> $configData['large'],
			),
		);
		foreach ($variants as $variant)
		{
			$data = array (
				'itemurl'	=> 'http://revision3.com/'.$feed.$variant['ext'].$subshow,
			);
			$dataStr = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/revision3/revision3?'.$dataStr,
				'dc:title'	=> $variant['desc'],
				'upnp:album_art'=> $variant['badge'],
				'upnp:class'	=> 'object.container',
			);
		}
		return $retMediaItems;
	}

	function _pluginTodayFeedList($queryData)
	{
		$retMediaItems = array();		
		$configData = getConfigData();
		$date = date('Y-m-d');
		$html = file_get_contents('http://revision3.com/schedule');
		// looks to be buggy on the R3 side, yes valid daily shows but some weeklies also appearing???
		if(preg_match('/<h4><a href="\/schedule\/'.str_replace('-','\/',$date).'">(.*?)<\/ul><\/div><\/div><hr class="clear clear-left" \/><div class="day-block">/',$html,$today))
		{
			if(preg_match_all('/<p class="title"><a href="\/(?P<show>.*?)" class="show-link">(?P<title>.*?)<\/a><\/p><\/li>/',$today[1],$shows))
			{
				for ($z = 0; $z < count($shows[1]); $z++)
				{
					if(!(($configData['dailyshows']=='OFF')&&($shows['show'][$z] == 'tzdaily')))
					{
						if ($shows['show'][$z] == 'tekzilla')
							$subshow = (($configData['dailyshows']=='ON')?'?subshow=true':'?subshow=false');
						else
							$subshow = '';
						// we're fast-tracking here, either user configuration or middle of the road video resolution
						$data = array (
							'itemurl'	=> 'http://revision3.com/'.$shows['show'][$z].(($configData['defaultres']!='')?$configData['defaultres']:'/feed/MP4-Large').$subshow,
							'break'		=> true,
						);
						$retMediaItems = array_merge($retMediaItems,_pluginCreateVideoItems($data));
						$idx = count($retMediaItems) -1;
						$retMediaItems[$idx]['dc:title'] = $shows['title'][$z].' - '.$retMediaItems[$idx]['dc:title'];
					}
				}
			}
		}
		if(empty($retMediaItems))
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/revision3/revision3?nothing=',
				'dc:title'	=> 'Apologies, no shows are scheduled to premiere today '.$date,
				'upnp:album_art'=> $configData['stopbadge'],
				'upnp:class'	=> 'object.container',
			);
		return $retMediaItems;	
	}

	function _pluginCreateVideoItems($queryData)
	{
		// full re-tool as simple XML seems to discard the media namespace and we wish to surface thumbnail
		// some code bloat but net result - thumbage!!!
		// (sh) 20011-02-19
		$url = $queryData['itemurl'];
		$retMediaItems = array();
		$xml = file_get_contents($url);
		$reader = new XMLReader();
		$reader->XML($xml);
		$elements = array ('title','author','category','pubDate');
		while ($reader->read())
		{
			if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->name == 'itunes:category'))
			{
				if($item['genre']=='') // multiple tags so take the first, has be the most important right!?
					$item['genre'] = $reader->getAttribute('text');
			}
			elseif (($reader->nodeType == XMLReader::ELEMENT) && ($reader->name == 'itunes:image'))
			{
				$item['badge'] = $reader->getAttribute('href'); // use for defaults
			}
			elseif (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'item'))
			{
				do
				{
					$reader->read();
					$name = $reader->name;
					if(in_array($name,$elements))
					{
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT)
							$item[$name] = (string)$reader->value;
					}
					elseif ($name == 'enclosure')
					{
						$item['url'] = $reader->getAttribute('url');
						$item['type'] = $reader->getAttribute('type');
						$item['length'] = $reader->getAttribute('length');
					}
					elseif ($name == 'media:thumbnail')
					{
						$item['thumb'] = $reader->getAttribute('url');
					}
				} while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'item')));
				$data = array(
					'url'	=> $item['url'],
				);
				$dataStr = http_build_query($data, 'pluginvar_');
				$retMediaItems[] = array (
					'id'		=> 'umsp://plugins/revision3/revision3?'.$dataStr,
					'dc:title'	=> $item['title'],
					'res'		=> $item['url'],
					'upnp:artist'	=> $item['author'],
					'upnp:genre'	=> (($item['category']!='')?$item['category']:$item['genre']),
					'upnp:album'	=> $item['title'],
					'upnp:album_art'=> (($item['thumb']!='')?$item['thumb']:$item['badge']),
					'size'		=> $item['length'],
					'dc:date'	=> $item['pubDate'],
					'protocolInfo'	=> 'http-get:*:'.(($item['type']!='')?$item['type']:'video/mp4').':*',
					'upnp:class'	=> 'object.item.videoItem',
				);
				if($queryData['break']==true)
					break;
			}
		}
		return $retMediaItems;	
	}

	function getConfigData()
	{
		$config = file_get_contents('/conf/config');
		if(preg_match('/REV3_EXCLUDE_SHOWS=\'(.+)\'/', $config, $m))$excludes = '|'.strtolower($m[1]).'|';
		// only process archiveed if requested (off by default)
		if(preg_match('/REV3_INCLUDE_ARCHIVE=\'(.+)\'/', $config, $m))$archives = strtoupper($m[1]);
		if(preg_match('/REV3_DAILY_SHOWS=\'(.+)\'/', $config, $m))$daily_shows = strtoupper($m[1]);
		if(preg_match('/REV3_DAILY_SHOWS=\'(.+)\'/', $config, $m))$daily_shows = strtoupper($m[1]);
		if(preg_match('/REV3_SHOW_TODAY=\'(.+)\'/', $config, $m))$show_today = strtoupper($m[1]);
		if(preg_match('/REV3_DEFAULT_RESOLUTION=\'(.+)\'/', $config, $m))$def_resolution = trim($m[1]);
		return array (
			'exclude'	=> $excludes,
			'archives'	=> $archives,
			'dailyshows'	=> $daily_shows,
			'showtoday'	=> $show_today,
			'defaultres'	=> $def_resolution,
			'search'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY0Z-O74N0I/AAAAAAAAAyM/fBRtOzJ6k4A/s200/rev3search.png',
			'badge'		=> 'http://lh3.ggpht.com/_xJcSFBlLg_Y/TQ_-D84OpOI/AAAAAAAAAFg/JFMIuOhXK78/s200/Revision3.png',
			'today'		=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TXULuQl_nZI/AAAAAAAAAdg/8cq0-vameS0/s200/rev3today.png',
			'today2'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TXeIlAX9iPI/AAAAAAAAAe4/JQ2DNavJpj0/s200/rev3Today.png',
			'large'		=> 'http://lh3.ggpht.com/_xJcSFBlLg_Y/TRDekc_vrfI/AAAAAAAAAF0/c-91Brvc4Wo/rev3-want-large.png',
			'medium'	=> 'http://lh3.ggpht.com/_xJcSFBlLg_Y/TRDektn37zI/AAAAAAAAAF4/X8KUbgDKk0U/rev3-want-medium.png',
			'small'		=> 'http://lh3.ggpht.com/_xJcSFBlLg_Y/TRDekz57jEI/AAAAAAAAAF8/XbMZnlpfelE/rev3-want-small.png',
			'stopbadge'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TWOoLmVMr6I/AAAAAAAAAYE/_-d60WDF-rQ/stop-a-cop8.png',
		);
	}

?>
