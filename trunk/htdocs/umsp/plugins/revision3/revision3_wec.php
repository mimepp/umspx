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

		$wec_options['REV3_INCLUDE_ARCHIVE'] = array(
			'configname'	=> 'REV3_INCLUDE_ARCHIVE',
			'configdesc'	=> 'Include archived Revision3 shows',
			'longdesc'	=> 'Include archived (no longer in production) shows on Revision3:<br>'
						.'There are no new episodes but some very good content here',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -40,
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);

		// dynamic build of exclude lists, will be none alpha sort
		$r3html = @file_get_contents('http://revision3.com/shows');
		if(preg_match_all('/<h3><a href="\/(.*?)">(.*?)<\/a><\/h3>/',$r3html,$r3shows))
		{
			$availvvv = array('allOnNoExludeRev3');
			$availnnn = array('<< No Excludes >>');
			$availvvv = array_merge((array)$availvvv,(array)$r3shows[1]);
			$availnnn = array_merge((array)$availnnn,(array)$r3shows[2]);
			$r3html = @file_get_contents('http://revision3.com/shows/archive');
			if(preg_match_all('/<h3><a href="\/(.*?)">(.*?)<\/a><\/h3>/',$r3html,$r3shows))
			{
				$availvvv = array_merge((array)$availvvv,(array)$r3shows[1]);
				$availnnn = array_merge((array)$availnnn,(array)$r3shows[2]);
			}
		}
		else
		{
			$availvvv = array (
					'allOnNoExludeRev3','appjudgment','tbhs','bytejacker','dan30','destructoid',
					'diggdialogg','diggnation','filmriot','geekbeattv','hak5',
					'hdnation','pennpoint','scamschool','scientifictuesdays',
					'tekzilla','tomstop5','trs','unboxingporn','animetv','coop',
					'ctrlaltchicken','diggreel','epicfu','foodmob','bites','thegameshow',
					'gigaom','ifanboy','ifmini','indigital','bytes','infected',
					'instmsgs','internetsuperstar','joegenius','jvsworld','landlinetv',
					'lilsuperstar','notmtv','pixelperfect','popsiren','psbite',
					'rev3gazette','rofl','socialbrew','systm','thebroken','webdrifter',
					'webzeroes','winelibraryreserve','winelibrarytv','xlr8rtv'
				);
			$availnnn = array (
					'<< No Excludes >>','AppJudgment','The Ben Heck Show','Bytejacker','Dan 3.0','Destructoid',
					'Digg Dialogg','Diggnation','Film Riot','GeekBeat.TV','Hak5','HD Nation',
					'Penn Point','Scam School','Scientific Tuesdays','Tekzilla','Tom\'s Top 5',
					'The Totally Rad Show','Unboxing Porn','AnimeTV','CO-OP','Ctrl+Alt+Chicken',
					'The Digg Reel','Epic Fu','Food Mob','Food Mob Bites','The Game Show',
					'The GigaOm Show','iFanboy','iFanboy Mini','InDigital','InDigital Bytes',
					'Infected by Martin Sargent','INST MSGS','Internet Superstar','Joe Genius',
					'JV\'s World','LandlineTV','Lil\' Internet Superstar',
					'Not Mainstream Typical Videos','PixelPerfect','popSiren','popSiren Bite',
					'The Revision3 Gazette','ROFL','Social Brew','Systm','thebroken','Web Drifter',
					'Web Zeroes','Wine Library Reserve','Wine Library TV','XLR8R TV'
				);

		}

		$wec_options['REV3_EXCLUDE_SHOWS'] = array(
			'configname'	=> 'REV3_EXCLUDE_SHOWS',
			'configdesc'	=> 'Exclude [shows] list',
			'longdesc'	=> 'This is a list of Revision3 shows that you wish to<br>'
						.'exclude from the UMSP menu<br>'
						.'Select the shows you wish to exclude<br>'
						.'The default is all shows are active',
			'group'		=> $pluginInfo['name'],
			'type'		=> WECT_MULTI,
			'displaypri'	=> -35,
			'fieldheight'	=> '5',
			'availval'	=> $availvvv,
			'availvalname'	=> $availnnn,
			'page'		=> WECP_UMSP,
			'defaultval'	=> array('allOnNoExludeRev3'),
			'currentval'	=> ''
		);

		$wec_options['REV3_DAILY_SHOWS'] = array(
			'configname'	=> 'REV3_DAILY_SHOWS',
			'configdesc'	=> 'Include Daily shows',
			'longdesc'	=> 'Include Daily shows in the listings returned for a given show<br>'
						.'This currently is only appicable to Tekzilla<br>',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -30,
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'defaultval'	=> 'ON',
			'currentval'	=> ''
		);



		$wec_options['REV3_DEFAULT_RESOLUTION'] = array(
			'configname'	=> 'REV3_DEFAULT_RESOLUTION',
			'configdesc'	=> 'Set A Default Video Resolution',
			'longdesc'	=> 'Set A Default Video Resolution<br>And skip through menu\'s faster',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -25,
			'type'		=> WECT_SELECT,
			'displaypri'	=> -35,
			'fieldheight'	=> '5',
			'availval'	=> array ('', '/feed/MP4-Small', '/feed/MP4-Large', '/feed/MP4-High-Definition'),
			'availvalname'	=> array ('Prompt Me', 'Small', 'Large', 'HD 720p'),
			'page'		=> WECP_UMSP,
			'defaultval'	=> '',
			'currentval'	=> ''
		);

		$wec_options['REV3_SHOW_TODAY'] = array(
			'configname'	=> 'REV3_SHOW_TODAY',
			'configdesc'	=> 'Include Today\'s Shows Menu',
			'longdesc'	=> 'The Today\'s Shows Menu displays shows that premiere today',
			'group'		=> $pluginInfo['name'],
			'displaypri'	=> -20,
			'type'		=> WECT_BOOL,
			'page'		=> WECP_UMSP,
			'availval'	=> array('OFF','ON'),
			'defaultval'	=> 'OFF',
			'currentval'	=> ''
		);

	}

?>
