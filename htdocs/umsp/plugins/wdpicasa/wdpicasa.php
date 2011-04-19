<?php

	/*

	(C) 2010 Stuart Hunter after Zoster et al.
	0.1 2010-11-21 Initial Version

	This WDPicasa plugin is designed for Zoster's USMP server which runs (amongst others)
	inside the EM7075 and DTS variant.

	Displays Picasa Web albums for users configured in PICASA_USERS_XML

	Example XML

<?xml version="1.0" encoding="UTF-8"?>
<list> 
  <!-- "Canned" uri supported by the gdata api -->
  <user>
    <propername>Picasa Team Featured Images</propername>
    <albumurl>http://picasaweb.google.com/data/feed/api/featured</albumurl>
    <maxresults>50</maxresults>
  </user>
  <!-- Specify username and password for all yor albums -->
  <user>
    <propername>Someone's Album</propername>
    <username>someone</username>
    <password>somepass</password>
    <maxresults>200</maxresults>
  </user>
  <!-- Search terms are supported, you can also do this via Search on your remote -->
  <user>
    <propername>Puppy Images Example</propername>
    <albumurl>http://picasaweb.google.com/data/feed/api/all?q=puppy</albumurl>
    <maxresults>20</maxresults>
  </user>
  <!-- Here is an example of a specific public album "somealbum created by someone" -->
  <user>
    <propername>Public User Album</propername>
    <albumurl>http://picasaweb.google.com/data/feed/api/user/someone/album/somealbum</albumurl>
  </user>
  <!-- An invite example, user sends an email with an invite - take username, albumid and auth string -->
  <user>
    <propername>Invite Example</propername>
    <albumurl>http://picasaweb.google.com/data/feed/api/user/someone/albumid/5544333222198177654?authkey=Tv1kRgCbbE69i4o1HcG8</albumurl>
  </user>
  <!-- Finally, public albums for a specified username -->
  <user>
    <propername>Test Anonymous [Public] Albums</propername>
    <username>username_or_gmail_address</username>
  </user>
</list>

	If only a username is provided then only public albums will be displayed
	If both username and password are provided all user albums will be displayed
	Alternativly you can specify an album URI if you know it

	No paging or cache mechanisms are currenly used so if albums are large - look out!

	This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
	In addtion to section 7 of the GPL terms, there is one additional term :
	G) You must keep this header intact when you intend to release modified copies of the program.

	Thank you, and enjoy this plugin.

	*/

	// helper routines
	include_once('wdpicasa-helper.php');

	function _pluginMain($prmQuery)
	{

		$queryData = array();
		if (strpos($prmQuery,'&amp;')!==false) $prmQuery=str_replace('&amp;','&',$prmQuery);
		parse_str($prmQuery, $queryData);
		// album specified and driven via the plugin or configurator
		if (isset($queryData['albumurl'])&&($queryData['albumurl']!=''))
		{
			return _pluginCreateImageItems($queryData);
		}
		elseif (isset($queryData['itemurl']))
		{
			return _pluginCreateImage($queryData);
		}
		elseif (isset($queryData['picasa_username']))
		{
			return _pluginCreateAlbumItems($queryData);
		}
		else
		{
			return _pluginCreateUserList();
		}

	}

	function _pluginCreateUserList()
	{

		$retListItems = array();
		$configData = getConfigData();
		if(($configData['xmlfile']!='')and(file_exists($configData['xmlfile'])))
		{
			$users_xml = file_get_contents($configData['xmlfile']);
			try
			{
				$node = new SimpleXMLElement($users_xml);
				foreach ($node->user as $user) 
				{
					$url = (string)$user->albumurl;
					// re-write to "standard" gdata api form if base form is presented
					if(strpos($url,'/base/')!==false)
						$url = preg_replace(array('/\/base\//','/(alt=rss&amp;|alt=rss&|alt=rss|&amp;alt=rss$|&alt=rss$)/'), array('/api/',''), $url);
					$uri = parse_url($url);
					$pass = (string)$user->password;
					if((strpos(strtolower((string)$uri['query']),'authkey=')!==false) || ($pass != ''))
					{
						$badge = $configData['badgeprivate'];
					}
					elseif ((strpos(strtolower((string)$uri['query']),'q=')!==false) || (strpos(strtolower((string)$uri['query']),'tag=')!==false))
					{
						$badge = $configData['badgesearch'];
					}
					else
						$badge = $configData['badgepublic'];
					$data = array (
						'picasa_username'	=> (string)$user->username,
						'picasa_password'	=> $pass,
						'albumurl'		=> urlencode($url),
						'propername'		=> (string)$user->propername,
						'max-results'		=> (string)$user->maxresults,
					);
					$dataStr = http_build_query($data,'','&amp;');
					$retListItems[] = array (
						'id'		=> 'umsp://plugins/wdpicasa/wdpicasa?'.$dataStr,
						'dc:title'	=> 'Picassa Web - '.str_replace("'","\'",(string)$user->propername),
						'res'		=> 'umsp://plugins/wdpicasa/wdpicasa?'.$dataStr,
						'upnp:album_art'=> $badge,
						'upnp:class'	=> 'object.container'
					);
				}
			}
			catch (Exception $e)
			{
				$node = null;
			}
			if(empty($retListItems))
			{
				$retListItems[] = array (
					'id'		=> 'umsp://plugins/wdpicasa/wdpicasa?badxml=',
					'dc:title'	=> 'The XML configuration file is either empty or invalid, check the XML is correctly formatted and does not contain unsupported entries.'
								.'See forum.wdlxtv.com for details. You can press Search at any time to find images.',
					'res'		=> 'umsp://plugins/wdpicasa/wdpicasa?badxml=',
					'upnp:album_art'=> $configData['stopbadge'],
					'upnp:class'	=> 'object.container'
				);
			}
    		} 
		else 
		{
			// Hint to configure
			$retListItems[] = array (
				'id'		=> 'umsp://plugins/wdpicasa/wdpicasa?nothing=',
				'dc:title'	=> 'Please create an XML configuration file, and configure with config_tool -c PICASA_USERS_XML=\'path to the file\' - '
							.'see forum.wdlxtv.com for details. You can press Search at any time to find images.',
				'res'		=> 'umsp://plugins/wdpicasa/wdpicasa?nothing=',
				'upnp:album_art'=> $configData['stopbadge'],
				'upnp:class'	=> 'object.container'
			);
		}
		return $retListItems;   
    
	}  // function

	function _pluginSearch($prmQuery)
	{
		//if (preg_match('/and dc:title contains "(.*?)"/', $prmQuery, $searchterm))
		if (preg_match('/and dc:(title|album|genre) contains "(.*?)"/', $prmQuery, $searchterm))
		{
			$param = array (
				'albumurl'	=> urlencode("http://picasaweb.google.com/data/feed/api/all?q={$searchterm[2]}"),
				'propername'	=> 'User Search '.ucfirst($searchterm[1]),
				'max-results'	=> 100 // should be configurable
			);
			return _pluginCreateImageItems($param);
		}
		else
		{
			return null;
		}
	}

	function _pluginCreateAlbumItems($param)
	{
		$auth = null;
		if($param['albumurl']!='')
		{
			$feedUrl = urldecode($param['albumurl']);
			// check if we have an inline auth key as per z80pio & mad_ady request
			if (preg_match('/authkey=(?P<auth>\w+)(&|\z)/i', $feedUrl, $m))
			{
				$auth = (string)$m['auth'];
			}
		}
		else
		{
			$picasa_base_url="http://picasaweb.google.com/data/feed/api/user";
			// feed URL, public albums
			($param['picasa_username']!='')?$feedUrl = $picasa_base_url.'/'.urlencode($param['picasa_username']).'?kind=album':$feedUrl = $picasa_base_url.'/all?kind=album';
			if(($param['max-results']!='')&&($param['max-results']>0)){$feedUrl = $feedUrl.'&max-results='.$param['max-results'];}
	
			// and if authorized, private too
			$auth = null;
			if($param['picasa_password']!='')
			{
				$auth =_googleAuthAccount($param['picasa_username'],$param['picasa_password']);
				if($auth!=null) $feedUrl = str_replace('http','https',$feedUrl);
			}
		
			$feedUrl .= ((($auth==null)&&($param['picasa_username']!=''))?'&access=visible':'&access=all');
		}	

		// fetch the requested URI, auth will be applied if available
		// the data are returned as as "simple" XML object
		$xml_album = _googleFetch($feedUrl,$auth);
		if ($xml_album)
		{
			$namespace = $xml_album->getDocNamespaces();
			// process album
			foreach( $xml_album->entry as $entry )
			{
	
				$title		= (string)$entry->title;
				$updated	= (string)$entry->updated;
				$gphoto		= $entry->children($namespace['gphoto']);
				$imgNumPhotos	= (string)$gphoto->numphotos;
				// don't process empty albums
				if($imgNumPhotos!=0)
				{
					$albumId	= (string)$gphoto->id;
					$imgUser	= (string)$gphoto->user;
					$imgName	= (string)$gphoto->name;
					$access		= (string)$gphoto->access; // private, public, etc...
					$media		= $entry->children($namespace['media']);
					$imgDesc	= (string)$media->group->description;
					$imgThumb	= (string)$media->group->thumbnail[0]->attributes();
					$albumfeedUrl	= "$picasa_base_url/$imgUser/albumid/$albumId?kind=photo"; // will return both photo & video
	
					$data = array (
						'albumurl'	=> $albumfeedUrl,
						'driver'	=> $param,
					);
	
					$dataStr = http_build_query($data, '','&amp;');
					$retListItems[] = array (
						'id'		=> 'umsp://plugins/wdpicasa/wdpicasa?'.$dataStr,
						'dc:title'	=> $title . (($imgDesc != '') ? " - $imgDesc" : '')." [$imgNumPhotos Photos] [$access]",
						'res'		=> 'umsp://plugins/wdpicasa/wdpicasa?'.$dataStr,
						'upnp:album_art'=> $imgThumb,
						'upnp:class'	=> 'object.container'
					);

				} // got photos?
	
			} // foreach
		}

		return $retListItems;

	}

	function _pluginCreateImage($param) {

		// will use this to overlay EXIF
        	return NULL;

	} # function

	function _pluginCreateImageItems($param)
	{

		$feedUrl = $param['albumurl'];
                if (array_key_exists('driver',$param))
		{
			if($param['driver']['picasa_password']!='')
			{
				$auth =_googleAuthAccount(urldecode($param['driver']['picasa_username']),urldecode($param['driver']['picasa_password']));
			}
		}
		else
		{
			$feedUrl = urldecode($feedUrl);
		}

		if(($param['max-results']!='')&&($param['max-results']>0))
		{
			$feedUrl .= ((strpos($feedUrl,'?')===false)?'?':'&').'&max-results='.$param['max-results'];
		}

		$configData = getConfigData();
		// fetch the requested URI, auth will be applied if available
		// the data are returned as as simple XML object
		$xml_album = _googleFetch($feedUrl,$auth);

		if($xml_album)
		{
			// protocol mapping, also used to drive item inclusion
			$proto = array (
				'image/gif'	=>	'image/gif:DLNA.ORG_PN=GIF_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
				'image/jpeg'	=>	'image/jpeg:DLNA.ORG_PN=JPEG_MED;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
				'image/png'	=>	'image/png:DLNA.ORG_PN=PNG_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000',
				'image/x-png'	=>	'image/png:DLNA.ORG_PN=PNG_LRG;DLNA.ORG_CI=01;DLNA.ORG_FLAGS=00f00000000000000000000000000000'
			);
			// we'll reduce the image size to help improve screen real estate
			// following valus are documented but appears to work with any input
			// 512 seems to be the "sweet spot" for the WD to play nice and display all content - YMMV
			$imgScale = $configData['imageresize']; // one of 32, 48, 64, 72, 144, 160, 200, 288, 320, 400, 512, 576, 640, 720, or 800
			$namespace = $xml_album->getDocNamespaces();

			foreach ($xml_album->entry as $entry) 
			{
				$media		= $entry->children($namespace['media']);
				$content	= $media->group->content->attributes();
				$cont_type	= (string)$content['type'];
				// only process image flavors for which we've supplied protocol mappings
				// video formats don't show up here so check is somewhat redundant
				if(array_key_exists($cont_type,$proto)) 
				{

					unset($data); // clear working variable
					$author		= (string)$media->group->credit;
					$caption	= trim((string)$media->description).'';
					$cont_url	= (string)$content['url'];
					//$cont_width	= (string)$content['width'];
					//$cont_height	= (string)$content['height'];
					$title		= $entry->title;
					if($caption=='')
						$caption= trim((string)$entry->summary).'';
					$updated	= $entry->updated;
					$gphoto		= $entry->children($namespace['gphoto']);
					$photoId	= (string)$gphoto->id;
					$height		= (string)$gphoto->height;
					$width		= (string)$gphoto->width;
					$size		= (string)$gphoto->size;
					$albumid	= (string)$gphoto->albumid;
					$commentCount	= (string)$gphoto->commentCount;

					# added lat/long 2010.11.29 (sh)
					#/georss:where/gml:Point/gml:pos
					# node no longer exists errors filling logs so I broke out and node walked
					$georss		= $entry->children($namespace['georss']);
					$gml            = (empty($georss))?null:$georss->children($namespace['gml']);
					$pos		= (string)((empty($gml))?'':(string)$gml->Point->pos);
					list($latitude,$longitude) = explode(' ',$pos);
					$dc_date = (($time!=='')?$time/1000:strtotime($updated));

					// if a video then videostatus will contain a string - can perform conditional procssing based on this
					// videos are converted to FLV to be compliant with You-Tube services, status of conversion is provided
					$videostatus    = (string)$gphoto->videostatus;
					// string is one of :
					//    * failed: a processing error has occured and the video should be deleted
					//    * pending: the video is still being processed
					//    * ready: the video has been processed but still needs a thumbnail
					//    * final: the video has been processed and has received a thumbnail
					// we'll only publish if is one of the later two status
					if ($videostatus=='') // empty = photo
					{
						// not a video so pull exif attributes
						$exif		= $entry->children($namespace['exif'])->tags;
						$fstop		= (string)$exif->fstop;
						$make		= (string)$exif->make;
						$model		= (string)$exif->model;
						$exposure	= (string)$exif->exposure;
						$distance	= (string)$exif->distance;
						$flash		= (string)$exif->flash;
						$focallength	= (string)$exif->focallength;
						$iso		= (string)$exif->iso;
						$time		= (string)$exif->time;

						// create photo item that "drive" image display
						$data = array (
							'itemurl'	=> ((($width<=$imgScale)&&($height<=$imgScale))?$cont_url:getThumbnailUrl($cont_url, $imgScale)),
							'imageid'	=> $photoId,
							'albuid'	=> $albumId,
							'cont_type'	=> $cont_type,
						);

						addPayloadTag($data,'fstop',$configData,(empty($fstop)?'n/a':'f/'.$fstop));
						addPayloadTag($data,'make',$configData,(empty($make)?'n/a':$make));
						addPayloadTag($data,'model',$configData,(empty($model)?'n/a':$model));
						addPayloadTag($data,'exposure',$configData,(empty($exposure)?'n/a':convertShutterFrac($exposure)));
						addPayloadTag($data,'distance',$configData,(empty($distance)?'n/a':$distance));
						addPayloadTag($data,'flash',$configData,(empty($flash)?'n/a':(($flash=='true')?'Yes':'No')));
						addPayloadTag($data,'focallength',$configData,(empty($focallength)?'n/a':$focallength.'mm'));
						addPayloadTag($data,'iso',$configData,(empty($iso)?'n/a':$iso));
						addPayloadTag($data,'time',$configData,(empty($time)?'n/a':date('Y-m-d H:i:s',$time/1000)));
						addPayloadTag($data,'latitude',$configData,(empty($latitude)?'n/a':$latitude));
						addPayloadTag($data,'longitude',$configData,(empty($longitude)?'n/a':$longitude));
						addPayloadTag($data,'artist',$configData,(empty($author)?'Unknown':trim($author)));
						//addPayloadTag($data,'caption',$configData,(empty($caption)?$title:trim($caption)));

						if(in_array($configData['overlaycaption'],array('N','S'))&&($caption!=''))
							$data['caption']=trim($caption);

						// fold caption if available into title, if empty use the image file name
						$dataStr = http_build_query($data, '', '&amp;');
						$retListItems[] = array (
							'id'		=> 'umsp://plugins/wdpicasa/wdpicasa?imageid='.$photoId,
							'dc:title'	=> makeSafeItemDescription(trim(
										((trim($caption)!='')?$caption:$title)
										." [$width"."x$height]"
										.((strtoupper($configData['showexif'])=='ON')?
											exifTagsX($data,false,$configData['displayexiftags']):'')
										),true,true),
							'res'		=> 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/wdpicasa/wdpicasa-proxy.php?'.$dataStr,
							'upnp:class'	=> 'object.item.imageitem.photo',
							'protocolInfo'  => 'http-get:*:'.$proto[$cont_type],
							'upnp:album_art'=> getThumbnailUrl($cont_url, 160), # 160 pix restriction
							'size'		=> 2*(($size!=='')?$size:($width*$height*20)), // totally bogus value if size not avail
							'upnp:artist'	=> $author,
							'upnp:genre'	=> '',
							'upnp:album'	=> $albumid,
							'dc:date'	=> date('Y-m-d H:i:s',$dc_date),
							'episode_ts'	=> $dc_date
						);

					}
					elseif(in_array($videostatus,array('ready','final')))
					{

						// we have a video so pull it's googlevideo URL
						// unfortunatly the details need to be sources from a side-car atom file
						// guessing that was the easiest impl. avoided breaks to existing gdata funcs
						// actually looks to be an issue with simplexml_load discarding details
						// load and grab attributes from raw file - minor hit as seems to scale well enough
						$atomvideo = _googleFetch((string)$entry->id,$auth,null,false,true);
						// simple scrape for the media URI - real Q&D
						if(preg_match_all("/<media:content url='(.*?)' height='(.*?)type='(.*?)' medium='video'\/>/",$atomvideo,$detail))
						{
							for ($z = 0; $z < count($detail[1]); $z++) 
							{
								if(strpos($detail[3][$z],'video/')!==false) // shockwave (flv) etc discarded
								{
									if(trim($caption)!=='')
										$caption = makeSafeItemDescription($caption,true,true);
									$retListItems[] = array (
										'id'		=> 'umsp://plugins/wdpicasa/wdpicasa?videoid='.$imgId,
										'dc:title'	=> $title.(($caption!=='')?" - $caption":'')." [$width"."x$height]",
										'res'		=> $detail[1][$z],
										'upnp:class'	=> 'object.item.videoitem',
										'protocolInfo'  => 'http-get:*:'.$detail[3][$z].':*',
										'upnp:album_art'=> getThumbnailUrl($cont_url, 160), # 160 pix restriction
										'dc:date'	=> date('Y-m-d H:i:s',$dc_date),
										'episode_ts'	=> $dc_date
									);
								} // original video container
							} // various google playback containers
						}

					}

				} // foreach

			} // if image

		} // have an XML object

		return $retListItems;

	}

	function addPayloadTag(&$payload=array(),$tag,$configData,$value='n/a')
	{
		if((strtoupper($configData['showexif'])=='ON')&&(('all'==$configData['displayexiftags'])||(strpos("|{$configData['displayexiftags']}|","|$tag|")!==false)))
			$payload[$tag] = $value;
	}

?>
