<?php
// Daily Podcasts UMSP plugin by PaulF
// http://forum.wdlxtv.com/viewtopic.php?f=49&t=725 

function __autoload($class_name) {
    include $class_name . '.php';
}

function _pluginMain($prmQuery) {
    $queryData = array();
    $prmQuery = stripcslashes($prmQuery);
    parse_str($prmQuery, $queryData);
    if ($prmQuery != '') {
        list($class, $path) = explode('::', stripcslashes($queryData['class']));
        $menu = new $class($path);
    } else {
        $menu = new DailyPodcastMenu();
    }
    $media = $menu->asUMSP;
    foreach($media as &$item){
        if(isset($item['upnp:album_art'])){
            $item['upnp:album_art'] = 'http://' . $_SERVER['HTTP_HOST'] . '/plugins/umsp/plugins/daily-podcasts/daily-podcasts.php?image=' .
                preg_replace('/ /', '%20', $item['upnp:album_art']);
        }
    }
    return $media;
} #end function _pluginMain
if(isset($_GET['image'])){
    $image = $_GET['image'];
    $image =  preg_replace('/ /', '%20', $image);
    $file = file_get_contents($image);
    header('Content-type: ');
    echo $file;
}

?>
