<?php

class DailyPodcastSubMenu extends DailyPodcastMenu{

    public function __construct($path){
        $feedlistXML = file_get_contents($this->tmp_file);
        $this->menu_path = $path;
        $this->menu_object = new SimpleXMLElement($feedlistXML);
        $this->submenu_object = $this->find_node(explode('/', $path), $this->menu_object);
    }

    public function find_node ($hier, $menu_obj){
        $top = array_shift($hier);
        foreach ($menu_obj->item as $item){
            if((string)$item->description == $top){
                if(count($hier) == 0){
                    return $item;
                } else {
                    return $this->find_node($hier, $item);
                }
            }
        }
        return NULL;
    }

}

?>
