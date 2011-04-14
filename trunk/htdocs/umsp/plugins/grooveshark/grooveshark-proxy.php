<?PHP

	/*

		(C) 2010 sombragris.
		http://forum.wdlxtv.com/viewtopic.php?f=53&t=2802
    
		This Grooveshark plugin is designed for Zoster's USMP server which runs (amongst others) 
		inside the EM7075 and DTS variant.
		This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
		In addtion to section 7 of the GPL terms, there is one additional term :
		G) You must keep this header intact when you intend to release modified copies of the program.
    
		Thank you, and enjoy this plugin.

	 */

include_once('grooveshark-helper.php');

// There is no MP3 cache or it failed... So let's get on
// OpenCache
if( file_exists('/var/cache/grooveshark') ) {
	$cache = new SimpleXMLElement( file_get_contents('/var/cache/grooveshark') );
} else {
	header("HTTP/1.0 404 Not Found");
	trigger_error('Unable to load grooveshark cache file ', E_USER_ERROR);
	exit();
}

$songToken = grooveshark_getTokenForSong($_GET['SongID']);

// ToDo: Necesito el puñetero ArtistID!!!

$stream = grooveshark_getStreamFromSongIDEx($_GET['SongID']);

// Tell grooveshark we are downloading this file
$result = grooveshark_markSongDownloadedEx($_GET['SongID'], $stream['streamServerID'], $stream['streamKey']);
// Ignore!
//if( $result['return'] !== '1' ) {
//	header("HTTP/1.0 404 Not Found");
//	trigger_error('Method <b>markSongDownloadedEx</b> returned <b>' . $result['return'] . '</b>', E_USER_ERROR);
//	exit();
//}

// ToDo: Start downloading
$fp = fsockopen($stream['ip'], 80, $errno, $errstr, 30);
if (!$fp) {
	header("HTTP/1.0 404 Not Found");
	trigger_error('Unable to open the stream server <b>' . $stream['ip']. '</b>', E_USER_ERROR);
	exit();
} else {
	$content = 'streamKey=' . urlencode($stream['streamKey']);
	$out = "POST /stream.php HTTP/1.1\r\n" .
	       "Host: " . $stream['ip'] . "\r\n" .
	       "Cookie: PHPSESSID=" . (string)$cache->herader->session . "\r\n" .
	       "Referer: http://listen.grooveshark.com/JSQueue.swf?20101203.19\r\n" .
	       "Content-type: application/x-www-form-urlencoded\r\n" .
	       "Content-length: " . strlen($content) . "\r\n\r\n" .
	       $content;

	fwrite($fp, $out);

	set_time_limit(0);

	$headerpassed = false;
	while ($headerpassed == false) {
		$line = fgets( $fp);
		if((stristr($line, 'Content-Type')) || (stristr($line, 'Content-Length'))) {
			header($line);
		}
		if( $line == "\r\n" ) {
			header('Content-Disposition: attachment; filename="' . urldecode($_GET['SongID']) . '.mp3"');
			$headerpassed = true;
		}
	}

	
	fpassthru($fp);
	fclose($fp);
}

//$result = _groovesharkMarkSongComplete();
//
// ToDo: Tell Grooveshark we are done
//$parameters = array(
//		'streamServerID'=> $stream['streamServerID'],
//		'streamKey'	=> $stream['streamKey'],
//		'songID'	=> $_GET['songID'],
//		'song' 		=> array(
//			'artistName'	=> null,
//			'albumID'	=> null,
//			'songID'	=> null,
//			'songName'	=> $_GET['songID'],
//			'token'		=> $stream['FileToken'], /* short token */
//			'artFilename'	=> null,
//			'albumName'	=> null,
//			'artistID'	=> null
//		)
//	);
//_groovesharkSend('markSongQueueSongPlayed');
?>
