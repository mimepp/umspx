<?php
// qiyi UMSP plugin by Daniel
// 奇异影视

	include('info.php');

//设置输出调试日志路径

//设置每页视频数量
define("ITEMPERPAGE",30);

function _pluginMain($prmQuery) {
	//l('[DEBUG]',$prmQuery);
	$queryData = array();
	parse_str($prmQuery, $queryData);
	if ($queryData['mode'] == 'root'){
		//根目录
		$items = _pluginCreateRootList($queryData['sort']);
		return $items;
	}else if($queryData['mode'] == 'channel'){
		//频道分页列表
		$items = _pluginCreateChannelPage($queryData['channel'],$queryData['sort']);
		return $items;
	}else if($queryData['mode'] == 'channelpage'){
		//频道分页视频列表
		$items = _pluginCreateMovieList($queryData['channel'],$queryData['page'],$queryData['sort']);
		return $items;
	}else if($queryData['mode'] == 'play'){
		//生成播放页面
		$items = _pluginCreatePlayList($queryData['movlink'],$queryData['name']);
		return $items;
	}else if($queryData['mode'] == 'playtv'){
		//生成播放TV页面
		$items = _pluginCreateTVPlayList($queryData['movlink'],$queryData['name']);
		return $items;
	}else{
		//第一页,选择排序方式
		$items = _pluginCreateSortList();
		return $items;
	}
}
//生成排序列表
function _pluginCreateSortList(){
	$sortarray = array(
		'按最新排序' => 2,
		'按关注排序' => 5,
		'按热播排序' => 3,
		'按好评排序' => 4
	);
	foreach ($sortarray as $name => $id) {
		$isort = $id;
		$data = array(
			'mode' => 'root',
			'sort' => $isort
		);
		$dataString = http_build_query($data, '','&amp;');
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
			'dc:title' => $name,
			'upnp:class' => 'object.container',
		);
	}
	return $retMediaItems;
}

//生成根目录列表
function _pluginCreateRootList($isort){
	$categories = array(
		'电影' => '_E7_94_B5_E5_BD_B1',
		'粤语电影' => '_E7_94_B5_E5_BD_B1_20_E7_B2_A4_E8_AF_AD_E7_94_B5_E5_BD_B1',
		'连续剧' => '_E7_94_B5_E8_A7_86_E5_89_A7',
		'粤语连续剧' => '_E7_94_B5_E8_A7_86_E5_89_A7_20_E7_B2_A4_E8_AF_AD_E7_94_B5_E8_A7_86_E5_89_A7',
		'动漫' => '_E5_8A_A8_E6_BC_AB',
		'纪录片' => '_E7_BA_AA_E5_BD_95_E7_89_87',
		'综艺' => '_E7_BB_BC_E8_89_BA',
		'音乐' => '_E9_9F_B3_E4_B9_90'
	);
	foreach ($categories as $name => $id) {
	$channel = $id;
	$data = array(
		'mode' => 'channel',
		'sort' => $isort,
		'channel' => $channel
	);
	$dataString = http_build_query($data, '','&amp;');
	$retMediaItems[] = array (
		'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
		'dc:title' => $name,
		'upnp:class' => 'object.container',
	);
	}
	return $retMediaItems;
}
//生成频道分页
function _pluginCreateChannelPage($channel,$isort)
{
	if(   $channel == '_E7_94_B5_E5_BD_B1' || 
		$channel == '_E7_94_B5_E8_A7_86_E5_89_A7' || 
		$channel == '_E7_94_B5_E8_A7_86_E5_89_A7_20_E7_B2_A4_E8_AF_AD_E7_94_B5_E8_A7_86_E5_89_A7' || 
		$channel == '_E7_94_B5_E5_BD_B1_20_E7_B2_A4_E8_AF_AD_E7_94_B5_E5_BD_B1' ){
			$html = file_get_contents('http://search.video.qiyi.com/category/' . $channel . '/1/'.$isort.'/1/' .ITEMPERPAGE.'//www/');
		} else {
		$html = file_get_contents('http://search.video.qiyi.com/searchCategory/' . $channel . '/1/'.$isort.'/1/' .ITEMPERPAGE.'/www/');
		}
		preg_match_all('/"sumPages":(.*?),"weight"/',$html,$sumPages);
		//页面数量
		$PagesNum = (int)$sumPages[1][0];
		
		for($z=0; $z<$PagesNum;$z++){
		$page = $z+1;
		$data = array(
			'mode' => 'channelpage',
			'sort' => $isort,
			'channel' => $channel,
			'page' => $page,
			);
		$dataString = http_build_query($data, '','&amp;');
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
			'dc:title' => '第'.$page.'页',
			'upnp:class' => 'object.container',
			);
		}
		return $retMediaItems;		
}
//生成频道分页视频列表
function _pluginCreateMovieList($channel,$page,$isort)
{
	if(   $channel == '_E7_94_B5_E5_BD_B1' || 
		$channel == '_E7_94_B5_E8_A7_86_E5_89_A7' || 
		$channel == '_E7_94_B5_E8_A7_86_E5_89_A7_20_E7_B2_A4_E8_AF_AD_E7_94_B5_E8_A7_86_E5_89_A7' || 
		$channel == '_E7_94_B5_E5_BD_B1_20_E7_B2_A4_E8_AF_AD_E7_94_B5_E5_BD_B1'){
			$html = file_get_contents('http://search.video.qiyi.com/category/' . $channel . '/1/'.$isort.'/' . $page . '/' .ITEMPERPAGE.'//www/');
		} else {
			$html = file_get_contents('http://search.video.qiyi.com/searchCategory/' . $channel . '/1/'.$isort.'/' . $page . '/' .ITEMPERPAGE.'/www/');	
		}
		preg_match_all('/"VrsVideoTv.tvName":"(.*?)","broadImg":/',$html,$title);
		preg_match_all('/"vrsVideoTv.TvBigPic":"(.*?)","VrsVideoTv.tvDesc":/',$html,$bigpic);		
		preg_match_all('/"TvApplication.purl":"(.*?)","category"/',$html,$movieurl);
		preg_match_all('/"tvsets":"(.*?)","firstUrl":/',$html,$tvsets);
		preg_match_all('/"firstUrl":"(.*?)","vrsVideoTv.TvBigPic/',$html,$firstUrl);
		
		for ($z = 0; $z < sizeof($title[1]); $z++) {
			if ($firstUrl[1][$z]!='')
			{
				$istv = 1;
				$movlink = urlencode($firstUrl[1][$z]);
			}else
			{				
				$istv =0;
				$movlink = urlencode($movieurl[1][$z]);
			}
			if ((int)$tvsets[1][$z]>0)
			{
				$name = $title[1][$z] . ' (共' . $tvsets[1][$z] . '集)';
			}else
			{
				$name = $title[1][$z];
			}
			
			$playmode = 'play';
			
			if($istv == 0){
				$playmode = 'play';
			}else if($istv == 1){
				$playmode = 'playtv';
			}else{
				$playmode = 'playtv';
			}
			
			$data = array(
				'mode' => $playmode,
				'movlink' => $movlink,
				'name' => $name
			);
			
			$dataString = http_build_query($data,'', '&amp;');
			$retMediaItems[] = array (
				'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
				'dc:title' => $name,
				'upnp:album_art'=> $bigpic[1][$z],
				'upnp:class' => 'object.container'
			);
		}
		return $retMediaItems;
}
//生成TV列表
function _pluginCreateTVPlayList($movlink,$name)
{
	$html = file_get_contents(urldecode($movlink));
	preg_match_all('/<a videoId="(.*?)"(.*?)href="(.*?)"(.*?)>(.*?)<\/a>/',$html,$tvurls);
	//l($tvurls);
	//$tvurls[3] url,tvurls[5] name
	for ($z = 0;$z<sizeof($tvurls[3]);$z++){
		$tvname = '';
		if(strlen($tvurls[5][$z])>4)
		{
			$tvname = $tvurls[5][$z];
		}else{
			$tvname = $name . ' - ' .$tvurls[5][$z];
		}
		$data = array(
			'mode' => 'play',
			'movlink' => $tvurls[3][$z],
			'name' => $tvname
			);
		$dataString = http_build_query($data,'', '&amp;');
		
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
			'dc:title' => $tvname,
			'upnp:class' => 'object.container'
			);
	}
	return $retMediaItems;	
}
//生成播放列表
function _pluginCreatePlayList($movlink,$name)
{
	$vurl = _getVideoUrl(urldecode($movlink));
	if ($vurl == '')
	{
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
			'res' => $vurl,
			'dc:title' => '该视频暂时不支持此平台',
			'upnp:class'	=> 'object.item.videoitem',
			'protocolInfo'	=> 'http-get:*:video/mp4:*',
		);
	}else{
		$retMediaItems[] = array (
			'id' => 'umsp://plugins/qiyi/qiyi?' . $dataString,
			'res' => $vurl,
			'dc:title' => '播放 '.$name,
			'upnp:class'	=> 'object.item.videoitem',
			'protocolInfo'	=> 'http-get:*:video/mp4:*',
		);
	}
	return $retMediaItems;
}

//获取播放地址
function _getVideoUrl($url) 
{
	$html = file_get_contents($url);
	preg_match_all('/tvId : "(.*?)",\/\/剧集id/',$html,$tvid);
	$html = file_get_contents('http://cache.video.qiyi.com/h5/v/' .$tvid[1][0] . '/');	
	preg_match_all('/"url":"(.*?)"/',$html,$vurl);
	$videourl = $vurl[1][0];
	return $videourl;
}

?>

