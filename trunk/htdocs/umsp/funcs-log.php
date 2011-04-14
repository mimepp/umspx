<?php

/*************************************************************
   Basic logging library for UMSP on wdtv live
   Version 0.1
   History
   When      Who      What
   16.05.10   cd      initial released version 0.1
*************************************************************/

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
$logFile = '/tmp/umsp-log.txt';
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

?>

