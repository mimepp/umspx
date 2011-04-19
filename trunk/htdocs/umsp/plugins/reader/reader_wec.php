<?php

	include_once(_getUMSPWorkPath() . '/funcs-config.php');
	include('info.php');
include_once($_SERVER[DOCUMENT_ROOT] . '/umsp/funcs-config.php');

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

		/*
		// INFO: READER_ITEMS_XML not yet implemented. You can customize the list with "UMSP Items Plugin" (Experimental - See the examples on the forum)
		$wec_options['READER_ITEMS_XML'] = array(
			'configname'	=> 'READER_ITEMS_XML',
			'configdesc'	=> 'Path and filename to an XML configuration file<br><i>(Required)</i>',
			'longdesc'	=> 'See web link above for details, XML file<br>'
						.'contains the name, url and type of RSS<br>'
						.'text feed',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_TEXT,
			'page'		=> WECP_UMSP,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> ''
		);*/
	}

?>

