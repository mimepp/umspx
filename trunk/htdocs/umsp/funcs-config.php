<?php

function _configMainHTML($prmPluginInfo, $prmStatus = '') {
    $html  = '<html xmlns="http://www.w3.org/1999/xhtml">';
    $html .= '<head>';
    $html .= '<meta content="text/html; charset=utf-8" http-equiv="Content-Type" />';
    $html .= '<title>' . $prmPluginInfo['name'] . '</title>';
    $html .= '</head>';
    $html .= '<body>';
    $html .= '<p align="center"><strong>' . $prmPluginInfo['name'] . '</strong><br>';
    $html .= $prmPluginInfo['desc'] . '</p>';
    $html .= '<table border="0" align="center" cellspacing="10">';
    $html .= '  <tr>';
    $html .= '    <td>Version: ' . $prmPluginInfo['version'] . '</td>';
    $html .= '    <td>Date: ' . $prmPluginInfo['date'] . '</td>';
    $html .= '  </tr>';
    $html .= '  <tr>';
    $html .= '    <td>Author: ' . $prmPluginInfo['author'] . '</td>';
    $html .= '    <td>ID: ' . $prmPluginInfo['id'] . '</td>';
    $html .= '  </tr>';
    $html .= '  <tr>';
    $html .= '  	<td colspan="2" align="center"><a href="' . $prmPluginInfo['url'] . '">Web Link</a></td>';
    $html .= '</table>';
    $html .= '<form method="get">';
    $html .= '<p align="center">';
    $html .= '<input type="radio" name="pluginStatus" value="on"' . ( ($prmStatus == 'on') ? ' checked ' : '' ) . '> Enabled';
    $html .= '<input type="radio" name="pluginStatus" value="off"' . ( ($prmStatus == 'off') ? ' checked ' : '' ) . '> Disabled<br>';
    $html .= '<!--<input type="submit" name="buttonSubmit" id="buttonSubmit" value="Submit" />-->';
    $html .= '<input type="submit" value="Submit" />';
    $html .= '</p>';
    $html .= '</form>';
    $html .= '<hr />';
    return $html;
} # end function

function _createPluginRootItems() {
    $pluginStatusAll = _readPluginStatusAll();
    if ( is_array($pluginStatusAll) ) {
        $pluginRootItems = array();
        foreach ( $pluginStatusAll as $pluginId => $pluginStatus) {
            $pluginConfigFile = $_SERVER["DOCUMENT_ROOT"] . '/umsp/plugins/' . $pluginId . '/config.php';
            if ( ($pluginStatus =='on') && (is_file($pluginConfigFile)) ) {
                unset($pluginInfo);
                define('_DONT_RUN_CONFIG_',1);
                include($pluginConfigFile);
                if ( is_array($pluginInfo) ) {
                    $pluginRootItems[] = array(	
                        'id'			=> 'umsp://plugins/' . $pluginInfo['id'],
                        'parentID'		=> '0',
                        'dc:title'		=> $pluginInfo['name'],
                        'desc'			=> $pluginInfo['desc'],
                        'upnp:class'	=> 'object.container',
                        'upnp:album_art'=> '',
                    );
                }
            }
        }
    }
    return $pluginRootItems;
}


function _readPluginStatus($prmPluginId) {
    # TODO: replace with config_tool?
    $status = _readPluginVar($prmPluginId);
    return $status;
} # end function

function _readPluginStatusAll() {
    # TODO: replace with config_tool?
    $pluginStatusAll = _readPluginVarAll();
    if ( is_array($pluginStatusAll) ) {
        return $pluginStatusAll;
    } else {
        return null;
    }
} # end function

function _writePluginStatus($prmPluginId, $prmStatus) {
    # TODO: replace with config_tool?
    # status should be 'on' or 'off'
    $result = _writePluginVar($prmPluginId, $prmStatus);
    return $result;
} # end function

#######################################################################
# TODO: replace with config_tool?
# TODO: remove?

function _getUMSPConfPath() {
	return getcwd() . '/conf';
}

function _getUMSPTmpPath() {
	return getcwd() . '/tmp';
}

function _getUMSPPluginPath() {
	return getcwd() . '/plugins';
}

function _getUMSPFont() {
	return getcwd() . '/plugins/reader/fonts/arial.ttf';
	//return getcwd() . '/font/wqy-zenhei/wqy-zenhei.ttc';
}

function _getUMSPFontBD() {
	return getcwd() . '/plugins/reader/fonts/arialbd.ttf';
	//return getcwd() . '/font/wqy-zenhei/wqy-zenhei.ttc';
}

function _readPluginVar($prmVarName) {
        $filename = _getUMSPConfPath() . '/umsp-plugins-status';
        if (!is_readable($filename)) { return null; }
        $handle = fopen($filename, 'r');
        $rawContent = fread($handle, 8192);
        fclose($handle);
        $varData = unserialize($rawContent);
        if (is_array($varData) && isset($varData[$prmVarName])) {
                return $varData[$prmVarName];
        } else {
                return null;
        } # end if
} # end function

function _readPluginVarAll() {
        $filename = _getUMSPConfPath() . '/umsp-plugins-status';
        if (!is_readable($filename)) { return null; }
        $handle = fopen($filename, 'r');
        $rawContent = fread($handle, 8192);
        fclose($handle);
        $varData = unserialize($rawContent);
        if ( is_array($varData) ) {
                return $varData;
        } else {
                return null;
        } # end if
} # end function

function _writePluginVar($prmVarName, $prmVarValue) {
    $filename = _getUMSPConfPath() . '/umsp-plugins-status';
    if (is_readable($filename)) {
        $handle = fopen($filename, 'r');
        $rawContent = fread($handle, 8192);
        fclose($handle);
        $prevData = unserialize($rawContent);
        if ($prevData === false || !is_array($prevData)) {
            $newData = array();
        } else {
            $newData = $prevData;
        }
    } else {
        $newData = array();
    }
    $newData[$prmVarName] = $prmVarValue;
    $filenameTmp = _getUMSPTmpPath() . '/umsp-plugins-status';
    $handle = fopen($filenameTmp, 'w');
    $rawContent = serialize($newData);
    $success = fwrite($handle, $rawContent);
    fclose($handle);
    # TODO: Better solution?
    exec('sudo cp ' . $filenameTmp . ' ' . $filename );
    return $success;
} # end function

?>
