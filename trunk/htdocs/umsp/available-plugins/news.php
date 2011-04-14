<?php
function _pluginMain($prmQuery) {
#------------------------------
$retMediaItems[] = array(
        'id'                    => 'umsp://plugins/news-dwdd',     #Attribute of item
        'parentID'              => '0',                             #Attribute of item
        'restricted'    => '1',                                     #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'De wereld draait door (SD)',
        'dc:date'               => '2009-12-30',
        'upnp:author'   => 'myAuthor',
        'upnp:artist'   => 'myArtist',
        'upnp:album'    => 'myAlbum',
        'upnp:genre'    => 'myGenre',
        'upnp:length'   => '2:10:20',
        'desc'                  => 'myDesc',
        'upnp:class'    => 'object.container',
        'upnp:album_art'=> '',
        'duration'              => 'myDur3',                    #Attribute of res
        'size'                  => 'mySize3',                   #Attribute of res in bytes
        'bitrate'               => 'myBitr',                    #Attribute of res
        'protocolInfo'  => '*:*:*:*',           		#Attribute of res
        'resolution'    => 'myReso',                            #Attribute of res
        'colorDepth'    => 'myColor',                           #Attribute of res
);
#------------------------------
$retMediaItems[] = array(
        'id'                    => 'umsp://plugins/news-penw',     #Attribute of item
        'parentID'              => '0',                             #Attribute of item
        'restricted'    => '1',                                     #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'Pauw en Witteman Vodcast (SD)',
        'dc:date'               => '2009-12-30',
        'upnp:author'   => 'myAuthor',
        'upnp:artist'   => 'myArtist',
        'upnp:album'    => 'myAlbum',
        'upnp:genre'    => 'myGenre',
        'upnp:length'   => '2:10:20',
        'desc'                  => 'myDesc',
        'upnp:class'    => 'object.container',
        'upnp:album_art'=> '',
        'duration'              => 'myDur3',                    #Attribute of res
        'size'                  => 'mySize3',                   #Attribute of res in bytes
        'bitrate'               => 'myBitr',                    #Attribute of res
        'protocolInfo'  => '*:*:*:*',           		#Attribute of res
        'resolution'    => 'myReso',                            #Attribute of res
        'colorDepth'    => 'myColor',                           #Attribute of res
);
#------------------------------
$retMediaItems[] = array(
        'id'                    => 'umsp://plugins/news-ednl',     #Attribute of item
        'parentID'              => '0',                             #Attribute of item
        'restricted'    => '1',                                     #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'Editie NL (RTL)',
        'dc:date'               => '2009-12-30',
        'upnp:author'   => 'myAuthor',
        'upnp:artist'   => 'myArtist',
        'upnp:album'    => 'myAlbum',
        'upnp:genre'    => 'myGenre',
        'upnp:length'   => '2:10:20',
        'desc'                  => 'myDesc',
        'upnp:class'    => 'object.container',
        'upnp:album_art'=> '',
        'duration'              => 'myDur3',                    #Attribute of res
        'size'                  => 'mySize3',                   #Attribute of res in bytes
        'bitrate'               => 'myBitr',                    #Attribute of res
        'protocolInfo'  => '*:*:*:*',           		#Attribute of res
        'resolution'    => 'myReso',                            #Attribute of res
        'colorDepth'    => 'myColor',                           #Attribute of res
);
#------------------------------
$retMediaItems[] = array(
        'id'                    => 'umsp://plugins/news-zdf',     #Attribute of item
        'parentID'              => '0',                           #Attribute of item
        'restricted'    => '1',                                   #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'ZDF heute-journal - das aktuelle Nachrichtenmagazin',
        'dc:date'               => '2009-12-30',
        'upnp:author'   => 'myAuthor',
        'upnp:artist'   => 'myArtist',
        'upnp:album'    => 'myAlbum',
        'upnp:genre'    => 'myGenre',
        'upnp:length'   => '2:10:20',
        'desc'                  => 'myDesc',
        'upnp:class'    => 'object.container',
        'upnp:album_art'=> '',
        'duration'              => 'myDur3',                    #Attribute of res
        'size'                  => 'mySize3',                   #Attribute of res in bytes
        'bitrate'               => 'myBitr',                    #Attribute of res
        'protocolInfo'  => '*:*:*:*',           		#Attribute of res
        'resolution'    => 'myReso',                            #Attribute of res
        'colorDepth'    => 'myColor',                           #Attribute of res
);
#------------------------------
$retMediaItems[] = array(
        'id'                    => 'umsp://plugins/news-mbh',     #Attribute of item
        'parentID'              => '0',                           #Attribute of item
        'restricted'    => '1',                                   #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'Man Bijt Hond',
        'dc:date'               => '2009-12-30',
        'upnp:author'   => 'myAuthor',
        'upnp:artist'   => 'myArtist',
        'upnp:album'    => 'myAlbum',
        'upnp:genre'    => 'myGenre',
        'upnp:length'   => '2:10:20',
        'desc'                  => 'myDesc',
        'upnp:class'    => 'object.container',
        'upnp:album_art'=> '',
        'duration'              => 'myDur3',                    #Attribute of res
        'size'                  => 'mySize3',                   #Attribute of res in bytes
        'bitrate'               => 'myBitr',                    #Attribute of res
        'protocolInfo'  => '*:*:*:*',           		#Attribute of res
        'resolution'    => 'myReso',                            #Attribute of res
        'colorDepth'    => 'myColor',                           #Attribute of res
);
return $retMediaItems;
} # end function

# XPlay_Daily_Video_Podcast


?>
