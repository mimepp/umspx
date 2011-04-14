<?php

	/*

	(C) 2010 Stuart Hunter after Zoster et al.
	0.1 2010-11-21 Initial Version
	0.2 2010-11-21 Added exif overlay and buffering scheme

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	*/
	include_once(str_replace('-proxy.php','',__FILE__).'-helper.php');

	if (isset($_GET['itemurl'])) 
	{

		// fetch and create a custom image from a Picasa web album 
		// inclusive of exif overlay if the caller is requesting such
		$url = $_GET['itemurl'];

		if(preg_match('/\.(jpg|jpeg|gif|png)$/i', $url)) // just to be safe
		{

			$ipw = imagecreatefromstring(file_get_contents($url));
			if (is_resource($ipw)===true)
			{
				$configData = getConfigData();
				($configData['legacy']=='ON')?
					$ipw=handleLegacy($ipw,$configData,$_GET):
						handleExif($ipw,$configData,$_GET);

				ob_start();
				switch ($_GET['cont_type']) 
				{
					case 'image/jpeg':
						imagejpeg($ipw,NULL,60);// lowered resolution!
						break;
					case 'image/png':
						imagepng($ipw,NULL,0);
						break;
					case 'image/x-png':
						imagepng($ipw,NULL,0);
						break;
					case 'image/gif':
						imagegif($ipw);
						break;
				}

				$imageData = ob_get_contents();
				$imageSize = ob_get_length();
				ob_end_clean();

				imagedestroy($ipw);

				header("Content-type: {$_GET['cont_type']}");
				header("Content-Size: $imageSize");
				header("Content-Length: $imageSize");
				header("Cache-Control: no-cache, must-revalidate");
				header("Pragma: no-cache");
				header('Connection: Close');
				header('Keep-Alive: 115');
				header('Connection: keep-alive');
				echo $imageData;

			}

		} // workable image

	}

?>
