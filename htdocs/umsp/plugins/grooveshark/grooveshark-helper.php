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

function grooveshark_authenticateUser($username, $password)
{
	$user_data = null;

	if( is_string($username) and is_string($password) ) {
		if( (strlen($username) > 0) and (strlen($password) > 0) ) {
			$parameters = array(
				'json' 	=> array(
					'header'	=> array(
						'client'	=> 'htmlshark',
						'clientRevision'=> '20100831'
					),
					'parameters'	=> array(
						'username'	=> $username,
						'password'	=> $password,
						'token'		=> md5($username.':'.$password),
						'session'	=> grooveshark_initiateSession()
					)
				),
				'uri'	=> array(
					'protocol'	=> 'https',
					'host'		=> 'listen.grooveshark.com'
				)
			);

			$user_data = _groovesharkSend('authenticateUser', $parameters);
		}
	}

	return $user_data;
}

function grooveshark_getCommunicationToken($host = null, $force = null)
{
	// Default host is cowbell
	if( is_null($host) )
		$host='cowbell.grooveshark.com';
	if( is_null($force) ) {
		$force = false;
	} elseif( !is_bool($force) ) {
		$force = false;
	}

	$token = null;
	// Load Cache
	if( file_exists('/var/cache/grooveshark') )
		$cache = new SimpleXMLElement( file_get_contents('/var/cache/grooveshark') );

	if( is_null($cache) )
		trigger_error('Unable to load cache', E_USER_ERROR);


	if( $force == false ) {
		$elements = $cache->xpath('/cache/communicationToken/server[@uri="'.$host.'"]');
		if( count($elements) > 0 ) {
			$token = $elements[0][0];
			if( $token == '' )
				$token = null;
		}
	}

	if( is_null($token) ) {
		// Request a token to the defined host
		$parameters = array(
			'json' 	=> array(
				'parameters' => array(
					'secretKey' => md5(grooveshark_initiateSession())
				)
			),
			'uri'	=> array(
				'host' => $host
			)
		);
		switch( strtolower($host) ) 
		{
			case 'listen.grooveshark.com':
				$parameters['json']['header'] = array(
					'client' => 'htmlshark',
					'clientRevision' => '20100831'
				);		
				break;
			case 'cowbell.grooveshark.com':
				$parameters['json']['header'] = array(
					'client' => 'jsqueue',
					'clientRevision' => '20101012.36'
				);
				break;
		}

		$token = _groovesharkSend('getCommunicationToken', $parameters);
		if( !is_null($token) ) {
			$elements = $cache->xpath('/cache/communicationToken/server[@uri="'.$host.'"]');
			if( count($elements) > 0 ) {
				$dom=dom_import_simplexml($elements[0]);
				$dom->parentNode->removeChild($dom);
			}
			$element = $cache->communicationToken->addChild('server', $token);
			$element->addAttribute("uri", $host);
			$cache->asXML('/var/cache/grooveshark');
		}
	}

	return $token;
}

function grooveshark_getCountry()
{
	// Load Cache
	if( file_exists('/var/cache/grooveshark') )
		$cache = new SimpleXMLElement( file_get_contents('/var/cache/grooveshark') );

	if( is_null($cache) )
		$cache = new SimpleXMLElement('<cache></cache>');

	$country = (string)$cache->country;
	if( $country == '' ) {
		$country = null;
	} else {
		$country = json_decode(base64_decode($country), true);
	}

	if( is_null($country) ) {
		$country = _groovesharkSend('getCountry');
		if( is_null($country) ) {
			$country = array(
				'ID'	=> 223,
				'CC1'	=> '0',
				'CC2'	=> '0',
				'CC3'	=> '0',
				'CC4'	=> '2147483648'
			);
		}
		$cache->country = base64_encode(json_encode($country));
		$cache->asXML('/var/cache/grooveshark');
	}

	return $country;
}

function grooveshark_getFavorites($type = 'Songs')
{
	$result = null;

	// Load Cache
	if( file_exists('/var/cache/grooveshark') ) {
		$cache = new SimpleXMLElement( file_get_contents('/var/cache/grooveshark') );
		if( !is_null($cache) ) {
			$parameters = array(
				'json' 	=> array(
					'header'	=> array(
						'client'	=> 'htmlshark',
						'clientRevision'=> '20100831'
					),
					'parameters'	=> array(
						'userID' => (string)$cache->user->userid[0],
						'ofWhat' => $type
					)
				),
				'uri'	=> array(
					'host'		=> 'listen.grooveshark.com'
				)
			);
			$result = _groovesharkSend('getFavorites', $parameters);
		} 
	}
	
	return $result;
}

function grooveshark_getSearchResultsEx($searchText, $type = 'Songs', $limit = null)
{
	// Not sure if 'limit' is allowed here :/
	$parameters = array(
		'json' 	=> array(
			'header'	=> array(
				'client'	=> 'htmlshark',
				'clientRevision'=> '20100831'
			),
			'parameters'	=> array(
				'query' => $searchText,
				'type'	=> $type
			)
		),
		'uri'	=> array(
			'host'		=> 'listen.grooveshark.com'
		)
	);

	$result = _groovesharkSend('getSearchResultsEx', $parameters);
	return $result['result'];
}

function grooveshark_getStreamFromSongIDEx($songID)
{
	$parameters = array(
		'json' 	=> array(
			'parameters'	=> array(
				'mobile' 	=> false,
				'prefetch'	=> false,
				'songID'	=> (integer)$songID,
				'country'	=> grooveshark_getCountry()
			)
		)
	);

	return _groovesharkSend('getStreamKeyFromSongIDEx', $parameters);
}

function grooveshark_getTokenForSong($songID)
{
	$parameters = array(
		'json' 	=> array(
			'header'	=> array(
				'client'	=> 'htmlshark',
				'clientRevision'=> '20100831'
			),
			'parameters'	=> array(
				'songID' => $songID,
				'country'	=> grooveshark_getCountry()
			)
		),
		'uri'	=> array(
			'host'		=> 'listen.grooveshark.com'
		)
	);

	return _groovesharkSend('getTokenForSong', $parameters);
}

function grooveshark_getUserSidebar() {
	$parameters = array(
		'json' 	=> array(
			'header'	=> array(
				'client'	=> 'htmlshark',
				'clientRevision'=> '20100831'
			)
		),
		'uri'	=> array(
			'host'		=> 'listen.grooveshark.com'
		)
	);
	return _groovesharkSend('getUserSidebar', $parameters);
}

function grooveshark_initiateQueue()
{
	return _groovesharkSend('initiateQueue');
}

function grooveshark_initiateSession($force = null)
{
	$cache = null;
	if( is_null($force) ) {
		$force = false;
	} elseif( !is_bool($force) ) {
		$force = false;
	}

	$session = null;
	if( file_exists('/var/cache/grooveshark') ) {
		$contents = file_get_contents('/var/cache/grooveshark');
		$cache = new SimpleXMLElement( $contents );

	}

	if( is_null($cache) )
		$cache = new SimpleXMLElement('<cache></cache>');

	if( $force == false ) {
		// Look for it in cache
		$session = (string)$cache->session[0];
		if( $session == '' )
			$session = null;
	}

	if( is_null($session) ) {
		$session = _groovesharkSend('initiateSession');
		if( !is_null($session) ) {
			$cache->session = $session;
			// When the session ID is renewed Communication Tokens *NEED* to be renewed
			$cache->communicationToken = null;
			$cache->asXML('/var/cache/grooveshark');
		}
	}

	return $session;
}

function grooveshark_markSongDownloadedEx($songID, $streamServerID = null, $streamKey = null)
{	
	if( is_null($streamServerID) || is_null($streamKey) ) {	
		$result = grooveshark_getStreamFromSongIDEx($songID);
		$streamServerID = $result['streamServerID'];
		$streamKey = $result['streamKey'];
	}

	$parameters = array(
		'json' 	=> array(
			'parameters'	=> array(
				'streamServerID'=> $streamServerID,
				'streamKey'	=> $streamKey,
				'songID'	=> (integer)$songID
			)
		)
	);

	// Usually return 1 (OK)
	return _groovesharkSend('markSongDownloadedEx', $parameters);
}

function grooveshark_playlistGetSongs($playlistID, $limit = null)
{
	$parameters = array(
		'json' 	=> array(
			'header'	=> array(
				'client'	=> 'htmlshark',
				'clientRevision'=> '20100831'
			),
			'parameters'	=> array(
				'playlistID'	=> $playlistID
			)
		),
		'uri'	=> array(
			'host'		=> 'listen.grooveshark.com'
		)
	);
	if( !is_null($limit) )
		$parameters['json']['parameters']['limit'] = $limit;

	$result = _groovesharkSend('playlistGetSongs', $parameters);
	return $result['Songs'];
}

function grooveshark_popularGetSongs($type = 'daily' , $limit = null)
{
	$parameters = array(
		'json' 	=> array(
			'header'	=> array(
				'client'	=> 'htmlshark',
				'clientRevision'=> '20100831'
			),
			'parameters'	=> array(
				'type'		=> $type
			)
		),
		'uri'	=> array(
			'host'		=> 'listen.grooveshark.com'
		)
	);

	if( !is_null($limit) )
		$parameters['json']['parameters']['limit'] = $limit;

	$result = _groovesharkSend('popularGetSongs', $parameters);
	return $result['Songs'];
}

function grooveshark_userGetPlaylists($userID)
{
	$parameters = array(
		'json' 	=> array(
			'header'	=> array(
				'client'	=> 'htmlshark',
				'clientRevision'=> '20100831'
			),
			'parameters'	=> array(
				'userID'		=> $userID
			)
		),
		'uri'	=> array(
			'host'		=> 'listen.grooveshark.com'
		)
	);

	$result = _groovesharkSend('userGetPlaylists', $parameters);
	return $result['Playlists'];
}

function _groovesharkGenerateCommToken($method, $uri)
{
	$token = grooveshark_getCommunicationToken($uri);

	if( !is_null($token) ) {
		// Finally generate the token
		$lastRandomizer = '';
		for($x=0;$x<6;$x++) {
			$val = rand(0,15);
			if($val < 10) {
				$lastRandomizer .= $val;
			} else {
				$lastRandomizer .= chr(97 + ($val - 10));
			}
		}

		$token = ($lastRandomizer . sha1($method . ':' . $token . ':quitStealinMahShit:' . $lastRandomizer));
	}

	return $token;
}


/*
 * Parameters = array(
 * 	'http' => array(
 * 		'method' => 'POST',
 * 		'headers' => array(
 * 			'Referer' => 'http://listen.grooveshark.com/',
 * 			....
 * 		)
 * 	),
 * 	'json' => array(
 * 		'header' => array(
 * 			'client' => 'jsqueue',
 * 			...
 * 		),
 * 		'parameters' => array(
 * 			'userID' => '0',
 * 			...
 * 		)
 * 	),
 * 	'uri' => array(
 * 		'protocol' => 'http'
 * 		'host' => 'listen.grooveshark.com',
 * 		'path' => '/more.php'
 * 	)
 * );
 */
function _groovesharksend($method, $parameters = null)
{
	$jsonQuery = array(
		'header' =>  array(
			'client'	=> 'jsqueue',
			'clientRevision'=> '20101012.36',
			'uuid' 		=> '0D7626AA-260D-4C58-AC98-FF0B56D9B268'
		),
		'method'	=> $method
	);

	$httpHeaders = array(
		'User-Agent'		=> 'Wget/1.12 (elf)',
		'Accept'		=> 'application/json, text/javascript, */*; q=0.01',
		'Host'			=> null,
		'Referer'		=> null,
		'Keep-Alive'		=> '115',
		'Connection'		=> 'keep-alive',
		'X-Requested-With'	=> 'XMLHttpRequest',
		'Content-Type'		=> 'application/json; charset=UTF-8',
		'Content-Length'	=> null,
		'Pragma'		=> 'no-cache',
		'Cache-Control'		=> 'no-cache'
	);

	$httpOptions = array(
		'http' => array(
			'method'	=> 'POST',
			'header'	=> '',
			'content'	=> ''
		)
	);

	$uri = array(
		'protocol' 	=> 'http',
		'host'		=> 'cowbell.grooveshark.com',
		'path'		=> '/more.php?'.$method
	);


	// Apply parameters
	if( is_array($parameters) ) {
		if( array_key_exists('json', $parameters) )
			$jsonQuery = array_merge($jsonQuery, $parameters['json']);
		if( array_key_exists('http', $parameters) ) {
			if( isset($parameters['http']['method']) ) 
				$httpOptions['http']['method'] = $parameters['http']['method'];
			if( is_array($parameters['http']['headers']) )
				$httpHeaders = array_merge($httpHeaders, $parameters['http']['headers']);
		}
		if( array_key_exists('uri', $parameters) )
			$uri = array_merge($uri, $parameters['uri']);
	}

	// Adjust the clientRevision field to known data
	switch( strtolower($jsonQuery['header']['client']) )
	{
		case 'jsqueue':
			$jsonQuery['header']['clientRevision'] = '20101012.36';
			$jsonQuery['header']['uuid'] = '0D7626AA-260D-4C58-AC98-FF0B56D9B268';
			break;
		case 'htmlshark':
			$jsonQuery['header']['clientRevision'] = '20100831';
			$jsonQuery['header']['uuid'] = '0EF6F632-F66A-42F3-AD2E-F45650ABE185';
			break;
		default:
			trigger_error('ERROR: Unknown client.', E_USER_ERROR);
			return null;
	}

	// Build a token if needed!
	if( ($method != 'initiateSession') ) {
		if( !isset($jsonQuery['header']['session']) )
			$jsonQuery['header']['session'] =  grooveshark_initiateSession();
		$httpHeaders['Cookie'] = 'PHPSESSID='.$jsonQuery['header']['session'];

		if( ($method != 'getCountry') and ($method != 'getCommunicationToken') and (!isset($jsonQuery['header']['country'])) ) {
			$jsonQuery['header']['country'] = grooveshark_getCountry();
		}

		if( ($method != 'getCommunicationToken') and (!isset($jsonQuery['header']['token'])) ) {
			$jsonQuery['header']['token'] = _groovesharkGenerateCommToken($method, $uri['host']);
		}
	}

	// Adjust some HTTP headers
	$httpHeaders['Host'] = $uri['host'];

	// Encode JSON array
	$httpOptions['http']['content'] = json_encode($jsonQuery);
	$httpHeaders['Content-Lenght'] = strlen($httpOptions['http']['content']);

	// Build headers
	foreach($httpHeaders as $key => $value) {
		if( !is_null($value) )
			$httpOptions['http']['header'] .= $key . ": " . $value . "\r\n";
	}
	$httpOptions['http']['header'] .= "\r\n";

	// Create context and send data...
	$streamContext = stream_context_create($httpOptions);
	$html = file_get_contents($uri['protocol'] . '://'. $uri['host'] . $uri['path']  , false, $streamContext);
	$result = json_decode($html, true);
	if( is_null($result) ) {
		trigger_error($html . ' in method <b>'.$method.'</b>',E_USER_ERROR);
		$result = null;
	} elseif( array_key_exists('result', $result) ) {
		$result = $result['result'];
	} elseif( array_key_exists('fault', $result) ) {
		/*
		 * Array
		 * (
		 *    [header] => Array
		 *        (
		 *            [session] => 32851382178ef9640b5fa3612ee5b6aa
		 *        )
		 *    [fault] => Array
		 *        (
		 *            [code] => 256
		 *            [message] => invalid token
		 *        )
		 *)
		 */
		print_r($result);
		$result=null;
	} else {
		trigger_error('Unknown response <quote>'.$html.'</quote> in method <b>'.$method.'</b>',E_USER_ERROR);
		$result=null;
	}

	return $result;
}
?>
