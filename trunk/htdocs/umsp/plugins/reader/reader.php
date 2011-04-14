<?php
	### Reader UMSP Plugin
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact 
	###
	### Version: 2.3 - 2011.02.28.
	###   added more generalized
	###   fixed using included fonts
	### Version: 2.1 - 2010.11.01.
	###   added some acceleration
	### Version: 2.0 - 2010.10.30.
	###   Rewritten to support national characters; Caching features
	### Version: 1.0 - 2010.08.09.
	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
	define("LOG_FILE", _getUMSPTmpPath() . '/umsp-log.txt');	
	function _pluginMain($prmQuery = "") {
		// Caching Feature
		$CACHING_TIME = 10;
		shell_exec('find /tmp -maxdepth 1 -name reader_* -mmin +'.$CACHING_TIME.' -exec rm -f {} \;');

		$queryData = array();
		parse_str($prmQuery, $queryData);
		l($queryData);
		$items = Array();
		if (isset($queryData["type"]) && file_exists(dirname(__FILE__)."/wrappers/".$queryData["type"].".php")) {
			require dirname(__FILE__)."/wrappers/".$queryData["type"].".php";
			$items = _pluginGetItem($queryData);
		} else {
			$items = _pluginGetCategories();
		};
		return $items;
	};

	function _pluginProxy($queryData = Array()) {
		if (isset($queryData["type"]) && file_exists(dirname(__FILE__)."/wrappers/".$queryData["type"].".php")) {
			require dirname(__FILE__)."/wrappers/".$queryData["type"].".php";
		};
		_pluginGetItem($queryData);
	};

	function _pluginGetCategories() {
		/*
		  EXAMPLES
		*/
		$CATEGORIES = Array(
			/*
			// INFO: Soon! Be patient! :)
			Array(
				'type'	=> "imap",
				'name'	=> "MAIL Reader",
			),
			*/
			/*
			// INFO: WDLXTV RSS Smartfeed dosn't work at the moment
			Array(
				'type'	=> "rss",
				'name'	=> "WDLXTV Firmware forum",
				'url'	=> "http://forum.wdlxtv.com/smartfeed.php?forum=10&limit=NO_LIMIT&sort_by=postdate_desc&feed_type=RSS2.0&feed_style=BASIC"
			),*/
			Array(
				'type'	=> "rss",
				'name'	=> "天气预报 上海",
				'url'	=> "http://weather.raychou.com/?/detail/58367/v1/rss"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "天气预报 成都",
				'url'	=> "http://weather.raychou.com/?/detail/56294/v1/rss"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "Google 焦点新闻",
				'url'	=> "http://news.google.com/news?pz=1&cf=all&ned=cn&hl=zh-CN&output=rss"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "搜狐 焦点新闻",
				'url'	=> "http://rss.news.sohu.com/rss/focus.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "搜狐 产业经济新闻",
				'url'	=> "http://rss.business.sohu.com/rss/chanjingxinwen.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "搜狐 体育频道热点新闻",
				'url'	=> "http://rss.news.sohu.com/rss/sports.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "搜狐 IT频道",
				'url'	=> "http://rss.news.sohu.com/rss/it.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "Engadget 中国版",
				'url'	=> "http://cn.engadget.com/rss.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "汽车资讯",
				'url'	=> "http://cn.autoblog.com/rss.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "新华网 - 军事新闻",
				'url'	=> "http://rss.xinhuanet.com/rss/mil.xml"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "CNN Europe Edition",
				'url'	=> "http://rss.cnn.com/rss/edition_europe.rss"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "Google News CA",
				'url'	=> "http://news.google.ca/news?pz=1&cf=all&ned=ca&hl=en&output=rss"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "TVProfil - (Cyrilic example)",
				'url'	=> "http://tvprofil.net/rss/?g=9&id=17213"
			),
			/* Some hungarian examples */
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Összes [web]",
				'url'	=> "http://hvg.hu/rss"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Itthon [web]",
				'url'	=> "http://hvg.hu/rss/itthon"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Világ [web]",
				'url'	=> "http://hvg.hu/rss/vilag"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Gazdaság [web]",
				'url'	=> "http://hvg.hu/rss/gazdasag"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG IT | Tudomány [web]",
				'url'	=> "http://hvg.hu/rss/tudomany"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Panoráma [web]",
				'url'	=> "http://hvg.hu/rss/panorama"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Karrier [web]",
				'url'	=> "http://hvg.hu/rss/karrier"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Gasztronómia [web]",
				'url'	=> "http://hvg.hu/rss/gasztronomia"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Helyi érték [web]",
				'url'	=> "http://hvg.hu/rss/helyiertek"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Kultúra [web]",
				'url'	=> "http://hvg.hu/rss/kultura"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Cégautó [web]",
				'url'	=> "http://hvg.hu/rss/cegauto"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Vállalkozó szellem [web]",
				'url'	=> "http://hvg.hu/rss/kkv"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Egészség [web]",
				'url'	=> "http://hvg.hu/rss/egeszseg"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Vélemény [web]",
				'url'	=> "http://hvg.hu/rss/velemeny"
			),
			Array(
				'type'	=> "hvg.hu",
				'name'	=> "HVG Sport [web]",
				'url'	=> "http://hvg.hu/rss/sport"
			),
			Array(
				'type'	=> "rss",
				'name'	=> "Index.hu [RSS]",
				'url'	=> "http://index.hu/24ora/rss/"
			)
		);

		$retItems = Array();
		foreach ($CATEGORIES as $id => $media) {
			$data = Array();
			foreach ($media as $med_name => $med_value) {
				if (!in_array($med_name, Array("name"))) {
					if ($med_name == "url") {
						$data[$med_name] = urlencode($med_value);
					} else {
						$data[$med_name] = $med_value;
					}
				};
			}
			$dataString = http_build_query($data, 'pluginvar_');
			$retItems[] = array (
				'id'			=> 'umsp://plugins/reader?' .str_replace("&", "&amp;", $dataString),
				'upnp:class'	=> 'object.container',
				'dc:title'		=> $media['name'],
			);
		};
		return $retItems;
	};

	function _drawText($text, $writeThisPage = -1, $maxPages = -1, $nbrCurPage = -1) {
		$arrParagraph = Array();
		foreach (explode("\n", $text) as $key=>$val) {
			$val = preg_replace(Array('/[ \t]+/i', '/^[ \t]+/i'), Array(' ', ''), $val);
			switch (true) {
				case (substr($val, 0, strlen("TITLE:")) == "TITLE:"):
					$arrParagraph[] = Array(
						"type"	=>	"title",
						"text"	=>	substr($val, strlen("TITLE:"))
					);
					break;
				case (substr($val, 0, strlen("HEADER:")) == "HEADER:"):
					$arrParagraph[] = Array(
						"type"	=>	"header",
						"text"	=>	substr($val, strlen("HEADER:"))
					);
					break;
				default:
					$arrParagraph[] = Array(
						"type"	=>	"regular",
						"text"	=>	$val
					);
			};
		};
		$cntParagraph = count($arrParagraph);
		for ($ii=1; $ii<=$cntParagraph; $ii++) {
			if (trim($arrParagraph[$cntParagraph-$ii]["text"]) == "") {
				unset($arrParagraph[$cntParagraph-$ii]);
			} else {
				break;
			};
		};

		// Set screen
		$img = @imagecreate(1280, 720);
		//$img = @imagecreatefromjpeg("/osd/image/original_bg.jpg");

		// Get Screen param
		$PX = imagesx($img);
		$PY = imagesy($img);
		$PDIFF = 20;
		$TDIFF = 10;
		$BOTTOMY = 10;

		// Set text-background;
		if ($writeThisPage != -1) {
			imagefilledrectangle ($img, $PDIFF, $PDIFF, $PX - $PDIFF, $PY - $PDIFF - $BOTTOMY, imagecolorallocatealpha($img, 0, 0, 0, 50));
		};

		$fontType = Array(
			"title"		=> Array(
				"file"	=> dirname(__FILE__)."/fonts/arialbd.ttf",
				"size"	=> 30,
				"color"	=> imagecolorallocate($img, 255, 255, 0)),
			"header"	=> Array(
				"file"	=> dirname(__FILE__)."/fonts/arialbd.ttf",
				"size"	=> 25,
				"color"	=> imagecolorallocate($img, 255, 255, 255)),
			"regular"	=> Array(
				"file"	=> dirname(__FILE__)."/fonts/arial.ttf",
				"size"	=> 25,
				"color"	=> imagecolorallocate($img, 242, 242, 242))
		);

		// Calculate picture
		$retPageStr = Array();
		$minX = $PDIFF + $TDIFF;
		$minY = $PDIFF + $TDIFF;
		$maxX = $PX - 2*($PDIFF + $TDIFF);
		$maxY = $PY - $PDIFF - $TDIFF - $BOTTOMY;
		$thisPage = 1;
		$xpos = $minX;
		$ypos = $minY;
		foreach ($arrParagraph as $key=>$curParagraph) {
			$thisFontType = $fontType[$curParagraph["type"]];
			$words = explode(" ", $curParagraph["text"]);
			$curStr = "";
			if (!isset($retPageStr[$thisPage]) && trim($curParagraph["text"]) == "") {
				continue;
			};
			if (trim($curParagraph["text"]) == "") {
				$words = Array("_NEWLINE_");
			};
			$nextTop = 0;
			for ($ii=0; $ii<count($words); $ii++) {
				if ($curStr != "") {
					$prevBbox = $bbox;
				};
				$bbox = imagettfbbox($thisFontType["size"], 0, $thisFontType["file"], $curStr.($curStr != ' '?' ':'').$words[$ii]);
				if ($curStr == "") {
					$prevBbox = $bbox;
				};
				$textWidth_WNW = max(array($bbox[0], $bbox[2], $bbox[4], $bbox[6])) - min(array($bbox[0], $bbox[2], $bbox[4], $bbox[6])); ### Textwidth with next word
				$textHeight = max(array($prevBbox[1], $prevBbox[3], $prevBbox[5], $prevBbox[7]))-min(array($prevBbox[1], $prevBbox[3], $prevBbox[5], $prevBbox[7]));

				if ($curStr != "" && $maxX < $textWidth_WNW) {
					//$xpos += max($prevBbox[0], $prevBbox[6]);
					$ypos -= min($prevBbox[5], $prevBbox[7]);
					if ($curStr != "" && $maxY < $ypos + $textHeight) {
						//$xpos = $minX + max($prevBbox[0], $prevBbox[6]);
						$ypos = $minY - min($prevBbox[5], $prevBbox[7]);
						$thisPage++;
					};
					if (!isset($retPageStr[$thisPage])) {
						$retPageStr[$thisPage] = "";
						switch ($curParagraph["type"]) {
							case "title":
								$retPageStr[$thisPage] .= "TITLE:";
								break;
							case "header":
								$retPageStr[$thisPage] .= "HEADER:";
								break;
						};
					};
					//Write curStr
					if ($curStr != "_NEWLINE_") {
						$retPageStr[$thisPage] .= ($retPageStr[$thisPage] == ""?"":" ").$curStr;
						if ($writeThisPage == $thisPage) {
							imagettftext($img, $thisFontType["size"], 0, $xpos, $ypos, $thisFontType["color"], $thisFontType["file"], $curStr);
						};
					};
					$ypos += max($prevBbox[1], $prevBbox[3]);
					$nextTop = 0;
					$curStr = $words[$ii];
				} else {
					$curStr .= ($curStr == ""?"":" ").$words[$ii];
				};
				if ($writeThisPage < $thisPage && $maxPages != -1 && $writeThisPage != -1) {
					break 2;
				};
			};
			if ($curStr != "") {
				//$xpos += max($prevBbox[0], $prevBbox[6]);
				$ypos -= min($prevBbox[5], $prevBbox[7]);
				if ($curStr != "" && $maxY < $ypos + $textHeight) {
					//$xpos = $minX + max($prevBbox[0], $prevBbox[6]);
					$ypos = $minY - min($prevBbox[5], $prevBbox[7]);
					$thisPage++;
				};
				if (!isset($retPageStr[$thisPage])) {
					$retPageStr[$thisPage] = "";
					switch ($curParagraph["type"]) {
						case "title":
							$retPageStr[$thisPage] .= "TITLE:";
							break;
						case "header":
							$retPageStr[$thisPage] .= "HEADER:";
							break;
					};
				};
				if ($curStr != "_NEWLINE_") {
					$retPageStr[$thisPage] .= ($retPageStr[$thisPage] == ""?"":" ").$curStr;
					if ($writeThisPage == $thisPage) {
						imagettftext($img, $thisFontType["size"], 0, $xpos, $ypos, $thisFontType["color"], $thisFontType["file"], $curStr);
					};
				};
				$ypos += max($prevBbox[1], $prevBbox[3]);
				$nextTop = 0;
			};
			$retPageStr[$thisPage] .= "\n";
			if ($writeThisPage < $thisPage && $maxPages != -1 && $writeThisPage != -1) {
				break 1;
			};
		};
		$cntRetPages = count($retPageStr);
		if ($maxPages != -1) {
			$cntRetPages = $maxPages;
		};
		if ($writeThisPage != -1 && 1 < $cntRetPages) {
			if ($nbrCurPage == -1) {
				$nbrCurPage = $writeThisPage;
			};
			$curStr = ($nbrCurPage == 1?"<> ":"<< ").$nbrCurPage." / ".$cntRetPages.($nbrCurPage == $cntRetPages?" <>":" >>");
			$thisFontType = $fontType["header"];
			$bbox = imagettfbbox($thisFontType["size"], 0, $thisFontType["file"], $curStr);
			$textWidth = max(array($bbox[0], $bbox[2], $bbox[4], $bbox[6])) - min(array($bbox[0], $bbox[2], $bbox[4], $bbox[6]));
			$textHeight = max(array($Bbox[1], $Bbox[3], $Bbox[5], $Bbox[7]))-min(array($Bbox[1], $Bbox[3], $Bbox[5], $Bbox[7]));
			imagettftext($img, $thisFontType["size"], 0, ($PX - $textWidth) / 2, $PY - ($PDIFF + $BOTTOMY + $textHeight)/2, $thisFontType["color"], $thisFontType["file"], str_replace("<>", "", $curStr));
		};

		if ($writeThisPage != -1) {
			//Return the image
			header("Content-type: image/jpeg");
			imagejpeg($img);
			imagedestroy($img);
			return NULL;
		} else {
			//Return titles
			return $retPageStr;
		};
	};

	function _getShortTitle($arrPageStr) {
		if (!is_array($arrPageStr)) {
			return substr(str_replace(Array("HEADER:", "TITLE:", "\r", "\n"), "", $arrPageStr), 0, 200);
		};
		if (1 < count($arrPageStr)) {
			foreach ($arrPageStr as $key=>$val) {
				$arrPageStr[$key] = substr(str_replace(Array("HEADER:", "TITLE:", "\r", "\n"), "", "[".$key."/".count($arrPageStr)."] ".($key != 1?"...":"").$val), 0, 200);
			};
		} else {
			$arrPageStr[1] = substr(str_replace(Array("HEADER:", "TITLE:", "\r", "\n"), "", $arrPageStr[1]), 0, 200);
		};
		return $arrPageStr;
	};

	function _removeHTML($s , $keep = '' , $expand = 'script|style|noframes|select|option'){
		/**///prep the string
		$s = ' ' . $s;

		/**///initialize keep tag logic
		if(strlen($keep) > 0){
			$k = explode('|',$keep);
			for($i=0;$i<count($k);$i++){
				$s = str_replace('<' . $k[$i],'[{(' . $k[$i],$s);
				$s = str_replace('</' . $k[$i],'[{(/' . $k[$i],$s);
			}
		}

		//begin removal
		/**///remove comment blocks
		while(stripos($s,'<!--') > 0){
			$pos[1] = stripos($s,'<!--');
			$pos[2] = stripos($s,'-->', $pos[1]);
			$len[1] = $pos[2] - $pos[1] + 3;
			$x = substr($s,$pos[1],$len[1]);
			$s = str_replace($x,'',$s);
		}

		/**///remove tags with content between them
		if(strlen($expand) > 0){
			$e = explode('|',$expand);
			for($i=0;$i<count($e);$i++){
				while(stripos($s,'<' . $e[$i]) > 0){
					$len[1] = strlen('<' . $e[$i]);
					$pos[1] = stripos($s,'<' . $e[$i]);
					$pos[2] = stripos($s,$e[$i] . '>', $pos[1] + $len[1]);
					$len[2] = $pos[2] - $pos[1] + $len[1];
					$x = substr($s,$pos[1],$len[2]);
					$s = str_replace($x,'',$s);
				}
			}
		}

		/**///remove remaining tags
		while(stripos($s,'<') > 0){
			$pos[1] = stripos($s,'<');
			$pos[2] = stripos($s,'>', $pos[1]);
			$len[1] = $pos[2] - $pos[1] + 1;
			$x = substr($s,$pos[1],$len[1]);
			$s = str_replace($x,'',$s);
		}

		/**///finalize keep tag
		for($i=0;$i<count($k);$i++){
			$s = str_replace('[{(' . $k[$i],'<' . $k[$i],$s);
			$s = str_replace('[{(/' . $k[$i],'</' . $k[$i],$s);
		}

		return trim($s);
	};
	function l()
	{
   $t = debug_backtrace();
   $args = func_get_args();
   ob_start();
   echo basename($t[0]["file"]).":{$t[0]["line"]} > ";
   var_dump($args);
   $data = ob_get_contents();
   ob_end_clean();
   file_put_contents(LOG_FILE,$data,FILE_APPEND);
   if(end($args) === 1) die;
	}
?>