<?php

	/*

	(C) 2010 Stuart Hunter
	0.1 2010-12-26 Initial Version

	Collection of utilities to allow for granular selection and subsequent download of SVN hosted plug-ins.
	Intended to be used with the web frontend also

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	Thank you, and enjoy this plugin.

	*/

	include_once(_getUMSPWorkPath() . '/funcs-config.php');
	include_once(str_replace('.php','',__FILE__).'-helper.php');

	function _pluginMain($prmQuery)
	{
		return _pluginCreatePluginList();
	}

	function _pluginCreatePluginList() 
	{
		$primeImageSize = 78 * 1024 * 2;
		$cache = getCacheFolder(); // if is null we won't cache
		$items = getConfigData();  // wraps our own and OOB configurator - auto-magic!
		// display option for user-configure universe
		if(($items['UMSPMAINT_ACTIVE_PLUGINS']!=''))
			$retListItems[] = array (
				'id'		=> 'umsp://plugins/umspmaint/umspmaint?configured',
				'dc:title'	=> 'Download/Update Configured UMSP Plugins',
				'upnp:album_art'=> 'http://lh4.ggpht.com/_xJcSFBlLg_Y/TROT7vsiAWI/AAAAAAAAAIE/tZv8XC7y27A/s200/upnp-maintenance.png',
				'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/umspmaint/umspmaint-proxy.php?method=configured',
				'upnp:class'	=> 'object.item.imageitem.photo',
				'protocolInfo'  => 'http-get:*:image/png:DLNA.ORG_PN=PNG_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
				'size'		=> $primeImageSize, // totally bogus image size with wiggle room
			);						
		// pull a list of plug-ins from SVN, we use the HTTP interface
		if(getSVNPlugins($plugins))
		{
			foreach($plugins[1] as $plugin)
			{
				// would be easier to just include the config but alas does not work
				// pull and scrape the code instead, once we have an id we can use the supported function calls
				// exclude, ourselves and none conforming plug-ins from this process
				if ($plugin!=='umspmaint')
				{
					// load up the plug-in info from SVN, check for old/new flavors
					if(loadPluginAttributes($info,$plugin,'info.php')==false)
						loadPluginAttributes($info,$plugin,'config.php');
					if(($info!==null)&&(preg_match('/(\'|")id(\'|")(.*?)=>(.*?)(\'|")(.*?)(\'|")(,|$)/',$info,$m)))
					{
						$id = $m[6];
						// now the following status isn't necassarily so
						// we may have deleted a plug-in but it still shows [in]active
						// there is a disconnect between the status cache and the actual install base
						// address the de-latch shortly for our own purposes - status cache will stay as is
						$pluginStatus = _readPluginStatus($id);
						if ($pluginStatus === null)
							$pluginStatus = 'load';
						$version = getPluginAttribute('version',$info);
						$name = getPluginAttribute('name',$info);
						$date = getPluginAttribute('date',$info);
						// cache badge art, why some images are problematic I can't get my head around
						// treat them all as local and we're good to go, sizing exercise complete 2010.12.28
						$badge = cacheBadge($cache,$id,getPluginAttribute('art',$info));
						$fname = ucwords(strtolower($name));
						$methods = array (
							'off'	=> array (
								'desc'	=> "Enable $fname",
								'do'	=> 'enable'
							),
							'on'	=> array (
								'desc'	=> "Disable $fname",
								'do'	=> 'disable'
							),
							'load'	=> array (
								'desc'	=> "Get and Enable $fname",
								'do'	=> 'get'
							),
						);
						// check for updates - test old/new file types
						// also supports plug-ins that have been deleted
						if(checkPluginUpdated($id,'info.php',$date,$version,$fname,$methods)==false)
							if(checkPluginUpdated($id,'config.php',$date,$version,$fname,$methods)==false)
								if(file_exists("/tmp/umsp-plugins/{$id}")==false)
									$pluginStatus = 'load';
						$data = array (
							'method'	=> $methods[$pluginStatus]['do'],
							'plugin'	=> $id,
						);
						$dataStr = http_build_query($data,'','&amp;');
						$retListItems[] = array (
							'id'		=> 'umsp://plugins/umspmaint/umspmaint?'.$dataStr,
							'dc:title'	=> $methods[$pluginStatus]['desc'],
							'upnp:album_art'=> $badge,
							'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/umspmaint/umspmaint-proxy.php?'.$dataStr,
							'upnp:class'	=> 'object.item.imageitem.photo',
							'protocolInfo'  => 'http-get:*:image/png:DLNA.ORG_PN=PNG_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
							'size'		=> $primeImageSize, // totally bogus image size with wiggle room
						);
						// if an update is available, add an extra menu pick
						if(($pluginStatus!=='load')&&(strpos('Update',$methods['load']['desc'])!==false))
						{
							$data = array (
								'method'	=> $methods['load']['do'],
								'plugin'	=> $id,
								'caller'	=> 'umsp'
							);
							$dataStr = http_build_query($data,'','&amp;');
							$retListItems[] = array (
								'id'		=> 'umsp://plugins/umspmaint/umspmaint?'.$dataStr,
								'dc:title'	=> $methods['load']['desc'],
								'upnp:album_art'=> $badge,
								'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/umspmaint/umspmaint-proxy.php?'.$dataStr,
								'upnp:class'	=> 'object.item.imageitem.photo',
								'protocolInfo'  => 'http-get:*:image/png:DLNA.ORG_PN=PNG_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
								'size'		=> $primeImageSize, // totally bogus image size with wiggle room
							);
						}
					}
				}
			}
		}
		// display an option for everything
		$retListItems[] = array (
			'id'		=> 'umsp://plugins/umspmaint/umspmaint?all',
			'dc:title'	=> 'Re-download/Update All UMSP Plugins',
			'upnp:album_art'=> 'http://lh5.ggpht.com/_xJcSFBlLg_Y/TRKEiD3GVAI/AAAAAAAAAGQ/2wy1LOR2-6o/s200/upnp-color.png',
			'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/umspmaint/umspmaint-proxy.php?method=all',
			'upnp:class'	=> 'object.item.imageitem.photo',
			'protocolInfo'  => 'http-get:*:image/png:DLNA.ORG_PN=PNG_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
			'size'		=> $primeImageSize, // totally bogus image size with wiggle room
		);
		return $retListItems;
	}

	function getPluginAttribute($attribute,$info)
	{
		if(preg_match('/(\'|")'.$attribute.'(\'|")(.*?)=>(.*?)(\'|")(.*?)(\'|")(,|$)/',$info,$m))
			return $m[6];
		else
			return null;
	}

	function loadPluginAttributes(&$info,$plugin,$file)
	{
		$saveErr = error_reporting(0); // kill file load [error] chatter
		try
		{
			$info = file_get_contents("http://svn.wdlxtv.com/svn/UMSP/plugins/{$plugin}/{$file}");
		}
		catch (Exception $e)
		{
			$info = null;
		}
		error_reporting($saveErr);
		return ($info != null);
	}

	function checkPluginUpdated($id,$file,$date,$version,$fname,&$methods)
	{
		$found = false;
		$pluginConfig = "/tmp/umsp-plugins/{$id}/{$file}";
		if(($pluginStatus=='on')&&(is_file($pluginConfig)))
		{
			unset($pluginInfo);
			if($file=='config.php')
				define('_DONT_RUN_CONFIG_',1);
			include($pluginConfig);
			if (is_array($pluginInfo)) 
			{
				// compare
				if(($pluginInfo['date']!=$date)||($pluginInfo['version']!=$version))
					$methods['load']['desc'] = "Update $fname (new version $version)";
			}
			if($file=='config.php')
				undefine('_DONT_RUN_CONFIG_');
			$found = true;
		}
		return $found;
	}

?>
