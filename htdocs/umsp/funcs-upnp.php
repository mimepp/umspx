<?php

function _parseUPnPRequest($prmRequest) {;
	$reader = new XMLReader();
	$reader->XML($prmRequest);
	while ($reader->read()) {
		if (($reader->nodeType == XMLReader::ELEMENT) && !$reader->isEmptyElement) {
			switch ($reader->localName) {
				case 'Browse':
					$retArr['action'] = 'browse';
					break;
				case 'Search':
					$retArr['action'] = 'search';
					break;
				case 'ObjectID':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
						$retArr['objectid'] = $reader->value;
					} # end if
					break;
				case 'BrowseFlag':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
						$retArr['browseflag'] = $reader->value;
					} # end if
					break;
				case 'Filter':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
						$retArr['filter'] = $reader->value;
					} # end if
					break;
				case 'StartingIndex':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
						$retArr['startingindex'] = $reader->value;
					} # end if
					break;
				case 'RequestedCount':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
						$retArr['requestedcount'] = $reader->value;
					} # end if
					break;
				case 'SearchCriteria':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
					  $retArr['searchcriteria'] = $reader->value;
					} # end if
					break;
				case 'SortCriteria':
					$reader->read();
					if ($reader->nodeType == XMLReader::TEXT) {
						$retArr['sortcriteria'] = $reader->value;
					} # end if
					break;
			} # end switch
		} # end if
	} #end while
	return $retArr;
} #end function


function _createDIDL($prmItems) {
	# TODO: put object.container in container tags where they belong. But as long as the WDTVL doesn't mind... ;)
	# $prmItems is an array of arrays
	$xmlDoc = new DOMDocument('1.0', 'utf-8');
	$xmlDoc->formatOutput = true;
	 
	# Create root element and add namespaces:
	$ndDIDL = $xmlDoc->createElementNS('urn:schemas-upnp-org:metadata-1-0/DIDL-Lite/', 'DIDL-Lite');
	$ndDIDL->setAttribute('xmlns:dc', 'http://purl.org/dc/elements/1.1/');
	$ndDIDL->setAttribute('xmlns:upnp', 'urn:schemas-upnp-org:metadata-1-0/upnp/');
	$xmlDoc->appendChild($ndDIDL);
	 
	# Return empty DIDL if no items present:
	if ( (!isset($prmItems)) || ($prmItems == '') ) {
		return $xmlDoc;
	} # end if
		 
	# Add each item in $prmItems array to $ndDIDL:
	foreach ($prmItems as $item) {
		$ndItem = $xmlDoc->createElement('item');
		$ndRes = $xmlDoc->createElement('res');
		$ndRes_text = $xmlDoc->createTextNode($item['res']);
		$ndRes->appendChild($ndRes_text);
		
		# Add each element / attribute in $item array to item node:
		foreach ($item as $key => $value) {
			# Handle attributes. Better solution?
			switch ($key) {
				case 'id':
					$ndItem->setAttribute('id', $value);
					break;
				case 'parentID':
					$ndItem->setAttribute('parentID', $value);
					break;
				case 'restricted':
					$ndItem->setAttribute('restricted', $value);
					break;
				case 'res':
					break;
				case 'duration':
					$ndRes->setAttribute('duration', $value);
					break;
				case 'size':
					$ndRes->setAttribute('size', $value);
					break;
				case 'bitrate':
					$ndRes->setAttribute('bitrate', $value);
					break;
				case 'protocolInfo':
					$ndRes->setAttribute('protocolInfo', $value);
					break;
				case 'resolution':
					$ndRes->setAttribute('resolution', $value);
					break;
				case 'colorDepth':
					$ndRes->setAttribute('colorDepth', $value);
					break;
				default:
					$ndTag = $xmlDoc->createElement($key);
					$ndItem->appendChild($ndTag);
					# check if string is already utf-8 encoded
					$ndTag_text = $xmlDoc->createTextNode((mb_detect_encoding($value,'auto')=='UTF-8')?$value:utf8_encode($value));
					$ndTag->appendChild($ndTag_text);
			} # end switch
			$ndItem->appendChild($ndRes);
		} # end foreach
		$ndDIDL->appendChild($ndItem);
	} # end foreach
	return $xmlDoc;
} # end function


function _createSOAPEnvelope($prmDIDL, $prmNumRet, $prmTotMatches, $prmResponseType = 'u:BrowseResponse', $prmUpdateID = '0') {
	# $prmDIDL is DIDL XML string
	# XML-Layout:
	#
	#		-s:Envelope
	#				-s:Body
	#						-u:BrowseResponse
	#								Result (DIDL)
	#								NumberReturned
	#								TotalMatches
	#								UpdateID
	#
	$doc  = new DOMDocument('1.0', 'utf-8');
	$doc->formatOutput = true;
	$ndEnvelope = $doc->createElementNS('http://schemas.xmlsoap.org/soap/envelope/', 's:Envelope');
	$doc->appendChild($ndEnvelope);
	$ndBody = $doc->createElement('s:Body');
	$ndEnvelope->appendChild($ndBody);
	$ndBrowseResp = $doc->createElementNS('urn:schemas-upnp-org:service:ContentDirectory:1', $prmResponseType);
	$ndBody->appendChild($ndBrowseResp);
	$ndResult = $doc->createElement('Result',$prmDIDL);
	$ndBrowseResp->appendChild($ndResult);
	$ndNumRet = $doc->createElement('NumberReturned', $prmNumRet);
	$ndBrowseResp->appendChild($ndNumRet);
	$ndTotMatches = $doc->createElement('TotalMatches', $prmTotMatches);
	$ndBrowseResp->appendChild($ndTotMatches);
	$ndUpdateID = $doc->createElement('UpdateID', $prmUpdateID); # seems to be ignored by the WDTVL
	#$ndUpdateID = $doc->createElement('UpdateID', (string)mt_rand(); # seems to be ignored by the WDTVL
	$ndBrowseResp->appendChild($ndUpdateID);
	 
	Return $doc;
}

?>