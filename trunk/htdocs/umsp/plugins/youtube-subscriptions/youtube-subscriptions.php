<?php
// Youtube get subcriptions UMSP plugin by Dan
// http://forum.wdlxtv.com/viewtopic.php?f=49&t=713

include_once('/usr/share/umsp/funcs-log.php');
// set the logging level, one of L_ALL, L_DEBUG, L_INFO, L_WARNING, L_ERROR, L_OFF
global $logLevel;
$logLevel = L_WARNING;
global $logIdent;
$logIdent = "YTSubscriptions";

date_default_timezone_set('UTC');
define("PLUGIN_NAME",str_replace('.php','',basename(__file__)));
define("PROXY_URL","http://127.0.0.1/umsp/plugins/".PLUGIN_NAME."/".PLUGIN_NAME."-proxy.php");
define("YT_CACHE_FILE",'/tmp/'.PLUGIN_NAME.'.cache');
set_time_limit(0);

function _pluginMain($prmQuery){
   $queryData = array();
   parse_str($prmQuery, $queryData);

   if (!is_file(YT_CACHE_FILE)) {
      $cmd = str_replace('.php','',__file__).'-helper.php';
      _logInfo("Forcing cache generation: sudo su -c '/usr/bin/php5-cgi {$cmd} --insecure > /dev/null'");
      shell_exec("sudo su -c '/usr/bin/php5-cgi {$cmd} --insecure > /dev/null'");
      return array(array (
         'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME,
         'dc:title'       => "loading content...",
         'upnp:album_art'=> 'http://127.0.0.1/umsp/media/YouTube.png',
         'upnp:class'   => 'object.container',   
         'protocolInfo'   => '*:*:*:*'             
      ));
   }

   if (!isset($queryData['youtube_username'])){
      //if it's only one user, select it automatically, without an additional screen
      $users = _getUserList();
      if(count($users) != 1){
        return $users; //show the full list
      }
      else{
        //just one user - speed up things - select this user
        $queryData['youtube_username'] = $users[0]['dc:title'];
        $queryData['cmd'] = "overview";
      }
   }
//	_logDebug("Random switch: ".$queryData['rnd']);
   switch($queryData['cmd']) {
      case 'overview':
         return _getOverview($queryData['youtube_username']);
      case 'all_subscriptions':
         return _getAllSubscriptions($queryData['youtube_username']);
      case 'new_subscription_videos':
         return _getNewSubscriptionVideos($queryData['youtube_username']);
      case 'subscription_videos':
         return _getVideos($queryData['youtube_username'], $queryData['user'], 'subscriptions_videos');
      case 'playlists':
      	 return _getPlaylists($queryData['youtube_username']);
      case 'playlists_videos':
      	 return _getVideos($queryData['youtube_username'], $queryData['user'], 'playlists');
      case 'favorite_videos':
      	 return _getVideos($queryData['youtube_username'], 0, 'favorite');
      case 'recommended_videos':
      	 return _getVideos($queryData['youtube_username'], 0, 'recommended');
   	  case 'uploaded_videos':
      	 return _getVideos($queryData['youtube_username'], 0, 'uploaded');
   }
}

function _pluginSearch($prmQuery){
	_logDebug("_pluginSearch: $prmQuery");
	//get the search term. $prmQuery is (upnp:class derivedfrom "object.item.videoItem") and dc:title contains "search term"
	preg_match("/dc:title contains \"(.*)\"/", $prmQuery, $tokens);
	if(isset($tokens[1])){
		//there is a search term - look through the cache and send back the results
		$searchTerm = preg_quote($tokens[1]);
		//$searchTerm = $tokens[1];
		
		$data = unserialize(file_get_contents(YT_CACHE_FILE));
		$retMediaItems = array();
		//we don't know the username, so look through the cache of all users (a possible minor bug)
		foreach($data as $key => $value){
			$youtube_username=$key;
			_logDebug("_pluginSearch: Looking through $youtube_username's channels");
			$ud = &$data[ $youtube_username ];

            $types = array('subscriptions_videos', 'playlists', 'favorite', 'recommended', 'uploaded'); //where to do the search
            foreach($types as $type){
			    //look in each channel except new_subscription_videos (since we will be getting duplicate results)
			    foreach($ud[$type] as $key => $value){
		            $category = $key;
		            $categoryName = ($key == "0")? $type : $key; //replace channel name with type for favorite/recommended/uploaded
				    _logDebug("_pluginSearch: Looking through $categoryName videos");
					
				    foreach($ud[$type][ $category ] as $v) {
				      //do a regex search for flexibility				  
				      
				      if(preg_match("/$searchTerm/i", $v['title'])){
				      	  _logDebug("_pluginSearch: Found video ".$v['title']);
					      $dataString = array('video_id' => $v['id'], 'ClipName' => $categoryName.": ".$v['title'] );
					      //create an encoded string of the parameters
					      $encodedParams="";
					      foreach ($dataString as $key=>$val){                           
						     $encodedParams.=$key.'='.rawurlencode($val).'&';
					      }                       
					      //cut the final &
					      $encodedParams = chop($encodedParams, '&');   
					      $thumb_url="http://i.ytimg.com/vi/".$v['id']."/default.jpg"; //derive the thumbnail from the video id
					      $retMediaItems[] = array (
						       'id'         => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . $v['id'],
						       'res'         => PROXY_URL . '?'.$encodedParams,
						       'dc:title'      => $categoryName.": ".$v['title'],
						       'upnp:class'   => 'object.item.videoitem',
						       'upnp:album_art'=> $thumb_url,
						       'protocolInfo'   => 'http-get:*:video/*:*',
					       );   
					    }
				    }
			    }
			}
		}
		if(count($retMediaItems)){
			//we have at least one result
			return $retMediaItems;
		}
		else{
			return array(array (
				 'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME,
				 'dc:title'       => "No videos found when searching for $tokens[1]",
				 'upnp:album_art'=> 'http://127.0.0.1/umsp/media/YouTube.png',
				 'upnp:class'   => 'object.container',   
				 'protocolInfo'   => '*:*:*:*'                
	   		));
		}
		
	}
	else{
		return array(array (
		     'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME,
		     'dc:title'       => "No search term provided",
		     'upnp:album_art'=> 'http://127.0.0.1/umsp/media/YouTube.png',
		     'upnp:class'   => 'object.container',   
		     'protocolInfo'   => '*:*:*:*'                
	   ));
   }
}

function _getUserList(){
   $xml = simplexml_load_file('/conf/account_list.xml');
   $accounts = $xml->xpath('//service[@name="YOUTUBE"]/account');
   
   foreach($accounts as $account)
   {
      $data = array('youtube_username' => (string)$account->username,
                  'youtube_password' => (string)$account->password,
                  'cmd' => 'overview');

      $retMediaItems[] = array (
         'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($data,'','&amp;'),
         'dc:title'       => (string)$account->username,
         'upnp:album_art'=> 'http://lh4.googleusercontent.com/_CsOEwmjx9p8/TZq5tzSCR4I/AAAAAAAAO6s/5iGde2KFdBo/yt_user.png',
         'upnp:class'   => 'object.container',   
         'protocolInfo'   => '*:*:*:*'             
      );      
   }
   return $retMediaItems;
}

function _getOverview($youtube_username){
   $data = unserialize(file_get_contents(YT_CACHE_FILE));
   $ud = &$data[ $youtube_username ];
   _logDebug("Building overview");
   $thumb_url = "http://lh6.googleusercontent.com/_CsOEwmjx9p8/TZ1nkPHMtRI/AAAAAAAAO7U/YzvL2RFemdw/yt-subscriptions.png";
   $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'all_subscriptions','rnd'=>rand());
   $retMediaItems[] = array (
      'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
      'dc:title'       => "Subscriptions",
      'upnp:class'   => 'object.container',
      'upnp:album_art'=> $thumb_url,
      'protocolInfo'   => '*:*:*:*'
   );
   
   $thumb_url = "http://lh6.googleusercontent.com/_CsOEwmjx9p8/TZ1nj28Ru1I/AAAAAAAAO7Q/riplGEmZu0k/yt-playlists.png";
   $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'playlists','rnd'=>rand());
   $retMediaItems[] = array (
      'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
      'dc:title'       => "Playlists",
      'upnp:class'   => 'object.container',
      'upnp:album_art'=> $thumb_url,
      'protocolInfo'   => '*:*:*:*'
   );
   
   $thumb_url = "http://lh4.googleusercontent.com/_CsOEwmjx9p8/TZ1njyVr_QI/AAAAAAAAO7M/_RqgYCoSKpM/yt-favorites.png";
   $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'favorite_videos','rnd'=>rand());
   $retMediaItems[] = array (
      'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
      'dc:title'       => "Favorites",
      'upnp:class'   => 'object.container',
      'upnp:album_art'=> $thumb_url,
      'protocolInfo'   => '*:*:*:*'
   );
   
   $thumb_url = "http://lh6.googleusercontent.com/_CsOEwmjx9p8/TZ1nj1ORTaI/AAAAAAAAO7I/36qU1JJkNjA/yt-recommended.png";
   $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'recommended_videos','rnd'=>rand());
   $retMediaItems[] = array (
      'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
      'dc:title'       => "Recommended",
      'upnp:class'   => 'object.container',
      'upnp:album_art'=> $thumb_url,
      'protocolInfo'   => '*:*:*:*'
   );
   
   $thumb_url = "http://lh3.googleusercontent.com/_CsOEwmjx9p8/TZ2635hKI5I/AAAAAAAAO7g/KBt3XLUVi5A/yt-uploaded.png";
   $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'uploaded_videos','rnd'=>rand());
   $retMediaItems[] = array (
      'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
      'dc:title'       => "Uploaded",
      'upnp:class'   => 'object.container',
      'upnp:album_art'=> $thumb_url,
      'protocolInfo'   => '*:*:*:*'
   );
   
   return $retMediaItems;
}

function _getAllSubscriptions($youtube_username){
   $data = unserialize(file_get_contents(YT_CACHE_FILE));
   $ud = &$data[ $youtube_username ];
   $thumb_url = "http://lh3.googleusercontent.com/_CsOEwmjx9p8/TZq7SIbQWII/AAAAAAAAO6w/EgvkwNRKQFU/yt_new_videos.jpg";
   $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'new_subscription_videos');
   $retMediaItems[] = array (
      'id'          => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
      'dc:title'       => "New Subscriptions Videos",
      'upnp:class'   => 'object.container',
      'upnp:album_art'=> $thumb_url,
      'protocolInfo'   => '*:*:*:*'
   );
    
   foreach($ud['subscriptions'] as $sub) {
      //peek inside each subscription and get the thumbnail for the first video
      if(isset($ud['subscriptions_videos'][$sub][0])){
        $thumb_url = "http://i.ytimg.com/vi/".$ud['subscriptions_videos'][$sub][0]['id']."/default.jpg";
      }
      else{
        $thumb_url = "http://127.0.0.1/umsp/media/YouTube.png";
      }
      $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'subscription_videos','user'=>$sub);
      $retMediaItems[] = array (
         'id'         => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
         'dc:title'      => $sub,
         'upnp:class'   => 'object.container',
         'upnp:album_art'=> $thumb_url,
         'protocolInfo'   => '*:*:*:*'
      );
   }
   return $retMediaItems;
}

function _getNewSubscriptionVideos($youtube_username) {
   $data = unserialize(file_get_contents(YT_CACHE_FILE));
   $ud = &$data[ $youtube_username ];
   $retMediaItems = array();
   foreach($ud['new_subscription_videos'] as $v) {
      $dataString = array('video_id' => $v['id'], 'ClipName' => $v['user'].": ".$v['title'] );
      //create an encoded string of the parameters
      $encodedParams="";
      foreach ($dataString as $key=>$val){
      	$encodedParams.=$key.'='.rawurlencode($val).'&';
      }
      //cut the final &
      $encodedParams = chop($encodedParams, '&'); 
      $thumb_url="http://i.ytimg.com/vi/".$v['id']."/default.jpg"; //derive the thumbnail from the video id
      $retMediaItems[] = array (
           'id'         => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . $v['id'],
           'res'         => PROXY_URL . '?'. $encodedParams, //http_build_query($dataString,'','&amp;'),
           'dc:title'      => $v['user'].": ".$v['title'],
           'upnp:class'   => 'object.item.videoitem',
           'upnp:album_art'=> $thumb_url,
           'protocolInfo'   => 'http-get:*:video/*:*',
       );   
   }
   //based on the search values, re-sort the array
   $sort = getConfigValue('YOUTUBE_SUBSCRIPTION_SORT');
   _logDebug("Sort order: $sort");
   if($sort == "new"){
        //they are already sorted newest first
        return $retMediaItems;
   }
   else if($sort == "old"){
        //reverse sort
        return array_reverse($retMediaItems);
   }
   else if($sort == "random"){
        //shuffle sort
        shuffle($retMediaItems);
        return $retMediaItems;
   }
   else{
        //invalid setting - send the results unsorted
        return $retMediaItems;
   }
   
}

function _getPlaylists($youtube_username){
   $data = unserialize(file_get_contents(YT_CACHE_FILE));
   $ud = &$data[ $youtube_username ];

   $retMediaItems = array();
   foreach($ud['playlists'] as $play => $val) {
   
        //peek inside each playlist and get the thumbnail for the first video
        if(isset($ud['playlists'][$play][0])){
            $thumb_url = "http://i.ytimg.com/vi/".$ud['playlists'][$play][0]['id']."/default.jpg";
        }
        else{
            $thumb_url = "http://127.0.0.1/umsp/media/YouTube.png";
        }
   	  
      $dataString = array('youtube_username'=>$youtube_username,'cmd'=>'playlists_videos','user'=>$play,'rnd'=>rand());
      $retMediaItems[] = array (
         'id'         => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . http_build_query($dataString,'','&amp;'),
         'dc:title'      => $play,
         'upnp:class'   => 'object.container',
         'upnp:album_art'=> $thumb_url,
         'protocolInfo'   => '*:*:*:*'
      );
   }
   
   return $retMediaItems;
}


function _getVideos($youtube_username, $subscription, $type){
   $data = unserialize(file_get_contents(YT_CACHE_FILE));
   $ud = &$data[ $youtube_username ];

   $retMediaItems = array();
   foreach($ud[ $type ][ $subscription ] as $v) {
      $dataString = array('video_id' => $v['id'], 'ClipName' => $v['title'] );
      //create an encoded string of the parameters
      $encodedParams="";
      foreach ($dataString as $key=>$val){                           
         $encodedParams.=$key.'='.rawurlencode($val).'&';
      }                       
      //cut the final &
      $encodedParams = chop($encodedParams, '&');   
      $thumb_url="http://i.ytimg.com/vi/".$v['id']."/default.jpg"; //derive the thumbnail from the video id
      $retMediaItems[] = array (
           'id'         => 'umsp://plugins/'.PLUGIN_NAME.'/'.PLUGIN_NAME.'?' . $v['id'],
           'res'         => PROXY_URL . '?'.$encodedParams,
           'dc:title'      => $v['title'],
           'upnp:class'   => 'object.item.videoitem',
           'upnp:album_art'=> $thumb_url,
           'protocolInfo'   => 'http-get:*:video/*:*',
       );   
   }
   //based on the search values, re-sort the array
   $sortVariable="YOUTUBE_SUBSCRIPTION_SORT";
   if($type == 'subscription_videos')
        $sortVariable = 'YOUTUBE_SUBSCRIPTION_SORT';
   if($type == 'playlists')
        $sortVariable = 'YOUTUBE_PLAYLIST_SORT';
        
   $sort = getConfigValue($sortVariable);
   _logDebug("Sort order: $sort");
   if($sort == "new" || $sort == "unsorted"){
        //they are already sorted newest first
        return $retMediaItems;
   }
   else if($sort == "old" || $sort == "reverse"){
        //reverse sort
        return array_reverse($retMediaItems);
   }
   else if($sort == "random"){
        //shuffle sort
        shuffle($retMediaItems);
        return $retMediaItems;
   }
   else{
        //invalid setting - send the results unsorted
        return $retMediaItems;
   }
}

function getConfigValue($key){
        $configFile = '/conf/config';
        $fh = fopen($configFile, 'r');
        while(!feof($fh)){
                //read line by line
                $line = fgets($fh);
                //look for the variable we're searching
                preg_match("/^$key=(?:\'|\")?(.*)(?:\'|\")$/", $line, $result);
                if(isset($result[1])){
                        fclose($fh);
                        return $result[1]; //we have a match;
                }
        }
        fclose($fh);
        return null;
}
?>
