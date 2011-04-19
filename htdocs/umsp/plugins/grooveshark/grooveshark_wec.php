<?PHP
	include ('info.php');
include_once($_SERVER[DOCUMENT_ROOT] . '/umsp/funcs-config.php');

// Does this WEC version support custom hooks?
if ((defined('WECVERSION')) && (WECVERSION >= 3)) {

	include_once(_getUMSPWorkPath() . '/funcs-config.php');

	// Insert badge if we have one
	if ((isset($pluginInfo['thumb']))&&($pluginInfo['thumb']!=''))
	{
		$desc = '<div style="float: left; padding: 4px 10px 4px 4px;"><img src="'.$pluginInfo['thumb'].'" width="60" height="60" alt="logo"></div>'
			.'<div>'.$pluginInfo['name']." v".$pluginInfo['version']." (".$pluginInfo['date'].") by "
			.$pluginInfo['author'].".<br>".$pluginInfo['desc']."<br>Information: <a href='".$pluginInfo['url']."'>".$pluginInfo['url']."</a>"
			.'</div>';
	}
	elseif ((isset($pluginInfo['art']))&&($pluginInfo['art']!=''))
	{
		$desc = '<div style="float: left; padding: 4px 10px 4px 4px;"><img src="'.$pluginInfo['art'].'" width="60" height="60" alt="logo"></div>'
			.'<div>'.$pluginInfo['name']." v".$pluginInfo['version']." (".$pluginInfo['date'].") by "
			.$pluginInfo['author'].".<br>".$pluginInfo['desc']."<br>Information: <a href='".$pluginInfo['url']."'>".$pluginInfo['url']."</a>"
			.'</div>';
	}
	else
	{
		$desc = $pluginInfo['name'].' v'.$pluginInfo['version'].' ('.$pluginInfo['date'].') by '
			.$pluginInfo['author'].'.<br>'.$pluginInfo['desc']."<br>Information: <a href='".$pluginInfo['url']."'>".$pluginInfo['url'].'</a>';
	}

	$key = strtoupper("{$pluginInfo['id']}_DESC");
	$wec_options[$key] = array(
		'configname'	=> $key,
		'configdesc'	=> $desc,
		'longdesc'	=> '',
		'group'		=> $pluginInfo['name'],
		'type'		=> WECT_DESC,
		'page'		=> WECP_UMSP,
		'displaypri'	=> -25,
		'availval'	=> array(),
		'availvalname'	=> array(),
		'defaultval'	=> '',
		'currentval'	=> ''
	);

	$wec_options[$pluginInfo['id']] = array(
		'configname'	=> $pluginInfo['id'],
		'configdesc'	=> 'Enable '.$pluginInfo['name'].' UMSP plugin',
		'longdesc'	=> '',
		'group'		=> $pluginInfo['name'],
		'type'		=> WECT_BOOL,
		'page'		=> WECP_UMSP,
		'displaypri'	=> -10,
		'availval'	=> array('off','on'),
		'availvalname'	=> array(),
		'defaultval'	=> '',
		'currentval'	=> '',
		'readhook'	=> wec_umspwrap_read,
		'writehook'	=> wec_umspwrap_write,
		'backuphook'	=> NULL,
		'restorehook'	=> NULL
	);

	$wec_options['GROOVESHARK_XML'] = array(
		'configname'	=> 'GROOVESHARK_XML',
		'configdesc'	=> 'Filename for Canned Search Criteria',
		'longdesc'	=> 'Location of file containing Filename for Canned Serach Criteria<br>.'
					.'That can be used by the plug-in',
		'group'		=> $pluginInfo['name'],
		'type'		=> WECT_TEXT,
		'page'		=> WECP_UMSP,
		'availval'	=> array(),
		'availvalname'	=> array(),
		'defaultval'	=> '',
		'currentval'	=> ''
	);

	$wec_options['GROOVESHARK_USERNAME'] = array(
		'configname'	=> 'GROOVESHARK_USERNAME',
		'configdesc'	=> '<a href="http://www.grooveshark.com/">Grooveshark</a> username',
		'longdesc'	=> 'Your username at grooveshark.com<br>'
					.'Not a mandatory field but extends the plugin features.',
		'group'		=> $pluginInfo['name'],
		'type'		=> WECT_TEXT,
		'page'		=> WECP_UMSP,
		'availval'	=> array(),
		'availvalname'	=> array(),
		'defaultval'	=> '',
		'currentval'	=> '',
		'readhook'	=> wec_grooveshark_read,
		'writehook'	=> wec_grooveshark_write,
		'backuphook'	=> NULL,
		'restorehook'	=> NULL
	);
	$wec_options['GROOVESHARK_PASSWORD'] = array(
		'configname'	=> 'GROOVESHARK_PASSWORD',
		'configdesc'	=> '<a href="http://www.grooveshark.com/">Grooveshark</a> password',
		'longdesc'	=> 'Your password at grooveshark.com<br>'
					.'Not a mandatory field but extends the plugin features.',
		'group'		=> $pluginInfo['name'],
		'type'		=> WECT_TEXT,
		'page'		=> WECP_UMSP,
		'availval'	=> array(),
		'availvalname'	=> array(),
		'defaultval'	=> '',
		'currentval'	=> '',
		'readhook'	=> wec_grooveshark_read,
		'writehook'	=> wec_grooveshark_write,
		'backuphook'	=> NULL,
		'restorehook'	=> NULL
	); 


	function wec_grooveshark_write($wec_option_arr, $value) {
		if( file_exists('/tmp/conf/grooveshark.conf') ) {
			$config = new SimpleXMLElement(file_get_contents('/tmp/conf/grooveshark.conf'));
		} else {
			$config = new SimpleXMLElement('<config></config>');
		}

		if( $wec_option_arr['configname'] == 'GROOVESHARK_USERNAME' ) {
			$config->username = $value;
			$config->asXML('/tmp/grooveshark.conf');
			# TODO: Better solution?
			exec('sudo cp /tmp/grooveshark.conf /tmp/conf/grooveshark.conf');
			exec('sudo rm /tmp/grooveshark.conf');
		} elseif( $wec_option_arr['configname'] == 'GROOVESHARK_PASSWORD' ) {
			if( trim($value) != '' ) {
				$config->password = $value;
				$config->asXML('/tmp/grooveshark.conf');
				# TODO: Better solution?
				exec('sudo cp /tmp/grooveshark.conf /tmp/conf/grooveshark.conf');
				exec('sudo rm /tmp/grooveshark.conf');
			}
		}
	}

	function wec_grooveshark_read($wec_option_arr) {
		if( file_exists('/tmp/conf/grooveshark.conf') ) {
			$config = new SimpleXMLElement(file_get_contents('/tmp/conf/grooveshark.conf'));
		} else {
			$config = new SimpleXMLElement('<config></config>');
		}
		global $wec_options;
		if( $wec_option_arr['configname'] == 'GROOVESHARK_USERNAME' ) {
			$wec_options[$wec_option_arr['configname']]['currentval'] = (string)$config->username[0];
		} elseif( $wec_option_arr['configname'] == 'GROOVESHARK_PASSWORD' ) {
			$wec_options[$wec_option_arr['configname']]['currentval'] = (string)$config->password[0];
		}
	}
}
?>

