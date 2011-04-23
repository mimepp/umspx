<?php

class jsonPodcastMenu extends DailyPodcastMenu{

    public function asUMSP(){
        if(!$this->menu_url) return parent::asUMSP();
        $limit = $this->menu_limit;

        $json = file_get_contents($this->menu_url);
        if($json === false){
            return null;
        } else {
            $json_array = json_decode($json, true);
            if(count($json_array['title']) > 0){
                $json_array = array($json_array);
            }
            $seen = array();
            $url = $this->menu_node->getElementsByTagName('url')->item(0);
            $oldurl = $this->menu_node->removeChild($url);
            foreach ($json_array as $podcast){
                $new_list_item = $this->dom_doc_object->createElement('item');

                if(isset($podcast['logo_url'])){
                    $new_image = $this->dom_doc_object->createElement('image', $podcast['logo_url']);
                    $new_list_item->appendChild($new_image);
                }
                $new_description = $this->dom_doc_object->createElement('description', $podcast['title']);
                $new_list_item->appendChild($new_description);

                if(isset($podcast['url'])){
                    $new_url_item = $this->dom_doc_object->createElement('item');
                    $new_url = $this->dom_doc_object->createElement('url', $podcast['url']);
                    $new_url_item->appendChild($new_url);
                    $new_url_description = $this->dom_doc_object->createElement('description', $podcast['title']);
                    $new_url_item->appendChild($new_url_description);
                    if(isset($podcast['logo_url'])){
                        $new_url_image = $this->dom_doc_object->createElement('image', $podcast['logo_url']);
                        $new_list_item->appendChild($new_url_image);
                    }

                    if($limit){
                        $new_limit = $this->dom_doc_object->createElement('limit', $limit);
                        $new_url_item->appendChild($new_limit);
                    }
                    $new_list_item->appendChild($new_url_item);
                }
                $this->menu_node->appendChild($new_list_item);
            }
            $this->write();
            return parent::asUMSP();
        }
    }

}

?>
