<?php

# import $fileTypesByExt
include_once 'file-types.php';


function _localMain($prmPath, $prmQuery) {
	$queryData = array();
	parse_str($prmQuery, $queryData);
	if ($queryData['showmenu'] == 'true') {
		$items = _localCreateMenuContent($prmPath);
	} else {
		switch ($queryData['flat_view']) {
			case 'dirs':
				$files = _localGetDirListTreeDirs($prmPath);
				break;
			case 'files':
				$files = _localGetDirListTreeFiles($prmPath);
				break;
			case 'dirsfiles':
				$files = _localGetDirListTreeDirsAndFiles($prmPath);
				break;
			default:
				$files = _localGetDirList($prmPath);
		} # end switch
		if ($queryData['filter_by_type'] != '') {
			$files = _localFilterByType($files, $queryData['filter_by_type']);
		} # end if
		$fileItems = _localGetFileAttributes($files);
		if ($queryData['sort_by'] != '') {
			$fileItems = _sortItems($fileItems, $queryData['sort_by']);
		} else {
			# default sort by title
			$fileItems = _sortItems($fileItems, 'title');
		} # end if
		$menuContainer = _localCreateMenuContainerItem($prmPath);
		$items = array_merge($menuContainer, $fileItems);
	} # end if
	return $items;
} # end function


function _localFilterByType($prmDirList, $prmFilter) {
	global $fileTypesByExt;
	switch ($prmFilter) {
		case 'audio':
			$strItemFilter = 'object.item.audioItem';
			break;
		case 'image':
			$strItemFilter = 'object.item.imageItem';
			break;
		case 'video':
			$strItemFilter = 'object.item.videoItem';
			break;
	} # end switch
	foreach ($prmDirList as $path) {
		if (is_file($path)) {
			# is file
			$newItem = array();
			$arrPathInfo = pathinfo($path);
			$fileExt = $arrPathInfo['extension'];
			$arrFileType = $fileTypesByExt[strtolower($fileExt)];
			if ($arrFileType['class'] == $strItemFilter) {
				$retArr[] = $path;
			} # end if
		} # end if
	} # end foreach
} # end function
	

function _localGetDirList($prmDirPath){
	# TODO: combine GetDirList functions
	$scanList = array_diff( scandir( $prmDirPath ), Array( '.', '..' ) );
	foreach ($scanList as $item) {
		# TODO: not really elegant:
		if ($prmDirPath == '/') {
			$retDirList[] = $prmDirPath . $item;
		} else {
			$retDirList[] = $prmDirPath . '/' . $item;
		}
	}
	return $retDirList;
}

function _localGetDirListTreeFiles($prmDirPath, $prmIsSubTree = false){
	# TODO: combine GetDirList functions
	static $counter = 0;
	static $retDirList = array();
	$dirs = array_diff( scandir( $prmDirPath ), Array( '.', '..' ) );
	foreach ($dirs as $d) {
		if (is_dir($prmDirPath.'/'.$d)){
			# is dir -> recurse
			$recurseDir = _localGetDirListTreeFiles( $prmDirPath.'/'.$d, true);
		} else {
			# is file
			$retDirList[] = $prmDirPath.'/'.$d;
		}
	}
	if ($prmIsSubTree) {
		return $recurseDir;
	} else {
		return $retDirList;
	}
} # end function


function _localGetDirListTreeDirs($prmDirPath, $prmIsSubTree = false){
	# TODO: combine GetDirList functions
	static $counter = 0;
	static $retDirList = array();
	$dirs = array_diff( scandir( $prmDirPath ), Array( '.', '..' ) );
	foreach( $dirs as $d ){
		if (is_dir($prmDirPath.'/'.$d)){
			# is dir -> recurse
			$retDirList[] = $prmDirPath.'/'.$d;
			$recurseDir = _localGetDirListTreeDirs( $prmDirPath.'/'.$d, true);
		}
	}
	echo ++$counter . "\r\n";
	if ($prmIsSubTree) {
		return $recurseDir;
	} else {
		return $retDirList;
	}
} # end function

function _localGetDirListTreeDirsAndFiles($prmDirPath, $prmIsSubTree = false){
	# TODO: combine GetDirList functions
	static $counter = 0;
	static $retDirList = array();
	$dirs = array_diff( scandir( $prmDirPath ), Array( '.', '..' ) );
	foreach( $dirs as $d ){
		if (is_dir($prmDirPath.'/'.$d)){
			# is dir -> recurse
			$retDirList[] = $prmDirPath.'/'.$d;
			$recurseDir = _localGetDirListTreeDirsAndFiles( $prmDirPath.'/'.$d, true);
		} else {
			# is file
			$retDirList[] = $prmDirPath.'/'.$d;
		}
	}
	echo ++$counter . "\r\n";
	if ($prmIsSubTree) {
		return $recurseDir;
	} else {
		return $retDirList;
	}
} # end function


function _localGetFileAttributes($prmDirList){
	# TODO: check slashes
	global $fileTypesByExt;
	foreach ($prmDirList as $path) {
		if (is_file($path)) {
			# is file
			$newItem = array();
			$arrPathInfo = pathinfo($path);
			$fileParentDir = $arrPathInfo['dirname']; # has no trailing slash
			$fileName = $arrPathInfo['basename'];	# name.ext
			$fileExt = $arrPathInfo['extension'];	# ext
			$fileNameNoExt = $arrPathInfo['filename'];	# name
			$arrFileType = $fileTypesByExt[$fileExt];
			if ($fileParentDir == '/') {
				$fileNameString = $fileName;
				$fileNameNoExtString = $fileNameNoExt;
			} else {
				$fileNameString = '/' . $fileNameString;
				$fileNameNoExtString = '/' . $fileNameNoExt;
			}
			$newItem['id']				= 'umsp://local' . $fileParentDir . $fileNameString;
			$newItem['parentID']		= 'umsp://local' . $fileParentDir;
			$newItem['dc:title']		= $fileName;
			$newItem['dc:date']			= date("c",filemtime($path));
			$newItem['upnp:class']		= (isset($arrFileType['class'])) ? $arrFileType['class'] : 'object.item.unknownItem' ;
			$newItem['upnp:album_art']	= $fileParentDir . $fileNameNoExtString . '.jpg';
			$newItem['res']				= 'file://'. $path;
			#$newItem['size']			= filesize($path);
			$newItem['size']			= trim(exec ('stat -c%s ' . escapeshellarg($path)));
			$newItem['protocolInfo']	= $arrFileType['mime'];
			$retArr[] = $newItem;
		} elseif (is_dir($path)) {
			# is dir
			$newItem = array();
			$arrPathInfo = pathinfo($path);
			$dirParentDir = $arrPathInfo['dirname']; # has no trailing slash
			$dirName = $arrPathInfo['basename'];
			if ($dirParentDir == '/') {
				$dirNameString = $dirName;
			} else {
				$dirNameString = '/' . $dirName;
			}
			$newItem['id']				= 'umsp://local' . $dirParentDir . $dirNameString;
			$newItem['parentID']		= 'umsp://local' . $dirParentDir;
			$newItem['dc:title']		= $dirName;
			$newItem['dc:date']			= date('c',filemtime($path));
			$newItem['upnp:class']		= 'object.container';
			$newItem['upnp:album_art']	= $dirParentDir . $dirNameString . '/folder.jpg';
			$retArr[] = $newItem;
		} else {
			# not a file or dir ??
		} # end if
	} # end foreach
	return $retArr;
} # end function


function _sortItems($prmItemArray, $prmSortBy, $prmSortDesc = 0) {
	$sortOrder = ($prmSortDesc == 0) ? 'asc' : 'desc';
	switch ($prmSortBy) {
		case 'id':
			$sortedItems = _php_multisort($prmItemArray, array(array('key'=>'id', 'sort'=>$sortOrder), array('key'=>'title', 'sort'=>'asc')));
			break;
		case 'title':
			$sortedItems = _php_multisort($prmItemArray, array(array('key'=>'dc:title', 'type'=>'string', 'sort'=>$sortOrder), array('key'=>'upnp:class', 'sort'=>'asc')));
			break;
		case 'size':
			$sortedItems = _php_multisort($prmItemArray, array(array('key'=>'size', 'type'=>'numeric', 'sort'=>$sortOrder), array('key'=>'title', 'sort'=>'asc')));
			break;
		case 'date':
			$sortedItems = _php_multisort($prmItemArray, array(array('key'=>'dc:date', 'sort'=>$sortOrder), array('key'=>'title', 'sort'=>'asc')));
			break;
		case 'shuffle':
			$sortedItems = $prmItemArray;
			shuffle($sortedItems); # shuffle is by ref
			break;
	} # end switch
	return $sortedItems;
} # end function


function _moveContainersUp($prmItemArray) {
	$tmpCont = array();
	$tmpRest = array();
	foreach ($prmItemArray as $item) {
		if ($item['upnp:class'] == 'object.container') {
			$tmpCont[] = $item;
		} else {
			$tmpRest[] = $item;
		} # end if
	} # end for each
	$retItems = array_merge($tmpCont, $tmpRest);
	return $retItems;
} # end function


function _formatBytes($bytes, $precision = 1) {
	$units = array('B', 'KB', 'MB', 'GB', 'TB');
	$bytes = max($bytes, 0);
	$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
	$pow = min($pow, count($units) - 1);
	$bytes /= pow(1024, $pow);
	return round($bytes, $precision) . ' ' . $units[$pow];
} # end function


function _php_multisort($data,$keys){
	##  PHPMultiSort  ##
	// Takes:
	//      $data,  multidim array
	//      $keys,  array(array(key=>col1, sort=>desc), array(key=>col2, type=>numeric)) 
	//      defaults = sort=>asc, type=>regular
	//      additional: sort=>desc, type=>string / type=>numeric

	// List As Columns
	foreach ($data as $key => $row) {
		foreach ($keys as $k){
			$cols[$k['key']][$key] = $row[$k['key']];
		}
	}
	// List original keys
	$idkeys=array_keys($data);
	// Sort Expression
	$i=0;
	foreach ($keys as $k){
		if($i>0){$sort.=',';}
		$sort.='$cols[\''.$k['key'].'\']';
		if($k['sort']){$sort.=',SORT_'.strtoupper($k['sort']);}
		if($k['type']){$sort.=',SORT_'.strtoupper($k['type']);}
		$i++;
	}
	$sort.=',$idkeys';
	// Sort Funct
	$sort='array_multisort('.$sort.');';
	eval($sort);
	// Rebuild Full Array
	foreach($idkeys as $idkey){
		$result[$idkey]=$data[$idkey];
	}
	return $result;
} # end function

function _localCreateMenuContainerItem($prmDirPath) {
	$newItem = array();
	$data = array(
			'showmenu' 	=> 'true',
			'rndtag' 	=> mt_rand(),	# random tag to make entry unique and therefore avoid caching by the WDTVL
		);
	$dataString = http_build_query($data, 'var_');
	$encDataString = htmlentities($dataString);
	$newItem['id']				= 'umsp://local' . $prmDirPath . '?' . $encDataString;
	$newItem['parentID']		= 'umsp://local' . $prmDirPath;
	$newItem['dc:title']		= ' -- Options / Sort --';
	$newItem['upnp:class']		= 'object.container';
	$newItem['upnp:album_art'] 	= '/osd/image/home_avsettings_sub_icon_n.png';
	$retItems[] = $newItem;
	return $retItems;
} # end function

function _localCreateMenuContent($prmDirPath) {
	$menuEntries = array(
		array(
			'menuEntryTitle' => '-- Shuffle --',
			'data'			=> array(
				'sort_by' 		=> 'shuffle',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Flat View Files --',
			'data'			=> array(
				'flat_view'		=> 'files',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Flat View Dirs --',
			'data'			=> array(
				'flat_view'		=> 'dirs',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Flat View Dirs & Files --',
			'data'			=> array(
				'flat_view'		=> 'dirsfiles',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Sort by Size (asc) --',
			'data'			=> array(
				'sort_by' 		=> 'size',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Sort by Size (desc) --',
			'data'			=> array(
				'sort_by' 		=> 'shuffle',
				'sort_desc'		=> 'true',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Sort by Date (asc) --',
			'data'			=> array(
				'sort_by' 		=> 'date',
				'rndtag' 		=> mt_rand(),
			),
		),
		array(
			'menuEntryTitle' => '-- Sort by Date (desc) --',
			'data'			=> array(
				'sort_by' 		=> 'date',
				'sort_desc'		=> 'true',
				'rndtag' 		=> mt_rand(),
			),
		),			
	);	
	foreach ($menuEntries as $menuEntry) {
		$newItem = array();
		$dataString = http_build_query($menuEntry['data'], 'var_');
		$encDataString = htmlentities($dataString);
		$newItem['id']			= 'umsp://local' . $prmDirPath . '?' . $encDataString;
		$newItem['parentID']	= 'umsp://local' . $prmDirPath;
		$newItem['dc:title']	= $menuEntry['menuEntryTitle'];
		$newItem['upnp:class']	= 'object.container';
		$retItems[] = $newItem;
	}
	return $retItems;
} # end function


?>
