<?php
include_once($_SERVER["DOCUMENT_ROOT"].'/umsp/funcs-config.php');
echo _getUMSPConfPath();
echo 'LOCATION: http://'.$_SERVER['SERVER_ADDR'].':'.$_SERVER['SERVER_PORT'].'/umsp/MediaServerServiceDesc.xml' . "\r\n";
var_dump($_SERVER);
echo $_SERVER['HTTP_HOST'];
echo php_uname();
echo PHP_OS;
echo DIRECTORY_SEPARATOR;
echo PHP_SHLIB_SUFFIX;
echo PATH_SEPARATOR;
?>

