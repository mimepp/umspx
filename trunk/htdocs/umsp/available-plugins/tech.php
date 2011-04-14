<?php
function _pluginMain($prmQuery) {
#------------------------------
$retMediaItems[] = array(
        'id'                    => 'umsp://plugins/tech-hwi',     #Attribute of item
        'parentID'              => '0',                             #Attribute of item
        'restricted'    => '1',                                     #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'HardwareInfo.tv (SD)',
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
        'id'                    => 'umsp://plugins/tech-hdnation',     #Attribute of item
        'parentID'              => '0',                           #Attribute of item
        'restricted'    => '1',                                   #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'HD Nation - By revision3.com (720P,sync testing)',
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
        'id'                    => 'umsp://plugins/tech-engadgethd',     #Attribute of item
        'parentID'              => '0',                           #Attribute of item
        'restricted'    => '1',                                   #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'EngadgetHD Audio podcast',
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
        'id'                    => 'umsp://plugins/tech-hdnationsd',     #Attribute of item
        'parentID'              => '0',                           #Attribute of item
        'restricted'    => '1',                                   #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'HD Nation - By revision3.com (HQ)',
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
        'id'                    => 'umsp://plugins/tech-hubble',     #Attribute of item
        'parentID'              => '0',                           #Attribute of item
        'restricted'    => '1',                                   #Attribute of item
        'dc:creator'    => 'myCreator',
        'dc:title'              => 'NASA HubbleCast HD',
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

?>
