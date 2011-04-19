<?php
function _pluginMain($prmQuery) {
        $reader = new XMLReader();
        $episodelistXML = file_get_contents('http://www.rtl.nl/service/rss/editienl/index.xml');
        $reader->XML($episodelistXML);
        while ($reader->read()) {
                if ($reader->nodeType == XMLReader::ELEMENT) {
            		if ($reader->localName == 'title') {
            			$title = $reader->readString ('title');
            		    } # end if
                        if ($reader->localName == 'enclosure') {
                                $location = $reader->getAttribute('url');
                                # We use a small proxy to get around redirection issues with our player.
                                $proxyUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/umsp/plugins/redir-proxy.php';
                                $data = array(
                                        'url'         => $location,
                                );
                                $dataString = http_build_query($data, 'pluginvar_');
                                $retMediaItems[] = array (
                                        'id'            => 'umsp://plugins/news-penw?' . $dataString,
                                        'dc:title'      => $title,
                                        'res'		=> $proxyUrl.'?stream='.$location,
                                        'upnp:class'	=> 'object.item.videoitem',
                                        'protocolInfo'	=> 'http-get:*:video/mp4:*',
                                );
                        } # end if
                } # end if
        } #end while
        return $retMediaItems;
} # end function
?>