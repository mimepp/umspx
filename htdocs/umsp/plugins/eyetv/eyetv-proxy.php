<?php
// eyetv UMSP plugin by Dan
// http://forum.wdlxtv.com/viewtopic.php?f=49&t=710

#set_time_limit(0);
require_once (str_replace('-proxy', '', basename(__file__)));

header("Content-Size: 65535");
header("Content-Length: 65535");
header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

if ($_SERVER[''] == 'HEAD') {
	header('Content-Type: video/mpeg');
	exit;
}

$rawURL = $_GET['itemurl'];
$parsedURL = parse_url($rawURL);
$itemHost = $parsedURL['host'];
$itemPath = $parsedURL['path'];
$itemPort = $parsedURL['port'];

if (isset ($_GET['item']) && is_numeric($_GET['item'])) {
	@file_get_contents('http://' . EYETV_IP . ':' . SERVER_PORT_CONTROLLER . '/' . $_GET['item'], NULL, NULL, 0, 1);
}

_eyetvGet($itemHost, ($itemPath ? $itemPath : "/"), ($itemPort ? $itemPort : 80));

function _eyetvGet($prmHost, $prmPath, $prmPort) {
	$fp = fsockopen($prmHost, $prmPort, $errno, $errstr, 30);

	if ($fp) {
		$out = "GET " . $prmPath . " HTTP/1.1" . "\r\n";
		$out .= "User-Agent: Wget/1.12 (elf)" . "\r\n";
		$out .= "Host: " . $prmHost . "\r\n";
		$out .= "Cache-Control: no-cache" . "\r\n";
		$out .= "Connection: Close" . "\r\n" . "\r\n";
		fwrite($fp, $out);

		header("Content-Type: video/mpeg");
		header("Content-Size: 65535");
		header("Content-Length: 65535");

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