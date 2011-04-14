<?php
	function _pluginGetT8ShowData(&$cat_num_title,&$html)
	{
		$ret = false;
		$html = file_get_contents('http://www.tube8.com/categories.html');
		if(preg_match_all('/<option cat="(?P<category>.*?)" value="(?P<id>.*?)">(?P<title>.*?)<\/option>/mis',$html,$cat_num_title))
			$ret = true;
		return $ret;
	}
?>
