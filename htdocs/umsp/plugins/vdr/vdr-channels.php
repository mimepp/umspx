<?php

/*************************************************************
   Channel parser script for VDR live TV streaming to UMSP
   Version 0.1
   History
   When      Who      What
   16.05.10   cd      initial released version 0.1
   URL
   http://forum.wdlxtv.com/viewtopic.php?f=49&t=517 
*************************************************************/

// shunte fixed log path 2010.12.28
include_once(_getUMSPWorkPath() . '/funcs-log.php');

// ********************************************************
// Settings, please adjust to your needs

// Server address and port
global $vdrAddress, $vdrPort;
$vdrAddress = 'fortknox';
$vdrPort = 3000;

// Logging see log_func.php
$logLevel = L_ERROR;
$logIdent = 'vdr-channels';

// show additional soundtracks or not
// with some channels wdtv couldn't switch soundtracks in my tests
define ('SHOW_TRAX', false);

// ********************************************************

function _logNode($level, $reader) {
    _log ($level, "type: " . $reader->nodeType);
    _log ($level, ", name: " . $reader->name);
    _log ($level, ", localName: " . $reader->localName);
    _log ($level, ", value: " . $reader->value);
    _log ($level, ", readString: " . $reader->readString());
}

function unhtmlentities($string)
{
    return html_entity_decode($string, ENT_COMPAT, 'utf-8');
}

function file_get_contents_utf8($fn) {
    $content = file_get_contents($fn);
    return mb_convert_encoding($content, 'UTF-8',
    mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));
}

function _pluginMain($prmQuery) {

   global $vdrAddress, $vdrPort;
    _logDebug ('$vdrAddress: ' . $vdrAddress . ', $vdrPort: ' . $vdrPort);

    _logInfo ('================= start ===================');
   
    $vdrURL = 'http://' . $vdrAddress . ':' . $vdrPort . '/';
    $vdrLaunchURL = 'http://localhost/umsp/plugins/vdr/vdr-proxy.php?itemUrl=' . $vdrURL;
    // $vdrLaunchURL = 'umsp://plugins/vdr/vdr-proxy?itemUrl=' . $vdrURL;
    $vdrChannelsURL = 'umsp://plugins/vdr/vdr-channels?';
   
    $ElmStart = 'div';
    $AttrStart = array ('class', 'contents');
    $ElmLink = 'a';
   
    $chan='';
    $chanIdent = '??chan??';

    if (!$prmQuery) {
        # Start URL, list channel groups
        $vdrServiceUrl = 'groups.html';
        # $vdrServiceUrl = 'tree.html';
        $ElmItem = 'div';
        $ItemIdent = 'class';
    }
    else {
        $prmQueryParts = explode ($chanIdent, $prmQuery);
        _logDebug ($prmQueryParts);
        if (count($prmQueryParts) > 1) {
            $prmQuery = $prmQueryParts[0];
            $chan = $prmQueryParts[1];
        }
        _logDebug ($chan);
        $vdrServiceUrl = $prmQuery;
        $ElmItem = 'li';
        $ItemIdent = 'value';
    }
   
    _logInfo ('$prmQuery: ' . $prmQuery);
    _logDebug ('$vdrURL: ' . $vdrURL);
    _logDebug ('$vdrServiceUrl: ' . $vdrServiceUrl);
   
    $channellistXML = file_get_contents_utf8($vdrURL . $vdrServiceUrl);
    _logDebug ($channellistXML);
   
    // filter singular "vod" attributes for they will "kill" the parser
    $channellistXML = preg_replace ('/\ vod/', '', $channellistXML);
    _logDebug ($channellistXML);

    $reader = new XMLReader();
    $reader->XML($channellistXML);
    $reader->setParserProperty (XMLReader::VALIDATE, false);
   
    while ($reader->read()) {

        # Read until list of channels or groups
        if (($reader->nodeType == XMLReader::ELEMENT) &&
            ($reader->localName == $ElmStart) &&
            ($reader->getAttribute($AttrStart[0]) == $AttrStart[1])) {

            # Read until first list item (containing channel info)
            while ($reader->read() AND
                !(($reader->nodeType == XMLReader::END_ELEMENT) &&
                ($reader->localName == $ElmStart) &&
                ($reader->getAttribute($AttrStart[0]) == $AttrStart[1]))) {

                if (($reader->nodeType == XMLReader::ELEMENT) &&
                    ($reader->localName == $ElmItem)) { // store item identifier for later
                   
                    $ItemID = $reader->getAttribute ($ItemIdent);

                    if (('' == $chan) || ($chan == $ItemID)) {

                        # Read until first link (containing channel default link or group)
                        while ($reader->read() &&
                            !(($reader->nodeType == XMLReader::ELEMENT) &&
                            ($reader->localName == $ElmLink))) { ; }

                        _logNode(L_INFO, $reader);

                        # get name and id                       
                        if ($reader->getAttribute('tvid')) {
                            $isChannel = true;
                            $newChannel['id'] = strval(count($retMediaItems)+1);
                            $newChannel['res'] = $vdrLaunchURL . $reader->getAttribute('href');
                            $newChannel['class'] = 'object.item.videoitem';
                            $newChannel['protocolInfo'] = 'http-get:*:video/mpeg2ts:*';
                        }
                        else {
                            $isChannel = false;
                            $newChannel['id']  = $vdrChannelsURL . $reader->getAttribute('href');
                            $newChannel['res'] = '';
                            $newChannel['class'] = 'object.container';
                            $newChannel['protocolInfo'] = '*:*:*:*';
                        }
                        // $newChannel['title'] = htmlentities($reader->readString(), ENT_COMPAT, 'UTF-8');
                        $newChannel['title'] =  mb_convert_encoding($reader->readString(), 'ISO-8859-1', 'auto');
                        _logDebug ($newChannel['title']);

                        # New channelinfo item parsed. Now add as media item:
                        $retMediaItems[] = array (
                            'dc:title'    => $newChannel['title'],
                            'id'          => $newChannel['id'],
                            'res'         => $newChannel['res'] ,
                            'upnp:class'  => $newChannel['class'],
                            'protocolInfo' => $newChannel['protocolInfo'],
                        );
                        _logDebug ($retMediaItems[count ($retMediaItems)-1]);

                        # Read other soundtracks
                        $hasSoundtrax = false;
                        while ($reader->read() &&
                            !(($reader->nodeType == XMLReader::END_ELEMENT) &&
                            ($reader->localName == $ElmItem))) {
                                if (isChannel &&
                                    ($reader->nodeType == XMLReader::ELEMENT) &&
                                    ($reader->localName == $ElmLink)) {
                                   
                                        if ('' == $chan) {
                                            $hasSoundtrax = true;
                                        }
                                        else {
                                            $retMediaItems[] = array (
                                                'id'            => strval(count($retMediaItems)+1),
                                                'res'           => $vdrLaunchURL . $reader->getAttribute('href'),
                                                'dc:title'      => $newChannel['title'] . ' - track: ' . $reader->readString() . ($reader->getAttribute('class') == 'dpid'?' dolby digital':''),
                                                'upnp:class'    => 'object.item.videoitem',
                                                'protocolInfo'  => 'http-get:*:video/mpeg2ts:*',
                                            );
                                        }
                                } # end if

                        } # end while

                        if (SHOW_TRAX && $hasSoundtrax) {
                            $retMediaItems[] = array (
                                'id'            => $vdrChannelsURL . $prmQuery . $chanIdent . $ItemID,
                                'res'           => '',
                                'dc:title'      => $newChannel['title'] . ' - other soundtracks',
                                'upnp:class'    => 'object.container',
                                'protocolInfo'  => '*:*:*:*',
                            );
                        }
                    }
                } #end if

            } #end while

        } #end if

    } #end while

    _logDebug ($retMediaItems);

    return $retMediaItems;

} # end function

?>

