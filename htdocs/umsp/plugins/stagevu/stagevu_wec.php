    <?php

       include ('info.php');

       // Does this WEC version support custom hooks?
       if ((defined('WECVERSION')) && (WECVERSION >= 3)) {

          include_once('/usr/share/umsp/funcs-config.php');

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
             'configname'   => $key,
             'configdesc'   => $desc,
             'longdesc'   => '',
             'group'      => $pluginInfo['name'],
             'type'      => WECT_DESC,
             'page'      => WECP_UMSP,
             'displaypri'   => -25,
             'availval'   => array(),
             'availvalname'   => array(),
             'defaultval'   => '',
             'currentval'   => ''
          );

          $wec_options[$pluginInfo['id']] = array(
             'configname'   => $pluginInfo['id'],
             'configdesc'   => 'Enable '.$pluginInfo['name'].' UMSP plugin',
             'longdesc'   => '',
             'group'      => $pluginInfo['name'],
             'type'      => WECT_BOOL,
             'page'      => WECP_UMSP,
             'displaypri'   => -10,
             'availval'   => array('off','on'),
             'availvalname'   => array(),
             'defaultval'   => '',
             'currentval'   => '',
             'readhook'   => wec_umspwrap_read,
             'writehook'   => wec_umspwrap_write,
             'backuphook'   => NULL,
             'restorehook'   => NULL
          );

        $wec_options['STAGEVU_SEARCH_SORT'] = array('configname' => 'STAGEVU_SEARCH_SORT',
            'configdesc' => "In which order should search results be sorted",
            'longdesc' => "",
            'group' => $pluginInfo['name'],
            'type' => WECT_SELECT,
            'page' => WECP_UMSP,
            'availval' => array('relevance','rating','views'),
            'availvalname' => array('Relevance','Rating','Views'),
            'defaultval' => 'views',
            'currentval' => '');

        $wec_options['STAGEVU_NUMBER_OF_ITEMS'] = array (
            'configname'    => 'STAGEVU_NUMBER_OF_ITEMS',
            'configdesc'    => 'Number of items to be fetched & displayed from StageVu.com',
            'longdesc'  => '',
            'group'     => $pluginInfo['name'],
            'type' => WECT_SELECT,
            'page' => WECP_UMSP,
            'availval' => array('7','14','21','28','35'),
            'availvalname' => array('7','14','21','28','35'),
            'defaultval' => '7',
            'currentval' => '');

	$wec_options['STAGEVU_CANNED_SEARCH_FILE'] = array(
		'configname'	=> 'STAGEVU_CANNED_SEARCH_FILE',
		'configdesc'	=> 'Filename for Canned Search Criteria',
		'longdesc'	=> 'Location of file containing Filename for Canned Serach Criteria<br>.'
					.'Plain-text, one search phrase/sentence per line',
		'group'		=> $pluginInfo['name'],
		'type'		=> WECT_TEXT,
		'page'		=> WECP_UMSP,
		'availval'	=> array(),
		'availvalname'	=> array(),
		'defaultval'	=> '',
		'currentval'	=> ''
	);

       }

    ?>

