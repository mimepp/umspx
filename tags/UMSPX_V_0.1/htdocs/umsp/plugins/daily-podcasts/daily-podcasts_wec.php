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
			'displaypri'	=> -25,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '',
			'currentval'	=> ''
		);

		$wec_options[$pluginInfo['id']] = array(
			'configname'	=> 'daily-podcasts',
			'configdesc'	=> "Enable ".$pluginInfo['name']." UMSP plugin",
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

		$wec_options['DAILY_PODCASTS_TMP'] = array (
			'configname'	=> 'DAILY_PODCASTS_TMP',
			'configdesc'	=> "Path and filename to a genrated writable XML configuration file<br>",
			'longdesc'	=> "A generated copy of DAILY_PODCASTS_XML<br>"
						."The file is used to store imported items imported by a &amp;lt;type&gt;PodcastMenu class.<br>"
						."Where &amp;lt;type&gt;  is a type of list URL, for example, opml, json etc.<br>"
						."Since the file is used to store imported items, it MUST be writable.",
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_TEXT,
			'page'		=> WECP_UMSP,
			'availval'	=> array(),
			'availvalname'	=> array(),
			'defaultval'	=> '/tmp/temp.xml',
			'currentval'	=> ''
		);

		$wec_options['DAILY_PODCASTS_XML'] = array (
			'configname'	=> 'DAILY_PODCASTS_XML',
			'configdesc'	=> "Path and filename to an XML configuration file<br><i>(Required)</i>",
			'longdesc'	=> "See web link above for details, and the<br>"
						."iTunes subscription importer by chameleon_skin<br>"
						."available at http://wdtvtools.jakereichert.com.",
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
