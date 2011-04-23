<?php
	### UMSP Reader's hvg.hu wrapper
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact 
	###
	### Version: 1.2 - 2011.02.28.
	###   modified due generalization
	### Version: 1.1 - 2010.10.30.
	###   Caching features
	### Version: 1.0 - 2010.08.09.
	
	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
	function _pluginGetItem($queryData) {
		// Set Plugin params
		$prmFeedUrl = urldecode($queryData["url"]);
		$curLink = (isset($queryData["id"])?$queryData["id"]:null);
		$curPage = (isset($queryData["page"])?$queryData["page"]:null);

		if (!is_null($curLink)) {
			$curLink = urldecode($curLink);
			$fileName = _getUMSPTmpPath() . "/reader_".md5($curLink);
			if (file_exists($fileName)) {
				$fileContent = file_get_contents($fileName);
				$pages = json_decode($fileContent, true);
			} else {
				// Get content
				$opts = array(
					'http'=>array(
						'method'=>"GET",
						'header'=>
							"Content-Type: text/html; charset=utf-8\r\n".
							"Cookie: seenit=he%20has;\r\n"
					)
				);
				$context = stream_context_create($opts);
				$html = file_get_contents($curLink, false, $context);

				$html = str_replace(Array("\r\n", "\r", "\n"), " ", $html);
				preg_match_all("|<meta property=\"og:title\" content=\"(.*)\"/>.*<div id=\"articleBody0\" class=\"articlecontent\">(.*)<div class=\"box articlemenu\">|U", $html, $out, PREG_SET_ORDER);
				if (isset($out[0][1]) && isset($out[0][2])) {
					$text = "TITLE:".$out[0][1]."\r\n\r\n".$out[0][2];
				};
				$text = _removeHTML($text , $keep = 'p|strong|br' , $expand = 'noscript|script|style|noframes|select|option|table|div');
				$text = preg_replace(Array('/[ \t]+/i', '/^[ \t]+/i', "/> /i", "/ <\//i"), Array(' ', '', ">", "</"), $text);

				$text = preg_replace("/<br[ ]*\/?>/", "\r\n", $text);
				$text = preg_replace("/<p><strong>([^<]*)<\/strong><\/p>/", 'HEADER:\1'."\r\n\r\n", $text);
				$text = preg_replace("/<p>([^<]*)<\/p>/", '\1'."\r\n\r\n", $text);
				$text = strip_tags($text);
				$text = preg_replace("/(\r\n)*$/", "", $text);
				$pages = _drawText($text);
				$fileContent = json_encode($pages);
			};

			// Return OnePage
			if (!is_null($curPage)) {
				if (isset($pages[$curPage])) {
					_drawText($pages[$curPage], 1, count($pages), $curPage);
				} else {
					_drawText("TITLE:ERROR", 1);
				};
				return NULL;
			} else {
				$pages = _getShortTitle($pages);
				for ($ii=1; $ii<=count($pages); $ii++) {
					$data = array(
						'type'	=> 'hvg.hu',
						'url'	=> urlencode($prmFeedUrl),
						'id'	=> urlencode($curLink),
						'page'	=> $ii
					);
					$dataString = http_build_query($data, 'pluginvar_');
					$retArray[] = Array(
						'upnp:class'	=> 'object.item.imageitem',
						'protocolInfo'	=> 'http-get:*:image/jpeg:DLNA.ORG_PN=JPEG_SM',
						'dc:date'		=> date('Y-m-d'),
						'dc:title'		=> html_entity_decode($pages[$ii]),
						'id'			=> "umsp://plugins/reader/reader?".$dataString,
						'res'			=> "http://" . $_SERVER['HTTP_HOST'] . "/umsp/proxy.php?plugin=reader&amp;".$dataString
					);
				};
			};
		} else {
			$fileName = _getUMSPTmpPath() . "/reader_".md5($prmFeedUrl);
			if (file_exists($fileName)) {
				$feedXML = file_get_contents($fileName);
			} else {
				$feedXML = file_get_contents($prmFeedUrl);
			};

			try {
				$simplexml = new SimpleXMLElement($feedXML);
			} catch(Exception $e) {
				echo 'Caught exception: ',  $e->getMessage(), "\n";
				return null;
			};

			$retArray = Array();
			foreach ($simplexml->xpath('//item') as $item) {
				$text = "";

				//Title
				if (isset($item->title)) {
					$title = (string)$item->title;
					$text .= "TITLE:".strip_tags($title)."\r\n\r\n";
				} else {
					$title = "N/A";
				};
				if (isset($item->description)) {
					$text .= "HEADER:".strip_tags((string)$item->description)."\r\n\r\n";
				};
				if (isset($item->encoded)) {
					$text .= strip_tags((string)$item->encoded);
				};

				$data = array(
					'type'	=> 'hvg.hu',
					'url' => urlencode($prmFeedUrl),
					'id' => urlencode((string)$item->link)
				);
				$dataString = http_build_query($data, 'pluginvar_');
				$retArray[] = Array(
					'upnp:class'	=> 'object.container',
					'dc:date'		=> date('Y-m-d'),
					'dc:title'		=> html_entity_decode($title),
					'id'			=> "umsp://plugins/reader/reader?".str_replace("&", "&amp;", $dataString)
				);
			};
			$fileContent = $simplexml->asXML();
		};
		if (!file_exists($fileName)) {
			file_put_contents($fileName, $fileContent);
		};
		return $retArray;
	};
?>