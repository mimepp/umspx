<?php
function _pluginMain($prmQuery) {
        $reader = new XMLReader();
        $episodelistXML = file_get_contents('http://hd.engadget.com/category/podcasts/rss.xml');
        $reader->XML($episodelistXML);
        while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT) {
            		if ($reader->localName == 'title') {
            			$title = $reader->readString ('title');
            		    } # end if
                        if ($reader->localName == 'enclosure') {
                                $location = $reader->getAttribute('url');
                                $data = array(
                                        'url'         => $location,
                                );
                                $dataString = http_build_query($data, 'pluginvar_');
                                $retMediaItems[] = array (
                                        'id'            => 'umsp://plugins/tech-engadgethd?' . $dataString,
                                        'dc:title'      => $title,
                                        'res'		=> $location,
                                        'upnp:class'	=> 'object.item.audioitem',
                                        'protocolInfo'  => 'http-get:*:audio/mpeg:*',
                                        'upnp:artist'   => 'Engadget HD',
                                        'upnp:album'    => $title,
                                );
                        } # end if
                } # end if
        } #end while
        return $retMediaItems;
} # end function

?>