<?php
	function _pluginGetPHShowData(&$cat_title,&$html)
	{
		// need to use curl, we have a redirect in there and simple file_get_ gets a 401 authorization error
		$html = shell_exec('curl -s -S -k http://www.pornhub.com/categories 2>&1');
		$ret = false;
		if(preg_match_all('/<a href="(?P<category>.+)" class="png"><strong>(?P<title>.+)<\/strong>/',$html,$cat_title))
			$ret = true;
		return $ret;
	}
?>
