<?php
function _pluginMain($prmQuery) {
        $reader = new XMLReader();
        $episodelistXML = file_get_contents('http://www.gametrailers.com/gtps3_podcast.xml');
// other feeds, tba
// http://www.gametrailers.com/gtrev_podcast.xml  = GameTrailers.com - Video Reviews
// http://www.gametrailers.com/gtprev_podcast.xml = GameTrailers.com - Video Previews
// http://www.gametrailers.com/gtiw_podcast.xml   = GameTrailers.com - Invisible Walls
// http://www.gametrailers.com/gtps3_podcast.xml  = GameTrailers.com - PS3 spotlight
// http://www.gametrailers.com/gt360_podcast.xml  = GameTrailers.com - Xbox360 spotlight
// http://www.gametrailers.com/gtbonusround_podcast.xml =  GameTrailers.com - Bonus Round
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
                                        'id'            => 'umsp://plugins/games-gt?' . $dataString,
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