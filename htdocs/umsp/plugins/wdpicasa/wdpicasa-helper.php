<?php

	/*

	(C) 2010 Stuart Hunter after Zoster et al.
	0.1 2010-11-21 Initial Version

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	*/

	function _googleAuthAccount($username, $password, $service = 'lh2', $source = 'wdtvext') 
	{

		// check if we already authorized this user
		$session_token = $username.'_'.$source.'_'.$service.'_auth_token';
		if ($_SESSION[$session_token]) 
		{
			return $_SESSION[$session_token];
		}

		$post_fields = "accountType=" . urlencode('HOSTED_OR_GOOGLE')
				. "&Email=" . urlencode($username)
				. "&Passwd=" . urlencode($password)
				. "&service=" . urlencode($service)
				. "&source=" . urlencode($source);

		// use curl, post mech a wash
		$scrape = "| grep '^Auth=' | cut -c 6-";
		$command = "/usr/bin/curl -s -S -k --location https://www.google.com/accounts/ClientLogin --data '$post_fields' --header 'Content-Type:application/x-www-form-urlencoded' $scrape 2>&1";
		// get the authorization token
		$response = shell_exec($command);
		if ($esponse!==false) 
		{
			$_SESSION[$session_token] = $response;
			return $response;
		}
		else
		{
			return null;
		}
	}  

	function _googleFetch($uri, $auth = NULL, $gsessionid = NULL, $recursive = false, $simplefetch = false) 
	{

		if ($auth!='') 
		{
			if ($gsessionid) {
				$uri .= "?gsessionid=$gsessionid";
			}

			if($uri!='')
			{
				$url = parse_url($uri);
				$url['path'] = substr($url['path'], 0, 1) == "/" ? $url['path'] : "/" . $url['path'];
				try {
					$fp = fsockopen($url['host'], 80, $errno, $errstr);
				} catch (Exception $e) {
					echo "Error calling fsockopen in ".__FUNCTION__." :: ".$e->getMessage()."<br>$uri\n";
				}
				if ($fp) 
				{
					$out = "GET {$url['path']} HTTP/1.0\r\n";
					$out .= "Host: {$url['host']}\r\n";
					$out .= "Authorization: GoogleLogin auth=$auth\r\n";
					$out .= "Connection: Close\r\n\r\n";
					fwrite($fp, $out);

					$response = '';
 					$headerPassed = false;
					while (!feof($fp)) 
					{
						$line = fgets($fp);
						if($line == "\r\n") $headerPassed = true;
						if($headerPassed) $response .= $line;   
					}
					fclose($fp);
					if ((strpos($response, 'Error 404') !== false)||(strpos($response, '404 Not Found') !== false)) 
					{
						return null; // no permissions!!!
					}
					else if (strpos($response, 'Token invalid') === false) 
					{
						if($simplefetch==true)
							return trim($response);
						else
							return simplexml_load_string(trim($response));
					}
					else if (strpos($response, 'Moved Temporarily') !== false) 
					{
						// get the gsessionid
						preg_match("/(gsessionid=)([\w|-]+)/", $response, $m);
						if (!$m[2]) 
						{
							return NULL;
						}
	
						// we need to call the function again, this time with gsessionid;
						// but only try once, so we don't get caught in a loop if there's
						if ($recursive === false) 
						{
							$response = _googleFetch($url, $auth, $m[2], true);
							if ($response!==null)
							{
								if($simplefetch==true)
									return trim($response);
								else
									return simplexml_load_string(trim($response));
							}
						}
	
					}
					else
					{
						// hopefully we never get here, pop for debug
						print_r($response);
					}
				}
	
			}

		}
		else
		{
			if ($uri!='')
			{
				// assume is a public album
				if($simplefetch==true)
					return file_get_contents(trim($uri));
				else
					return simplexml_load_file(trim($uri));
			}
		}

		return null;

	} // function

	function getThumbnailUrl($imgSrc, $imgSize)
	{
		if($imgSize!=-1)
		{
			$img = explode('/', $imgSrc);
			array_splice($img, count($img)-1, 0, 's'.$imgSize);
			$imgSrc = implode('/', $img);
		}
		return (string)$imgSrc;
	}

	function convertShutterFrac($dec)
	{

		// covert decimal shutter speed to
		// standard fractional notation
		$ret = '';
		if ($dec!='')
		{
			if ((1 / $dec) > 1)
			{
				if ((number_format((1 / $dec), 1)) == 1.3
					or number_format((1 / $dec), 1) == 1.5
					or number_format((1 / $dec), 1) == 1.6
					or number_format((1 / $dec), 1) == 2.5)
				{
					$ret = '1/'.number_format((1 / $dec), 1, '.', '').' sec';
				}
				else
				{
					$ret = '1/'.number_format((1 / $dec), 0, '.', '').' sec';
				}
			}
			else
			{
				$ret = "$dec sec";
			}
		}
		return $ret;

	}

	function formatLongLat($value,$type='long')
	{
		if($value=='') $value = 'n/a';
		return (($value!='n/a')?number_format(abs($value),6).'° '
			.(($value >= 0)?(($type=='long')?'E':'N'):(($type=='long')?'W':'S')):$value);
	}

	function displayTag($exif,$name,$tag,$sp='=',$lf='&',$displayExifItems='all')
	{
		if(('all'==$displayExifItems)||(strpos("|$displayExifItems|","|$tag|")!==false))
		{
			switch($tag)
			{
				case('caption'):
					$val=str_replace(chr(92),'',trim($exif[$tag]));break; // pain in the pants! whats with the crazy escaping?!?
				case('latitude'):
					$val=trim(formatLongLat($exif['latitude'],'lat'));break;
				case('longitude'):
					$val=trim(formatLongLat($exif['longitude'],'long'));break;
				default:
					$val=trim($exif[$tag]);
			}
			$ret = $name.$sp.$val.$lf;
		}
		else
		{
			$ret = null;
		}
		return $ret;
	}

	function exifTagsX($exif,$format_lf=false,$displayExifItems='all')
	{
		$lf=(($format_lf)?'&':' ');
		$sp=(($format_lf)?'=':': ');
		return (($format_lf)?'':' [')
			.trim (
				displayTag($exif,'Artist','artist',$sp,$lf,$displayExifItems)
				//.(($format_lf)?displayTag($exif,'Caption','caption',$sp,$lf,$displayExifItems):'')
				.displayTag($exif,'Camera','make',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Model','model',$sp,$lf,$displayExifItems)
				.displayTag($exif,'ISO','iso',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Exposure','exposure',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Aperture','fstop',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Focal Length','focallength',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Flash Used','flash',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Latitude','latitude',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Longitude','longitude',$sp,$lf,$displayExifItems)
				.displayTag($exif,'Taken','time',$sp,$lf,$displayExifItems)
			)
			.(($format_lf)?'':']');

	}

	function resizeImage($img, $pcnt = 1)
	{
  
		if($pcnt == 1) return $img;    
		$width = imagesx($img);
		$height = imagesy($img);
		$new_width = $width * $pcnt;
		$new_height = $height * $pcnt;
 		$image = imageCreateTrueColor($new_width, $new_height);
		imageCopyResampled($image, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
		return $image;
	}

	function imageFillRoundedRect($img,$x,$y,$cx,$cy,$radius,$color)
	{
		imagefilledrectangle($img,$x,$y+$radius,$cx,$cy-$radius,$color);
		imagefilledrectangle($img,$x+$radius,$y,$cx-$radius,$cy,$color);
		$diameter = 2 * $radius;
		imagefilledellipse($img, $x+$radius, $y+$radius, $diameter, $diameter, $color);
		imagefilledellipse($img, $x+$radius, $cy-$radius, $diameter, $diameter, $color);
		imagefilledellipse($img, $cx-$radius, $cy-$radius, $diameter, $diameter, $color);
		imagefilledellipse($img, $cx-$radius, $y+$radius, $diameter, $diameter, $color);
	}

	function imageFillRoundedRectBorder($img,$x,$y,$cx,$cy,$border,$radius,$color,$colorPrime)
	{
		imageFillRoundedRect(
			$img,
			$x,$y,
			$cx,$cy,
			$radius,$color
		);
		imageFillRoundedRect(
			$img,
			$x+$border,$y+$border,
			$cx-$border,$cy-$border,
			$radius,$colorPrime
		);
	}

	function imageFillRectBorder($img,$x,$y,$cx,$cy,$border,$radius,$color,$colorPrime)
	{
		imagefilledrectangle (
			$img,
			$x,$y,
			$cx,$cy,
			$color
		);
		imagefilledrectangle (
			$img,
			$x+$border,$y+$border,
			$cx-$border,$cy-$border,
			$colorPrime
		);
	}

	function fontTable($fontsize = 20,$trans_pcnt = 100)
	{
		return Array(
				'title'	=> Array (
					'fontfile'	=> '/usr/share/fonts/truetype/msttcorefonts/georgiab.ttf',
					'size'		=> $fontsize,
					'color'		=> hexdec('0x00FFF200')
				),
				'heading'	=> Array (
					'fontfile'	=> '/usr/share/fonts/truetype/msttcorefonts/verdanab.ttf',
					'size'		=> $fontsize-4,
					'color'		=> hexdec('0x00FF9000')
				),
				'datum'		=> Array (
					'fontfile'	=> '/usr/share/fonts/truetype/msttcorefonts/verdana.ttf',
					'size'		=> $fontsize-4,
					'color'		=> hexdec('0x00FFFFFF')
				)
			);
	}

	function getConfigData()
	{
		$config = file_get_contents('/conf/config');
		if(preg_match('/PICASA_USERS_XML=\'(.+)\'/', $config, $m))$xml_file = $m[1];
		if(preg_match('/PICASA_IMAGE_RESIZE=\'(.+)\'/', $config, $m))$image_resize = $m[1];
		if(preg_match('/PICASA_MAX_PHOTOS=\'(.+)\'/', $config, $m))$max_photos = $m[1];
		if(preg_match('/PICASA_SHOW_EXIF=\'(.+)\'/', $config, $m))$show_exif = $m[1];
		if(preg_match('/PICASA_SHOW_COMMENTS=\'(.+)\'/', $config, $m))$show_comments = $m[1];
		if(preg_match('/PICASA_EXIF_OVERLAY_POSN=\'(.+)\'/', $config, $m))$exif_overlay_posn = $m[1];
		if(preg_match('/PICASA_EXIF_OVERLAY_PCNT=\'(.+)\'/', $config, $m))$exif_overlay_pcnt = $m[1];
		if(preg_match('/PICASA_EXIF_OVERLAY_SIZE=\'(.+)\'/', $config, $m))$exif_overlay_size = $m[1];
		if(preg_match('/PICASA_LEGACY_INTERFACE=\'(.+)\'/', $config, $m))$exif_legacy_interface = $m[1];
		if(preg_match('/PICASA_EXIF_DISPLAY_ITEMS=\'(.+)\'/', $config, $m))$exif_display_items = $m[1];
		if(preg_match('/PICASA_OVERLAY_CAPTION=\'(.+)\'/', $config, $m))$caption_overlay = $m[1];
		if(preg_match('/PICASA_CAPTION_COLOR=\'(.+)\'/', $config, $m))$caption_color = $m[1];
		return array (
			'xmlfile'		=> $xml_file,
			'legacy'		=> strtoupper((($exif_legacy_interface!='')?$exif_legacy_interface:'OFF')),
			'showexif'		=> strtoupper((($show_exif!='')?$show_exif:'OFF')),
			'overlaycaption'	=> strtoupper((($caption_overlay!='')?$caption_overlay:'OFF')),
			'displayexiftags'	=> (($exif_display_items!='')?$exif_display_items:'all'),
			'exifovrposition'	=> strtoupper((($exif_overlay_posn!='')?$exif_overlay_posn:'E')),
			'exifovrpercent'	=> (($exif_overlay_pcnt!='')?$exif_overlay_pcnt:'70'),
			'exifovrsize'		=> (($exif_overlay_size!='')?$exif_overlay_size:'16'),
			'imageresize'		=> (($image_resize!='')?$image_resize:'800'),
			'maxphotos'		=> $max_photos,
			'displaycomments'	=> strtoupper((($show_comments!='')?$show_comments:'OFF')),
			'captioncolor'		=> (($caption_color!='')?$caption_color:'REV'),
			// externalized these to save space, served from Picasa - of course ;)
			'badge'			=> 'http://lh5.ggpht.com/_xJcSFBlLg_Y/TP3vozw0D6I/AAAAAAAAACc/YvoHm40QXBM/s144/picasa256.png',
			'badgeprivate'		=> 'http://lh5.ggpht.com/_xJcSFBlLg_Y/TP3vovfJ2NI/AAAAAAAAACc/HC-AxhXnpb8/s144/picasa256-private.png',
			'badgepublic'		=> 'http://lh6.ggpht.com/_xJcSFBlLg_Y/TP3vop9ynDI/AAAAAAAAACc/1rQcmRO4bJY/s144/picasa256-public.png',
			'badgesearch'		=> 'http://lh5.ggpht.com/_xJcSFBlLg_Y/TP3vod-Oh-I/AAAAAAAAACc/_tigbKza-tQ/s144/picasa256-search.png',
			'stopbadge'		=> 'http://lh6.googleusercontent.com/_xJcSFBlLg_Y/TWOoLmVMr6I/AAAAAAAAAYE/_-d60WDF-rQ/stop-a-cop8.png',
		);
	}

	// remove any formats that will cause the UMSP server to hang!!!
	// move this to misc funcs when we're able to
	function makeSafeItemDescription($desc,$stripSpecial=false,$stripTags=true,$safeTags=null)
	{
		// remove usual descriptive "junk"
		$ret = preg_replace('/\s\s+/','',preg_replace(array('/\n/','/\r/','/\n\r/','/\r\n/','/\t/'),array(' ',' ',' ',' ',' '),trim($desc)));
		// strip HTML special escape'd chars as requested
		if($stripSpecial==true)
			$ret = preg_replace('/&#?[a-z0-9]{2,8};/i','',$ret);
		// strip HTML tags as requested, user can pass safe tag set
		if($stripTags==true)
			$ret = strip_tags($ret,$safeTags);
		return $ret;
	}

	function exifBadge ($configData,$getargs,&$fonts=array())
	{

		$exif = exifTagsX($getargs,true,$configData['displayexiftags']);
		// we have exif formatted as a parameter string, fold into array
		parse_str($exif, $exifData);
		$sep = ''; $outhead = ''; $outdata = '';
		foreach ($exifData as $heading => $datum)
		{
			$outhead .= $sep.$heading;
			$outdata .= $sep.': '.$datum;
			$sep = "\n";
		}
		$outhead = str_replace('_',' ',$outhead);
		$margin = 8;
		$fonts = (empty($fonts)?fontTable($configData['exifovrsize']):$fonts);
		$exif_title = 'Photo Information';
		$bx_title = imagettfbbox($fonts['title']['size'], 0, $fonts['title']['fontfile'], $exif_title);
		$bx_head = imagettfbbox($fonts['heading']['size'], 0, $fonts['heading']['fontfile'], $outhead);
		$bx_data = imagettfbbox($fonts['datum']['size'], 0, $fonts['datum']['fontfile'], $outdata);

		$titleTop = abs($bx_title[7] - $bx_title[1]) * 2.4;
		$headWidth = abs($bx_head[2] - $bx_head[0]);

		return array (
			'title'		=>	$exif_title,
			'outhead'	=>	$outhead,
			'outdata'	=>	$outdata,
			'fonttable'	=>	$fonts,
			'ovrwidth'	=>	abs($bx_data[2] - $bx_data[0]) + $headWidth + (2*$margin),
			'ovrheight'	=>	abs($bx_head[7] - $bx_head[1]) + $titleTop + (2*$margin),
			'titlewidth'	=>	abs($bx_title[2] - $bx_title[0]),
			'titletop'	=>	$titleTop,
			'tabletop'	=>	(1.4*$titleTop)-$margin, // factor font size dependent? need algo
			'headwidth'	=>	$headWidth,
			'margin'	=>	$margin,
		);

	}

	function paintExifBadge ($xbg,$badge,$fonts,$offsetx,$offsety)
	{

		imageFillRoundedRectBorder (
			$xbg,
			$offsetx,$offsety,
			$offsetx+$badge['ovrwidth'],$offsety+$badge['ovrheight'],
			3, // border
			30,// radius
			hexdec('0x00303030'),hexdec('0x00202020')
		);

		// factors here are font size dependent - need to work an algo
		imagettftext(
			$xbg, $fonts['title']['size'], 0, 
			$offsetx+abs(($badge['ovrwidth']-$badge['titlewidth']) / 2), $offsety+(0.8*$badge['titletop']-$badge['margin']), 
			$fonts['title']['color'], 
			$fonts['title']['fontfile'], 
			$badge['title']
		);
		imagettftext(
			$xbg, $fonts['heading']['size'], 0, 
			$offsetx+$badge['margin'], $offsety+$badge['tabletop'], 
			$fonts['heading']['color'], 
			$fonts['heading']['fontfile'], 
			$badge['outhead']
		);
		imagettftext(
			$xbg, $fonts['datum']['size'], 0, 
			$offsetx+($badge['margin']+$badge['headwidth']), $offsety+$badge['tabletop'], 
			$fonts['datum']['color'], 
			$fonts['datum']['fontfile'], 
			$badge['outdata']
		);
	}

	function getPayload($configData,$imageid)
	{
		if(file_exists($configData['cachefile']))
		{
			$cached = file_get_contents('file://'.$configData['cachefile']);
			//$cached = file_get_contents($configData['cachefile']);
			if(preg_match('/'.$imageid.'=<wdpicasa>(.*?)<\/wdpicasa>/', $cached, $payload))
			{
				$ret = json_decode(utf8_encode($payload[1]), true);
			}
		}
		return $ret;
	}

	function handleExif($img,$configData,$getargs)
	{
		if($configData['showexif']=='ON')
		{

			$badge = exifBadge($configData,$getargs,$fonts);
			$img_w = imagesx($img);
			$img_h = imagesy($img);

			list($orient,$wx,$wy) = ($img_h>=$img_h)?array('l',1.11,1.25):array('p',1.11,1.25);
			
			// if we can overlay and not swamp the image then continue
			if(($img_w>=($wx*$badge['ovrwidth']))&&($img_h>=($wy*$badge['ovrheight'])))
			{

				$xbg = imagecreatetruecolor($badge['ovrwidth'], $badge['ovrheight']);
				imagealphablending($xbg, true);
				imagesavealpha($xbg, true);

				switch (strtoupper($configData['exifovrposition'])) 
				{
					case 'N' : $badge_x = (($img_w - $badge['ovrwidth']) / 2); $badge_y = $badge['margin'];
						break;
					case 'NE' : $badge_x = $img_w-($badge['ovrwidth']+$badge['margin']); $badge_y = $badge['margin'];
						break;
					case 'E' : $badge_x = $img_w-($badge['ovrwidth']+$badge['margin']); $badge_y = (($img_h - $badge['ovrheight']) / 2);
						break;
					case 'SE' : $badge_x = $img_w-($badge['ovrwidth']+$badge['margin']); $badge_y = $img_h-($badge['ovrheight']+$badge['margin']);
						break;
					case 'S' : $badge_x = (($img_w - $badge['ovrwidth']) / 2); $badge_y = $img_h-($badge['ovrheight']+$badge['margin']);
						break;
					case 'SW' : $badge_x = $badge['margin']; $badge_y = $img_h-($badge['ovrheight']+$badge['margin']);
						break;
					case 'W' : $badge_x = $badge['margin']; $badge_y = (($img_h - $badge['ovrheight']) / 2);
						break;
					default : $badge_x = $badge['margin']; $badge_y = $badge['margin'];
						break;

 				}

				// prep the EXIF overlay
				imagecopy($xbg, $img, 0, 0,$badge_x, $badge_y, $badge['ovrwidth'], $badge['ovrheight']); // copy background - like working with win32 primitives!!!

				paintExifBadge ($xbg,$badge,$fonts,0,0);

				imagealphablending($img,true);
				imagecopymerge($img, $xbg, $badge_x, $badge_y, 0, 0, $badge['ovrwidth'], $badge['ovrheight'],$configData['exifovrpercent']);
				imagesavealpha($img, true);
				imagedestroy($xbg); // free resource

			}

		}
		// no checks are made for clash between EXIF and caption placement!!!
		if(in_array($configData['overlaycaption'],array('N','S'))&&($getargs['caption']!=''))
		{
			$img_w = imagesx($img);
			$img_h = imagesy($img);
			$cap_sz = 28;
			if($fonts==null)
				$fonts = fontTable($cap_sz);
			$bx_cap = imagettfbbox($cap_sz, 0, $fonts['heading']['fontfile'], $getargs['caption']);
			if($configData['overlaycaption']=='N')
				$ypos = min(ceil(0.08 * $img_h),(2*abs($bx_cap[7] - $bx_cap[1])));
			else
				$ypos = max(ceil(0.92 * $img_h),($img_h-(2*abs($bx_cap[7] - $bx_cap[1]))));
			$cap_center = ceil(($img_w - $bx_cap[2]) / 2);
			if(($configData['captioncolor']=='')||($configData['captioncolor']=='REV'))
			{
				$reverse = ReverseImageColorAt($img, ceil($img_w/2), $ypos); // reverse color to ensure caption is visible
				imagettftext($img, $cap_sz, 0, $cap_center, $ypos, $reverse, $fonts['heading']['fontfile'], $getargs['caption']);
			}
			else
				imagettftext($img, $cap_sz, 0, $cap_center, $ypos, $configData['captioncolor'], $fonts['heading']['fontfile'], $getargs['caption']);
		} 
	}

	function handleLegacy($img,$configData,$getargs)
	{
		$img_w = imagesx($img);
		$img_h = imagesy($img);
		$border = 10;
		$picture_offset=0;
		$new_w = $img_w+2*$border;
		$new_h = $img_h+2*$border;

		if($configData['showexif']=='ON')
		{
			$badge = exifBadge ($configData,$getargs,$fonts);
			list($exif_offset,$picture_offset)=
				(in_array($configData['exifovrposition'],array('S','SW','W','NW'))?array($border,(2*$border)+$badge['ovrwidth']):array($border+$new_w,0));
			$new_w += (2*$border)+$badge['ovrwidth'];
		}
		$canvas = imagecreatetruecolor($new_w, $new_h);
		imagealphablending($canvas, true);
		imagesavealpha($canvas, true);

		// paint picture with border
		imageFilledRectangle (
			$canvas,$picture_offset,0,
			$picture_offset+(2*$border)+$img_w,$new_h,
			hexdec('0x00FFFFFF')
		);
		imagecopy($canvas, $img, $picture_offset+$border, $border, 0, 0, $img_w, $img_h);
		imagedestroy($img); // free original

		if($configData['showexif']=='ON')
		{

			switch (strtoupper($configData['exifovrposition'])) 
			{
				case 'N' : $badge_y = $border;
					break;
				case 'NE' : $badge_y = $border;
					break;
				case 'E' : $badge_y = (($new_h - $badge['ovrheight']) / 2);
					break;
				case 'SE' : $badge_y = $new_h-($badge['ovrheight']+$border);
					break;
				case 'S' : $badge_y = $new_h-($badge['ovrheight']+$border);
					break;
				case 'SW' : $badge_y = $new_h-($badge['ovrheight']+$border);
					break;
				case 'W' : $badge_y = (($new_h - $badge['ovrheight']) / 2);
					break;
				default : $badge_y = $border;
					break;
 			}
			paintExifBadge ($canvas,$badge,$fonts,$exif_offset,$badge_y);
		}

		// return updated image
		return $canvas;
	}

	function getImageComments($procuri,$auth)
	{
		$comments = array();
		$commdata = _googleFetch($procuri,$auth,null,false,true);
		// comments 1=pub,3=poster,4=comment
		if(preg_match_all("/<\/id><published>(.*?)<\/published><updated>(.*?)2007#comment'\/><title type='text'>(.*?)<\/title><content type='text'>(.*?)<\/content>/msi",$commdata,$detail))
		{
			for ($z = 0; $z < count($detail[1]); $z++) 
			{
				$comments[] = array (
					'pubdate'	=> date('Y-m-d H:i:s', strtotime(trim($detail[1][$z]))),
					'author'	=> trim($detail[3][$z]),
					'comment'	=> trim($detail[4][$z]),
				);
			}
		}
		return $comments;
	}

	function reverseImageColorAt($dimg,$ix,$iy)
	{
                $rgb=ImageColorsForIndex($dimg,ImageColorAt($dimg, $ix, $iy));
		// reverse, deal with pure gray
                $rgb['red'] = abs($rgb['red']-255);
                $rgb['green']=abs($rgb['green']-170);
                $rgb['blue']=abs($rgb['blue']-85);
		// set color in image color space
		return ImageColorAllocate( $dimg, $rgb['red'], $rgb['green'], $rgb['blue'] );
	}

?>
