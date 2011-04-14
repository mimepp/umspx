<?php

$rawURL = $_GET['itemurl'];
$parsedURL = parse_url($rawURL);
$itemHost = $parsedURL['host'];
$itemPath = $parsedURL['path'];
_quicktimeGet($itemHost, $itemPath);

function _quicktimeGet($prmHost, $prmPath) {
	$fp = fsockopen($prmHost, 80, $errno, $errstr, 30);
	if (!$fp) {
		echo "$errstr ($errno)<br />\n";
	} else {
		$out  = 'GET '. $prmPath .' HTTP/1.1' ."\r\n";
		$out .= 'User-Agent: QuickTime.7.6.5 (qtver=7.6.5;os=Windows NT 5.1Service Pack 3)' ."\r\n";
		$out .= 'Host: ' . $prmHost . "\r\n";
		$out .= 'Cache-Control: no-cache' ."\r\n";
		$out .= "\r\n";
		fwrite($fp, $out);
		$headerpassed = false;
		while ($headerpassed == false) {
			$line = fgets( $fp);
			if((stristr($line, 'Content-Type')) || (stristr($line, 'Content-Length'))) {
				header($line);
			}
			if( $line == "\r\n" ) {
				$headerpassed = true;
			}
		}
		fpassthru($fp);
		fclose($fp);
	}
}
?>
