    <?php

        /*

                (C) 2011 monkmad
                http://forum.wdlxtv.com/viewtopic.php?f=53&t=3673
   
                This StageVu plugin is designed for Zoster's USMP server which runs (amongst others)
                inside the EM7075 and DTS variant.
                This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
                In addtion to section 7 of the GPL terms, there is one additional term :
                G) You must keep this header intact when you intend to release modified copies of the program.
   
                Thank you, and enjoy this plugin.

         */


	$GLOBALS['configData'] = getConfigData();
	function writeLog($logMessage) {
	  $loggingEnabled = false;
	  if ($loggingEnabled) {
	    $myFile = "/tmp/stagevu.log";
	    $fh = fopen($myFile, 'a') or die("can't open file");
	    fwrite($fh, date("d/m/y : H:i:s", time()) . " " . $logMessage . "\n");
	    fclose($fh);
	  }
	}

	function multi_implode($glue, $pieces)
	{
		$string='';

		if(is_array($pieces))
		{
			reset($pieces);
			while(list($key,$value)=each($pieces))
			{
				$string.=$glue.multi_implode($glue, $value);
			}
		}
		else
		{
			return $pieces;
		}

		return trim($string, $glue);
	}

    writeLog(__FUNCTION__.": "."Starting StageVu plugin log");

    function _pluginMain($prmQuery) {
      writeLog(__FUNCTION__.": "."Entering _pluginMain function");
      writeLog(__FUNCTION__.": "."prmQuery: $prmQuery");
      $queryData = array();
      parse_str(urldecode($prmQuery), $queryData);
      if ($queryData['destMenu'] =='CategorySort') {
        $items = _pluginCreateCategorySortList($queryData['url']);
        writeLog(__FUNCTION__.": "."Exiting _pluginMain function");
        return $items;
      } else if ($queryData['destMenu'] =='CategoryVideos') {
        $items = _pluginCreateVideoItems($queryData['url'],1);
        writeLog(__FUNCTION__.": "."Exiting _pluginMain function");
        return $items;
      } else if ($queryData['destMenu'] =='Categories') {
        $items = _pluginCreateCategoryList();
        writeLog(__FUNCTION__.": "."Exiting _pluginMain function");
        return $items;
      } else if ($queryData['destMenu'] =='CannedSearches') {
        $items = _pluginCreateCannedSearchList();
        writeLog(__FUNCTION__.": "."Exiting _pluginMain function");
        return $items;
      } else if ($queryData['destMenu'] =='CannedSearchResults') {
        $items = _pluginSearch('(upnp:class derivedfrom "object.item.videoItem") and dc:title contains "'.urldecode($queryData['searchTerm']).'"');
        writeLog(__FUNCTION__.": "."Exiting _pluginMain function");
        return $items;
      } else {
        $items = _pluginCreateTopMenu();
        writeLog(__FUNCTION__.": "."Exiting _pluginMain function");
        return $items;
      }
    }

    function _pluginCreateTopMenu() {
	writeLog(__FUNCTION__.": "."Entering _pluginCreateTopMenu function");
	$data = array(
	  'destMenu' => 'Categories'
	);
	$dataString = urlencode(http_build_query($data, 'pluginvar_'));
	$retMediaItems[] = array (
	  'id' => 'umsp://plugins/stagevu?' . $dataString,
	  'dc:title' => 'Categories',
	  'upnp:class' => 'object.container',
	);

	if (!(is_null($GLOBALS['configData']['cannedSearchList']))) {
		$data = array(
		  'destMenu' => 'CannedSearches'
		);
		$dataString = urlencode(http_build_query($data, 'pluginvar_'));
		$retMediaItems[] = array (
		  'id' => 'umsp://plugins/stagevu?' . $dataString,
		  'dc:title' => 'Canned Searches',
		  'upnp:class' => 'object.container',
		);
	}

	writeLog(__FUNCTION__.": "."Exiting _pluginCreateTopMenu function");
	return $retMediaItems;
    }

    function _pluginCreateCannedSearchList() {
      writeLog(__FUNCTION__.": "."Entering _pluginCreateCannedSearchList function");

      foreach ($GLOBALS['configData']['cannedSearchList'] as $searchTerm) {
        $data = array(
          'searchTerm' => urlencode($searchTerm),
	  'destMenu' => 'CannedSearchResults'
        );
        $dataString = urlencode(http_build_query($data, 'pluginvar_'));
        $retMediaItems[] = array (
          'id' => 'umsp://plugins/stagevu?' . $dataString,
          'dc:title' => $searchTerm,
          'upnp:class' => 'object.container',
        );
      }
      writeLog(__FUNCTION__.": "."Exiting _pluginCreateCannedSearchList function");
      return $retMediaItems;
    }


    function _pluginCreateCategoryList() {
      writeLog(__FUNCTION__.": "."Entering _pluginCreateCategoryList function");
      $categories = array(
        'ANIMATION' => 'Animation',
        'COMEDY' => 'Comedy',
        'FILMS & MOVIES' => 'Films+and+Movies',
        'MUSIC' => 'Music',
        'TV-SHOWS' => 'Television',
        'ART' => 'Art',
        'BLOGS' => 'Blogs',
        'SPORTS' => 'Sports',
        'NEWS & POLITICS' => 'News+and+Politics',
        'GAMES' => 'Games',
        'EDUCATIONAL' => 'Educational',
        'OTHER' => 'Others'
      );


      foreach ($categories as $name => $id) {
        $url = "http://stagevu.com/search?keywords=&category=" . $id . "&perpage=".$GLOBALS['configData']['numItems']."&sortby=StageVuCatSortCriteria&ascdesc=DESC&page=" ;
        $data = array(
          'url' => ($url),
	  'destMenu' => 'CategorySort'
        );
        $dataString = urlencode(http_build_query($data, 'pluginvar_'));
        $retMediaItems[] = array (
          'id' => 'umsp://plugins/stagevu?' . $dataString,
          'dc:title' => $name,
          'upnp:class' => 'object.container',
        );
      }
      writeLog(__FUNCTION__.": "."Exiting _pluginCreateCategoryList function");
      return $retMediaItems;
    }

    function _pluginCreateCategorySortList($url) {
      writeLog(__FUNCTION__.": "."Entering _pluginCreateCategorySortList function");
      $categories = array(
        'Sort by Ratings' => 'ratings',
        'Sort by Views' => 'views',
        'Sort by Relevance' => 'relevance'
      );



      foreach ($categories as $name => $id) {
        $sorturl = str_replace('StageVuCatSortCriteria',$id,$url) ;
        $data = array(
          'url' => ($sorturl),
	  'destMenu' => 'CategoryVideos'
        );
        $dataString = urlencode(http_build_query($data, 'pluginvar_'));
        $retMediaItems[] = array (
          'id' => 'umsp://plugins/stagevu?' . $dataString,
          'dc:title' => $name,
          'upnp:class' => 'object.container',
        );
      }
      writeLog(__FUNCTION__.": "."Exiting _pluginCreateCategorySortList function");
      return $retMediaItems;
    }

    function _pluginCreateVideoItems($url,$pageCount) {
	  writeLog(__FUNCTION__.": "."Entering _pluginCreateVideoItems function");
	  writeLog(__FUNCTION__.": "."url: $url");
	  writeLog(__FUNCTION__.": "."pageCount: $pageCount");
      $retMediaItems = array();
      for ($i = 1; $i <= $pageCount; $i++) {
        writeLog(__FUNCTION__.": "."Page iteration: $i");
	array_splice($retMediaItems, count($retMediaItems), 0, stageVuGetVideosList($url.$i));
      }
      writeLog(__FUNCTION__.": "."Exiting _pluginCreateVideoItems function");
      return $retMediaItems;
    }

function _pluginSearch($prmQuery) {
    preg_match('/and dc:title contains "(.*?)"/', $prmQuery, $searchstring);
    if ( isset($searchstring[1]) ) {
        $searchUrl = "http://stagevu.com/search?keywords=" . urlencode($searchstring[1]) ."&category=&perpage=".$GLOBALS['configData']['numItems']."&sortby=".$GLOBALS['configData']['srchSort']."&ascdesc=DESC&page=";
        $items = _pluginCreateVideoItems($searchUrl,1);
        return $items;
    } else {
        return null;
    }
}

function stageVuGetVideosList($stageVuSearchLink) {
	$ctx = stream_context_create(array(
	    'http' => array(
		'timeout' => 60,
		'user_agent' => 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.13) Gecko/20101206 Ubuntu/10.04 (lucid) Firefox/3.6.13'
		)
	    )
	);
	//$html = file_get_contents('http://stagevu.com/search?keywords=&category=Films+and+Movies&perpage=5&sortby=relevance&ascdesc=DESC&page=1',0,$ctx);
	$html = file_get_contents($stageVuSearchLink,0, $ctx);

	$dom = new DOMDocument();
	$dom->loadHTML($html);

	$xpath = new DOMXPath($dom);

	$tags = $xpath->query('//div[@class="result1"]/div[@class="resultcont"]|//div[@class="result2"]/div[@class="resultcont"]');
	foreach ($tags as $tag) {
	    $link = $tag->getElementsByTagName( "a" );
	    $title = $link->item(0)->nodeValue;
	    $url = $link->item(0)->getAttribute('href');

	    $img = $tag->getElementsByTagName( "img" );
	    $imgsrc = $img->item(0)->getAttribute('src');

	    $p = $tag->getElementsByTagName( "p" );
	    $ptext = $p->item(0)->nodeValue;
	    $videoInfo = stageVuGetVideoInfo($url);

	    $data = array(
	    'mov_url' => $videoInfo["videoURL"]
	    );
	    $dataString = urlencode(http_build_query($data, 'pluginvar_'));
            $retMediaItems[] = array (
              'id' => 'umsp://plugins/stagevu?' . $dataString,
              'dc:title' => '['.$videoInfo["duration"].'] '.$title,
	      'desc' => $ptext,
              'res' => $videoInfo["videoURL"],
	      'duration' => '2:10:20',
              'upnp:class' => 'object.item.videoitem',
              'upnp:album_art' => $imgsrc,
              'upnp:length' => '2:10:20',
              'protocolInfo' => 'http-get:*:*:*'
            );
	}
	return $retMediaItems;
}

function stageVuGetVideoInfo($videoPageURL) {
	$return = array("videoURL" => "", "duration" => "");
	$url = "not found";
	$length = "0:0";
	$html = file_get_contents($videoPageURL);
	$dom = new DOMDocument();
	$dom->loadHTML($html);

	$xpath = new DOMXPath($dom);

	$tags = $xpath->query('//div[@id="vidbox"]/div/div/object');
	foreach ($tags as $tag) {
	    $link = $tag->getElementsByTagName( "embed" );
	    $url = $link->item(0)->getAttribute('src');
	    break;
	}

	$tags = $xpath->query('//div[@id="infocontent"]/table/tr/td');
	foreach ($tags as $tag) {
	    $length = $tag->nodeValue;
	    if (preg_match('/[0-9]+[:]+[0-9]+/',$length)) {
		break;
	    }
	}

	$return["videoURL"] = $url;
	$return["duration"] = $length;
	return $return;
}

function getConfigData()
{
        $config = file_get_contents('/conf/config');
        if(preg_match('/STAGEVU_SEARCH_SORT=\'(.+)\'/', $config, $m)) {
                $srchSort = strtolower($m[1]);
	} else {
		$srchSort = 'views';
	}
        if(preg_match('/STAGEVU_NUMBER_OF_ITEMS=\'(.+)\'/', $config, $m)) {
                $numItems = $m[1];
	} else {
		$numItems = '7';
	}
        if(preg_match('/STAGEVU_CANNED_SEARCH_FILE=\'(.+)\'/', $config, $m)) {
                if (is_file($m[1])) {
			if (is_readable($m[1])) {
			    $cannedSearchList = file($m[1], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
			} else {
			    $cannedSearchList = null;
			}
		} else {
			$cannedSearchList = null;
		}

	} else {
		$cannedSearchFile = null;
	}
        return array (
                'srchSort'      	=> $srchSort,
                'numItems'      	=> $numItems,
		'cannedSearchList'	=> $cannedSearchList,
                'badge'         	=> 'http://stagevu.com/img/white/newtitle.png',
        );
}


	writeLog(__FUNCTION__.": "."Ending StageVu plugin log");
    ?>
