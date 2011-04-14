<?php
// Dreambox E2 UMSP plugin by Toni
// http://forum.wdlxtv.com/viewtopic.php?f=49&t=320

function _pluginMain($prmQuery) {

  $queryData = array();
  parse_str($prmQuery, $queryData);

  if (file_exists('/conf/config')) {
    $config = file_get_contents('/conf/config');
   
    if(preg_match('/DREAMBOX_HOSTNAME=\'(.+)\'/', $config, $results)) {
      $dreamboxAddress = $results[1];
    }
    
    if(preg_match('/DREAMBOX_PROTECTED=\'ON\'/', $config, $results)) {  
      if(preg_match('/DREAMBOX_WEBACCOUNT=\'(.+)\'/', $config, $results)) {
        $account = $results[1];
      }
      if(preg_match('/DREAMBOX_WEBPASSWORD=\'(.+)\'/', $config, $results)) {
        $password = $results[1];
      }
      $dreamboxAddress = $account . ":" . $password . "@" . $dreamboxAddress;
    }    
  }  
  
  if ($queryData['bouquet'] != '') {
    return Dreambox_E2_Channels($queryData, $dreamboxAddress);
  }
  elseif ($queryData['recordingspath'] != '') {
    return Dreambox_E2_Recordings($queryData, $dreamboxAddress);
  } else {
      $myMediaItems[] = array(
            'id'            => 'umsp://plugins/dreambox-e2?bouquet=root',
            'parentID'      => 'umsp://plugins/dreambox-e2',
            'restricted'    => '1',
            'dc:title'      => 'Dreambox E2 Channels',
            'upnp:class'    => 'object.container',
            'upnp:album_art'=> '',
    );
    $myMediaItems[] = array(
            'id'            => 'umsp://plugins/dreambox-e2?recordingspath=root',
            'parentID'      => 'umsp://plugins/dreambox-e2',
            'restricted'    => '1',
            'dc:title'      => 'Dreambox E2 Recordings',
            'upnp:class'    => 'object.container',
            'upnp:album_art'=> '',
    );
    
    return $myMediaItems;
  }
}

function Dreambox_E2_Recordings($queryData, $dreamboxAddress) {
   
  if ($queryData['recordingspath'] != 'root') {
    $path = urldecode($queryData['recordingspath']);
  } else {
     # read the locations from Dreambox
     # I assume there is only one location, in my case it's '/hdd/movie/'
 
    # Location URL, list all recordings
    $dreamboxLocationsUrl = 'web/getlocations';
 
    $reader = new XMLReader();
    $locationsXML = file_get_contents('http://' . $dreamboxAddress . '/' . $dreamboxLocationsUrl);
 
    $reader->XML($locationsXML);
    while ($reader->read()) {
      if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2locations')) {
        #
        # Read e2locations child nodes until end
        #
        do {
            $reader->read();
            if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2location')) {
              $path  = $reader->readString();
            }
        } while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'e2location')));
      }
    }
  }

# First, read the media player items, as this is the *only* way to read the subdirectories

  # Media URL, list media items under the given path
  $dreamboxMedialistUrl = 'web/mediaplayerlist?path=' . $path;
 
  $reader = new XMLReader();
  $medialistXML = file_get_contents('http://' . $dreamboxAddress . '/' . $dreamboxMedialistUrl);
 
  $reader->XML($medialistXML);
  while ($reader->read()) {
    if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2file')) {
      #
      # Read e2file child nodes until end
      #
      do {
          $reader->read();
          if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2isdirectory')) {
            $newDir['isdirectory']  = $reader->readString();
          }
          if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2servicereference')) {
            $newDir['directory'] = utf8_encode(utf8_decode($reader->readString()));
          }
      } while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'e2file')));
      #
      # New channelinfo item parsed. Now add as media item, unless it's the trashcan directory
      # or the parent directory (WD TV remote has a 'back' button)
      #
   
      if ( $newDir['isdirectory'] == 'True' &&
           strncmp($path, $newDir['directory'], strlen($newDir['directory'])) &&
           $newDir['directory'] != $path . '.trashcan/' ) {          
        $retMediaItems[] = array (
          'id'        => 'umsp://plugins/dreambox-e2?recordingspath=' . urlencode($newDir['directory']),
          'dc:title'  => utf8_encode(utf8_decode(basename($newDir['directory']))),
          'upnp:class' => 'object.container'
        );
      }
    } # end if
  } #end while
 
 
# Now, read the movie information from this directory
 
  # Movies URL, list all recordings
  $dreamboxMovielistUrl = 'web/movielist?dirname=' . $path;
 
  #$reader = new XMLReader();
  $movielistXML = file_get_contents('http://' . $dreamboxAddress . '/' . $dreamboxMovielistUrl);
 
  $reader->XML($movielistXML);
  while ($reader->read()) {
    if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2movie')) {
      #
      # Read channelinfo child nodes until end
      #
      do {
          $reader->read();
              if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2filename')) {
          $newMovie['id']  = $reader->readString();
        }
              if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2title')) {
          $newMovie['title'] = utf8_encode(utf8_decode($reader->readString()));
              }
              if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2description')) {
          $newMovie['description'] = utf8_encode(utf8_decode($reader->readString()));
              }
              if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2filesize')) {
          $newMovie['filesize'] = $reader->readString();
              }
      } while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'e2movie')));
      #
      # New channelinfo item parsed. Now add as media item:
      #
   
      $retMediaItems[] = array (
        'id'        => $newMovie['id'],
        'res'       => 'http://' . $dreamboxAddress . ':80/file?file=' . $newMovie['id'],
#        'res'       => 'http://localhost/umsp/plugins/dreambox-e2/dreambox-proxy.php?itemUrl=http://' . $dreamboxAddress . ':80/file?file=' . $newMovie['id'],
        'dc:title'  => $newMovie['title'],
        'desc'      => $newMovie['description'],
        'size'      => $newMovie['filesize'],
        'upnp:class'    => 'object.item.videoitem',
        'protocolInfo'    => 'http-get:*:video/mpeg:*'
      );
    } # end if
  } #end while

  return $retMediaItems; 
} # end function


function Dreambox_E2_Channels($queryData, $dreamboxAddress) {
  
  # Do we have a bouquet as the parameter?

  if ($queryData['bouquet'] != 'root') {
    $bouquet = $queryData['bouquet'];
  } else {
    $bouquet = '';   
  }

  # No, we need to ask for the list of bouquets
  if ( $bouquet == '' ) {

    # Get all bouquets
    $dreamboxBouquetsUrl = 'web/getservices';
 
    $reader = new XMLReader();
    $bouquetsXML = file_get_contents('http://' . $dreamboxAddress . '/' . $dreamboxBouquetsUrl);
 
    $reader->XML($bouquetsXML);
    while ($reader->read()) {
      if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2service')) {
        #
        # Read e2service child nodes until end
        #

        do {
            $reader->read();
            if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2servicereference')) {
              $newBouquet['sref']  = $reader->readString();
            }
            if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2servicename')) {
              $newBouquet['title'] = utf8_decode($reader->readString());
            }
        } while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'e2service')));
        #
        # New bouquet item parsed. Now add as media item:
        #
         
        $retMediaItems[] = array (
          'id'         => 'umsp://plugins/dreambox-e2/dreambox-e2?bouquet=' . $newBouquet['sref'],
          'dc:title'   => utf8_encode(utf8_decode($newBouquet['title'])),
          'upnp:class' => 'object.container'
        );   
      } # end if
    } #end while

    # If there's just one bouquet, jump directly into the bouquet contents
    # Othervise show the bouquet list

    if ( count($retMediaItems) == 1 ) {   
      return _pluginMain('bouquet=' . $newBouquet['sref']);
    } else {
      return $retMediaItems;     
    }
  } else { 
    # We have a bouquet sRef as a parameter -> list all channels in that bouquet

    # $bouquet looks like '1:7:1:0:0:0:0:0:0:0:FROM BOUQUET \"userbouquet.favourites.tv\" ORDER BY bouquet',
    # but we should feed it in as '1:7:1:0:0:0:0:0:0:0:FROM%20BOUQUET%20%22userbouquet.favourites.tv%22%20ORDER%20BY%20bouquet'

    $dreamboxServiceUrl = 'web/getservices?sRef=' . str_replace('\\"', "%22", str_replace(" ", "%20", $bouquet));
   
    $reader = new XMLReader();
    $channellistXML = file_get_contents('http://' . $dreamboxAddress . '/' . $dreamboxServiceUrl);
 
    $reader->XML($channellistXML);
    while ($reader->read()) {
      if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2service')) {
        #
        # Read channelinfo child nodes until end
        #
        do {
          $reader->read();
          if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2servicereference')) {
            $newChannel['id']  = $reader->readString();
          }
          if (($reader->nodeType == XMLReader::ELEMENT) && ($reader->localName == 'e2servicename')) {
            $newChannel['title'] = $reader->readString();
          }
        } while (!(($reader->nodeType == XMLReader::END_ELEMENT) && ($reader->localName == 'e2service')));
        #
        # New channelinfo item parsed. Now add as media item:
        #

        $retMediaItems[] = array (
          'id'             => $newChannel['id'],
          'res'            => 'http://localhost/umsp/plugins/dreambox-e2/dreambox-proxy.php?itemUrl=http://' . $dreamboxAddress . ':8001/' . $newChannel['id'],
          'dc:title'       => utf8_encode(utf8_decode($newChannel['title'])),
          'upnp:class'     => 'object.item.videoitem.videoBroadcast',
          # picons should be under /usr/lib/enigma2/python/Plugins/Extensions/WebInterface/web-data/streampage on the Dreambox
          'upnp:album_art' => 'http://' . $dreamboxAddress . '/web-data/streampage/' . rawurlencode($newChannel['title']) . '.png',
          'protocolInfo'   => 'http-get:*:video/mpeg:DLNA.ORG_PN=MPEG_PS_PAL;DLNA.ORG_OP=00;DLNA.ORG_CI=1'
        );
      } # end if
    } # end while
    return $retMediaItems; 
  } # end if
}
?>