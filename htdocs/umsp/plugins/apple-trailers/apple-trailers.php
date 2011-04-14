<?php

	/*
	(C) 2011 WDLX team, shunte, RMerlin, zoster et. al.

	Apple Trailers plug-in

	Apple Trailers plug-in is designed for Zoster's UMSP server which runs (amongst others) inside 
	the EM7075 and DTS variant.

	Original author cannot be determined, this 2.0 version is a top-down rewrite
	Contributing authors are listed

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	Thank you, and enjoy this plugin.
	*/
	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
	function _pluginMain($prmQuery)
	{
		$queryData = array();
		$configData = getConfigData();
		if (strpos($prmQuery,'&amp;')!==false) $prmQuery=str_replace('&amp;','&',$prmQuery);
		parse_str($prmQuery, $queryData);
		// and away we go...
		if ($queryData['mov_url'] !='')
			$items = _pluginCreateVideoItems($queryData,$configData);
		elseif ($queryData['showuri'] !='')
			$items = _pluginCreateVideoItemsHTML($queryData,$configData);
		elseif ($queryData['search']==true)
		{
			exec("sudo chmod 666 /tmp/ir_injection && sudo echo E > /tmp/ir_injection &");
			$items = _pluginCreateMovieList($configData);
		}
		else
			$items = _pluginCreateMovieList($configData);
		return $items;
	}

	function getConfigData()
	{
		$config = file_get_contents(_getUMSPConfPath() . '/config');
		if(preg_match('/APPLETRAILERS_SHOWDATE=\'(.+)\'/', $config, $m))$showdate = strtoupper($m[1]);
		if(preg_match('/APPLETRAILERS_SHOWSEARCH=\'(.+)\'/', $config, $m))$showsearch = strtoupper($m[1]);
		if(preg_match('/APPLETRAILERS_SORT=\'(.+)\'/', $config, $m))$sort = strtoupper(trim($m[1]));
		if(preg_match('/APPLETRAILERS_DEFAULT_RESOLUTION=\'(.+)\'/', $config, $m))$defaultres = trim($m[1]);
		return array (
			'defaultres'	=> (($defaultres==null)?'':$defaultres),
			'showdate'	=> (($showdate=='')?false:($showdate=='ON')),
			'showsearch'	=> (($showsearch=='')?false:($showsearch=='ON')),
			'sort'		=> (($sort=='')?'ALPHA':$sort),
			'proxy'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/apple-trailers/apple-trailers-proxy.php',
			'search'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY_HmEfJdPI/AAAAAAAAA0o/Pc2m97yHNHs/s200/apple-search.png', 
			'hd1080p'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY-oD_0KZ5I/AAAAAAAAA0E/0q055mWsFdg/s200/apple-hd1080p.png',
			'hd720p'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY-oEDr0IoI/AAAAAAAAA0Q/MO_sGyPHjTQ/s200/apple-hd720p.png',
			'hd480p'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY-oECKSWVI/AAAAAAAAA0I/Ne5HeqjoYFY/s200/apple-hd480p.png',
			'large'		=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY-oERWdM6I/AAAAAAAAA0U/nogCzV-oocY/s200/apple-large.png',
			'medium'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY-oElt0clI/AAAAAAAAA0Y/wYczyTI-hqA/s200/apple-medium.png',
			'small'		=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TY-oEUyL00I/AAAAAAAAA0M/G0F0dPCpZWs/s200/apple-small.png',
			'stopbadge'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TWOoLmVMr6I/AAAAAAAAAYE/_-d60WDF-rQ/stop-a-cop8.png',
		);
	}

	function _pluginSearch($prmQuery)
	{
		$retItems = array();		
		$configData = getConfigData();
		if (preg_match('/and dc:(title|album|genre) contains "(.*?)"/', $prmQuery, $searchterm))
		{
			// use the json search interface to scrape for items
			// http://trailers.apple.com/trailers/home/scripts/quickfind.php?q=
			$uri = 'http://trailers.apple.com/trailers/home/scripts/quickfind.php?q='.urlencode($searchterm[2]);
			$json_str = file_get_contents($uri);
			if('{"error":false,"results":[]}'!=$json_str)	// quick test not empty
			{
				$items = json_decode($json_str,true);
				$baseUrl = 'http://trailers.apple.com';
				if(false==$items['error'])
				{
					foreach($items['results'] as $item)
					{
						# apple site is speedy enough to do this in one step but
						# we  won't push  it, so we'll break  into a  multi-part
						$data = array (
							'showuri'	=> $baseUrl.$item['location'].'includes/playlists/web.inc',
							'title'		=> $item['title'],
						);
						$dataStr = http_build_query($data,'','&amp;');
						$retItems[] = array (
							'id'		=> 'umsp://plugins/apple-trailers/apple-trailers?'.$dataStr,
							'dc:title'	=> $item['title'],
							'upnp:album_art'=> $baseUrl.$item['poster'],
							'dc:date'	=> $item['releasedate'],
							'upnp:class'	=> 'object.container',
						);
					}
				}
			}
		}
		if(empty($retItems))
			$retItems[] = array (
				'id'		=> 'umsp://plugins/apple-trailers/apple-trailers?',
				'dc:title'	=> 'Apologies, search did not return any results',
				'upnp:album_art'=> $configData['stopbadge'],
				'upnp:class'	=> 'object.container',
			);
		unset($configData);
		unset($items);
		return $retItems;
	}

	function makeUriResolution($movUrl,$resolution)
	{
		$baseStr  = '_h640w.mov'; # size = requested large from XML
		return str_replace($baseStr, $resolution, $movUrl);
	}

	function _pluginCreateMovieList($configData)
	{

		# Nodes are currently only parsed by name not parent.
		#
		$reader = new XMLReader();
		$itemsXML = file_get_contents('http://www.apple.com/trailers/home/xml/current.xml');
		$reader->XML($itemsXML);

		while ($reader->read()) {
			if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'movieinfo')) {
				#
				# Read movieinfo child nodes until end
				#
				do {
					$newMovie['id']  = $reader->getAttribute('id');
					$reader->read();
					switch ($reader->localName) {
						case 'title':
							# /records/movieinfo/info/title
							$reader->read();
							if ($reader->nodeType == XMLReader::TEXT) {
								$newMovie['title'] = $reader->value;
							}
						case 'location':
							# /records/movieinfo/poster/xlarge
							# or /records/movieinfo/poster/location
							$reader->read();
							if ($reader->nodeType == XMLReader::TEXT) {
								$newMovie['poster'] = $reader->value;
							}
						case 'large':
							# /records/movieinfo/preview/large
							$reader->read();
							if ($reader->nodeType == XMLReader::TEXT) {
								$newMovie['movurl'] = $reader->value;
							}
						case 'postdate':
							# postdate
							$reader->read();
							if ($reader->nodeType == XMLReader::TEXT) {
								$newMovie['date'] = $reader->value;
							}
					} # end switch
				} while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'movieinfo')));
				#
				# New moveinfo item parsed. Now add as media item:
				#
				$newMovie['title'] .= ($configData['showdate']?" ({$newMovie['date']})":'');
				if(''==$configData['defaultres'])
					$data = array(
						'title'		=> urlencode($newMovie['title']),
						'movie_id'	=> $newMovie['id'],
						'mov_url'	=> $newMovie['movurl'],
					);
				else
					$data = array(
						'itemurl'	=> makeUriResolution($newMovie['movurl'],$configData['defaultres']),
					);
				$dataStr = http_build_query($data,'','&amp;');
				if(''==$configData['defaultres'])
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/apple-trailers/apple-trailers?'.$dataStr,
						'dc:title'	=> trim($newMovie['title']),
						'upnp:album_art'=> $newMovie['poster'],
						'dc:date'	=> $newMovie['date'],
						'episode_ts'	=> strtotime($newMovie['date']),
						'upnp:class'	=> 'object.container',
					);
				else
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/apple-trailers/apple-trailers?'.$dataStr,
						'dc:title'	=> trim($newMovie['title']),
						'upnp:album_art'=> $newMovie['poster'],
						'dc:date'	=> $newMovie['date'],
						'episode_ts'	=> strtotime($newMovie['date']),
						'res'		=> $configData['proxy'].'?'.$dataStr,
						'upnp:class'	=> 'object.item.videoitem',
						'protocolInfo'  => 'http-get:*:video/quicktime:*',
					);
			}
		}

		# handle user requested sort
		switch ($configData['sort'])
		{
			case 'ALPHA':
				usort($retMediaItems,'compareAlpha');
				break;
			case 'DATE':
				usort($retMediaItems,'compareDate');
				break;
			case 'UNSORTED':
				break;
			default:
				break;
		}
		if($configData['showsearch'])
		{
			$searchItem = array();
			$searchItem[] = array (
					'id'		=> 'umsp://plugins/apple-trailers/apple-trailers?search=1',
					'dc:title'	=> 'Search Apple-Trailers : press search on remote or use this option',
					'upnp:album_art'=> $configData['search'],
					'upnp:class'	=> 'object.container',
				);
			$retMediaItems = array_merge($searchItem,$retMediaItems);
		}

		unset($configData);
		unset($reader);
		return $retMediaItems;

	}

	function _pluginCreateVideoItemsHTML($queryData,$configData)
	{
		$retMediaItems = array();
		$showuri = $queryData['showuri'];
		$title = urldecode($queryData['title']);
		try
		{
			$html = file_get_contents($showuri);
		}
		catch (Exception $e)
		{
			$html = null;
		}
		if($html!=null)
		{
			if(preg_match_all(
				'|<li class="(.*?)"><div class="grid2col"><div class="column first"><h3>(?P<cliptitle>.*?)</h3>'
				.'<p>Posted: (?P<postdate>.*?)<br />Runtime: (?P<dur>.*?)</p><p class="hd"><span>HD</span>.*?<img src="(?P<thumb>.*?)".*?<li><h4>Download</h4>'
				.'</li>(?P<items>.*?)</div><!--/grid2col-->|mis',
				$html,$shows,PREG_SET_ORDER)
			)
			{
				foreach($shows as $show)
				{
					if(preg_match_all('|<li class="hd"><a href="(?P<itemurl>.*?)" class="target-quicktimeplayer">(?P<resolution>.*?)<span>HD</span></a></li>|mis',$show['items'],$variants,PREG_SET_ORDER))
					{
						foreach ($variants as $variant)
						{
							$data = array(
								'itemurl'	=> fixItemUrl($variant['itemurl']),
							);
							$dataStr = http_build_query($data, 'pluginvar_');
							$retMediaItems[] = array (
								'id' 		=> 'umsp://plugins/apple-trailers/apple-trailers?'.$dataStr,
								'dc:title' 	=> $title.' '.$show['cliptitle'].' '.trim($variant['resolution']),
								'res'		=> $configData['proxy'].'?'.$dataStr,
								'dc:date'	=> $show['postdate'],
								'episode_ts'	=> strtotime($show['postdate']),
								'duration'	=> numericDuration($show['dur']),
								'upnp:album_art'=> $show['thumb'],
								'upnp:class'	=> 'object.item.videoitem',
								'protocolInfo'  => 'http-get:*:video/quicktime:*',
							);
						}
					}
				}
			}
		}
		unset($variants);
		unset($shows);
		unset($html);
		unset($configData);
		return $retMediaItems;
	}

	function _pluginCreateVideoItems($queryData,$configData)
	{
		$prmMovUrl = $queryData['mov_url'];
		$title = urldecode($queryData['title']);
		$retMediaItems = array();
		# this kinda redundant as if we're here we don't have a default
		$descAppend = ((''==$configData['defaultres'])?': You can set a default resolution through the webend':'');
		#
		# Create variants from $prmMovUrl:
		# 
		$variants = array (
			array(
				'desc'	=> 'Small',
				'ext'	=> '_h320.mov',
				'badge'	=> 'small',
			),
			array(
				'desc'	=> 'Medium',
				'ext'	=> '_h480.mov',
				'badge'	=> 'medium',
			),
			array(
				'desc'	=> 'Large',
				'ext'	=> '_h640w.mov',
				'badge'	=> 'large',
			),
			array(
				'desc'	=> 'HD 480p',
				'ext'	=> '_h480p.mov',
				'badge'	=> 'hd480p',
			),
			array(
				'desc'	=> 'HD 720p',
				'ext'	=> '_h720p.mov',
				'badge'	=> 'hd720p',
			),
			array(
				'desc'	=> 'HD 1080p',
				'ext'	=> '_h1080p.mov',
				'badge'	=> 'hd1080p',
			),
		);
	
		foreach ($variants as $variant)
		{
			$data = array(
				'itemurl'	=> makeUriResolution($prmMovUrl,$variant['ext'])
			);
			$dataStr = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id' 		=> 'umsp://plugins/apple-trailers/apple-trailers?'.$dataStr,
				'dc:title' 	=> $variant['desc'].((''!=$title)?" - $title":'').$descAppend,
				'res'		=> $configData['proxy'].'?'.$dataStr,
				'upnp:album_art'=> $configData[$variant['badge']],
				'upnp:class'	=> 'object.item.videoitem',
				'protocolInfo'  => 'http-get:*:quicktime:*',
			);
		}
		unset($configData);
		unset($variants);
		return $retMediaItems;

	}

	function fixItemUrl($itemUrl)
	{
		return preg_replace(
			array('/_320.mov/','/_480.mov/','/_640w.mov/','/_480p.mov/','/_720p.mov/','/_1080p.mov/'),
			array('_h320.mov','_h480.mov','_h640w.mov','_h480p.mov','_h720p.mov','_h1080p.mov'),
			trim($itemUrl)
		);
	}

	function compareDate($a, $b)
	{
		return(($a['episode_ts']==$b['episode_ts'])?0:(($a['episode_ts']<$b['episode_ts'])?1:-1));
	}

	function compareAlpha($a, $b)
	{
		return strcmp($a['dc:title'],$b['dc:title']);
	}

	function numericDuration ($dur)
	{
		$ret = 0;
		$delem = explode(':',$dur);
		if(count($delem)==2)
			$xply = array(3600,60,1);
		else
			$xply = array(60,1);
		for ($z = 0; $z < count($delem); $z++)
		{
			$ret += $xply[$z] * $delem[$z];
		}
		return $ret;
	}

?>
