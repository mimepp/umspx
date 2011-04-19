<?php

function _pluginMain($prmQuery) {
	$queryData = array();
	parse_str($prmQuery, $queryData);
	if ($queryData['station_id'] !='') {
		$items = _pluginCreateStreamList($queryData['station_id']);
		return $items;
	} elseif ($queryData['genre'] !='') {
		$items = _pluginCreateStationList($queryData['genre']);
		return $items;
	} else {
		$items = _pluginCreateGenreList();
		return $items;
	} # end if
} # end function

function _pluginSearch($prmQuery) {
    preg_match('/and dc:title contains "(.*?)"/', $prmQuery, $searchstring);
    if ( isset($searchstring[1]) ) {
        $items = _pluginCreateStationList(urlencode($searchstring[1]), 'search');
        return $items;
    } else {
        return null;
    }
}

function _pluginCreateGenreList() {
	$reader = new XMLReader();
	$genrelistXML = shell_exec('/usr/bin/curl -s -e http://www.shoutcast.com/Internet-Radio/ http://api.shoutcast.com/legacy/genrelist\?k\=so1N15vhCB78Z6k4');
	$reader->XML($genrelistXML);
	while ($reader->read()) {
		if ($reader->nodeType == XMLReader::ELEMENT) {
			if ($reader->localName == 'genre') {
				$genreName = $reader->getAttribute('name');
				$data = array(
					'genre' 	=> $genreName,
				);
				$dataString = http_build_query($data, 'pluginvar_');
				$retMediaItems[] = array (
					'id' 		=> 'umsp://plugins/shoutcast?' . $dataString,
					'dc:title' 	=> $reader->getAttribute('name'),
					'upnp:class'=> 'object.container',
				);
			} # end if
		} # end if
	} #end while
	return $retMediaItems;
} # end function


function _pluginCreateStationList($prmValue, $prmType = 'genre') {
	$reader = new XMLReader();
	$stationlistXML = shell_exec('/usr/bin/curl -s -e http://www.shoutcast.com/Internet-Radio/ http://api.shoutcast.com/legacy/genrelist\?k\=so1N15vhCB78Z6k4\&' . $prmType . '=' . $prmValue);
	$reader->XML($stationlistXML);
	while ($reader->read()) {
		if ($reader->nodeType == XMLReader::ELEMENT) {
			if ($reader->localName == 'station') {
				# For node value:
				# $reader->read();
				# if ($reader->nodeType == XMLReader::TEXT) {
				$data = array(
					'station_id' => $reader->getAttribute('id'),
				);
				$dataString = http_build_query($data, 'pluginvar_');
				$retMediaItems[] = array (
					'id' 			=> 'umsp://plugins/shoutcast?' . $dataString,
					'dc:title' 		=> $reader->getAttribute('name'),
					'upnp:class'	=> 'object.container',
				);
			} # end if
		} # end if
	} #end while
	return $retMediaItems;	
} # end function


function _pluginCreateStreamList($prmID) {
	$proxyUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/shoutcast-proxy.php';
	$streamlistPLS = file_get_contents('http://yp.shoutcast.com/sbin/tunein-station.pls?id=' . $prmID);
#	$tmpData = parse_ini_string($streamlistXML, false, INI_SCANNER_RAW); # TODO: dont use until PHP5.3
	$tmpData = _my_parse_ini_string($streamlistPLS);
	foreach ($tmpData as $key => $val) {
		$tmpData[strtolower($key)] = $val;
	} # end foreach
	$plsData = $tmpData['playlist'];
	foreach ($plsData as $key => $val) {
		$plsData[strtolower($key)] = $val;
	} # end foreach
	$streamCount = $plsData['numberofentries'];
	for ($i = 1; $i <= $streamCount; $i++) {
		$data = array(
			'stream_url' => $plsData['file'. $i],
		);
	$dataString = http_build_query($data, 'var_');
	$encDataString = htmlentities($dataString);
		$retMediaItems[] = array (
			'id' 			=> 'umsp://plugins/shoutcast?stream=' . $i,
			'dc:title' 		=> $plsData['title'. $i],
			'res'			=> $proxyUrl.'?'. $encDataString,
			'upnp:class'	=> 'object.item.audioitem',
			'upnp:album_art'=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/media/generic.jpg',
			'protocolInfo'	=> 'http-get:*:audio/mpeg:*',
			'upnp:album'	=> 'Powered by',
			'upnp:artist'	=> 'WWW.SHOUTCAST.COM',
		);
	} # end for
	return $retMediaItems;
} # end function

?>
