<?php
# (C) 2010 Alex Meijer for Eminent Europe B.V.
#
# This bbc.tv plugin is designed for Zoster's USMP server which runs (amongst others) inside the EM7075 and DTS variant.
# This code is GPL licensed. Please read it here: http://www.gnu.org/licenses/gpl.html
# In addtion to section 7 of the GPL terms, there is one additional term :
# G) You must keep this header intact when you intend to release modified copies of the program.
#
# Thank you, and enjoy this plugin.
function _pluginMain($prmQuery) {
        $queryData = array();
        parse_str($prmQuery, $queryData);
//	print_r ($queryData);
//	print_r ($prmQuery);
        if ($queryData['feed'] !='') { 
                $items = _pluginCreateVideoItems($queryData['feed']);
                return $items;
        } else {
                $items = _pluginCreateFeedList();
                return $items;
        } # end if
} # end function

function _pluginCreateFeedList($feed){
$channels = array(  "radio1/reviewshow", 
		    "radio1/mills",
		    "radio1/radio1doc",
		    "radio1/r1chart",
		    "radio1/r1mix",
		    "radio1/moyles",
		    "radio1/moylesen",
		    "radio1/huwintro",
		    "radio1/zane",
		    "radio2/pauljones",
		    "radio/worldbiz",
		    "radio/newspod",
);

foreach ($channels as $key => $value)
        {
        $baseurl = 'http://downloads.bbc.co.uk/podcasts/';
        $feedrss = '/rss.xml';
        $feedurl = $baseurl . $value . $feedrss;
        $reader = new XMLReader();
	# attempt to read the feed title from the rss xml
        $episodelistXML = file_get_contents($feedurl);
        $reader->XML($episodelistXML);
        while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT) {
                        if ($reader->localName == 'channel') {
                                $title = ($reader->readString ('title'));
                                $showname = explode("http", $title);
                            } # end if
                } # end if
        } # end while
	# Build the reply array
        $retMediaItems[] = array (
                            'id'            => 'umsp://plugins/bbc?feed=' . $feedurl,
                            'dc:title'      => $showname[0],
                            'res'           => 'umsp://plugins/bbc?feed=' . $feedurl,
	                    'upnp:class'    => 'object.container',
                            ); // end array building
        } // end foreach
return $retMediaItems;
} // end function

function _pluginCreateVideoItems($feed) {
#
        $reader = new XMLReader();
        $episodelistXML = file_get_contents($feed);
        $reader->XML($episodelistXML);
        while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT) {
                        if ($reader->localName == 'title') {
                                $title = $reader->readString ('title');

                            } # end if
                        if ($reader->localName == 'enclosure') {
                                $location = $reader->getAttribute('url');
                                if (strpos($location, '.mp3') !== false || strpos($location, '.wav') !== false)
                                {
                                $data = array(
                                        'url'         => $location,
                                );
                                $dataString = http_build_query($data, 'pluginvar_');
                                $retMediaItems[] = array (
                                        'id'            => 'umsp://plugins/bbc?' . $dataString,
                                        'dc:title'      => $title,
                                        'res'           => $location,
					'upnp:class'    => 'object.item.audioitem',
                    			'protocolInfo'  => 'http-get:*:audio/mpeg:*',
                                );
                                }
                        } # end if
                } # end if
        } #end while
        return $retMediaItems;
} # end function
?>
