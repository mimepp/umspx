<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>SSDP Broadcast</title>
</head>

<body>
<?php
$result = _myCheck();
?>
<br />
<br />
<form id="form1" name="form1" method="get" action="">
  <label>Mediaserver IP (WDTV)
  </label>
  <br />
  <br />
  <label>Send SSDP broadcast to localhost
  <input type="submit" name="btnSend" id="id-btnSend" value="Submit" />
  </label>
</form>
<br />
<?php echo $result; ?>
</body>
</html>

<?php
function _myCheck() {
	if ($_GET['btnSend']) {
#		$ssdpStatus = _sddpSendX($msIP);
		$ssdpStatus = _sddpSend($msIP);
		$strSSDPResult = 'SSDP sent at '.date('H:i:s').' Status: ' .$ssdpStatus  ;
	}
	return $strSSDPResult;
}

function _sddpSend($frame, $delay=15, $host="239.255.255.250", $port=1900) {
	$strHeader  = 'NOTIFY * HTTP/1.1' . "\r\n";
	$strHeader .= 'HOST: 239.255.255.250:1900' . "\r\n";
	$strHeader .= 'LOCATION: http://'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].'/umsp/MediaServerServiceDesc.xml' . "\r\n";
	$strHeader .= 'SERVER: MYFAKESERVER UDP 127' . "\r\n";
	$strHeader .= 'CACHE-CONTROL: max-age=7200' . "\r\n";
	$strHeader .= 'NT: urn:schemas-upnp-org:device:MediaServer:1' . "\r\n";
	$strHeader .= 'NTS: ssdp:alive' . "\r\n";
	$strHeader .= 'USN: uuid:badbabe1-6666-6666-6666-f00d00c0ffee::urn:schemas-upnp-org:device:MediaServer:1' . "\r\n". "\r\n";
    $buf = $strHeader;
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    socket_bind($socket, $_SERVER['SERVER_ADDR']);
    socket_sendto($socket, $buf, strlen($buf), 0, $host, $port);
    socket_close($socket);
    usleep($delay*1000);
}
?>

