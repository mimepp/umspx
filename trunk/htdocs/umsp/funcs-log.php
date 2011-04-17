<?php

/*************************************************************
   Basic logging library for UMSP on wdtv live
   Version 0.1
   History
   When      Who      What
   16.05.10   cd      initial released version 0.1
*************************************************************/
include_once('funcs-config.php');
error_reporting(E_ALL ^ E_NOTICE);	// avoid the notice message.
define("LOG_FILE", _getUMSPTmpPath() . '/umsp-log.txt');	

define('L_ALL',0);
define('L_DEBUG',1);
define('L_INFO',2);
define('L_WARNING',3);
define('L_ERROR',4);
define('L_OFF',5);

$scriptPath = explode('/',$_SERVER['PHP_SELF']);
global $logLevel, $logIdent, $logFile;

// ********************************************************
// set defaults, override in including script if neccessary
$logLevel = L_ERROR;
$logIdent = $scriptPath[count($scriptPath)-1];
$logFile = _getUMSPTmpPath() . '/umsp-log.txt';
// ********************************************************

function _log ($level, $someText, $someVar = null) {
   global $logLevel, $logIdent, $logFile;
    if ($level >= $logLevel) {
        $fh = fopen($logFile, 'a') or die();
        fwrite($fh, date('Y.m.d H:i:s') . ' ' . $logIdent . ' - ' . $someText . "\n");
        if ($someVar) {
             fwrite($fh, print_r($someVar, true) . "\n");
        }
        fclose($fh);
    }
}

function _logDebug ($someText, $someVar = null) {
   _log(L_DEBUG, $someText, $someVar);
}

function _logInfo ($someText, $someVar = null) {
   _log(L_INFO, $someText, $someVar);
}

function _logWarning ($someText, $someVar = null) {
   _log(L_WARNING, $someText, $someVar);
}

function _logError ($someText, $someVar = null) {
   _log(L_ERROR, $someText, $someVar);
}

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

