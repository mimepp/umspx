<?php

	include ('info.php');
include_once($_SERVER[DOCUMENT_ROOT] . '/umsp/funcs-config.php');

	// Does this WEC version support custom hooks?
	if ((defined('WECVERSION')) && (WECVERSION >= 3)) {

		include_once(_getUMSPWorkPath() . '/funcs-config.php');

		// Insert badge if we have one
		if ((isset($pluginInfo['thumb']))&&($pluginInfo['thumb']!=''))
		{
			$desc = '<div style="float: left; padding: 4px 10px 4px 4px;"><img src="'.$pluginInfo['thumb'].'" width="60" height="60" alt="logo"></div>'
				.'<div>'.$pluginInfo['name']." v".$pluginInfo['version']." (".$pluginInfo['date'].") by "
				.$pluginInfo['author'].".<br>".$pluginInfo['desc']."<br>Information: <a href='".$pluginInfo['url']."'>".$pluginInfo['url']."</a>"
				.'</div>';
		}
		elseif ((isset($pluginInfo['art']))&&($pluginInfo['art']!=''))
		{
			$desc = '<div style="float: left; padding: 4px 10px 4px 4px;"><img src="'.$pluginInfo['art'].'" width="60" height="60" alt="logo"></div>'
				.'<div>'.$pluginInfo['name']." v".$pluginInfo['version']." (".$pluginInfo['date'].") by "
				.$pluginInfo['author'].".<br>".$pluginInfo['desc']."<br>Information: <a href='".$pluginInfo['url']."'>".$pluginInfo['url']."</a>"
				.'</div>';
		}
		else
		{
			$desc = $pluginInfo['name'].' v'.$pluginInfo['version'].' ('.$pluginInfo['date'].') by '
				.$pluginInfo['author'].'.<br>'.$pluginInfo['desc']."<br>Information: <a href='".$pluginInfo['url']."'>".$pluginInfo['url'].'</a>';
		}

		$key = strtoupper("{$pluginInfo['id']}_DESC");
		$wec_options[$key] = array(
			'configname'	=> $key,
			'configdesc'	=> $desc,
			'longdesc'	=> '',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_DESC,
			'page'		=> WECP_UMSP,
			'displaypri'	=> -50,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> ''
		);

		$wec_options[$pluginInfo['id']] = array(
			'configname'	=> $pluginInfo['id'],
			'configdesc'	=> 'Enable '.$pluginInfo['name'].' UMSP plugin',
			'longdesc'	=> '',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'displaypri'	=> -40,
			'availval'	=> array('off','on'),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> '',
			'readhook'	=> wec_umspwrap_read,
			'writehook'	=> wec_umspwrap_write,
			'backuphook'	=> NULL,
			'restorehook'	=> NULL
		);

		 $wec_options['PICASA_USERS_XML'] = array(
			'configname'	=> 'PICASA_USERS_XML',
			'configdesc'	=> 'Filename for Picasa User information',
			'longdesc'	=> 'Location of file containing Picasa User information.',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -35,
			'type'		=> WECT_TEXT,
			'page'		=> WECP_UMSP,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '-1',
			'currentval'	=> ''
		);

		 $wec_options['PICASA_MAX_PHOTOS'] = array(
			'configname'	=> 'PICASA_MAX_PHOTOS',
			'configdesc'	=> 'Maximum Photos in an Album',
			'longdesc'	=> 'Maximum Photos to display in a given album.<br>'
						.'Set to NULL to automatically display maximum<br>'
						.'You can also specify a maximum with the XML configuration.',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -30,
			'type'		=> WECT_INT,
			'page'		=> WECP_UMSP,
			'defaultval'	=> '',
			'currentval'	=> ''
		);

		$wec_options['PICASA_IMAGE_RESIZE'] = array(
			'configname'	=> 'PICASA_IMAGE_RESIZE',
			'configdesc'	=> 'Resize image [max pixels]',
			'longdesc'	=> 'Resize image [max pixels].',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -25,
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('-1','72', '144', '160', '200', '288', '320', '400', '512', '576', '640', '720', '800', '1200', '1600'),
			'availvalname'	=> array('No Resize','72px', '144px', '160px', '200px', '288px', '320px', '400px', '512px', '576px', '640px', '720px', '800px', '1200px', '1600px'),
			'defaultval'	=> '800',
			'currentval'	=> ''
		);

		$wec_options['PICASA_OVERLAY_CAPTION'] = array(
			'configname'	=> 'PICASA_OVERLAY_CAPTION',
			'configdesc'	=> 'Display Photo Caption',
			'longdesc'	=> 'Display Photo Caption Overlayed On The Image',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -20,
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','N','S'),
			'availvalname'	=> array('Hide','North','South'),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);

		$wec_options['PICASA_CAPTION_COLOR'] = array(
			'configname'	=> 'PICASA_CAPTION_COLOR',
			'configdesc'	=> 'Color Of The Photo Caption',
			'longdesc'	=> 'Color Of The Photo Caption Overlayed On The Image<br>'
						.'You can choose to reverse the background (photo) color [default].<br>'
						.'Or use a specific color from the available list.',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -18,
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('REV','16773632','16748544','16777215','16776960','16711935','16711680','12632256','8421504','8421376','8388736','8388608','65535','65280','32896','32768','255','128','0'),
			'availvalname'	=> array('Reverse Background','EXIF Title','EXIF Caption','White','Yellow','Fuchsia','Red','Silver','Gray','Olive','Purple','Maroon','Aqua','Lime','Teal','Green','Blue','Navy','Black'),
			'defaultval'	=> 'REV',
			'currentval'	=> ''
		);

		$wec_options['PICASA_SHOW_EXIF'] = array(
			'configname'	=> 'PICASA_SHOW_EXIF',
			'configdesc'	=> 'Display EXIF if available',
			'longdesc'	=> 'Display EXIF if available.',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -15,
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'availvalname'	=> array('Hide','Show'),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);

		$wec_options['PICASA_EXIF_DISPLAY_ITEMS'] = array(
			'configname'	=> 'PICASA_EXIF_DISPLAY_ITEMS',
			'configdesc'	=> 'Choose EXIF items to display',
			'longdesc'	=> 'Choose EXIF items to display<br>'
						.'Comments are displaye with EXIF data so you can limit how much<br>'
						.'screen realestate EXIF items occupy',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -10,
			'type'		=> WECT_MULTI,
			'page'		=> WECP_UMSP,
			'availval'	=> array('all','fstop','artist','make','exposure','flash','focallength','latitude','longitude','iso','model','time'),
			'availvalname'	=> array('<< All EXIF Tags >>','Aperture','Artist','Camera','Exposure','Flash Used','Focal Length','Latitude','Longitude','ISO','Model','Taken'),
			'defaultval'	=> array('all'),
			'currentval'	=> ''
		);

		$wec_options['PICASA_EXIF_OVERLAY_PCNT'] = array(
			'configname'	=> 'PICASA_EXIF_OVERLAY_PCNT',
			'configdesc'	=> 'Percent transparency of EXIF overlay',
			'longdesc'	=> 'Percent transparency of the EXIF overlay bounding<br>'
						.'box should this encroach on the displayed imageposition',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -05,
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('10', '20', '30', '40', '50', '60', '70', '80', '90', '100'),
			'availvalname'	=> array('10%', '20%', '30%', '40%', '50%', '60%', '70%', '80%', '90%', '100%'),
			'defaultval'	=> '70',
			'currentval'	=> ''
		);

		$wec_options['PICASA_EXIF_OVERLAY_POSN'] = array(
			'configname'	=> 'PICASA_EXIF_OVERLAY_POSN',
			'configdesc'	=> 'Position of the EXIF overlay',
			'longdesc'	=> 'Position of the EXIF overlay, specified as compass<br>'
						.'points.  The EXIF information will display in this position<br>'
						.'and attempt to maximize the image viewport',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -01,
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'),
			'availvalname'	=> array('North', 'North East', 'East', 'South East', 'South', 'South West', 'West', 'North West'),
			'defaultval'	=> 'E',
			'currentval'	=> ''
		);

		$wec_options['PICASA_EXIF_OVERLAY_SIZE'] = array(
			'configname'	=> 'PICASA_EXIF_OVERLAY_SIZE',
			'configdesc'	=> 'EXIF data font size',
			'longdesc'	=> 'Font size used by the EXIF overlay; this equates to<br>'
						.'the size of the final size of the badge that the<br>'
						.'EXIF data is displayed upon.',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> 05,
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('12', '13', '14', '15', '16', '18', '20', '22', '24', '26', '28', '32', '36'),
			'availvalname'	=> array('12pt.', '13pt.', '14pt.', '15pt.', '16pt.', '18pt.', '20pt.', '22pt.', '24pt.', '26pt.', '28pt.', '32pt.', '36pt.'),
			'defaultval'	=> '16',
			'currentval'	=> ''
		);

		$wec_options['PICASA_LEGACY_DISPLAY'] = array(
			'configname'	=> 'PICASA_LEGACY_DISPLAY',
			'configdesc'	=> 'Display Legacy Picasa Web Layout',
			'longdesc'	=> 'Display Legacy Picasa Web Layout.<br>'
						.'Rendering this layoute takes aditional time and resources<br>'
						.'Some buffering issues were also encountered but YMMV<br>'
						.'Renddering speed is relative and only really comes into play<br>'
						.'if EXIF data are displayed',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> 10,
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'availvalname'	=> array('Off : snappy overlay','On : slower legacy format'),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);

	}

?>

