<?php

include ('info.php');

//error_reporting(0);
header("Cache-Control: no-store, must-revalidate, post-check=0, pre-check=0, false");
header("Content-Length: 2000000000");



if ($_SERVER[''] == 'HEAD') {
	header('Content-Type: video/flv');
	exit;
}


$rawURL = $_GET['itemurl'];
$parsedURL = parse_url($rawURL);
$itemHost = $parsedURL['host'];
$itemPath = $parsedURL['path'];
$itemPort = $parsedURL['port'];


_NetiTVGet($itemHost, ($itemPath ? $itemPath : "/"), ($itemPort ? $itemPort : 80));

function _NetiTVGet($prmHost, $prmPath, $prmPort) {
	$fp = fsockopen($prmHost, $prmPort, $errno, $errstr, 30);

	if ($fp) {
		$out = "GET " . $prmPath . " HTTP/1.1" . "\r\n";
		$out .= "User-Agent: http_parser" . "\r\n";
		$out .= "Host: " . $prmHost . "\r\n";

		fwrite($fp, $out);

		header("Content-Type: video/flv");
		header("Content-Length: 2000000000");

		$headerpassed = false;
		while ($headerpassed == false) {
			$line = fgets($fp);
			if ($line == "\r\n") {
				$headerpassed = true;
			}
		}

		set_time_limit(0);
		while (!feof($fp)) {
			echo fread($fp, 1024 * 8);
			flush();
			ob_flush();
		}
		set_time_limit(30);
		fclose($fp);
	}
}
?>