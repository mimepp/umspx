<?php

# This version checks the QT files. But Apple/Akamai doesn't seem to like the short interval.

function _pluginMain($prmQuery) {
	$queryData = array();
	parse_str($prmQuery, $queryData);
	if ($queryData['mov_url'] !='') {
		$items = _pluginCreateVideoItems($queryData['mov_url']);
		return $items;
	} else {
		$items = _pluginCreateMovieList();
		return $items;
	} # end if
} # end function

function _pluginCreateMovieList() {
	# Nodes are currently only parsed by name not parent.
	#
	$reader = new XMLReader();
	$stationlistXML = file_get_contents('http://www.apple.com/trailers/home/xml/current.xml');
	$reader->XML($stationlistXML);
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
						} # end if
					case 'location':
						# /records/movieinfo/poster/xlarge
						# or /records/movieinfo/poster/location
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$newMovie['poster'] = $reader->value;
						} # end if
					case 'large':
						# /records/movieinfo/preview/large
						$reader->read();
						if ($reader->nodeType == XMLReader::TEXT) {
							$newMovie['movurl'] = $reader->value;
						} # end if
				} # end switch
			} while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'movieinfo')));
			#
			# New moveinfo item parsed. Now add as media item:
			#
			$data = array(
				'movie_id'	=> $newMovie['id'],
				'mov_url'	=> $newMovie['movurl']
			);
			$dataString = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id' 			=> 'umsp://plugins/apple-trailers/apple-trailers?' . $dataString,
				'dc:title' 		=> $newMovie['title'],
				'upnp:class'	=> 'object.container',
				'upnp:album_art'=> $newMovie['poster'],
			);
		} # end if
	} #end while
	return $retMediaItems;	
} # end function

function _pluginCreateVideoItems($prmMovUrl) {
	#
	# Apple-Trailers needs a special proxy script that inserts a Quicktime User-Agent header.
	# Make sure that proxy is reachable:
	$proxyUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/apple-trailers/apple-trailers-proxy.php';
	#
	# Create variants from $prmMovUrl:
	# 
	$baseStr  = '_h640w.mov';
	$variants[] = array('desc' => 'Small',		'ext' => '_h320.mov');
	$variants[] = array('desc' => 'Medium',		'ext' => '_h480.mov');
	$variants[] = array('desc' => 'Large',		'ext' => '_h640w.mov');
	$variants[] = array('desc' => 'HD 480p',	'ext' => '_h480p.mov');
	$variants[] = array('desc' => 'HD 720p',	'ext' => '_h720p.mov');
	$variants[] = array('desc' => 'HD 1080p',	'ext' => '_h1080p.mov');
	
	foreach ($variants as $variant) {
		$itemURL = str_replace($baseStr, $variant['ext'], $prmMovUrl);
		$isValidFile = _quicktimeCheck($itemURL);
		if ($isValidFile) {
			$data = array(
				'itemurl'	=> $itemURL,
			);
			$dataString = http_build_query($data, 'pluginvar_');
			$retMediaItems[] = array (
				'id' 			=> 'umsp://plugins/apple-trailers/apple-trailers?' . $dataString,
				'dc:title' 		=> $variant['desc'],
				'res'			=> $proxyUrl.'?'.$dataString,
				'upnp:class'	=> 'object.item.videoitem',
				'protocolInfo'  => 'http-get:*:quicktime:*',
			);
		} # end if
	} # end foreach
	return $retMediaItems;
} # end function

function _quicktimeCheck($prmURL) {
	$parsedURL = parse_url($prmURL);
	$itemHost = $parsedURL['host'];
	$itemPath = $parsedURL['path'];
	$isValidFile = false;
	$fp = fsockopen($itemHost, 80, $errno, $errstr, 30);
	if (!$fp) {
		echo "$errstr ($errno)<br />\n";
	} else {
		$out  = 'HEAD '. $itemPath .' HTTP/1.1' ."\r\n";
		$out .= 'User-Agent: QuickTime.7.6.5 (qtver=7.6.5;os=Windows NT 5.1Service Pack 3)' ."\r\n";
		$out .= 'Host: ' . $itemHost ."\r\n";
		$out .= 'Cache-Control: no-cache' ."\r\n";
		$out .= 'Connection: close' ."\r\n";
		$out .= "\r\n";
		fwrite($fp, $out);
		$headerpassed = false;
		while (($headerpassed == false) && (!feof($fp))) {
			$line = fgets($fp);
			if (stristr($line, 'video/quicktime')) {
				$isValidFile = true;
			} # end if
			if ($line == "\r\n") {
				$headerpassed = true;
			} # end if
		} # end while
		fclose($fp);
	} # end if
	return $isValidFile;
} # end function


?>
