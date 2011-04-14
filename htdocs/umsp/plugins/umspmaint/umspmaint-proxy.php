<?php

	/*

	(C) 2010 Stuart Hunter
	Performs an SVN or localized enable/disable and pops an [Success] image when complete
	The image will have a text based status painted upon it
	0.1 2010-12-22 Initial Version

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	*/

	if (isset($_GET['method'])) 
	{
		$ipw = imagecreatefromstring(file_get_contents('http://lh4.ggpht.com/_xJcSFBlLg_Y/TRwLNSk6_8I/AAAAAAAAAKk/JKhsFD9S4sY/s720/success2w3.png'));
		if (is_resource($ipw)===true) // hmm, bail cos the image didn't load - really?!?
		{

			include_once('/usr/share/umsp/funcs-config.php');
			include_once(str_replace('-proxy.php','',__FILE__).'-helper.php');

			$fonts = fontTable(20);

			// do work here - eat any output - we're an image remember!
			// we'll gather status as we go along - this will be "purified" before display
			ob_start();
			switch($_GET['method'])
			{
				case 'all':
					// refresh all plugins, calls the canned routine
					$status = getAllPlugins($_GET['caller']);
					break;
				case 'configured':
					// refresh all configured plugins, bit expensive if all are selected!
					$status = getConfiguredPlugins();
					break;
				case 'disable':
					// disable a plugin
					$res = _writePluginStatus($_GET['plugin'], 'off');
					$status = (($res==true)?$_GET['plugin'].' disabled':'Problem disabling '.$_GET['plugin']);
					break;
				case 'enable':
					// enable a plugin
					$res = _writePluginStatus($_GET['plugin'], 'on');
					$status = (($res==true)?$_GET['plugin'].' enabled':'Problem enabling '.$_GET['plugin']);
					break;
				case 'get':
					// get plugin from SVN and enable
					$status = getAndEnablePlugins(array($_GET['plugin']));
					break;
			}
			// lower portion of the image allows for a status message, paint on the image to give user feedback
			imagepng(paintStatus($ipw,$fonts,$status),NULL,0);
			$imageData = ob_get_contents();
			$imageSize = ob_get_length();
			ob_end_clean();
			imagedestroy($ipw);
			header('Content-type: image/png');
			header("Content-Size: $imageSize");
			header("Content-Length: $imageSize");
			header('Cache-Control: no-cache, must-revalidate');
			header('Pragma: no-cache');
			header('Connection: Close');
			header('Keep-Alive: 115');
			header('Connection: keep-alive');
			echo $imageData;
		}
	}

?>
