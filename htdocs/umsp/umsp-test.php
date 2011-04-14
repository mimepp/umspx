<?php
	### Test UMSP service
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact
	###
	### Version: 1.0 - 2010.10.12.

	error_reporting(E_ALL ^ E_NOTICE);	// avoid the notice message.
	header ("Content-Type: text/html; charset=UTF-8");
	include_once($_SERVER["DOCUMENT_ROOT"]."/umsp/funcs-misc.php");

	echo '<html xmlns="http://www.w3.org/1999/xhtml">'.
		'<head>'.
			'<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />'.
			'<title>UMSP Test</title>'.
		'</head>'.
		'<body>';

	$arrItems = NULL;
	if (isset($_GET["plugin"])) {
		$arrItems = _callPlugin("umsp/".$_GET["plugin"], http_build_query($_GET));
	} elseif (isset($_POST["search_string"])) {
		$arrItems = _callPluginSearch('and dc:title contains "'.$_POST["search_string"].'"');
	};
	if (is_null($arrItems)) {
		include ($_SERVER["DOCUMENT_ROOT"]."/umsp/media-items.php");
		$arrItems = $myMediaItems;
	};
	if (function_exists('_pluginSearch')) {
		echo '<form method="post" action="'.$_SERVER['PHP_SELF'].'">'.
				'<table>'.
					'<tr>'.
						'<td>Search:</td>'.
						'<td><input type="text" name="search_string"/></td>'.
						'<td><input type="submit" value="OK"/></td>'.
					'</tr>'.
				'</table>'.
			'</form>';
	};
	if (!empty($arrItems)) {
		foreach ($arrItems as $item) {
			if (isset($item["res"])) {
				$url = str_replace("127.0.0.1", $_SERVER["HTTP_HOST"], $item["res"]);				
			} else {
				if (strpos($item["id"], "umsp://plugins/") !== FALSE) {					
					$umspUrl = parse_url($item["id"]);					
					//$url = $_SERVER["SCRIPT_URI"]."?plugin=".basename($umspUrl["path"]).($umspUrl["query"] != ''?"&".$umspUrl["query"]:"");										
					$url = $_SERVER["SCRIPT_URI"]."?plugin=".basename($umspUrl["path"]).($umspUrl["query"] != ''?"&".$umspUrl["query"]:"");					
				} else {
					$url = $item["id"];
				};
			};
			echo "<a href=\"".$url."\">".$item["dc:title"]."</a><br/>";
		};
	};
	echo "</body>";
?>