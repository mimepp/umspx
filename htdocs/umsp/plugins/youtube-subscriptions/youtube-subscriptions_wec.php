<?php

	include_once('/usr/share/umsp/funcs-config.php');
	include ('info.php');

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

		$wec_options['YOUTUBE_VIDEOS_PER_CHANNEL'] = array (
			'configname'	=> 'YOUTUBE_VIDEOS_PER_CHANNEL',
			'configdesc'	=> 'Number of videos per channel',
			'longdesc'	=> 'Number of videos per channel<br>'
						.'Default is 30.',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_INT,
			'page'		=> WECP_UMSP,
			'defaultval'	=> '30',
			'currentval'	=> ''
		);

		$wec_options['YOUTUBE_NEW_VIDEOS'] = array (
			'configname'	=> 'YOUTUBE_NEW_VIDEOS',
			'configdesc'	=> 'Maximum number of videos in New Subscriptions. Also, Playlists/Favorites and Recommended will be limited to this limit.',
			'longdesc'	=> 'Maximum number of videos in New Subscriptions.<br>'
			            .'The maximum number of videos in a playlist/favorites/recommended will also be limited to this number.<br>'
						.'Default is 300',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_INT,
			'page'		=> WECP_UMSP,
			'defaultval'	=> '300',
			'currentval'	=> ''
		);
		
		$wec_options['PROXY_LED'] = array (
			'configname'	=> 'PROXY_LED',
			'configdesc'	=> 'Turn on the power LED when the proxy is active',
			'longdesc'	=> 'Turn on the power LED each time the proxy is working<br>'
							.'Turn off the power LED when the proxy passes control to the player.<br>'
							.'This way you know the proxy is still working when navigating.',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'availvalname'	=> array(),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);
		
		$wec_options['YOUTUBE_SUBSCRIPTION_SORT'] = array (
			'configname'	=> 'YOUTUBE_SUBSCRIPTION_SORT',
			'configdesc'	=> 'Sort order for Youtube subscriptions channel videos/favorites/recommended',
			'longdesc'	=> 'Set subscription sort order for channel videos and for new subscriptions.<br>'
			                .'Also, the order is applied for favorite/recommended videos<br>'
			                .'This does not affect the order in which the subscription channels are listed (alphabetically).<br>'
			                .'Note: Selecting shuffle will randomize the results each time you select the folder!<br>'
			                .'The default value is New videos first',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('new','old','random'),
			'availvalname'	=> array('New videos first','Old videos first','Shuffle'),
			'defaultval'	=> 'new',
			'currentval'	=> ''
		);
		
		$wec_options['YOUTUBE_PLAYLIST_SORT'] = array (
			'configname'	=> 'YOUTUBE_PLAYLIST_SORT',
			'configdesc'	=> 'Sort order for Youtube playlist videos',
			'longdesc'	=> 'Set playlist videos sort order.<br>'
			                .'Note: Selecting shuffle will randomize the results each time you select the folder!<br>'
			                .'The default value is Leave As-Is',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_SELECT,
			'page'		=> WECP_UMSP,
			'availval'	=> array('unsorted','reverse','random'),
			'availvalname'	=> array('Leave As-Is','Reverse order','Shuffle'),
			'defaultval'	=> 'unsorted',
			'currentval'	=> ''
		);
	}

?>

