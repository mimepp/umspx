<?php

function _pluginMain($prmQuery) {
	$queryData = array();
	parse_str($prmQuery, $queryData);
	if (!isset($queryData['command'])) {
		$items = _pluginCreateMenuContent();
	} else {
		$items = _pluginExecuteCommand($queryData['command']);
	} # end if

	return $items;
} # end function


function _pluginExecuteCommand($prmCommand) {
	switch ($prmCommand) {
		case 'Eject-Drive':
			shell_exec('sudo eject -T /dev/sr0 || sudo cd.eject /dev/sr0;');
			break;
		case 'Browse-Drive':
			shell_exec('sudo cd.mount /dev/sr0');
			$files = _localGetDirList('/tmp/optical-drive/');
//			$retItems = _localFilterByType($files, $queryData['filter_by_type']);
	                $retItems = _localGetFileAttributes($files);  
			break;
	} # end switch
	return $retItems;
} # end function
	

function _pluginCreateMenuContent() {
	$menuEntries = array(
/*		array(
			'menuEntryTitle' => 'Browse Optical Drive',
			'data'			=> array(
				'command' 		=> 'Browse-Drive',
				'rndtag' 		=> mt_rand(),
			),
			'upnp:album_art'=> '/osd/image/optical.thumb',
		),
*/		array(
			'menuEntryTitle' => 'Eject Drive',
			'data'			=> array(
				'command' 		=> 'Eject-Drive',
				'rndtag' 		=> mt_rand(),

			),
			'upnp:album_art'=> '/osd/image/optical-eject.thumb',
		),
	);	
	foreach ($menuEntries as $menuEntry) {
		$newItem = array();
		$dataString = http_build_query($menuEntry['data'], 'var_');
		$encDataString = htmlentities($dataString);
		$newItem['id']			= 'umsp://plugins/optical-drive/' . '?' . $encDataString;
		$newItem['parentID']	= 'umsp://plugins/optical-drive/';
		$newItem['dc:title']	= $menuEntry['menuEntryTitle'];
		$newItem['upnp:class']	= 'object.container';
		$newItem['upnp:album_art'] = $menuEntry['upnp:album_art'];
		$retItems[] = $newItem;
	}
	return $retItems;
} # end function

?>



