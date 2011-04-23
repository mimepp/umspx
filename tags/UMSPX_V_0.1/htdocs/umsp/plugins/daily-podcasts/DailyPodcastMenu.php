<?php

class DailyPodcastMenu extends DailyPodcast{
    public $xpath_query;
    public $dom_doc_object;
    public $dom_xpath_object;
    public $menu_node;
    public $outline_queries = array(
            'url' => '@xmlUrl',
            'image' =>'@imageHref',
            'description' => '@text'
    );

    public function __set($var, $val){
        $this->$var = $val;
    }

    public function __get($var){
        if(isset($this->$var)){
            return $this-$var;
        } elseif(method_exists($this, $var)){
            return $this->$var();
        } else {
            preg_match('/^menu_(.*)/', $var, $matches);
            if(isset($matches[1])) return $this->menu_value($matches[1]);
            throw new Exception("Property $var does not exist");
        }
    }


    public function menu_value($xpath){
        if($this->menu_node->nodeName == 'outline'){
            if(isset($this->outline_queries[$xpath])){
                $xpath = $this->outline_queries[$xpath];
            } else {
                return null;
            }
        }
        $node_list = $this->dom_xpath_object->query($xpath, $this->menu_node);
        if($node_list->length > 0){
            return $node_list->item(0)->nodeValue;
        } else {
            return null;
        }
    }

    public function __construct($query = 'empty'){
        if($query == 'empty'){
            $feedlistXML = strstr(file_get_contents($this->xml_file),"<");
            $root = true;
        } else {
            $feedlistXML = strstr(file_get_contents($this->tmp_file),"<");
            $root = false;
        }

        $this->dom_doc_object = new DOMDocument();
        $this->dom_doc_object->preserveWhiteSpace = false;
        $this->dom_doc_object->loadXML($feedlistXML);
        $this->dom_xpath_object = new DOMXPath($this->dom_doc_object);
        if($query !== 'empty'){
            $this->xpath_query = $query;
        } elseif($this->dom_xpath_object->query('/list')->length > 0){
            $this->xpath_query = '/list';
        } elseif ($this->dom_xpath_object->query('/opml/body')->length > 0){
            $this->xpath_query = '/opml/body';
        }
        $this->menu_node = $this->dom_xpath_object->query($this->xpath_query)->item(0);

        if($root) $this->write();
    }

    public function write(){
        $this->dom_doc_object->formatOutput = true;
        $this->dom_doc_object->save($this->tmp_file);
    }

    public function abs_url($host_url, $relative_url){
        if($relative_url == '') return null;
        if(preg_match('/\/\//', $relative_url)) return $relative_url;
        $host = preg_replace('/(.*\/\/.*?)\/.*/', "$1", $host_url);
        return $host . $relative_url;
    }

    public function url_class($item, $url){
        $class = $this->dom_xpath_object->query('url/@class', $item)->length;
        if($class){
            $class = $this->dom_xpath_object->query('url/@class', $item)->item(0)->nodeValue;
            $class .= 'PodcastMenu';
        } else {
            preg_match('/([^\.]*)$/', $url, $matches);
            switch (strtolower($matches[1])) {
                case 'opml':
                    $class = 'opmlPodcastMenu';
                    break;
                case 'json':
                    $class = 'jsonPodcastMenu';
                    break;
                default:
                    $class = 'rssPodcastMenu';
            }
        }
        return $class;
    }

    public function asUMSP(){
        $media_items = array();
        $outline_index = $item_index = 0;
        $sub_menus = $this->dom_xpath_object->query('outline | item', $this->menu_node);
        foreach ($sub_menus as $menu_item){
            ${$menu_item->nodeName . "_index"}++;
            $this->menu_node = $menu_item;
            if($this->menu_url){
                $class = $this->url_class($menu_item, $this->menu_url);
            } else {
                $class = 'DailyPodcastMenu';
            }
            switch($menu_item->nodeName){
                case 'item';
                    if($class == 'rssPodcastMenu'){
                        $new_object = new $class($this->xpath_query . "/item[$item_index]");
                        $media_array = $new_object->asUMSP();
                        break;
                    }
                case 'outline';
                    $id = sprintf("umsp://plugins/daily-podcasts/daily-podcasts?class=%s::%s/%s[%s]",
                        $class, $this->xpath_query, $menu_item->nodeName,
                        ${$menu_item->nodeName . "_index"});
                    $media = array (
                        'id'                =>  $id,
                        'dc:title'          =>  $this->menu_description,
                        'upnp:class'        => 'object.container',
                        'protocolInfo'      => '*:*:*:*',
                        'restricted'        => '1',
                        'parentID'          => '0',
                    );
                    if($this->menu_image){
                        $media['upnp:album_art'] = $this->abs_url($this->menu_url, $this->menu_image);
                    }
                    $media_array = array($media);
            }
            if(count($media_array) > 0) $media_items = array_merge($media_items, $media_array);
        }
        return $media_items;
    }

    public function asHTML(){
        $items = array();
        $umsp_items = $this->asUMSP();
        foreach ($umsp_items as $item){
            if(isset($item['res'])){
                $href = $item['res'];
            } else {
                $href = $item['id'];
                $href = preg_replace('/.*class=/', '', $href);
                $href = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . '?class=' . $href;
            }
            if(isset($item['upnp:album_art'])){
                $img = str_replace('127.0.0.1', $_SERVER['HTTP_HOST'], $item['upnp:album_art']);
            } else {
                $img = 'default_folder.png';
            }
            $text = $item['dc:title'];
            $items[] = compact('href', 'img', 'text');
        }
        return $items;
    }

}

?>
