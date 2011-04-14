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

		$wec_options['FILMTRAILER_BASEURL'] = array(
			'configname'	=> 'FILMTRAILER_BASEURL',
			'configdesc'	=> 'Film Trailer Language',
			'longdesc'	=> 'Select the language to use with the Film Trailer Plug-in',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_SELECT,
			'availval'	=> array (
						'http://dk.filmtrailer.com/','http://fi.filmtrailer.com/','http://fr.filmtrailer.com/',
						'http://de.filmtrailer.com/','http://it.filmtrailer.com/','http://es.filmtrailer.com/',
						'http://se.filmtrailer.com/','http://ch.filmtrailer.com/','http://ch-fr.filmtrailer.com/',
						'http://nl.filmtrailer.com/','http://uk.filmtrailer.com/',
					),
			'availvalname'	=> array(
						'Denmark','Finland','France','Germany','Italy','Spain','Sweden','Switzerland',
						'Switzerland (fr)','The Netherlands','United Kingdom',
					),
			'page'		=> WECP_UMSP,
			'defaultval'	=> 'http://uk.filmtrailer.com/',
			'currentval'	=> ''
		);
								
		$wec_options['FILMTRAILER_MAXTRAILERS'] = array(
			'configname'	=> 'FILMTRAILER_MAXTRAILERS',
			'configdesc'	=> 'Maximum Number Of Trailers To Retrieve',
			'longdesc'	=> 'Maximum Number Of Trailers To Retrieve',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_SELECT,
			'availval'	=> array (
						'200','150','100','90','80','70','60','50','40','30','20','10',
					),
			'availvalname'	=> array(
						'200 Trailers','150 Trailers','100 Trailers','90 Trailers','80 Trailers','70 Trailers','60 Trailers',
						'50 Trailers','40 Trailers','30 Trailers','20 Trailers','10 Trailers',
					),
			'page'		=> WECP_UMSP,
			'defaultval'	=> '100',
			'currentval'	=> ''
		);

		$wec_options['FILMTRAILER_ORDERBY'] = array(
			'configname'	=> 'FILMTRAILER_ORDERBY',
			'configdesc'	=> 'In which order should trailers be sorted',
			'longdesc'	=> 'Order the trailer listing by Title or Release Date',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('Title','Date'),
			'availvalname'	=> array('Title','Release Date'),
			'defaultval'	=> 'Title',
			'currentval'	=> ''
		);								
	}

?>

