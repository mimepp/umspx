<?php
#	Crucial elements for an item are: 
#	dc:title		The title of the item
#	upnp:class		The media class of the item (see below)
#	res				This is the actual location of the file (either remote "http://someserver.com/file.avi" or local "file:///pathOnWDTV/file.avi")
#	protocolInfo	The media protocol. Syntax is very important or the WDTV will just reset. Refer to /docs/wdtv-mediaprotocols.txt
#					The server reply "content-type" seems to be more relevant than any setting here.
#					Setting '*:*:*:*' seems to work in some/most? cases.
#
#	Play around with the other stuff ;)
#
# UPnP classes:
# object.item.audioItem
# object.item.imageItem
# object.item.videoItem
# object.item.playlistItem	not recognized by WDTV?
# object.item.textItem		not recognized by WDTV?
# object.container			WDTLV will send a browse request for this item when you enter it.

header ("Content-Type: text/html; charset=UTF-8");
# Copy and repeat:
#-----------------------------
if (file_exists("/dev/sr0")) {
	error_log("cdrom exists!!!\n");
	$myMediaItems[] = array(	
		'id'			=> 'umsp://plugins/optical-drive',
		'parentID'		=> '0',
		'dc:title'		=> 'Optical Drive Manager',
		'upnp:class'	=> 'object.container',
		'upnp:album_art'=> '',
	);
};
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/netitv',
	'parentID'		=> '0',
	'dc:title'		=> mb_convert_encoding("netitv.com 天翼视讯", "UTF-8", "GBK"),
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> 'http://www.netitv.com/a_images/tysx_1_2/logo.gif',
);
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/qiyi',
	'parentID'		=> '0',
	'dc:title'		=> mb_convert_encoding("QIYI.COM 奇艺", "UTF-8", "GBK"),
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> 'http://www.qiyipic.com/common/images/logo.png',
);
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/apple-trailers',
	'parentID'		=> '0',
	'dc:title'		=> 'apple-trailers',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TZDbFvRJSmI/AAAAAAAAA1A/P-V72s4dzYM/s60/apple-logo-2pgray.png',
);
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/weather',
	'parentID'		=> '0',
	'dc:title'		=> 'weather',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> 'http://a1.twimg.com/profile_images/454108683/AccuWxLogoTwitter_breaking_bigger.png',
);
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/tube8',
	'parentID'		=> '0',
	'dc:title'		=> 'tube8',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> 'http://lh3.googleusercontent.com/_xJcSFBlLg_Y/TWM_L7Vo2nI/AAAAAAAAAXM/74F1vbmw7yE/s200/t8logo2.png',
);
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/reader',
	'parentID'		=> '0',
	'dc:title'		=> 'rss reader',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> 'http://lh4.ggpht.com/_xJcSFBlLg_Y/TRK6wWOo1TI/AAAAAAAAAHo/iCot-wogg30/s60/reader.png',
);
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/shoutcast',
	'parentID'		=> '0',
	'dc:title'		=> 'Shoutcast',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/bbc',
	'parentID'		=> '0',
	'dc:title'		=> 'BBC Audio Podcasts (beta!)',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/revision3',
	'parentID'		=> '0',
	'dc:title'		=> 'Revision3 Vodcasts',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
);
#------------------------------

if (file_exists(_getUMSPConfPath() . "/umsp.php")) {
	include(_getUMSPConfPath() . "/umsp.php");
};

# 'XML UMSP Items Plugin' support
if (file_exists(_getUMSPPluginPath() . "/umsp-items/umsp-items.func.php") 
	&& (file_exists(_getUMSPConfPath() . "/umsp-items.xml") || file_exists(_getUMSPPluginPath() . "/umsp-items/umsp-items.xml"))) {
	require_once (_getUMSPPluginPath() . "/umsp-items/umsp-items.func.php");
	$myMediaItems = _pluginCreateItems();
};

include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
$pluginItems = _createPluginRootItems();
if ( is_array($pluginItems) ) {
	$myMediaItems = array_merge($myMediaItems, $pluginItems);
}

/*
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/bliptv',	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '1',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Blip.tv Vodcasts',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
#	'res'			=> '',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
#	'protocolInfo'	=> '*:*:video/avi:*',		#Attribute of res
	'protocolInfo'	=> '*:*:*:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/comedy',	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '1',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Category: Comedy (RSS Vodcasts)',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
#	'res'			=> '',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
#	'protocolInfo'	=> '*:*:video/avi:*',		#Attribute of res
	'protocolInfo'	=> '*:*:*:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/culture',	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '1',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Category: Culture (RSS Vodcasts)',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
#	'res'			=> '',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
#	'protocolInfo'	=> '*:*:video/avi:*',		#Attribute of res
	'protocolInfo'	=> '*:*:*:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/games',	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '1',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Category: Games (RSS Vodcasts)',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
#	'res'			=> '',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
#	'protocolInfo'	=> '*:*:video/avi:*',		#Attribute of res
	'protocolInfo'	=> '*:*:*:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/news',	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '1',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Category: News (RSS Vodcasts)',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
#	'res'			=> '',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
#	'protocolInfo'	=> '*:*:video/avi:*',		#Attribute of res
	'protocolInfo'	=> '*:*:*:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);
#------------------------------
$myMediaItems[] = array(	
	'id'			=> 'umsp://plugins/tech',	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '1',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Category: Technology & Gadgets (RSS Vodcasts)',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.container',
	'upnp:album_art'=> '',
#	'res'			=> '',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
#	'protocolInfo'	=> '*:*:video/avi:*',		#Attribute of res
	'protocolInfo'	=> '*:*:*:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);

#------------------------------
$myMediaItems[] = array(	
	'id'			=> strval(count($myMediaItems)+1),	#Attribute of item
	'parentID'		=> '0',							#Attribute of item
	'restricted'	=> '0',							#Attribute of item
	'dc:creator'	=> 'myCreator',
	'dc:title'		=> 'Geenstijl.tv : Rutger meets de adellijke babyboomert in de Saab - 720P',
	'dc:date'		=> '2009-12-30',
	'upnp:author'	=> 'myAuthor',
	'upnp:artist'	=> 'myArtist',
	'upnp:album'	=> 'myAlbum',
	'upnp:genre'	=> 'myGenre',
	'upnp:length'	=> '2:10:20',
	'desc'			=> 'myDesc',
	'upnp:class'	=> 'object.item.videoItem',
	'upnp:album_art'=> '',
	'res'			=> 'http://flv.dumpert.nl/353462f8_adel.mp4',
	'duration'		=> 'myDur3',				#Attribute of res
	'size'			=> 'mySize3',				#Attribute of res in bytes
	'bitrate'		=> 'myBitr',				#Attribute of res
	'protocolInfo'	=> 'http-get:*:video/mp4:*',		#Attribute of res
	'resolution'	=> 'myReso',				#Attribute of res
	'colorDepth'	=> 'myColor',				#Attribute of res
);
*/
?>
