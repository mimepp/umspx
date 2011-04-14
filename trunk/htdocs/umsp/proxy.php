<?php
	### callProxy UMSP service
	### Copyright 2010 Bagira
	### GPLv3
	### Stipulations:
	### - this entire header must be left intact 
	###
	### Version: 1.0 - 2010.10.30.

	include_once($_SERVER["DOCUMENT_ROOT"]."/umsp/funcs-misc.php");

	if (isset($_GET["plugin"])) {
		$fileName = _getPluginFile($_GET["plugin"]);
		if (is_null($fileName)) {
			exit;
		};
		require $fileName;
		if (function_exists('_pluginProxy')) {
			_pluginProxy($_GET);
		};
	};
?>