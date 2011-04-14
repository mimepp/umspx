<?php
$fileTypesByExt = array(
	'wav' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-wav:*',
	),
	'mpa' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/mpeg:*',
	),
	'.mp1' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/mpeg:*',
	),
	'mp3' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/mpeg:*',
	),
	'aiff' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-aiff:*',
	),
	'aif' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-aiff:*',
	),
	'wma' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-ms-wma:*',
	),
	'lpcm' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/lpcm:*',
	),
	'aac' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-aac:*',
	),
	'm4a' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-m4a:*',
	),
	'ac3' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-ac3:*',
	),
	'pcm' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/lpcm:*',
	),
	'flac' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/flac:*',
	),
	'ogg' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:application/ogg:*',
	),
	'mka' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-matroska:*',
	),
	'mp4a' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/x-m4a:*',
	),
	'mp2' => array(
		'class' => 'object.item.audioItem',
		'mime' => 'file-get:*:audio/mpeg:*',
	),
	'gif' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/gif:*',
	),
	'jpg' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/jpeg:*',
	),
	'jpe' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/jpeg:*',
	),
	'png' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/png:*',
	),
	'tiff' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/tiff:*',
	),
	'tif' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/tiff:*',
	),
	'jpeg' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/jpeg:*',
	),
	'bmp' => array(
		'class' => 'object.item.imageItem',
		'mime' => 'file-get:*:image/bmp:*',
	),
	'asf' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/x-ms-asf:*',
	),
	'wmv' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/x-ms-wmv:*',
	),
	'mpeg2' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2:*',
	),
	'avi' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/x-msvideo:*',
	),
	'divx' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/x-msvideo:*',
	),
	'mpg' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg:*',
	),
	'm1v' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg:*',
	),
	'm2v' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg:*',
	),
	'mp4' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mp4:*',
	),
	'mov' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/quicktime:*',
	),
	'vob' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/dvd:*',
	),
	'dvr-ms' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/x-ms-dvr:*',
	),
	'dat' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg:*',
	),
	'mpeg' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg:*',
	),
	'm1s' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg:*',
	),
	'm2p' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2:*',
	),
	'm2t' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'm2ts' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'mts' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'ts' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'tp' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'trp' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'm4t' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2ts:*',
	),
	'm4v' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/MP4V-ES:*',
	),
	'vbs' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2:*',
	),
	'mod' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mpeg2:*',
	),
	'mkv' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/x-matroska:*',
	),
#	'iso' => array(
#		application/x-isoview
#		'mime' => 'file-get:*:application/x-isoview:*',
#	'ogm' => array(
#		application/ogg
#		'mime' => 'file-get:*:application/ogg:*',
	'3g2' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mp4:*',
	),
	'3gp' => array(
		'class' => 'object.item.videoItem',
		'mime' => 'file-get:*:video/mp4:*',
	),
);
?>
