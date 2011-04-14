<?php

	include ('info.php');

	// Does this WEC version support custom hooks?
	if ((defined('WECVERSION')) && (WECVERSION >= 3)) {

		include_once('/usr/share/umsp/funcs-config.php');
		include_once(str_replace('_wec.php','',__FILE__).'-helper.php');

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

		getSVNPlugins($plugins_svn);
		foreach($plugins_svn[1] as $plugin_svn)
		{
			if($plugin_svn !== 'umspmaint')
			{
				$availvvv[] = $plugin_svn;
				$availnnn[] = ucwords(str_replace('-',' ',$plugin_svn));
			}
		}
		$wec_options['UMSPMAINT_ACTIVE_PLUGINS'] = array(
			'configname'	=> 'UMSPMAINT_ACTIVE_PLUGINS',
			'configdesc'	=> 'Active plug-ins',
			'longdesc'	=> 'List of Actively Supported Plug-ins<br>'
						.'These plug-ins will be pulled from SVN when you request.',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_MULTI,
			'fieldheight'	=> '5',
			'page'		=> WECP_UMSP,
			'availval'	=> $availvvv,
			'availvalname'	=> $availnnn,
			'defaultval'	=> $availvvv,
			'currentval'	=> '',
			'readhook'	=> wec_umspmaint_read,
			'writehook'	=> wec_umspmaint_write,
			'backuphook'	=> NULL,
			'restorehook'	=> NULL
		);

		$wec_options['UMSPMAINT_DELETE_INACTIVE_PLUGINS'] = array(
			'configname'	=> 'UMSPMAINT_DELETE_INACTIVE_PLUGINS',
			'configdesc'	=> 'Delete SVN plug-ins <b><i>not</i></b> on your Active list',
			'longdesc'	=> 'Delete SVN plug-ins <b><i>not</i></b> on your Active list<br>'
						.'To save space on your WD delete SVN hosted plug-in you do not use.',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('off','on'),
			'availvalname'	=> array(),
			'defaultval'	=> 'off',
			'currentval'	=> '',
			'readhook'	=> wec_umspmaint_read,
			'writehook'	=> wec_umspmaint_write,
			'backuphook'	=> NULL,
			'restorehook'	=> NULL
		);

	}

?>

