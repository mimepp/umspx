<?php

class rssPodcastMenu extends DailyPodcastMenu{

    static function cmp($a, $b) {
        return ( $a['episode_ts'] < $b['episode_ts'] ? -1 : 1 );
    }
    public function asUMSP(){
        date_default_timezone_set('Etc/GMT-0');
        $MediaItems = array();
        $feedXML = strstr(file_get_contents($this->menu_url),"<");
        $feedXML = preg_replace('/(itunes|media):(image|thumbnail)/', '$1$2', $feedXML);
        $feedXML = preg_replace('/<sapo:videoURL>(.*?)<\/sapo:videoURL>/', '<enclosure url="$1/mov/1" type="video/mpeg" />', $feedXML);
        try {
            $simplexml = new SimpleXMLElement($feedXML);
        } catch(Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
            return null;
        }
        $itemlist = $simplexml->xpath('//item');
        $show_ts = strtotime($itemlist[0]->pubDate);
        $max_show_time = 60 * 60 * 12; # 12 hours of seconds
        $newFeed = array (
            'feedimage'   => (string)$simplexml->channel[0]->image->url,
            'itunesimage' => (string)$simplexml->channel[0]->itunesimage['href'],
            'album'       => (string)$simplexml->channel[0]->title
        );
        $show_mode = FALSE;
        $rev_mode = FALSE;
        if($this->menu_limit == ''){
            $max = 1000;
        } else {
            preg_match('/((\-|\+)?)(\d*)(.*)/', $this->menu_limit, $matches);
            $max = $matches[3];
            if(preg_match('/(s|S)/', $matches[4])){
                $show_mode = TRUE;
                $max--;
            }
            if(preg_match('/\-/', $matches[2])){
                $rev_mode = TRUE;
            }
        }
        foreach ($itemlist as $item) {
            $newItem = array (
                'title'   => (string)$item->title,
                'artist'  => (string)$item->pubDate,
                'id'      => (string)$item->guid,
                'url'     => (string)$item->enclosure['url'],
                'type'    => (string)$item->enclosure['type'],
                'genre'   => (string)$item->category,
            );
            preg_match('/img\s*src=[\'"](.*?)[\'"]/', (string)$item->description, $matches);
            if(count($matches) > 1){
                $newItem['imageurl'] = $matches[1];
            } else {
                if(count($item->mediathumbnail) > 0){
                    $newItem['imageurl'] = (string)$item->mediathumbnail['url'];
                } else {
                    $newItem['imageurl'] = (string)$item->imageurl;
                }
            }
            $itemType = explode('/', $newItem['type'], 2);
            if(isset($this->menu_class)){
                $upnpClass = (string)$this->menu_class;
            } else {
                switch (strtolower($itemType[0])) {
                    case 'video':
                        $upnpClass = 'object.item.videoItem';
                        break;
                    case 'audio':
                        $upnpClass = 'object.item.audioItem';
                        break;
                    case 'image':
                        $upnpClass = 'object.item.imageItem';
                        break;
                    default:
                        $upnpClass = 'object.item.videoItem';
                }
            }
            if($newItem['imageurl'] !== ""){
                $arturl = $newItem['imageurl'];
            } else {
                if($newFeed['feedimage'] == ""){
                    $arturl = $newFeed['itunesimage'];
                } else {
                    $arturl = $newFeed['feedimage'];
                }
            }
            $arturl = preg_replace('/ /', '%20', $arturl);
            $episode_ts = strtotime($item->pubDate);
            if($show_mode){
                if(($show_ts - $episode_ts) > $max_show_time){
                    $show_ts = $episode_ts;
                    --$max;
                }
            } else {
                --$max;
            }
            if($max < 0) {break;};
            $MediaItems[] = array (
                'id'                => 'umsp://plugins/daily-podcasts/daily-podcasts?' . $newItem['id'],
                'dc:title'          => $this->menu_header . $newItem['title'] . $this->menu_footer,
                'res'               => $newItem['url'],
                'upnp:class'        => $upnpClass,
                'protocolInfo'      => 'http-get:*:' . $newItem['type'] . ':*',
                'upnp:artist'       => $newItem['artist'], #artist = pubDate
                'upnp:genre'        => $newItem['genre'],
                'upnp:album'        => $newFeed['album'],
                'upnp:album_art'    => $arturl,
                'episode_ts'        => strtotime($item->pubDate),
            );
        }
        usort($MediaItems, array('rssPodcastMenu', 'cmp'));
        if($rev_mode){ $MediaItems = array_reverse($MediaItems); }
        return $MediaItems;
    }

}

?>
