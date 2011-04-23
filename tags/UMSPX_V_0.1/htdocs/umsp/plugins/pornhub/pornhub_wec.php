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

		 $wec_options['PORNHUB_MAX_PAGE_DEPTH'] = array(
			'configname'	=> 'PORNHUB_MAX_PAGE_DEPTH',
			'configdesc'	=> 'Maximum Page Depth to Process',
			'longdesc'	=> 'Maximum Page Depth to process for each category.<br>'
						.'Th eplugin must read in and process a page of a website to develop<br>'
						.'the UMSP menu items for you to select.<br>'
						.'The mre pages the plugin has to process the slower the plugin will be.',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -30,
			'type'		=> WECT_INT,
			'page'		=> WECP_UMSP,
			'defaultval'	=> '4',
			'currentval'	=> ''
		);

		include("{$pluginInfo['id']}-helper.php");
		if(_pluginGetPHShowData($cats,$catshtml))
		{
			$availvvv = array('allOnNoExcludes');
			$availnnn = array('<< No Excludes >>');
			for ($z = 0; $z < count($cats[0]); $z++)
			{
				$availvvv[] = $cats['title'][$z];
				$availnnn[] = $cats['title'][$z];
			}
			$wec_options['PORNHUB_EXCLUDE_SHOWS'] = array(
				'configname'	=> 'PORNHUB_EXCLUDE_SHOWS',
				'configdesc'	=> 'Exclude [categories] list',
				'longdesc'	=> 'This is a list of '.$pluginInfo['name'].' shows that you wish to<br>'
							.'exclude from the UMSP menu<br>'
							.'Select the shows you wish to exclude<br>'
							.'The default is all shows are active',
				'group'		=> $pluginInfo['name'],
				'type'		=> WECT_MULTI,
				'fieldheight'	=> '10',
				'availval'	=> $availvvv,
				'availvalname'	=> $availnnn,
				'page'		=> WECP_UMSP,
				'defaultval'	=> array('allOnNoExcludes'),
				'currentval'	=> ''
			);
		}
		$catshtml = null;
	}

?>

