<?php

	include ('info.php');

	// Does this WEC version support custom hooks?
	if ((defined('WECVERSION')) && (WECVERSION >= 3)) {

		include_once('/usr/share/umsp/funcs-config.php');
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

		$wec_options[$pluginInfo['id']] = array (
			'configname'	=> $pluginInfo['id'],
			'configdesc'	=> 'Enable '.$pluginInfo['name'].' UMSP plugin',
			'longdesc'	=> '',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'displaypri'	=> -45,
			'availval'	=> array('off','on'),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> '',
			'readhook'	=> wec_umspwrap_read,
			'writehook'	=> wec_umspwrap_write,
			'backuphook'	=> NULL,
			'restorehook'	=> NULL
		);

		$wec_options['APPLETRAILERS_SORT'] = array(
			'configname'	=> 'APPLETRAILERS_SORT',
			'configdesc'	=> "In which order should trailers be sorted",
			'longdesc'	=> "",
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('Unsorted','Alpha','Date'),
			'availvalname'	=> array('Leave As-Is','Alphabetical','Release Date'),
			'displaypri'	=> -40,
			'defaultval'	=> 'Alpha',
			'currentval'	=> ''
		);

		$wec_options['APPLETRAILERS_SHOWDATE'] = array (
			'configname'	=> 'APPLETRAILERS_SHOWDATE',
			'configdesc'	=> 'Display the date when the trailer was posted',
			'longdesc'	=> '',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'displaypri'	=> -30,
			'availval'	=> array('OFF','ON'),
			'availvalname'	=> array(),
			'defaultval'	=> 'OFF',
			'currentval'	=> '',
		);

		$wec_options['APPLETRAILERS_DEFAULT_RESOLUTION'] = array(
			'configname'	=> 'APPLETRAILERS_DEFAULT_RESOLUTION',
			'configdesc'	=> 'Set A Default Video Resolution',
			'longdesc'	=> 'Set A Default Video Resolution<br>And skip through menu\'s faster',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -25,
			'type'		=> WECT_SELECT,
			'fieldheight'	=> '7',
			'availval'	=> array ('', '_h320.mov', '_h480.mov', '_h640w.mov', '_h480p.mov', '_h720p.mov', '_h1080p.mov'),
			'availvalname'	=> array ('Prompt Me', 'Small', 'Medium', 'Large', 'HD 480p', 'HD 720p', 'HD 1080p'),
			'page'		=> WECP_UMSP,
			'defaultval'	=> '',
			'currentval'	=> ''
		);

		$wec_options['APPLETRAILERS_SHOWSEARCH'] = array (
			'configname'	=> 'APPLETRAILERS_SHOWSEARCH',
			'configdesc'	=> 'Display the option in menu',
			'longdesc'	=> '',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'displaypri'	=> -20,
			'availval'	=> array('OFF','ON'),
			'availvalname'	=> array(),
			'defaultval'	=> 'OFF',
			'currentval'	=> '',
		);

	}

?>

