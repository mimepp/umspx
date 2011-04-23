<?php

class opmlPodcastMenu extends DailyPodcastMenu{

    public function asUMSP(){
        if(!$this->menu_url) return parent::asUMSP();
        $opml_xml = file_get_contents($this->menu_url);
        $opml = new DOMDocument();
        $opml->loadXML($opml_xml);
        $opml_xpath = new DOMXPath($opml);
        $opml_body = $opml_xpath->query('/opml/body')->item(0);
        $url = $this->menu_node->getElementsByTagName('url')->item(0);
        $oldurl = $this->menu_node->removeChild($url);        
        foreach($opml_body->childNodes as $child){
            $new_child = $this->dom_doc_object->importNode($child, true);
            $this->menu_node->appendChild($new_child);
        }
        $this->write();
        return parent::asUMSP();
    }
}

?>
