#!/usr/bin/php5-cgi
<?php

include_once('/usr/share/umsp/funcs-log.php');
// set the logging level, one of L_ALL, L_DEBUG, L_INFO, L_WARNING, L_ERROR, L_OFF
global $logLevel;
$logLevel = L_WARNING;
global $logIdent;
$logIdent = 'YTSubscriptions-helper';

//workaround to fix the issue where /tmp/umsp-log.txt is not created, and will be created and owned by root
touch("/tmp/umsp-log.txt"); //create the file if it doesn't exist
chmod("/tmp/umsp-log.txt", 0666); //rw to everybody

//path to your account_list file
$account_list_file='/conf/account_list.xml';

//IP/dns name for gdata.youtube.com
$youtubeIP='74.125.39.118';

//default values
$videoCountPerChannel=30;
$newSubscriptionVideos=300;

//youtube developer key for mad_ady. Don't use it for other projects, please
$developerKey='AI39si77LEtwwQMWZlnJ-AeV_jQ24KbfVjRvgv7fpQMhsrd6XAkrop2FmjHzxbBozUaAD30aknjD1iwgnjogz-5S2lhGj_Ga-w'; 

$insecure='';
$options  = getopt('i', array('insecure'));
if(isset($options['i']) || isset($options['insecure'])){
	$insecure = '--insecure'; //pass to curl
}

define("LOG_FILE",'/tmp/umsp-log.txt');
set_time_limit(0);

$t = microtime(true);
main();
echo "Total execution time: ".(microtime(true) - $t);

function main()
{
   //get the number of videos per channel
   global $videoCountPerChannel;
   $videoCountOverride = getConfigValue('YOUTUBE_VIDEOS_PER_CHANNEL');
   $videoCountPerChannel = ($videoCountOverride == null)? $videoCountPerChannel : $videoCountOverride;
   //get the number of new videos
   global $newSubscriptionVideos;
   $newSubscriptionOverride = getConfigValue('YOUTUBE_NEW_VIDEOS');
   $newSubscriptionVideos =  ($newSubscriptionOverride == null)? $newSubscriptionVideos : $newSubscriptionOverride;
   
   
   $user = _getUser();
   _logDebug("result of _getUser():\n".serialize($user));
   $user_data = array();

   foreach ($user as $u) {
      //authenticate user
      echo "Authenticating ".$u['youtube_username']."\n";
      flush();
      $user_data[ $u['youtube_username'] ]['auth_token'] = _getAuthToken($u['youtube_username'], $u['youtube_password']);
	  _logDebug("User data for ".$u['youtube_username']."\n".serialize($user));
      
      echo "Getting subscriptions for ".$u['youtube_username']."\n";
      flush();
      $us = _getUserSubscriptions($user_data[ $u['youtube_username'] ]['auth_token'], $u['youtube_username']);
      _logDebug("User subscriptions for ".$u['youtube_username']."\n".serialize($us));
      
      echo "Downloading information for first $videoCountPerChannel clips from each of these channels: ". (implode(", ", $us))."\n";
      flush();
      $t = _getUserVideos($us,$videoCountPerChannel, $user_data[ $u['youtube_username'] ]['auth_token']);

      $user_data[ $u['youtube_username'] ]['new_subscription_videos'] = $t['videos'];
      $user_data[ $u['youtube_username'] ]['subscriptions_videos'] = $t['user_videos'];
     
      //remove subscriptions without any content
      $user_with_videos = array_keys($t['user_videos']);
      foreach($us as $k => $v) {
         if (!in_array($v, $user_with_videos))
            unset($us[ $k ]);
      }
      $user_data[ $u['youtube_username'] ]['subscriptions'] = $us;
      
      //get any playlists
      echo "Getting playlists for ".$u['youtube_username']."\n";
      flush();
      $up = _getUserPlaylists($user_data[ $u['youtube_username'] ]['auth_token'], $u['youtube_username']);
      _logDebug("User playlists for ".$u['youtube_username']."\n".serialize($up));
      echo "Downloading information for clips from each of these playlists: ". (implode(", ", array_keys($up)))."\n";
      flush();
      $playlistVideos  = _getPlaylistVideos($up, $videoCountPerChannel, $user_data[ $u['youtube_username'] ]['auth_token']);
      
      //add the playlist videos to the file
      $user_data[ $u['youtube_username'] ]['playlists'] = $playlistVideos;
      
      //get favorite videos
      echo "Getting favorites for ".$u['youtube_username']."\n";
      flush();
      $favoriteVideos  = _getFavoriteVideos($u['youtube_username'], $videoCountPerChannel, $user_data[ $u['youtube_username'] ]['auth_token']);
      
      //add the playlist videos to the file
      $user_data[ $u['youtube_username'] ]['favorite'] = array();
      $user_data[ $u['youtube_username'] ]['favorite'][] = $favoriteVideos;
      
      //get recommended videos
      echo "Getting recommended videos for ".$u['youtube_username']."\n";
      flush();
      $recommendedVideos  = _getRecommendedVideos($u['youtube_username'], $videoCountPerChannel, $user_data[ $u['youtube_username'] ]['auth_token']);
      
      //add the playlist videos to the file
      $user_data[ $u['youtube_username'] ]['recommended'] = array();
      $user_data[ $u['youtube_username'] ]['recommended'][] = $recommendedVideos;
      
      //get uploaded videos
      echo "Getting uploaded videos for ".$u['youtube_username']."\n";
      flush();
      $uploadedVideos  = _getUploadedVideos($u['youtube_username'], $videoCountPerChannel, $user_data[ $u['youtube_username'] ]['auth_token']);
      
      //add the playlist videos to the file
      $user_data[ $u['youtube_username'] ]['uploaded'] = array();
      $user_data[ $u['youtube_username'] ]['uploaded'][] = $uploadedVideos;
      
   }
   
    /*The data structure for youtube-subscriptions.cache is as follows:
        $user_data [ $username ] ['auth_token']
        $user_data [ $username ] ['new_subscription_videos'] [ $timestamp ] ['id']
        $user_data [ $username ] ['subscriptions_videos'] [ $channel ] [ $i ] ['id']
        $user_data [ $username ] ['playlists'] [ $playlist ] [ $i ] ['id']
        $user_data [ $username ] ['favorite'] [ 0 ] [ $i ] ['id']
        $user_data [ $username ] ['recommended'] [ 0 ] [ $i ] ['id']
        $user_data [ $username ] ['uploaded'] [ 0 ] [ $i ] ['id']
      */
   echo "Writing /tmp/youtube-subscriptions.cache\n";
   file_put_contents('/tmp/youtube-subscriptions.cache',serialize($user_data));
}

function _getUserVideos(array $users, $count, $authToken)
{
   global $youtubeIP;
   global $newSubscriptionVideos;
   $videos = array();
   $user_videos = array();
   foreach($users as $user)
   {
   	  //$count is limited to 50 results by youtube API. Make several requests if necessary
	   $total_subscriptions = $count;
	   $pr = 50;
	   $videoCount=0;

	   //get channels
	   for($i=0;$i<(int)ceil($total_subscriptions/$pr);$i++) {

		  $start = ($i*$pr) + 1;
		  $t = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/{$user}/uploads?orderby=published&start-index={$start}&max-results={$pr}", $authToken);
		  $x = simplexml_load_string($t);
		  if(!$x) {
		     continue;
		  }

		  $x->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
		  $entries = $x->xpath("//a:entry");

		  foreach($entries as $t) {
		  	 if($videoCount >= $count)
		  	 	break;
		     $ts = strtotime(substr($t->published, 0, 10).' '.substr($t->published, 11, 8));
		     $vid = mb_substr($t->id,mb_strrpos($t->id,'/')+1,mb_strlen($t->id));
		     $d = array(
		        'user'=>$user,
		        'title'=>(string)$t->title,
		        'id'=>$vid,
		         #'video_link'=>"http://www.youtube.com/watch?v={$vid}&t=&fmt=22",
		        );
		     $user_videos[ $user ][] = $d;
		     $videos[ $ts ] = $d;
		     $videoCount++;
		  }
	  }
	  if (isset($user_videos[ $user ])) echo "$user: ".(count ($user_videos[$user]))." videos\n";
      flush();
   }
   
   foreach($user_videos as $k=>$v) {
      if (count($v) == 0) unset($user_videos[ $k ]);
   }

   krsort($videos);
   $videos = array_slice($videos,0,$newSubscriptionVideos,true); //keep the new subscription videos list to a reasonable size
   
   return array('videos'=>$videos, 'user_videos'=>$user_videos);
}

function _getPlaylistVideos(array $playlists, $count, $authToken)
{
   global $youtubeIP;
   $videos = array();

   foreach($playlists as $playlist => $url)
   {
   	  //trim the url
   	  preg_match("/gdata\.youtube\.com\/(.*)/", $url, $match);
   	  $playlistUrl = $match[1];
   	  $videos[ $playlist ] = array();
      
      preg_match("/openSearch:totalResults>(\d+)</s",_get_youtube_feed($youtubeIP,"gdata.youtube.com","/".$playlistUrl."&start-index=1&max-results=1", $authToken),$m);

   	  $total_items = $m[1];
   	  $pr = 20;

   	  //get videos
      for($i=0;$i<(int)ceil($total_items/$pr);$i++) {

		  $start = ($i*$pr) + 1;
		  
		  $t = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/$playlistUrl"."&start-index=$start&max-results={$pr}", $authToken);
		  $x = simplexml_load_string($t);
		  if(!$x) {
		     continue;
		  }

		  $x->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
		  $x->registerXPathNamespace('yt', 'http://gdata.youtube.com/schemas/2007');
		  $x->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
		  $entries = $x->xpath("//a:entry");
		  
		  foreach($entries as $t) {
			 //get the video id the hard way - since simple xml parsing fails
			 $videoID=(string) $t->link[0]->attributes()->href;
			 preg_match("/watch\?v=([^\&]+)\&/", $videoID, $match);
			 $videoID=$match[1];
			 _logDebug("parsed video id $videoID");
			 
		     $d = array(
		        'user'=>$playlist,
		        'title'=>(string)$t->title,
		        'id'=>$videoID, //will be set later
		        );
		     array_push($videos[ $playlist ], $d);
		  }
	  }
   	  $videos[ $playlist ] = array_slice($videos[ $playlist ],0,$newSubscriptionVideos,true); //keep the new subscription videos list to a reasonable size   
   	  echo "$playlist: ".count($videos[$playlist])."\n";
   }
   
   return $videos;
}

function _getFavoriteVideos($user, $count, $authToken)
{
   global $youtubeIP;
   $videos = array();
      
  preg_match("/openSearch:totalResults>(\d+)</s",_get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/favorites?v=2&start-index=1&max-results=1", $authToken),$m);

  $total_items = $m[1];
  $pr = 20;

  //get videos
  for($i=0;$i<(int)ceil($total_items/$pr);$i++) {

      $start = ($i*$pr) + 1;
      
      $t = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/favorites?v=2&start-index=$start&max-results={$pr}", $authToken);
      $x = simplexml_load_string($t);
      if(!$x) {
         continue;
      }

      $x->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
      $x->registerXPathNamespace('yt', 'http://gdata.youtube.com/schemas/2007');
      $x->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
      $entries = $x->xpath("//a:entry");
      
      foreach($entries as $t) {
	     //get the video id the hard way - since simple xml parsing fails
	     $videoID=(string) $t->link[0]->attributes()->href;
	     preg_match("/watch\?v=([^\&]+)\&/", $videoID, $match);
	     $videoID=$match[1];
	     _logDebug("parsed video id $videoID");
	     
         $d = array(
            'user'=>$playlist,
            'title'=>(string)$t->title,
            'id'=>$videoID, 
            );
         array_push($videos, $d);
      }
  }
  $videos = array_slice($videos,0,$newSubscriptionVideos,true); //keep the new subscription videos list to a reasonable size   
  echo "favorites: ".count($videos)."\n";
   return $videos;
}

function _getRecommendedVideos($user, $count, $authToken)
{
   global $youtubeIP;
   $videos = array();
      
  preg_match("/openSearch:totalResults>(\d+)</s",_get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/recommendations?v=2&start-index=1&max-results=1", $authToken),$m);

  $total_items = $m[1];
  $pr = 20;

  //get videos
  for($i=0;$i<(int)ceil($total_items/$pr);$i++) {

      $start = ($i*$pr) + 1;
      
      $t = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/recommendations?v=2&start-index=$start&max-results={$pr}", $authToken);
      $x = simplexml_load_string($t);
      if(!$x) {
         continue;
      }

      $x->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
      $x->registerXPathNamespace('yt', 'http://gdata.youtube.com/schemas/2007');
      $x->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
      $entries = $x->xpath("//a:entry");
      
      foreach($entries as $t) {
	     //get the video id the hard way - since simple xml parsing fails
	     $videoID=(string) $t->link[0]->attributes()->href;
	     preg_match("/watch\?v=([^\&]+)\&/", $videoID, $match);
	     $videoID=$match[1];
	     _logDebug("parsed video id $videoID");
	     
         $d = array(
            'user'=>$playlist,
            'title'=>(string)$t->title,
            'id'=>$videoID, 
            );
         array_push($videos, $d);
      }
  }
  $videos = array_slice($videos,0,$newSubscriptionVideos,true); //keep the new subscription videos list to a reasonable size   
  echo "recommended: ".count($videos)."\n";
   return $videos;
}

function _getUploadedVideos($user, $count, $authToken)
{
   global $youtubeIP;
   $videos = array();
      
  preg_match("/openSearch:totalResults>(\d+)</s",_get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/{$user}/uploads?orderby=published&v=2&start-index=1&max-results=1", $authToken),$m);

  $total_items = $m[1];
  $pr = 20;

  //get videos
  for($i=0;$i<(int)ceil($total_items/$pr);$i++) {

      $start = ($i*$pr) + 1;
      
      $t = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/{$user}/uploads?orderby=published&start-index={$start}&max-results={$pr}", $authToken);
      $x = simplexml_load_string($t);
      if(!$x) {
         continue;
      }

      $x->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
      $x->registerXPathNamespace('yt', 'http://gdata.youtube.com/schemas/2007');
      $x->registerXPathNamespace('media', 'http://search.yahoo.com/mrss/');
      $entries = $x->xpath("//a:entry");
      
      foreach($entries as $t) {
	     //get the video id the hard way - since simple xml parsing fails
	     $videoID=(string) $t->link[0]->attributes()->href;
	     preg_match("/watch\?v=([^\&]+)\&/", $videoID, $match);
	     $videoID=$match[1];
	     _logDebug("parsed video id $videoID");
	     
         $d = array(
            'user'=>$playlist,
            'title'=>(string)$t->title,
            'id'=>$videoID, 
            );
         array_push($videos, $d);
      }
  }
  $videos = array_slice($videos,0,$newSubscriptionVideos,true); //keep the videos list to a reasonable size   
  echo "uploaded: ".count($videos)."\n";
  return $videos;
}


function _getUserSubscriptions($authToken, $YouTubeUserName)
{
   global $youtubeIP;
   preg_match("/openSearch:totalResults>(\d+)</s",_get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/subscriptions?v=2&start-index=1&max-results=1", $authToken),$m);

   $total_subscriptions = $m[1];
   $subscriptions = array();
   $pr = 20;

   //get channels
   for($i=0;$i<(int)ceil($total_subscriptions/$pr);$i++) {

      $start = ($i*$pr) + 1;
      $t = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/subscriptions?v=2&start-index={$start}&max-results={$pr}", $authToken);
      $x = simplexml_load_string($t);
      if(!$x) continue;

      $x->registerXPathNamespace('a', 'http://www.w3.org/2005/Atom');
      $t = $x->xpath("//a:entry//a:title");
      foreach($t as $v) {
         preg_match("/:(.+)$/",$v,$s);
         $s = (string)trim($s[1]);
         if (!empty($s)) $subscriptions[] = $s;
         #break(2);
      }
   }
   natcasesort($subscriptions);
   return $subscriptions;   
}

function _getUserPlaylists($authToken, $YouTubeUserName)
{
   global $youtubeIP;
   preg_match("/openSearch:totalResults>(\d+)</s",_get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/playlists?v=2&start-index=1&max-results=1", $authToken),$m);

   $total_playlists = $m[1];
   $playlists = array();
   $pr = 20;

   //get playlists
   for($i=0;$i<(int)ceil($total_playlists/$pr);$i++) {

      $start = ($i*$pr) + 1;
      $xml = _get_youtube_feed($youtubeIP,"gdata.youtube.com","/feeds/api/users/default/playlists?v=2&start-index={$start}&max-results={$pr}", $authToken);
      $x = simplexml_load_string($xml);
      if(!$x) continue;
	  
	  foreach ($x->entry as $entry){
	  	//get the title and the url
	  	$src = $entry->content->attributes()->src;
	  	$playlists[ (string) $entry->title ] = (string)$src;
	  	
	  }
	
   }
   //send back array[playlistName]=url;
   natcasesort($playlists);
   return $playlists;   
}

function _getUser()
{
   global $account_list_file;
   $xml = simplexml_load_file($account_list_file);
   $data = $xml->xpath('//service[@name="YOUTUBE"]/account');
   
   $accounts = array();
   
   foreach($data as $account)
   {
      $accounts[] = array('youtube_username' => (string)$account->username,
                     'youtube_password' => (string)$account->password);     
   }
   return $accounts;
}

function _get_youtube_feed($ip,$host,$path,$authToken)
{
   global $developerKey;
   $fp = fsockopen($ip, 80, $errno, $errstr, 30);
   if (!$fp) {
       echo "$errstr ($errno)<br>\n";
   } else {
       $out = "GET {$path} HTTP/1.0\r\n";
       $out .= "Host: {$host}\r\n";
       $out .= "Authorization: GoogleLogin auth=$authToken\r\n";
       $out .= "X-GData-Key: key=$developerKey\r\n";
       $out .= "Connection: Close\r\n\r\n";
       _logDebug("_get_youtube_feed: about to send to $ip:\n$out");
       
       fwrite($fp, $out);
     
      $content = '';
      $headerPassed = false;
       while (!feof($fp)) {
           $l = fgets($fp);
         if($l == "\r\n") $headerPassed = true;
         if($headerPassed) $content .= $l;
   
       }
       fclose($fp);
//       _logDebug("_get_youtube_feed: received:\n$content\n");
      preg_match("/(<\?.*>)/s",$content,$m);
      return trim($m[1]);
   }
}

function _getAuthToken ($username, $password){
	global $youtubeIP;
	global $account_list_file;
	global $insecure;
	
	//url_encode the user/password
	$ytUser = urlencode($username);
	$ytPass = urlencode($password);
	//use curl since it uses https	
	$out = array();
	$command = "/usr/bin/curl -s -S $insecure --location https://www.google.com/accounts/ClientLogin --data 'Email=$ytUser&Passwd=$ytPass&service=youtube&source=wdtvext' --header 'Content-Type:application/x-www-form-urlencoded' 2>&1";
	_logDebug("Authenticating with command: $command");
	
	exec($command, $out);
	_logDebug(implode("\n", $out));
	
	foreach ($out as $line){
		//we're looking for this line:
		// Auth=DQAAAJ8AAADBW15VA3cV9OmZeBF0maTTyCfHxdtEj62tyHCO696_CFYe6mUpwrF1DXHnpepVXKMFj3vgtoVHLXYv0qX9Hk1fawpea5XuZbEZZBNdfaW9-hNWbpDmE-_Rl_0g3RPoBNZLkIxJaeDz4d-M2WLmyiTcmVlcvbDhI1xc7mxfkDPLKdBLdJkC1fAFUbZHqq5mKfbWB0se4hLObnxWBEj-Rzr-
		preg_match("/^Auth=(.*)$/", $line, $result);
		if(isset ($result[1])){
			//there's a match, this is the line we were after
			return $result[1];
		}
		preg_match("/SSL certificate problem, verify that the CA cert is OK./", $line, $result);
		if(isset ($result[0])){
			//curl certificate problem. Check if the time is reasonable
			if(date("Y") == 2000){
				echo "Your system date is not set. The script is unable to check the Google's/Youtube's server identity unless the date is correct. Set the date by hand, or run ntpdate pool.ntp.org and try again.\n";
			}
			else{
				echo "Unable to check Google's/Youtube's certificate (curl issue - see http://forum.wdlxtv.com/viewtopic.php?f=38&t=2469&start=0).\n";
			}
			echo "You can still connect without doing this check (but you will be vulnerable to Man-In-The-Middle attacks) by calling this script with --insecure parameter. Please note that your authentication is still encrypted, but you can't be 100% sure you are talking to a genuine Google/Youtube server.\n";
			exit;
		}
	}
	
	//if we got here, there was an error during the authentication process
	die ("Unable to authenticate $username with Youtube. Please check that the username and password are corectly set in $account_list_file or run this script in debug mode");
}

function getConfigValue($key){
	$configFile = '/conf/config';
	$fh = fopen($configFile, 'r');
	while(!feof($fh)){
		//read line by line
		$line = fgets($fh);
		//look for the variable we're searching
		preg_match("/^$key=(?:\'|\")?([0-9]+)(?:\'|\")$/", $line, $result);
		if(isset($result[1])){
			fclose($fh);
			return $result[1]; //we have a match;
		}
	}
	fclose($fh);
	return null;
}
?>

