<?php
function _pluginMain($prmQuery) {
        $reader = new XMLReader();
        $episodelistXML = file_get_contents('http://www.zdf.de/ZDFmediathek/podcast/222506?view=podcast');
        // http://feeds2.feedburner.com/manbijthond/vodcast
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
                                        'id'            => 'umsp://plugins/news-zdf?' . $dataString,
                                        'dc:title'      => $title,
                                        'res'		=> $location,
                                        'upnp:class'	=> 'object.item.videoitem',
                                        'protocolInfo'	=> 'http-get:*:video/mp4:*',
                                );
                        } # end if
                } # end if
        } #end while
        return $retMediaItems;
} # end function
?>