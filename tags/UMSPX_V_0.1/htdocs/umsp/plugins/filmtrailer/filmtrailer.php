<?php

	/*

	(C) 2011 gpica, shunte after Zoster et al.

	Full credit goes to gpica who posted a FilmTrailer based plug-in to the forum
	I didn't realize this plugin existed until it was mentioned on the inclusion thread
	Whilst this plugin codebase does not contain any of the gpica solution I figure it
	should be a shared credit.

	This FilmTrailer plugin is designed for Zoster's USMP server which runs (amongst others)
	inside the EM7075 and DTS variant.

	Multi-language is supported by scraping the region based content from filmtrailer.com
	You may configure the ordering of the trailer results
	You may also configure the maximum number of trailers to display for a given option

	These are configurable through the webend

	The plug-in will also rerturn additional content beyond the trailers should it be 
	supported for a paticular movie, DVD or Game - this content is fully dynamic and
	may or may not be available for a given region. Note this may return copious 
	options, I counted 179 variations for True Grit on the German site

	Full search is also supported

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	Thank you, and enjoy this plugin.

	*/

	function _pluginMain($prmQuery)
	{
		$queryData = array();
		parse_str($prmQuery, $queryData);
		$configData = getConfigData();
		$items = array();
		if($queryData['search'])
		{
			$items = _pluginCreateCategories($configData);
			exec("sudo chmod 666 /tmp/ir_injection && sleep 2 && sudo echo E > /tmp/ir_injection &");
		}
		elseif(''!=$queryData['category'])
			$items = _pluginCreateCategoryItems($queryData,$configData);
		elseif(''!=$queryData['content'])
			$items = _pluginCreateContentItems($queryData,$configData);
		else
			$items = _pluginCreateCategories($configData);
		return $items;
	}

	function getConfigData()
	{
		$config = file_get_contents('/conf/config');
		if(preg_match('/FILMTRAILER_BASEURL=\'(.+)\'/', $config, $m))$baseurl = $m[1];
		if(preg_match('/FILMTRAILER_MAXTRAILERS=\'(.+)\'/', $config, $m))$maxresult = $m[1];
		if(preg_match('/FILMTRAILER_ORDERBY=\'(.+)\'/', $config, $m))$orderby = $m[1];
		if(preg_match('/FILMTRAILER_DEFAULT_RESOLUTION=\'(.+)\'/', $config, $m))$defaultres = $m[1];
		return array (
			'baseurl'	=> $baseurl,	// no default - user must select via language prompt on WEC
			'maxresult'	=> (($maxresult!='')?$maxresult:10),
			'orderby'	=> (($orderby!='')?$orderby:'Title'),
			'defaultres'	=> (($defaultres!='')?$defaultres:'ASK'),
			'badge'		=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TWgGrr4kqhI/AAAAAAAAAak/ONLrOa8i0H0/s200/filmtrailer-logo-pop.png',
			'searchbadge'	=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TYt733DoPPI/AAAAAAAAAxg/HGde5sy5a-U/s200/filmtrailer-logo-pop-search.png',
			'stopbadge'	=> 'http://lh5.googleusercontent.com/_xJcSFBlLg_Y/TWLvRkeoFhI/AAAAAAAAAXA/-3ms-_TCzSQ/s200/stop-a-cop3.png',
		);
	}

	function _pluginCreateCategories($configData)
	{
		$retMediaItems=array();
		if($configData['baseurl']!='')
		{
			// get category options from the selected region based site
			$html = file_get_contents($configData['baseurl']);
			if(preg_match_all('/<link rel="alternate" type="application\/rss\+xml" title="(?P<title>.*?)" href="(?P<url>.*?)"\/>/',$html,$cats))
			{
				for ($z = 0; $z < count($cats[0]); $z++)
				{
					$data = array (
						'category' => $cats['url'][$z]
					);
					$dataStr = http_build_query($data, 'pluginvar_');
					// titles are in the requested language
					// some site versions have additional menu picks, e.g. German site has several options related to DVD releases
					// **** NOT ALL Of THESE COMBINATIONS HAVE BEEN TESTED ****
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?'.$dataStr,
						'dc:title'	=> html_entity_decode($cats['title'][$z],0,"UTF-8"),
						'upnp:class'	=> 'object.container',
					);
				}
			}
			if(preg_match('|<input class="search" type="text" name="q" value="(?P<search>.*?)"|',$html,$search)===false)
				$search['search'] = 'Search : search with your remote or via this option';
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?'
							.http_build_query(array('search'=>true), 'pluginvar_'),
				'dc:title'	=> makeSafeItemDescription(html_entity_decode($search['search'])),
				'upnp:album_art'=> $configData['searchbadge'],
				'upnp:class'	=> 'object.container',
			);
			unset($html);
		}
		else
		{
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?not=configured',
				'dc:title'	=> 'The plugin is not configured, please configure through the webend and specify your language',
				'upnp:album_art'=> $configData['stopbadge'],
				'upnp:class'	=> 'object.container',
			);
		}
		return $retMediaItems;
	}

	function _pluginCreateCategoryItems($queryData,$configData)
	{
		// uri contains the number of results to return, the default is 30, we'll replace this number
		// with the configured number of trailers; upto 200 trailers are supported via this mechanism
		// the atom feed flavor is a *very* complete feed,  the RSS gives a lot of descriptive detail 
		// but is missing the thumbnail attribute, atom supplies required basics and processes a snap
		$url = $queryData['category'];
		$uri = str_replace('.rss.','.atom.',$url);
		foreach(array('Latest','Next') as $snip){$uri = str_replace($snip.'30',"$snip{$configData['maxresult']}",$uri);}
		$retMediaItems = array();
		$xml = file_get_contents($uri);
		$rss = new SimpleXMLElement($xml);
		foreach ($rss->xpath('//item') as $item) 
		{
			$title = makeSafeItemDescription(html_entity_decode((string)$item->title));
			$desc = html_entity_decode((string)$item->description);
			preg_match('/<img src="(.+)" alt="/',$desc,$thumb);
			$data = array(
				'content'	=> (string)$item->link
			);
			$dataStr = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?'.$dataStr,
				'dc:title'	=> $title,
				'upnp:album_art'=> $thumb[1],
				'dc:date'	=> (string)$item->pubDate,
				'upnp:class'	=> 'object.container',
			);			
		}
		// custom ordering
		if('Date'==$configData['orderby'])
			usort($retMediaItems, 'checkDates');
		return $retMediaItems;
	}

	function _pluginCreateContentItems($queryData,$configData)
	{
		$retMediaItems = array();
		$html = file_get_contents($queryData['content']);
		$tag = rawurlencode($queryData['content']);
		if(preg_match('/<param name="FlashVars" value="file=(.*?)" \/>/',$html,$uri))
		{
			$xml = file_get_contents($uri[1]);
			$rss = new SimpleXMLElement($xml);
			$movie = $rss->xpath('//movie');
			$title = (string)$movie[0]->title;
			$thumb = (string)$movie[0]->poster;
			$clips = $rss->xpath('//clips/clip');
			foreach ($clips as $clip) 
			{
				$attr = $clip->attributes();
				$subtitle = trim((string)$attr['name']);
				$duration = (string)$clip->duration;
				$files = $clip->xpath('//file');
				foreach ($files as $file) 
				{
					$url = (string)$file->url;
					if((strpos(strtolower($url),'.3gp')==false)&&(strpos(strtolower($url),'.flv')==false))	// skip "mobile" and problematic content
					{
						$attr = $file->attributes();
						$size = (string)$attr['size'];
						$dims = (string)$file->transfert;
						if(strpos($url,'.wmv'))
							$type = 'video/x-ms-wmv';
						if(strpos($url,'.flv'))
							$type = '*';//'video/x-flv';	// problematic playback?!?
						if(strpos($url,'.mp4'))
							$type = 'video/mp4';
						else
							$type = '*';
						if(strpos($url,'?')!==false)
							$res = explode('?',$url);
						else
							$res[0] = $url;
						$retMediaItems[] = array (
							'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?item='.$tag,
							'dc:title'	=> $title.((''!=$subtitle)?": $subtitle":'')." ($size $dims)",
							'res'		=> $res[0],
							'duration'	=> $duration,
							'upnp:album'	=> $title,
							'upnp:class'	=> 'object.item.videoItem',
							'upnp:album_art'=> $thumb,
							'protocolInfo' => "get-http:*:$type:*"
						);
					}
				}
			}
 		}
		return $retMediaItems;
	}

	function _pluginSearch($prmQuery)
	{
		$retMediaItems=array();
		if (preg_match('/and dc:(title|album|genre) contains "(.*?)"/', $prmQuery, $searchterm))
		{
			$configData = getConfigData();
			if($configData['baseurl']!='')
			{
				$uri = $configData['baseurl'].'/cinema/search-'.$configData['maxresult'].'/?q='.urlencode($searchterm[2]);
				// slight variation here as we have to scrape the HTML, couldn't find an RSS/Atom flavor of search
				$html = file_get_contents($uri);
				if(preg_match_all('/<a href="(?P<suburi>.+)"><img src="(?P<thumb>.+)" alt="(.+)" title="(?P<title>.+)" width="88" height="120"\/><\/a>/',$html,$results))
				{
					$premierdate=array();
					preg_match('/<table class="list" style="float:(left|right)"><tbody>(?P<datetbl>.*?)<\/table/mis',$html,$data);
					preg_match_all('/<td>(?P<date1>.+)[\-\/](?P<date2>\d\d)[\-\/](?P<date3>.+)<\/td>/',$data[0],$dates);
					for ($z = 0; $z < count($dates[0]); $z++)
					{
						// easier way than this - has to be a better regex?!?
						$temp = substr($dates['date1'][$z].'/'.$dates['date2'][$z].'/'.$dates['date3'][$z],-11);
						if(strpos($temp,'</a>')==false)
							$premierdate[]=$temp;
					}
					for ($z = 0; $z < count($premierdate); $z++)
					{						
						$data = array(
							'content'	=> $configData['baseurl'].$results['suburi'][$z]
						);
						$dataStr = http_build_query($data, 'pluginvar_');
						$retMediaItems[] = array (
							'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?'.$dataStr,
							'dc:title'	=> html_entity_decode($results['title'][$z],0,"UTF-8").' ('.$premierdate[$z].')',
							'upnp:album_art'=> $results['thumb'][$z],
							'dc:date'	=> $premierdate[$z],
							'upnp:class'	=> 'object.container',
						);
					}			
				}
				else
				{
					$retMediaItems[] = array (
						'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?nothing=returned',
						'dc:title'	=> makeSafeItemDescription($searchterm[2].' : 0 results'),
						'upnp:album_art'=> $configData['stopbadge'],
						'upnp:class'	=> 'object.container',
					);
				}
				// custom ordering
				if('Date'==$configData['orderby'])
					usort($retMediaItems, 'checkDates');
			}
			else
			{
				$retMediaItems[] = array (
					'id'		=> 'umsp://plugins/filmtrailer/filmtrailer?not=configured',
					'dc:title'	=> 'The plugin is not configured, please configure through the webend and specify your language',
					'upnp:album_art'=> $configData['stopbadge'],
					'upnp:class'	=> 'object.container',
				);
			}
		}
		return $retMediaItems;
	}

	function checkDates($date1, $date2)
	{
		if (strtotime($date1['dc:date']) == strtotime($date2['dc:date'])) return 0;
		return (strtotime($date1['dc:date']) < strtotime($date2['dc:date']))?1:-1;
	}

	// remove any formats that will cause the UMSP server to hang!!!
	// move this to misc funcs when we're able to
	function makeSafeItemDescription($desc,$stripSpecial=false,$stripTags=true,$safeTags=null)
	{
		// remove usual descriptive "junk"
		$ret = preg_replace('/\s\s+/','',preg_replace(array('/\n/','/\r/','/\n\r/','/\r\n/','/\t/'),array(' ',' ',' ',' ',' '),trim($desc)));
		// strip HTML special escape'd chars as requested
		if($stripSpecial==true)
			$ret = preg_replace('/&#?[a-z0-9]{2,8};/i','',$ret);
		// strip HTML tags as requested, user can pass safe tag set
		if($stripTags==true)
			$ret = strip_tags($ret,$safeTags);
		return $ret;
	}

?>
