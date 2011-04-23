<?php
	echo "Downloading/updating UMSP plugins from SVN...<br />";
	echo '<pre>' . shell_exec("sudo /etc/init.d/S64umsp svn") . '</pre>';
?>
