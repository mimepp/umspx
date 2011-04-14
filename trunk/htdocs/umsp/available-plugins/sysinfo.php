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
		case 'disk-usage':
			$resultString = shell_exec('df -h');
			break;
		case 'cpu-usage':
			$resultString = shell_exec('top -n1');
			break;
		case 'processes':
			$resultString = shell_exec('ps');
			break;
		case 'mount':
			$resultString = shell_exec('mount');
			break;
		case 'blkid':
			$resultString = shell_exec('blkid');
			break;
	} # end switch
	$splitString = explode("\n", $resultString);
	$retItems = _createMessageItems($splitString);
	return $retItems;
} # end function
	

function _pluginCreateMenuContent() {
	$menuEntries = array(
		array(
			'menuEntryTitle' => '-- CPU Usage --',
			'data'			=> array(
				'command' 		=> 'cpu-usage',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Disk Usage --',
			'data'			=> array(
				'command' 		=> 'disk-usage',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Processes --',
			'data'			=> array(
				'command' 		=> 'processes',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Mount Table --',
			'data'			=> array(
				'command' 		=> 'mount',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Block Device ID --',
			'data'			=> array(
				'command' 		=> 'blkid',
				'rndtag' 		=> mt_rand(),
			),
		),			
	);	
	foreach ($menuEntries as $menuEntry) {
		$newItem = array();
		$dataString = http_build_query($menuEntry['data'], 'var_');
		$encDataString = htmlentities($dataString);
		$newItem['id']			= 'umsp://plugins/sysinfo/' . '?' . $encDataString;
		$newItem['parentID']	= 'umsp://plugins/sysinfo/';
		$newItem['dc:title']	= $menuEntry['menuEntryTitle'];
		$newItem['upnp:class']	= 'object.container';
		$retItems[] = $newItem;
	}
	return $retItems;
} # end function

?>
