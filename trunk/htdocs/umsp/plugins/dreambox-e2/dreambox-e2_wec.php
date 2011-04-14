<?php

	include_once('/usr/share/umsp/funcs-config.php');
	include('info.php');

	// Does this WEC version support custom hooks?
	if ((defined('WECVERSION')) && (WECVERSION >= 3)) {

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
			'displaypri'	=> -25,
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
			'displaypri'	=> -10,
			'availval'	=> array('off','on'),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> '',
			'readhook'	=> wec_umspwrap_read,
			'writehook'	=> wec_umspwrap_write,
			'backuphook'	=> NULL,
			'restorehook'	=> NULL
		);

		$wec_options['DREAMBOX_HOSTNAME'] = array(
			'configname'	=> 'DREAMBOX_HOSTNAME',
			'configdesc'	=> 'Dreambox hostname or IP address<br><i>(Required)</i>',
			'longdesc'	=> 'Dreambox hostname or IP address<br><i>(Required)</i>',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_TEXT,
			'page'		=> WECP_UMSP,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> ''
		);

	       $wec_options['DREAMBOX_PROTECTED'] = array(
			'configname'	=> 'DREAMBOX_PROTECTED',
			'configdesc'	=> 'Password protection',
			'longdesc'	=> 'Dreambox web interface is password-protected',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'availvalname'	=> array('Hide','Show'),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);

		$wec_options['DREAMBOX_WEBACCOUNT'] = array(
			'configname'	=> 'DREAMBOX_WEBACCOUNT',
			'configdesc'	=> 'Dreambox account</i>',
			'longdesc'	=> 'Dreambox web interface account',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_TEXT,
			'page'		=> WECP_UMSP,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> ''
		);

		$wec_options['DREAMBOX_WEBPASSWORD'] = array(
			'configname'	=> 'DREAMBOX_WEBPASSWORD',
			'configdesc'	=> 'Dreambox password',
			'longdesc'	=> 'Dreambox web interface password',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_TEXT,
			'page'		=> WECP_UMSP,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> ''
		);
	}

?>

