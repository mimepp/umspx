<?php

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

function _pluginMain($prmQuery) {
	$items = array();
	$queryData = array();

	parse_str($prmQuery, $queryData);

	if( isset($queryData['menu_id']) && !is_null($queryData['menu_id']) &&
		is_numeric($queryData['menu_id']) &&
		in_array((int)$queryData['menu_id'], Array(0, 1, 98, 99))) {
		switch ((int)$queryData['menu_id']) {
			case 0:
				$items = _pluginPopularCreateMenu();
				break;
			case 1:
				$items = _pluginPlaylistCreateMenu();
				break;
			case 98:
				$items = _pluginCannedSearchesMenu();
				break;
			case 99:
				$items = _pluginFavoritesGetSongs();
				break;
		};
	} elseif( isset($queryData['popular_id']) && !is_null($queryData['popular_id']) ) {
		$items = _pluginPopularPlaylist($queryData['popular_id']);
	} elseif( isset($queryData['playlist_id']) && !is_null($queryData['playlist_id']) ) {
		$items = _pluginPlaylistPopulate($queryData['playlist_id']);
	} elseif( isset($queryData['search_data']) && $queryData['search_data'] != '') {
		$items = _pluginSearch('(upnp:class derivedfrom "object.item.videoItem") and dc:title contains "'.urldecode($queryData['search_data']).'"');
	} else {
		$items = _pluginCreateMainMenu();
	};

	return $items;
};

function _pluginSearch($prmQuery) {
	$retMediaItems = array();
	$searchPhrase = '';
	$searchType = 'Songs';

	// Search modifiers: http://forums.grooveshark.com/discussion/comment/1468/#Comment_1468
	// Will only work on title search.
	if ( preg_match('/and dc:title contains "(.*?)"/', $prmQuery, $searchstring) > 0 ) {
		$searchPhrase = $searchstring[1];
	} elseif( preg_match('/and upnp:album contains "(.*?)"/', $prmQuery, $searchstring) > 0 ) {
		$searchPhrase = 'Album:'.$searchstring[1];
	} elseif( preg_match('/and upnp:genre contains "(.*?)"/', $prmQuery, $searchstring) > 0 ) {
		$searchPhrase = 'Genre:'.$searchstring[1];
	}

	if( preg_match('/^(.*?):(.*?)$/', $searchPhrase, $searchstring) > 0 ) {
		if(strtolower($searchstring[1]) == 'playlist') {
			$searchType = 'Playlists';
			$searchPhrase = $searchstring[2];
		} elseif((strtolower($searchstring[1]) != 'name')or(strtolower($searchstring[1]) != 'about')or(strtolower($searchstring[1]) != 'user')) {
			$searchType = 'Playlists';
			// Search phrase remains the same.
		}
	}
	
	$results = grooveshark_getSearchResultsEx($searchPhrase, $searchType);
	if( $searchType == 'Songs' ) {
		return _pluginParseAudioItems($results);
	} elseif($searchType == 'Playlists') {
		return _pluginParsePlaylistItems($results);
	}
}

function _pluginCreateMainMenu()
{
	// Menu entries
	$entries = array();
	$entries[] = array(
		'name' 	=> 'Popular music',
		'id'	=> '0',
		'image' => 'http://i54.tinypic.com/5cg711.png'
	);

	// Here is where we initialize our chache :)
	$cache = _pluginInitialize();

	// Are we authenticated?
	if( $cache->user->userid[0] != '0' ) {
		$results = grooveshark_getUserSidebar();
		if( count($results['playlists']) ) {
			$entries[] = array(
				'name'	=> 'Playlists',
				'id'	=> '1',
				'image'	=> 'http://i52.tinypic.com/144440l.png'
			);
		};
/*
		if( count($results['subscribedPlaylists']) ) {
			$entries[] = array(
				'name'	=> 'Suscribed Playlists',
				'id'	=> '2',
				'image'	=> null
			);
		}
		if( count($results['stations']) ) {
			$entries[] = array(
				'name'	=> 'Stations',
				'id'	=> '2',
				'image'	=> null
			);
		};
*/
		$entries[] = array(
			'name' 	=> 'Favorites',
			'id'	=> '99',
			'image' => 'http://i55.tinypic.com/24awz6d.png'
		);
	};

	// Canned searches
	$xml = _pluginCannedSearchesFind();
	if( !is_null($xml) ) {
		$entries[] = array(
			'name' 	=> 'Canned searches',
			'id'	=> '98',
			'image' => 'http://i52.tinypic.com/2dgqpgp.png'
		);
	}

	foreach($entries as $entry) {
		$data = array(
			'menu_id' => $entry['id']
		);

		$dataStr = http_build_query($data, 'pluginvar_');
		$retMediaItems[] = array (
			'id'		=> 'umsp://plugins/grooveshark?'.$dataStr,
			'dc:title'	=> $entry['name'],
			'res'		=> 'umsp://plugins/grooveshark?'.$dataStr,
			'upnp:album_art'=> $entry['image'],
			'upnp:class'	=> 'object.container'
		);
	}

	return $retMediaItems;
}

function _pluginCannedSearchesFind() {
	$xml = null;
	$config = file_get_contents('/conf/config');
	if (preg_match('/GROOVESHARK_XML=\'(.+)\'/', $config, $m)) {
		if( file_exists($m[1]) ) {
			$file = file_get_contents($m[1]);
			try {
				$xml = new SimpleXMLElement($file);
				if( !isset($xml->songs) || count($xml->songs->children()) == 0 ) {
					$xml = NULL;
				};
			} catch (Exception $e) {
			};
		};
	};
	if( is_null($xml) ) {
		$out = array();
		exec("sudo find '/tmp/media/usb/' -name 'grooveshark.xml'", $out);
		if( count($out) > 0 ) {
			foreach($out as $filename) {
				$file = file_get_contents($filename);
				try {
					$xml = new SimpleXMLElement($file);
					if( !is_null($xml) ) {
						if( isset($xml->songs) && count($xml->songs->children()) > 0 ) {
							break;
						} else {
							$xml = null;
						};
					};
				} catch (Exception $e) {
				};
			};
		};
	};
	return $xml;
};

function _pluginCannedSearchesMenu() {
	
	$retMediaItems = array();

	$xml = _pluginCannedSearchesFind();

	if( !is_null($xml) ) {
		// ToDo: Maybe in a future we could enhance canned searches with
		//       specific Albums, Playlists, Users searches
		foreach($xml->songs->song as $song) {
			$search = trim((string)$song->search[0]);
			if( !empty($search) ) {
				$badge = trim((string)$song->badge);
				if( empty($badge) )
					$badge = null;

				$displayname = (string)$song->displayname[0];
				if( empty($displayname) )
					$displayname = $search;

				$data = array (
					'search_data'	=> urlencode($search)
					//'maxresults'	=> (string)$groove->maxresults
				);
				$dataStr = http_build_query($data,'pluginvar_');

				$retMediaItems[] = array(
					'id'		=> 'umsp://plugins/grooveshark?'.$dataStr,
					'dc:title'	=> $displayname,
					'res'		=> 'umsp://plugins/grooveshark?'.$dataStr,
					'upnp:album_art'=> $badge,
					'upnp:class'	=> 'object.container'
				);
			};
		};
	};

	return $retMediaItems;
};

function _pluginFavoritesGetSongs() {
	$results = grooveshark_getFavorites('Songs');
	return _pluginParseAudioItems($results);
};

function _pluginPlaylistCreateMenu() {
	$retMediaItems = array();

	// Load Cache
	if( file_exists('/var/cache/grooveshark') ) {
		$cache = new SimpleXMLElement( file_get_contents('/var/cache/grooveshark') );

		if( !is_null($cache) ) {
			if( (string)$cache->user->userid[0] != '0' ) {
				$result = grooveshark_userGetPlaylists((string)$cache->user->userid[0]);
				$retMediaItems = _pluginParsePlaylistItems($result);
			};
		};
	};
	return $retMediaItems;
};

function _pluginPlaylistPopulate($playlistID) {
	$results = grooveshark_playlistGetSongs($playlistID);
	return _pluginParseAudioItems($results);
};

function _pluginPopularCreateMenu() {
	$entries = array(
		array(
			'name' 	=> 'Popular music today',
			'id'	=> 'daily',
			'image' => 'http://i54.tinypic.com/2e1fukg.png'
		),
		array(
			'name'	=> 'Popular music on the month',
			'id'	=> 'monthly',
			'image'	=> 'http://i54.tinypic.com/awdy0i.png'
		)
	);

	foreach($entries as $entry) {
		$data = array(
			'popular_id' => $entry['id']
		);

		$dataStr = http_build_query($data, 'pluginvar_');
		$retMediaItems[] = array (
			'id'		=> 'umsp://plugins/grooveshark?'.$dataStr,
			'dc:title'	=> $entry['name'],
			'res'		=> 'umsp://plugins/grooveshark?'.$dataStr,
			'upnp:album_art'=> $entry['image'],
			'upnp:class'	=> 'object.container'
		);
	};

	return $retMediaItems;
};

function _pluginPopularPlaylist($type = 'daily') {
	$entries = grooveshark_popularGetSongs($type);
	return _pluginParseAudioItems($entries);
}



function _pluginParseAudioItems($songs) {
	foreach($songs as $song) {
		$http_query = array(
			'SongID'	=> urlencode($song['SongID'])
		);
		$http_query = http_build_query($http_query);
		$proxyUri = 'http://localhost/umsp/plugins/grooveshark/grooveshark-proxy.php?' . $http_query;

		$title = $song['ArtistName'] . ' - ' . $song['Name'];

		$retMediaItems[] = array (
			'id'		=> 'umsp://plugins/grooveshark?'.$http_query,
			'dc:title'	=> $title,
			'res'		=> $proxyUri,
			'upnp:album'	=> $song['AlbumName'],
			'upnp:artist'	=> $song['ArtistName'],
			'upnp:album_art'=> (!empty($song['CoverArtFilename']) ? 'http://beta.grooveshark.com/static/amazonart/m' . $song['CoverArtFilename'] : null),
			'upnp:class'	=> 'object.item.audioItem',
			'protocolInfo'	=> 'http-get:*:audio/mpeg:*',
		);
	};

	return $retMediaItems;
};

function _pluginParsePlaylistItems($playlists) {
	$retMediaItems = array();

	foreach($playlists as $playlist) {
		$data = array(
			'playlist_id' => $playlist['PlaylistID']
		);

		$dataStr = http_build_query($data, 'pluginvar_');
		$retMediaItems[] = array (
			'id'		=> 'umsp://plugins/grooveshark?'.$dataStr,
			'dc:title'	=> ( empty($playlist['About']) ? $playlist['Name'] : $playlist['Name'] . ': ' . $playlist['About'] ),
			'res'		=> 'umsp://plugins/grooveshark?'.$dataStr,
			//'upnp:album_art'=> $entry['Picture'],
			'upnp:class'	=> 'object.container'
		);
	};

	return $retMediaItems;
};

function _pluginInitialize() {
	$cache = NULL;

	// Initiate session (Initializes ccache as well)
	$session = grooveshark_initiateSession(true);

	// Load the cache
	// Store userid in cache
	if( file_exists('/var/cache/grooveshark') ) {
		$cache = new SimpleXMLElement( file_get_contents('/var/cache/grooveshark') );
	}
	if( is_null($cache) )
		$cache = new SimpleXMLElement('<cache></cache>');

	if( file_exists('/tmp/conf/grooveshark.conf') ) {
		$xml = new SimpleXMLElement(file_get_contents('/tmp/conf/grooveshark.conf'));
		$username = $xml->username[0];
		$password = $xml->password[0];

		$user = grooveshark_authenticateUser((string)$username, (string)$password);

		$cache->user->userid = $user['userID'];
		$cache->user->authtoken = $user['authToken'];
	} else {
		$cache->user->userid = '0';
		$cache->user->authtoken = null;
	}

	$queueID = grooveshark_initiateQueue();
	$cache->queueid = $queueID;

	$cache->asXML('/var/cache/grooveshark');
	return $cache;
};
?>