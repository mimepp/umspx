<?php
	### UMSP Reader's RSS wrapper
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact
	###
	### Version: 1.4 - 2011.02.28.
	###   modified due generalization
	### Version: 1.3 - 2010.11.01.
	###   added some acceleration
	### Version: 1.2 - 2010.10.30.
	###   Caching features
	### Version: 1.1 - 2010.10.27.
	###   XMLReader -> SimpleXML; id coded in md5
	### Version: 1.0 - 2010.08.09.

	include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
	function _pluginGetItem($queryData) {
		// Set Plugin params
		$prmFeedUrl = urldecode($queryData["url"]);
		$curLink = (isset($queryData["id"])?$queryData["id"]:null);
		$curPage = (isset($queryData["page"])?$queryData["page"]:null);

		$prmFeedUrl = urldecode($prmFeedUrl);
		$fileName = _getUMSPTmpPath() . "/reader_rss_".md5($prmFeedUrl);
		if (file_exists($fileName)) {
			$contentXML = file_get_contents($fileName);
		} else {
			$contentXML = file_get_contents($prmFeedUrl);
		};

		$retArray = _parseRSSContent($prmFeedUrl, $contentXML, $curLink, $curPage);
		file_put_contents($fileName, $contentXML);
		return $retArray;
	};

	function _parseRSSContent($feedUrl, &$content, $selItem, $selPage) {
		$retArray = Array();
		try {
			$simplexml = new SimpleXMLElement($content);
		} catch(Exception $e) {
			echo 'Caught exception: ', $e->getMessage(), "\n";
			return NULL;
		};

		foreach ($simplexml->xpath('//item') as $item) {
			$pages = Array();
			$text = "";

			//Title
			if (isset($item->title)) {
				$title = (string)$item->title;
				$text .= "TITLE:".strip_tags($title)."\r\n\r\n";
			} else {
				$title = "N/A";
			};
			//If cached
			if (isset($item['readerpluginpages'])) {
				$pages = unserialize(utf8_decode((string)$item['readerpluginpages']));
				if (count($pages) == 1) {
					$text = $pages[1];
				};
			} else {
				if (isset($item->description)) {
					$text .= "HEADER:".strip_tags((string)$item->description)."\r\n\r\n";
				};
				$child_content = $item->children('content', TRUE);
				if (isset($child_content->encoded)) {
					$text .= strip_tags(str_replace(Array("<br>", "<br/>"), "\n", (string)$child_content->encoded));
				};
				if (isset($item->text)) {
					$text .= strip_tags((string)$item->text);
				};
			};
			$text = trim($text);
			// Draw OnePage
			if (!is_null($selItem) && $selItem == md5((string)$item->link) && !is_null($selPage)) {
				if (isset($item['readerpluginpages'])) {
					_drawText($pages[$selPage], 1, count($pages), $selPage);
				} else {
					_drawText($text, $selPage);
				};
				break;
			} else {
				// If select an item
				if (!is_null($selItem)) {
					if ($selItem == md5((string)$item->link)) {
						if (empty($pages)) {
							$pages = _drawText($text);
							$item->addAttribute('readerpluginpages', utf8_encode(serialize($pages)));
							$arrChildName = Array();
							foreach ($item->children() as $child) {
								$childName = $child->getName();
								if (!in_array($childName, Array("link", "title"))) {
									$arrChildName[] = $childName;
								};
							};
							foreach ($arrChildName as $key=>$val) {
								unset($item->$val);
							};
						};
						$pages = _getShortTitle($pages);
						for ($ii=1; $ii<=count($pages); $ii++) {
							$data = array(
								'type'	=> 'rss',
								'url'	=> urlencode($feedUrl),
								'id'	=> md5((string)$item->link),
								'page'	=> $ii
							);
							$dataString = http_build_query($data, 'pluginvar_');
							$retArray[] = Array(
								'upnp:class'	=> 'object.item.imageitem',
								'protocolInfo'	=> 'http-get:*:image/jpeg:DLNA.ORG_PN=JPEG_SM',
								'dc:date'		=> date('Y-m-d'),
								'dc:title'		=> html_entity_decode($pages[$ii]),
								'id'			=> "umsp://plugins/reader/reader?".$dataString,
								'res'			=> "http://127.0.0.1/umsp/proxy.php?plugin=reader&amp;".str_replace("&", "&amp;", $dataString)
							);
						};
					};
				} else {
					// Decide: ONE or MORE pages
					if (count($pages) == 0) {
						$pages = Array(1,2);
						if (count(explode("\n", $text)) <= 25 && strlen($text) < 600) {
							$pages = _drawText($text);
							$item->addAttribute('readerpluginpages', utf8_encode(serialize($pages)));
							$arrChildName = Array();
							foreach ($item->children() as $child) {
								$childName = $child->getName();
								if (!in_array($childName, Array("link", "title"))) {
									$arrChildName[] = $childName;
								};
							};
							foreach ($arrChildName as $key=>$val) {
								unset($item->$val);
							};
						};
					};
					// There is only ONE page
					if (count($pages) == 1) {
						$data = array(
							'type'	=> 'rss',
							'url'	=> urlencode($feedUrl),
							'id'	=> md5((string)$item->link),
							'page'	=> 1
						);
						$dataString = http_build_query($data, 'pluginvar_');
						$retArray[] = Array(
							'upnp:class'	=> 'object.item.imageitem',
							'protocolInfo'	=> 'http-get:*:image/jpeg:DLNA.ORG_PN=JPEG_SM',
							'dc:date'		=> date('Y-m-d'),
							'dc:title'		=>	html_entity_decode($title),
							'id'			=>	"umsp://plugins/reader?".str_replace("&", "&amp;", $dataString),
							'res'			=>	"http://127.0.0.1/umsp/proxy.php?plugin=reader&amp;".str_replace("&", "&amp;", $dataString)
						);
					} else {
						$data = Array(
							'type'	=> 'rss',
							'url' => urlencode($feedUrl),
							'id' => md5((string)$item->link)
						);
						$dataString = http_build_query($data, 'pluginvar_');
						$retArray[] = Array(
							'upnp:class'	=> 'object.container',
							'dc:date'		=> date('Y-m-d'),
							'dc:title'		=> html_entity_decode($title),
							'id'			=> "umsp://plugins/reader?".str_replace("&", "&amp;", $dataString)
						);
					};
				};
			};
		};
		$content = $simplexml->asXML();
		return $retArray;
	};
?>