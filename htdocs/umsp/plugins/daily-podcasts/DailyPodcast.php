<?php

class DailyPodcast {

    public function xml_file(){
        $config = file_get_contents('/conf/config');
        preg_match_all('/DAILY_PODCASTS_XML=\'(.*?)\'\n/', $config, $matches, PREG_PATTERN_ORDER);
        $xml_file = $matches[1][0];
        return $xml_file;
    }

    public function tmp_file(){
        $config = file_get_contents('/conf/config');
        preg_match_all('/DAILY_PODCASTS_TMP=\'(.*?)\'\n/', $config, $matches, PREG_PATTERN_ORDER);
        if(isset($matches[1][0])){
            $tmp_file = $matches[1][0];
        } else {
            $tmp_file = '/tmp/default_temp.xml';
        }
        return $tmp_file;
    }

}

?>
