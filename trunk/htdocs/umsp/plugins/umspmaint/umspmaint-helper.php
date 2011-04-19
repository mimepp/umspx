<?php

	/*

	(C) 2010 Stuart Hunter
	0.1 2010-12-26 Initial Version

	Collection of utilities to allow for granular selection and subsequent download of SVN hosted plug-ins.
	Intended to be used with the web frontend also

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	*/

	function getSVNPlugins(&$plugins)
	{
		// use HTML interface, get plug-ins listed
		$html = file_get_contents('http://svn.wdlxtv.com/svn/UMSP/plugins/');
		return (preg_match_all('/<li><a href="(.*?)\/">(.*?)\/<\/a><\/li>/',$html,$plugins));
	}

	function getAllPlugins($caller='wec')
	{
		// use the canned script for all plugins
		// somewhat problematic as we will replace ourselves, and although is linux because
		// is web impl. we may run into issues - may have to break out and go "long" form!!!
		// final solution will be to push these routines to a umsp-maint.php in the trunk
		if($caller=='wec')
		{
			$errors = 0;
			$status = shell_exec('sudo /etc/init.d/S64umsp svn');
			$found = 0;
			foreach($status as $line)
			{
				if(($export == null)&&(strpos($line,'Exported revision') !== false))
				{
					preg_match('/Exported revision (.*?)(\.|$)/msi',$line,$rev);
					$export = "Exported revision {$rev[1]}";
				}
			}
			return (($export != null)?"$export for all plug-ins".(($errors != 0)?" but $errors errors encountered!":''):$status);
		}
		else
		{
			// umsp call - go long form and skip self
			if(getSVNPlugins($plugins)==true)
			{
				foreach($plugins[1] as $plugin)
				{
					if($plugin!='umspmaint') $process[] = $plugin;
				}
				return getAndEnablePlugins($process);
			}
		}
	}

	function getAndEnablePlugins($plugins)
	{
		$errors = 0;
		foreach($plugins as $plugin)
		{
			// get and enable, code in config.php will take care of any prerequisites
			$cmd = 'sudo svn export --trust-server-cert --non-interactive --no-auth-cache --force --username guest --password guest '
					.'http://svn.wdlxtv.com/svn/UMSP/plugins/'.$plugin.'/ /tmp/umsp-plugins/'.$plugin.'/';
			$res = shell_exec($cmd);
			if (strpos($res,'Exported revision') !== false)
			{
				// need to parse to look for errors and proceed accordingly
				$status = array_merge((array)$status,(array)$res);
				$res = _writePluginStatus($_GET['plugin'], 'on');
				$status[] = (($res==true)?$plugin.' enabled':'Problem enabling '.$plugin);
			}
			else
			{
				if (is_array($plugin)==true)
					$status[] = 'Problem with export and enabling of '.implode(',',$plugin);
				else
					$status[] = 'Problem with export and enabling of '.$plugin;
				$errors++;
			}
		}
		$found = 0;
		foreach($status as $line)
		{
			if(($export == null)&&(strpos($line,'Exported revision') !== false))
			{
				preg_match('/Exported revision (.*?)(\.|$)/msi',$line,$rev);
				$export = "Exported revision {$rev[1]}";
			}
			if(strpos($line,' enabled') !== false) $found++;
		}
		return (($export != null)?"$export for $found plug-ins".(($errors != 0)?" but $errors errors encountered!":''):$status);
	}

	function deleteNonActivePlugins($activeplugins)
	{
		// for SVN plug-ins not on the active list delete from the WD
		// SVN plug-ins only - we're not deleting user or base plug-ins
		if(getSVNPlugins($plugins)==true)
		{
			foreach($plugins[1] as $plugin)
			{
				if(($plugin!='umspmaint')&&(in_array($pugin,$activeplugins)==false)&&(file_exists(_getUMSPWorkPath() . "/plugins/$plugin")))
				{
					$status[] = shell_exec("sudo rm -fr \"_getUMSPWorkPath() . \"/plugins/$plugin\"");
				}
			}
		}
		return $status;
	}

	function getOOBConfig()
	{
		// use standard configurator, not ideal!
		$config = file_load_contents('/conf/config');
		if(preg_match('/UMSPMAINT_ACTIVE_PLUGINS=\'(.+)\'/', $config, $m))
			$items['UMSPMAINT_ACTIVE_PLUGINS'] = $m[1];
		else
			$items['UMSPMAINT_ACTIVE_PLUGINS'] = '';
		if(preg_match('/UMSPMAINT_DELETE_INACTIVE_PLUGINS=\'(.+)\'/', $config, $m))
			$items['UMSPMAINT_DELETE_INACTIVE_PLUGINS'] = $m[1];
		else
			$items['UMSPMAINT_DELETE_INACTIVE_PLUGINS'] = 'off';
		return $items;
	}

	function getConfigData()
	{
		// melds standard OOB configuration and where we want to be
		$cache = getCacheFolder();
		if($cache != null)
			$items = getUMConfig($cache.'/umspmaint.conf');
		else
			$items = getOOBConfig();
		return $items;
	}

	function getConfiguredPlugins()
	{
		$items = getConfigData();
		if(($items['UMSPMAINT_ACTIVE_PLUGINS']==''))
		{
			$status = getAllPlugins();
		}
		else
		{
			$plugins = explode('|',$items['UMSPMAINT_ACTIVE_PLUGINS']);
			$status = getAndEnablePlugins($plugins);
			if(strtoupper($items['UMSPMAINT_ACTIVE_PLUGINS'])=='ON')
				deleteNonActivePlugins($plugins);
		}
		return $status;
	}

	function getStick()
	{
		// not everyone has a thumb drive plugged in
		// so if none found default to null and deal with in caller
		$stick = null;
		$tests =array('.wd_tv','.wdtvext-plugins','wdtvlive.bin','root.bin','umsp-user-config-here'); // last is our own magic key
		foreach ($tests as $test) 
		{
			// great idea from sombragris, scan all attached storage for configurator
			// use /tmp/media/usb/ path instead of /tmp/mnt/
			// re-think with several NFS shares, a NAS etc this is slow ... slow ... slow ... not acceptable
			//$res = shell_exec("find '/tmp/media/usb/' -name '$test'");
			$res = shell_exec("find '/tmp/mnt/' -name '$test'");
			if(trim($res)!='')
			{
				$stick = str_replace($test,'',trim($res));
				break;
			}
		}
		// check for null in caller and default as appropriate
		return $stick;
	}

	function makeCacheFolder($folder)
	{
		if (!file_exists($folder)) 
		{
			// make cache folder to store cached images and hash table (assuming in same directory)
			$oldumask = umask(0);
			@mkdir($folder, 0777);
			umask($oldumask);
		}
	}

	function getCacheFolder()
	{
		$stick = getStick();
		if($stick!='')
		{
			$cachefolder = $stick.'imgumspmaint';
			makeCacheFolder($cachefolder);
		}
		else
		{
			// volatile cache - not great more thought on this!!!
			$cachefolder = '/tmp/imgumspmaint';
			makeCacheFolder($cachefolder);
		}
		return $cachefolder;
	}

	function cacheBadge($cachefolder,$id,$badge)
	{
		$ret = $badge;
		if(($cachefolder!='')&&($badge!=''))
		{
			$hash = md5($id);
			$test = "$cachefolder/$hash.png";
			if(file_exists($test))
			{
				$ret = $test;
			}
			else
			{
				// get the file and store, used for show art as well as menu badges
				$size = getimagesize($badge);
				switch($size['mime'])
				{
					case 'image/jpeg': $im = imagecreatefromjpeg($badge); break;
					case 'image/gif': $im = imagecreatefromgif($badge); break;
					case 'image/png': $im = imagecreatefrompng($badge); break;
					case 'image/x-png': $im = imagecreatefrompng($badge); break;
					default: $im = false; break;
				} // switch
				if ($im)
				{
					imagealphablending($im, true);
					imagesavealpha($im, true);
					imagepng($im,$test);
					imagedestroy($im);
				}
				$ret = $test;
			}
		}
		return $ret;
	}

	function paintStatus($img,$fonts,$status)
	{
		if(is_array($status)==true)
		{
			$status = $status[0]; // need to address
		}
		$img_w = imagesx($img);
		$img_h = imagesy($img);
		$bx_status = imagettfbbox($fonts['title']['size'], 0, $fonts['title']['fontfile'], $status);
		imagettftext(
			$img, $fonts['title']['size'], 0, 
			(($img_w-(abs($bx_status[2] - $bx_status[0]))) / 2), $img_h - 20,
			$fonts['title']['color'], 
			$fonts['title']['fontfile'], 
			$status
		);
		// redundant given displays to black
		imagealphablending($img, true);
		imagesavealpha($img, true);
		return $img;
	}

	function fontTable($fontsize = 20)
	{
		return Array(
				'title'	=> Array (
					'fontfile'	=> '/usr/share/fonts/truetype/msttcorefonts/georgiab.ttf',
					'size'		=> $fontsize,
					'color'		=> hexdec('0x00FFF200')
				),
			);
	}

	function getUMConfig($ini)
	{
		$items=array();
		if(file_exists($ini))
		{
			$fh = fopen($ini, 'r' );
			while($line = fgets($fh))
			{
				// not a comment, obviously if comments present they'll be removed on save - rethink???
				if (preg_match('/^#/',$line) == false)
				{
					preg_match('/^(.*?)=\'(.*?)\'$/',$line,$match);
					$items[$match[1]] = trim($match[2]);
				}
			}
			fclose($fh);
		}
		return $items;
	}

	function saveConfigData($items)
	{
		$cache = getCacheFolder();
		if($cache != null)
			saveUMConfig($cache.'/umspmaint.conf',$items);
		else
			saveOOBConfig($items);
	}

	function saveUMConfig($ini,$items)
	{
		$go_config = true;
		$fh = fopen($ini, 'w') or $go_config = false;
		if($go_config==true)
		{
			foreach($items as $name => $value)
			{
				$data=((is_array($value))?implode('|',$value):$value);
				fwrite($fh, "$name='$data'\n");
			}
			fclose($fh);
		}
		else
			saveOOBConfig($items);
	}

	function saveOOBConfig($items)
	{
		foreach($item as $name => $value)
		{
			$data=str_replace("'","\'",((is_array($value))?implode('|',$value):$value));
			shell_exec('sudo /sbin/config_tool -c '.$name."='".$value."'");
		}
		shell_exec('sudo /sbin/config_tool -s');
	}

	function wec_umspmaint_write($wec_option_arr, $value) 
	{
		// simple name value config, emulates /conf/config but stored on removable media
		$modified = false;
		$items = getConfigData();
		// if value not as currently set - update
		if($items[$wec_option_arr['configname']]!=$value)
		{
			$items[$wec_option_arr['configname']] = $value;
			$modified = true;
		}
		if($modified == true) saveConfigData($items);
	}

	function wec_umspmaint_read($wec_option_arr) 
	{
		global $wec_options;
		$items = getConfigData();
		if($wec_option_arr['type'] == WECT_MULTI)
			$wec_options[$wec_option_arr['configname']]['currentval'] = explode('|',(string)$items[$wec_option_arr['configname']]);
		else
			$wec_options[$wec_option_arr['configname']]['currentval'] = (string)$items[$wec_option_arr['configname']];
	}

?>
