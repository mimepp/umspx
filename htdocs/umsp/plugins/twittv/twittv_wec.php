<?php

	include_once(_getUMSPWorkPath() . '/funcs-config.php');
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

		$twithtml = file_get_contents('http://twit.tv/');
		if(preg_match_all('/<li class="(leaf|leaf first|leaf last|leaf first active-trail|leaf last active-trail|leaf active-trail)"><a href="(.*?)"(.*?)>(.*?)<\/a><\/li>/',$twithtml,$shows))
		{
			$availvvv = array('allOnNoExcludes');
			$availnnn = array('<< No Excludes >>');
			for ($z = 0; $z < count($shows[1]); $z++)
			{
				$availvvv[] = str_replace('/','',trim($shows[2][$z]));
				$availnnn[] = trim($shows[4][$z]);
			}
		}
		else
		{
			$availvvv = array (
					'allOnNoExcludes','twit','tnt','fourcast','ipt','gtt','twig','ww','mbw','ttg','sn',
					'natn','DGW','nsfw','htg','twich','mh','kiki','FLOSS','mc',
					'twil','specials','FIB','abby','roz','radio_leo','twif','cgw','fr'
			);
			$availnnn = array(
					'<< No Excludes >>','this WEEK in TECH','Tech News Today','FourCast','iPad Today','Green Tech Today',
					'this WEEK in GOOGLE','Windows Weekly','MacBreak Weekly','The Tech Guy',
					'Security Now','net@night','Daily Giz Wiz','NSFW','Home Theater Geeks',
					'this WEEK in COMPUTER HARDWARE','Maxwell&#039;s House','Dr. Kiki&#039;s Science Hour',
					'FLOSS Weekly','Munchcast','this WEEK in LAW','TWiT Live Specials','Futures in Biotech',
					'Abby&#039;s Road','Roz Rows','Radio Leo','this WEEK in FUN','Current Geek Weekly','Frame Rate'
			);
		}
		$wec_options['TWITTV_EXCLUDE_SHOWS'] = array(
			'configname'	=> 'TWITTV_EXCLUDE_SHOWS',
			'configdesc'	=> 'Exclude [shows] list',
			'longdesc'	=> 'This is a list of TWiT.tv shows that you wish to<br>'
						.'exclude from the UMSP menu<br>'
						.'Select the shows you wish to exclude<br>'
						.'The default is all shows are active',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_MULTI,
			'fieldheight'	=> '5',
			'availval'	=> $availvvv,
			'availvalname'	=> $availnnn,
			'page'		=> WECP_UMSP,
			'defaultval'	=> array('allOnNoExcludes'),
			'currentval'	=> ''
		);

	}

?>

