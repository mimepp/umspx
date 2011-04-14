<?php
	### XML UMSP Items Plugin
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact 
	###
	### Version: 1.2 - 2010.10.30.
	###   * added Parental control
	### Version: 1.0 - 2010.10.12.

	include_once($_SERVER["DOCUMENT_ROOT"]."/umsp/funcs-misc.php"); 
	require_once "umsp-items.func.php";

	function _pluginMain($prmQuery = "") {
		$queryData = array();
		parse_str($prmQuery, $queryData);
		if (isset($queryData['param'])) {
			$items = _pluginCreateItems($queryData['param']);
		} else {
			$items = _pluginCreateItems();
		};
		return $items;
	};

	function _pluginSearch($prmQuery) {
		preg_match('/and dc:title contains "(.*?)"/', $prmQuery, $searchstring);
		if ( isset($searchstring[1]) ) {
			$parentalCode = $searchstring[1];
			$umspParentalCodes = _readTmpVar("umspParentalCodes");
			if (is_null($umspParentalCodes)) {
				$umspParentalCodes = Array();
			};
			if (file_exists(_getUMSPConfPath() . "/umsp-items.xml")) {
				$path = _getUMSPConfPath() . "/umsp-items.xml";
			} elseif (file_exists(dirname(__FILE__)."/umsp-items.xml")) {
				$path = dirname(__FILE__)."/umsp-items.xml";
			} else {
				return $NULL; // Exception
			}
			$content = file_get_contents($path);
			if ($content === FALSE) return NULL; // Exception
			$allUmspItems = new SimpleXMLElement($content);
			$items = $allUmspItems->xpath("//item[@parentalCode='".$parentalCode."' and @type='directory']");
			if (0 < count($items)) {
				for ($dirNames = "", $ii=0; $ii<count($items); $ii++) {
					if (isset($items[$ii]["dc.title"])) {
						if ($dirNames != "") {
							$dirNames .= ", ";
						};
						$dirNames .= "'".$items[$ii]["dc.title"]."'";
					};
				};
				if (!in_array($parentalCode, $umspParentalCodes)) {
					$umspParentalCodes[] = $parentalCode;
					$text = "Parental control OFF: ".$dirNames.". Press [HOME]!";
				} else {
					$umspParentalCodes = array_diff($umspParentalCodes, array($parentalCode));
					$text = "Parental control ON: ".$dirNames.". Press [HOME]!";
				};
				_writeTmpVar("umspParentalCodes", $umspParentalCodes);
				_clearCache();
				return _createMessageItems($text);
			};
		};
		return null;
	};
?>