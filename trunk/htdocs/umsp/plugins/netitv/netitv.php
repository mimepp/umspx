<?php
// NetiTV UMSP plugin by Daniel

	include('info.php');

// 天翼视讯(目前直播不支持,点播完美)

define("LOG_FILE",'/tmp/umsp-log.txt');	

function _pluginMain($prmQuery) {
	//l('[DEBUG]',$prmQuery);
	$queryData = array();
	parse_str($prmQuery, $queryData);
	//根目录
	if ($queryData['mode'] == 'root'){
	$items = _pluginCreateRootList();
	return $items;
	}else if($queryData['mode'] == 'channel'){
		//生成频道页面
		//mode=channel&channel=21002&id=2
		$items = _pluginCreateChannelList($queryData['channel'],$queryData['id']);
		return $items;
	}else if($queryData['mode'] == 'channelpage'){
		//生成频道分页
		//mode=channelpage&channel=22010&id=1276
		$items = _pluginCreateChannelPage($queryData['channel'],$queryData['id']);
		return $items;
	}else if($queryData['mode'] == 'movielist'){
		//生成电影列表
		//mode=movielist&channel=21001&id=2400&page=1
		$items = _pluginCreateMovieList($queryData['channel'],$queryData['id'],$queryData['page']);
		return $items;
	}else if($queryData['mode'] == 'moviedetail'){
		//生成电影播放页面
		//mode=moviedetail&channel=21001&movieid=355045
		$items = _pluginCreateMovieDetail($queryData['channel'],$queryData['movieid']);
		return $items;
	}
	else{
	$items = _pluginCreateRootList();
	return $items;
	}
}

//生成根目录
function _pluginCreateRootList(){
	$html = file_get_contents('http://www.netitv.com/channel.xml');	
	$html = str_replace("\r","",$html);
	$html = str_replace("\n","",$html);
	preg_match_all('/<channel>(.*?)<\/channel>/',$html,$channelstr);
	for ($z = 0; $z < sizeof($channelstr[1]); $z++) {
		preg_match_all('/<id>(.*?)<\/id>/',$channelstr[1][$z],$idstr);
		preg_match_all('/\<name\>\<\!\[CDATA\[(.*?)\]\]\>\<\/name\>/',$channelstr[1][$z],$namestr);
		preg_match_all('/\<uuid\>\<\!\[CDATA\[(.*?)\]\]\>\<\/uuid\>/',$channelstr[1][$z],$uuidstr);
		preg_match_all('/\<url\>\<\!\[CDATA\[(.*?)\]\]\>\<\/url>/',$channelstr[1][$z],$picstr);
		
		$data = array(
			'mode' => 'channel',
			'channel' => $uuidstr[1][0],
			'id' => $idstr[1][0]
		);
		$picurl = 'http://127.0.0.1/umsp/media/generic.jpg';
		if (strlen($picstr[1][0])>5)
		{
			$picurl='http://www.netitv.com/'.$picstr[1][0];
		}

		$dataString = http_build_query($data, '','&amp;');
		//mode=channel&channel=21002&id=2
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/netitv/netitv?' . $dataString,
			'dc:title' => $namestr[1][0],
			'upnp:album_art'=> $picurl,
			'upnp:class' => 'object.container',
		);
	}
	return $retMediaItems;

}
//生成频道页面
function _pluginCreateChannelList($uuid,$id){
	$url = 'http://www.netitv.com/'.$uuid.'/nodeXml/'.$id.'.xml';
	$html = file_get_contents($url);	
	$html = str_replace("\r","",$html);
	$html = str_replace("\n","",$html);

	preg_match_all('/<node>(.*?)<\/node>/',$html,$nodestr);
	for ($z = 0; $z < sizeof($nodestr[1]); $z++) {
		//<id>2400</id>
		//<name><![CDATA[大片]]></name>
		//<root>2400</root>
		//http://www.netitv.com/21001/newsXml/2400_1.xml
		//mode=channeltype&channel=22010&id=1276
		preg_match_all('/<id>(.*?)<\/id>/',$nodestr[1][$z],$idstr);
		preg_match_all('/\<name\>\<\!\[CDATA\[(.*?)\]\]\>\<\/name\>/',$nodestr[1][$z],$namestr);
		preg_match_all('/\<url\>\<\!\[CDATA\[(.*?)\]\]\>\<\/url>/',$nodestr[1][$z],$picstr);
		
		$data = array(
			'mode' => 'channelpage',
			'channel' => $uuid,
			'id' => $idstr[1][0],
		);
		$picurl = 'http://127.0.0.1/umsp/media/generic.jpg';
		if (strlen($picstr[1][0])>5)
		{
			$picurl='http://www.netitv.com/'.$picstr[1][0];
		}
		$dataString = http_build_query($data, '','&amp;');
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/netitv/netitv?' . $dataString,
			'dc:title' => $namestr[1][0],
			'upnp:album_art'=> $picurl,
			'upnp:class' => 'object.container',
		);
		
	}
	//l($retMediaItems);
	return $retMediaItems;

}
//生成频道分页
function _pluginCreateChannelPage($channel,$id)
{
	$picurl = 'http://c.dryicons.com/images/icon_sets/shine_icon_set/png/256x256/pages.png';
	$url = 'http://www.netitv.com/'.$channel.'/newsXml/'.$id.'_1.xml';
	$html = file_get_contents($url);	
	$html = str_replace("\r","",$html);
	$html = str_replace("\n","",$html);
	preg_match_all('/<page_num>(.*?)<\/page_num>/',$html,$pagestr);
	$pagenum = (int)$pagestr[1][0];
	if($pagenum == 1){
		return _pluginCreateMovieList($channel,$id,1);
	}
	for($z=0; $z<$pagenum;$z++){
		$page = $z+1;
		$data = array(
			'mode' => 'movielist',
			'channel' => $channel,
			'id' => $id,
			'page' => $page,
		);
		$dataString = http_build_query($data, '','&amp;');
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/netitv/netitv?' . $dataString,
			'dc:title' => '第'.$page.'页',
			'upnp:album_art'=> $picurl,
			'upnp:class' => 'object.container',
    );
		
	}
	return $retMediaItems;
	
}
//生成节目页面
function _pluginCreateMovieList($channel,$id,$page)
{
	//http://www.netitv.com/21001/newsXml/2400_1.xml
	$url = 'http://www.netitv.com/'.$channel.'/newsXml/'.$id.'_'.$page.'.xml';
	$html = file_get_contents($url);	
	$html = str_replace("\r","",$html);
	$html = str_replace("\n","",$html);
	//<movie id="362269"></movie>
	//<name><![CDATA[怦然心动]]></name>
	//<url meta="1"><![CDATA[/21001/cms_images/www1/2010-12/21/cms_be3e94c7a9b44c4ea535554b7d204c73.jpg]]></url> 
	//<playurls></playurls>
	//<url islive="0" type="3" bit_stream="2" ivolume="1" fileid="359825" vpid="362269" integral="0" content_telecomid="00000005001600021292240627272"><![CDATA[http://vods.netitv.com//00000005//DISK2/20101221/ws_20101221_prxd_0800.mp4]]></url>
	//<url islive="0" type="3" bit_stream="1" ivolume="1" fileid="359826" vpid="362269" integral="0" content_telecomid="00000005001600021292240627271"><![CDATA[http://vods.netitv.com//00000005//DISK2/20101221/ws_20101221_prxd_0352.mp4]]></url>
	//http://www.netitv.com/21001/proXml/362269_1.xml
	preg_match_all('/<movie(.*?)<\/movie>/',$html,$moviestr);
	for ($z = 0; $z < sizeof($moviestr[0]); $z++) {
		preg_match_all('/\<movie id="(.*?)"\>/',$moviestr[0][$z],$movieid);
		preg_match_all('/\<name\>\<\!\[CDATA\[(.*?)\]\]\>\<\/name\>/',$moviestr[0][$z],$namestr);
		preg_match_all('/\<url meta="1"\>\<\!\[CDATA\[(.*?)\]\]\>\<\/url\>/',$moviestr[0][$z],$picstr);

			$data = array(
				'mode' => 'moviedetail',
				'channel' => $channel,
				'movieid' => $movieid[1][0],
			);
			$picurl = 'http://127.0.0.1/umsp/media/generic.jpg';
			if (strlen($picstr[1][0])>5)
			{
				$picurl='http://www.netitv.com/'.$picstr[1][0];
			}
			$dataString = http_build_query($data, '','&amp;');
				$retMediaItems[] = array (
					'id' => 'umsp://plugins/netitv/netitv?' . $dataString,
					'dc:title' => $namestr[1][0],
					'upnp:album_art'=> $picurl,
					'upnp:class' => 'object.container',
		);		
	}
	//l($retMediaItems);
	return $retMediaItems;
}
//生成电影播放页面
function _pluginCreateMovieDetail($channel,$movieid){
	$picurl = 'http://www.icosky.com/icon/png/Media/Buttons/Button%20Play.png';
	$url = 'http://www.netitv.com/'.$channel.'/proXml/'.$movieid.'_1.xml';
	$html = file_get_contents($url);	
	$html = str_replace("\r","",$html);
	$html = str_replace("\n","",$html);
	//生成高清列表 (.*?)
	//<url islive="0" type="3" bit_stream="2" ivolume="1" fileid="303025" vpid="268122" integral="0" content_telecomid="00000019001600030000000694940"><![CDATA[http://vods.netitv.com//dsj2/2010/08/31/4811551c-98f5-4f62-a5c4-eb12f9400f79.mp4]]></url>
	//<name><![CDATA[怦然心动]]></name>
	
	preg_match_all('/\<name\>\<\!\[CDATA\[(.*?)\]\]\>\<\/name\>/',$html,$namestr);

	preg_match_all('/\<url islive="(0|1)" type="3" bit_stream="2" ivolume="(.*?)"(.*?)\<\!\[CDATA\[(.*?)\]\]\>\<\/url\>/',$html,$moviestr);

	for ($z = 0; $z < sizeof($moviestr[0]); $z++) {
				//$moviestr[1] islive,$moviestr[2] ivolume,$moviestr[4] movieurl
			$islive = (int)$moviestr[1][$z]; //是不是直播,1是直播,0是点播
			$title = $namestr[1][0].'-'.$moviestr[2][$z];//名字
			$url = $moviestr[4][$z];//地址
			
			if($islive == 1){
				//直播
				$title = $title.'[直播][WDTV不支持]';
				$url = _GetTVUrl($url);
			}
			
		//$moviestr[1] islive,$moviestr[2] ivolume,$moviestr[4] movieurl
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/netitv/netitv?' . $dataString,
			'res' => $url,
			'dc:title' => '播放 '.$title .'(高清)',
			'upnp:album_art'=> $picurl,
			'upnp:class'	=> 'object.item.videoitem',
			'protocolInfo'	=> '*:*:*:*',
		);
	}
	//生成标清列表
	preg_match_all('/\<url islive="(0|1)" type="3" bit_stream="1" ivolume="(.*?)"(.*?)\<\!\[CDATA\[(.*?)\]\]\>\<\/url\>/',$html,$moviestr);
	for ($z = 0; $z < sizeof($moviestr[0]); $z++) {
		//$moviestr[1] islive,$moviestr[2] ivolume,$moviestr[4] movieurl
			$islive = (int)$moviestr[1][$z]; //是不是直播,1是直播,0是点播
			$title = $namestr[1][0].'-'.$moviestr[2][$z];//名字
			$url = $moviestr[4][$z];//地址


			if($islive == 1){
				//直播
				$title = $title.'[直播][WDTV不支持]';
				$url = _GetTVUrl($url);
			}
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/netitv/netitv?' . $dataString,
			'res' => $url,
			'dc:title' => '播放 '.$title .'(标清)',
			'upnp:album_art'=> $picurl,
			'upnp:class'	=> 'object.item.videoitem',
			'protocolInfo'	=> '*:*:*:*',
		);
	}
	//l($retMediaItems);
	return $retMediaItems;
}
//_另外的获取直播地址的方法
function _GetTVUrl_2($url)
{
	$tvurl = 'http://biz.vsdn.tv380.com/playlive.php?'.$url;
	//l('DEBUG--------GetTVUrl',$tvurl);
	return $tvurl;
}
//获取直播地址
function _GetTVUrl($url)
{
	//l('DEBUG--------GetTVUrl',$url);
	$tvurl = 'http://biz.vsdn.tv380.com/ts.php?uuid='.$url;
	$html = file_get_contents($tvurl);
	//var playurl="rtmp://222.73.105.187:1755/30395NfWL5jwG------X-5c0U0-5c05ck---5ckRXYHHkXki4RY0YgUHYmXHYUlm-w40YbgX4i4UillwiSewTejjjTm7ijN--5N5mfWL5j5N.I.o5j0Yg5c0kXgR5c-5cL5cR5ck---5c-5N5m.I.o5j5NpoF5j0k0J0gJXRJg5lH-5c0k0J0gJXRJ0H5lH-H-5cH-H-5N5mpoF5j5NK8z5jvBovIp5l0kk5cdfdK5lk00J0--JRHJRr5lH-5c.sQ.o5lBsA.5cds5l5csA5l0krR0UHrgg5cvf5lk-gH5N5mK8z5j5NxdoB5jSewTTu5w00YkkgkkX-Y-5c00YJkkgJkkXJY-5N5mxdoB5j";goOn(playurl);
	$html = str_replace("\r","",$html);
	$html = str_replace("\n","",$html);
	$html = str_replace("rtmp://","http://",$html);
	preg_match_all('/playurl="(.*?)";/',$html,$urlstr);
	$retUrl = $urlstr[1][0].'/temp.flv';
	//$retUrl = 'http://localhost/umsp/plugins/netitv/netitv-proxy.php?itemurl='.$retUrl;
	//l('DEBUG--------GetTVUrl2',$retUrl);
	return $retUrl;
}
/**
* 调试
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

