<?
	### XML UMSP Items Functions
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact 
	###
	### Version: 1.2 - 2010.10.30.
	###   * added Parental control
	### Version: 1.1 - 2010.10.15.
	###   * added default protocolInfo, if not defined
	### Version: 1.0 - 2010.10.12.

	function umsp_sort($a, $b) {
		if (!isset($a['order']) || !isset($b['order'])) {
			return 0;
		};
		if ((int)$a['order'] == (int)$b['order']) {
			return 0;
		}
		return ((int)$a['order'] < (int)$b['order']) ? -1 : 1;
	};

	function begin_with($str, $params) {
		if (!is_array($params)) {
			$params = Array($params);
		};
		foreach ($params as $val) {
			$x = strpos($str, $val);
			if ($x !== FALSE && $x == 0) {
				return TRUE;
			};
		};
		return FALSE;
	};

	function _pluginCreateItems($param = NULL) {
		$retItems = Array();
		if (file_exists(_getUMSPConfPath() . "/umsp-items.xml")) {
			$path = _getUMSPConfPath() . "/umsp-items.xml";
		} elseif (file_exists(dirname(__FILE__)."/umsp-items.xml")) {
			$path = dirname(__FILE__)."/umsp-items.xml";
		} else {
			return $retItems; // Exception
		}
		$content = file_get_contents($path);
		if ($content === FALSE) return $retItems; // Exception
		$allUmspItems = new SimpleXMLElement($content);

		if (is_null($param)) {
			$items = $allUmspItems->xpath("/items");
		} else {
			$items = $allUmspItems->xpath("//item[@id='".$param."']");
		};
		if (count($items) == 0) {
			return $retItems;
		};

		$parentalCode = $items[0]["parentalCode"];
		if (!is_null($parentalCode)) {
			$parentalCode = (string)$parentalCode;
			$umspParentalCodes = _readTmpVar("umspParentalCodes");
			if (is_null($umspParentalCodes)) {
				$umspParentalCodes = Array();
			};
			if (!in_array($parentalCode, $umspParentalCodes)) {
				return _createMessageItems("Press [SEARCH] and enter parental code!");
			};
		};
		foreach($items[0]->children() as $item) {
			if (!isset($item['disabled'])) {
				$curItem = Array();
				$pluginVarData = Array();
				foreach ($item->attributes() as $key => $val) {
					if (!begin_with($key, array("type", "param."))) {
						$curVal = (string)$val;
						if (begin_with($curVal, "php:")) {
							eval("\$curVal = ".substr($curVal, strlen("php:")).";");
						};
						if (!isset($curItem[str_replace(".", ":", $key)])) {
							$curItem[str_replace(".", ":", $key)] = $curVal;
						}
					} else
					if (begin_with($key, "param.")) {
						$paramKey = substr($key, strlen("param."));
						$pluginVarData[$paramKey] = (string)$item[$key];
					} else
					if ($key == "type" && (string)$val == "directory" && isset($item["id"])) {
						$pluginVarData["param"] = (string)$item["id"];
						$curItem["id"] = "umsp-items";
					};
				};

				if (isset($curItem["id"])) {
					if (!isset($curItem["dc:title"])) {
						$curItem["dc:title"] = "ID:".$curItem["id"];
					};
					if (!isset($curItem["upnp:class"])) {
						$curItem["upnp:class"] = "object.container";
					};
					if (!isset($curItem["order"])) {
						$curItem["order"] = "9999";
					};
					if (!isset($curItem["protocolInfo"]) && $curItem["upnp:class"] != "object.container") {
						$curItem["protocolInfo"] = "*:*:*:*";
					};

					$dataString = (!empty($pluginVarData)?'?'.http_build_query($pluginVarData, 'pluginvar_'):"");
					$curItem["id"] = "umsp://plugins/".$curItem["id"].str_replace("&", "&amp;", $dataString);
					$retItems[] = $curItem;
				};
			};
		};

		if (!is_null($myMediaItems)) {
			usort($myMediaItems, "umsp_sort");
		};
		return $retItems;
	};
?>