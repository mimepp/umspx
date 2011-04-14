<?php
# (C) 2010 Alex Meijer for Eminent Europe B.V.
#
# This blip.tv plugin is designed for Zoster's USMP server which runs (amongst others) inside the EM7075 and DTS variant.
# This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
# In addtion to section 7 of the GPL terms, there is one additional term :
# G) You must keep this header intact when you intend to release modified copies of the program.
#
# Thank you, and enjoy this plugin.
function _pluginMain($prmQuery) {
#
# This function is started if user clicks on the 'Revision3' option in the menu
# It has no parameters and will run 'PluginCreateFeedlist' that returns an array with feeds.
# If run with a feed (/revision3.php?feed=animetv) it will run the function _pluginFeedResolution
# and if run with both present, it will return a list of items in the respective feed.
#
        $queryData = array();
        parse_str($prmQuery, $queryData);
        if ($queryData['feed'] !='') { 
                $items = _pluginCreateVideoItems($queryData['feed']);
                return $items;
        } else {
                $items = _pluginCreateFeedList();
                return $items;
        } # end if
} # end function

function _pluginCreateFeedList($feed){
$arr = array( 	"chipandironicus",
		"cinemassacre", 
		"achievementhunter",
		"beachesoceanwaves",
		"besttechienetshow",
		"ambnib1",
		"rrpgreviews",
		"hdnetfights",
		"teamfedora",
		"mahalovideogames", 
		"macmost",
		);
asort($arr);
foreach ($arr as $key => $value)
        {
        $retMediaItems[] = array (
                            'id'            => 'umsp://plugins/bliptv?feed=' . $value,
                            'dc:title'      => 'Blip.tv - ' . ucfirst($value),
                            'res'           => 'umsp://plugins/bliptv?feed=' . $value,
	                    'upnp:class'    => 'object.container',
                            ); // end array building
        } // end foreach
return $retMediaItems;
} // end function

function _pluginCreateVideoItems($feed) {
#
        # Create the actual XML feed url with resolution selection added at the end.
        #
        # For blip we need for example http://achievementhunter.blip.tv/rss
        $baseurl = "http://";
        $basereso = ".blip.tv/rss";
        #
        # Create an array by filling it with the actual XML feed
        #
        # Create variants from $feed:
        #
        $feedurl = $baseurl . $feed . $basereso;
        $reader = new XMLReader();
        $episodelistXML = file_get_contents($feedurl);
        $reader->XML($episodelistXML);
        while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT) {
                        if ($reader->localName == 'title') {
                                $title = $reader->readString ('title');

                            } # end if
                        if ($reader->localName == 'enclosure') {
                                $location = $reader->getAttribute('url');
                                if (strpos($location, '.mov') !== false || strpos($location, '.mp4') !== false || strpos($location, '.m4v') !== false || strpos($location, '.wmv') !== false) // ** hiero
                                {
                                $data = array(
                                        'url'         => $location,
                                );
                                $dataString = http_build_query($data, 'pluginvar_');
                                if (strpos($location, '.mov') !== false) {$proto = 'http-get:*:quicktime:*';}
                                if (strpos($location, '.mp4') !== false || strpos($location, '.m4v')!== false) {$proto = 'http-get:*:video/mp4:*';}
                                if (strpos($location, '.wmv') !== false) {$proto = 'http-get:*:video/x-ms-wmv:*';}
                                $retMediaItems[] = array (
                                        'id'            => 'umsp://plugins/bliptv?' . $dataString,
                                        'dc:title'      => $title,
                                        'res'           => $location,
                                        'upnp:class'    => 'object.item.videoitem',
                                        'protocolInfo'  => $proto,
                                );
                                }
                        } # end if
                } # end if
        } #end while
        return $retMediaItems;
} # end function
?>