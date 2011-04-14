<?php
/**
 * CONFIG
 */

header("Cache-Control: no-cache, must-revalidate");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");
 
/* sample config
config_tool -c EYETV_IP=192.168.178.50
config_tool -c EYECONNECT_TOKEN=d2d[...]561
config_tool -c EYETV_SERVER_PORT_STREAM=8484
config_tool -c EYETV_SERVER_PORT_CONTROLLER=8485
 */
 
if (file_exists('/conf/config')) {
   $config = file_get_contents('/conf/config');

   if(preg_match('/EYETV_IP=\'(.+)\'/', $config, $m)) {
        $EYETV_IP = $m[1];
   }
   if(preg_match('/EYECONNECT_TOKEN=\'(.+)\'/', $config, $m)) {
        $EYECONNECT_TOKEN = $m[1];
   }
   if(preg_match('/EYETV_SERVER_PORT_STREAM=\'(.+)\'/', $config, $m)) {
        $EYETV_SERVER_PORT_STREAM = $m[1];
   }
   if(preg_match('/EYETV_SERVER_PORT_CONTROLLER=\'(.+)\'/', $config, $m)) {
        $EYETV_SERVER_PORT_CONTROLLER = $m[1];
   }
}

define("LOG_FILE",'/tmp/umsp-log.txt');
define("PLUGIN_NAME",str_replace('.php','',basename(__file__)));
define("PLUGIN_PROXY",str_replace('.php','',basename(__file__)).'-proxy');
define('EYETV_IP',$EYETV_IP);
define('EYECONNECT_TOKEN',$EYECONNECT_TOKEN);
define('SERVER_PORT_STREAM',$EYETV_SERVER_PORT_STREAM);
define('SERVER_PORT_CONTROLLER',$EYETV_SERVER_PORT_CONTROLLER);

function _pluginMain($prmQuery) {
  return _pluginCreateChannelList();
}

function _pluginCreateChannelList()
{
	$retMediaItems = array();
	$t = gzdecode(file_get_contents("http://".EYETV_IP.":2170/live/channels/0/0/",false));
	if ($t) {
		$channels = json_decode($t);
	}

	if ($channels)
	{
		$epg_data = getEPGData($channels->total);
		l($epg_data);
		foreach($channels->channelList as $channel)
		{
			$item = $channel->displayNumber;
			$name = strtoupper($channel->name);
			
			$epg = array_shift($epg_data);
			$epg_now = trim(preg_replace("/\(.*\)$/","",$epg->EPGnow));
			$epg_next = trim(preg_replace("/\(.*\)$/","",$epg->EPGnext));

			$url_data = array('item' => $item, 'itemurl' => "http://".EYETV_IP.":".SERVER_PORT_STREAM);
		    $url_data_string = http_build_query($url_data, '', '&amp;');

			if (!empty($epg_now)) {
				$title = "{$name} - now: {$epg_now} | up next: {$epg_next}";
			} else {
				$title = $name;
			}

			$retMediaItems[] = array (
				'id' => 'umsp://plugins/'.PLUGIN_NAME.'?' . $item,
				'dc:title' => $title,
				'duration' => '99:99:99',
				'upnp:class' => 'object.item.videoitem',
				'res' => 'http://127.0.0.1/umsp/plugins/'.PLUGIN_NAME.'/'.PLUGIN_PROXY.'.php?'.$url_data_string,
				'protocolInfo' => 'http-get:*:*:*',
			);		
		}		
	}
	return $retMediaItems;
}

/**
 * HELPER FUNCTIONS
 */

function gzdecode($data){
  $g=tempnam('/tmp','ff');
  @file_put_contents($g,$data);
  ob_start();
  readgzfile($g);
  $d=ob_get_clean();
  return $d;
}

function getEPGData($max_channels = 30)
{l(EYECONNECT_TOKEN);
	$opts = array('http'=>array(
		'method'=>"GET",
		'header' => "X-Eyeconnect-Client: iPhoneApp1\r\n" .
					"X-Eyeconnect-Token: ".EYECONNECT_TOKEN."\r\n" .
					"Connection: keep-alive\r\n\r\n"
					)
		);
	$context = stream_context_create($opts);
	$t = gzdecode(file_get_contents("http://".EYETV_IP.":2170/live/channels/1/0/0/{$max_channels}/_IPHONE_CHANNELS",false,$context));
	if ($t) {
		$epg_data = json_decode($t);
	}
	return $epg_data->channelList;
}

/**
 * Debug Logs
 * stops if last param is 1
 */
function l()
{
	$t = debug_backtrace();
	$args = func_get_args();
	ob_start();
	echo basename($t[0]["file"]).":{$t[0]["line"]} > ";
	var_dump($args);
	$data = ob_get_contents();
	ob_end_clean();
	file_put_contents(LOG_FILE,$data,FILE_APPEND);
	if(end($args) === 1) die;
}

?>