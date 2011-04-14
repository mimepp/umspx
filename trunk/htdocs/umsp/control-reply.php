<?php
	# for debugging:
	# header("content-type: text/xml");
	include_once('funcs-upnp.php');
	include_once('funcs-misc.php');
	include_once('funcs-local.php'); # TODO: move include to switch
	include('media-items.php');

	# Parse the request from WDTVL
	$requestRaw = file_get_contents('php://input');
	if ($requestRaw != '') {
		$upnpRequest = _parseUPnPRequest($requestRaw);
		# DEBUG:
		#_var_dump_to_file($requestRaw);
	} else {
		echo 'No Request! wtf?';
		exit;
	} # end if


	# The objectId should be '0' for the root container or in URL format.
	# eg 'umsp://plugins/apple-trailers?arg=val'
	# or 'umsp://local/tmp/media/usb?random=true&sort_attr=name'
	#
	# The WDTVL usually requests a range of 64 items so the items of the last request are cached to speed up the scrolling.
	#
	#
	switch ($upnpRequest['action']) {
		case 'search':
			$responseType = 'u:SearchResponse';
			$items = _callPluginSearch($upnpRequest['searchcriteria']);
			break;
		case 'browse':
			$responseType = 'u:BrowseResponse';
			$items = array();
			$cachedItems = _readCache($upnpRequest['objectid']);
			if (isset($cachedItems)) {
				# cache hit
				$items = $cachedItems;
			} else {
				# cache miss
				if ($upnpRequest['objectid'] == '0') {
					# Root items:
					$items = $myMediaItems;
					# Version message disabled:
					# $msgItems = _createMessageItems(array('UMSP v0.1.3'));
					# $items = array_merge($items, $msgItems);
					break;
				} else {
					# The parse_url function returns an array in this format:
					# Array (
					#	[scheme] => http
					#	[host] => hostname
					#	[user] => username
					#	[pass] => password
					#	[path] => /path
					#	[query] => arg=value
					#	[fragment] => anchor
					# )
					$reqObjectURL = parse_url($upnpRequest['objectid']);
					switch ($reqObjectURL['scheme']) {
						case 'umsp':
							switch ($reqObjectURL['host']) {
								case 'plugins':
									# handle plugins
									# call with parameters query and path
									$items = _callPlugin($reqObjectURL['path'], $reqObjectURL['query']);
									break;
								case 'local':
									# handle local content
									# call with parameters query and path
									$items = _localMain($reqObjectURL['path'], $reqObjectURL['query']);
									break;
							} # end switch
							# cache items
							if (0 < count($items)) {
								_writeCache($upnpRequest['objectid'], $items);
							};
							break;
							# URL is not umsp:// try to load URL as DIDL-XML ??
					} # end switch
				};
			} # end if
			break;
	} # end switch

	$totMatches = count($items);
	if ($totMatches == 0) {
		$domDIDL = _createDIDL('');
		$numRet = 0;
	} else {
		$slicedItems = array_slice($items, $upnpRequest['startingindex'], $upnpRequest['requestedcount']);
		$domDIDL = _createDIDL($slicedItems);
		$numRet = count($slicedItems);
	}
	# Build DIDL-XML from $myMediaItems array
	$xmlDIDL = $domDIDL->saveXML();

	# Build SOAP-XML reply from DIDL-XML and send it to WDTVL
	$domSOAP = _createSOAPEnvelope($xmlDIDL, $numRet, $totMatches, $responseType);
	echo $domSOAP->saveXML();
?>