<?php
function _pluginMain($prmQuery) {

        $reader = new XMLReader();
//        $episodelistXML = file_get_contents('http://revision3.com/hdnation/feed/MP4-hd30');  the 30fps version doesn't work correct ?
        $episodelistXML = file_get_contents('http://revision3.com/hdnation/feed/MP4-High-Definition');
//	$episodelistXML = file_get_contents('http://revision3.com/hdnation/feed/MP4-Large');
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
                                        'id'            => 'umsp://plugins/tech-hdnation2?' . $dataString,
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