<?php
$dhandle = opendir('./plugins');
$files = array();

if ($dhandle) {
   while (false !== ($fname = readdir($dhandle))) {
      if (($fname != '.') && ($fname != '..') && (is_dir("./plugins/" . $fname)) &&
          ($fname != basename($_SERVER['PHP_SELF']))) {
          $files[] = $fname;
      }
   }
   closedir($dhandle);
}

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">
<html xmlns=\"http://www.w3.org/1999/xhtml\">
<head>
<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
<title>UMSP Plugin Setup</title>
<link type=\"text/css\" rel=\"stylesheet\" href=\"/css/dhtmlwindowcontent.css\" />
</head>
<body>\n";

echo "<h2>UMSP Plugin Setup</h2>\n";
echo "To enable/disable or configure settings for a particular plugin click its link below:<br/>\n";
echo "<ul>";
foreach( $files as $fname )
{
    echo "<li>";
    if (file_exists("./plugins/" . $fname . "/config.php")) {
        echo "<a href=\"./plugins/" . $fname . "/config.php\">" . $fname . "</a>";
    } else {
        echo $fname;
    }
}
echo "</ul>";
echo "</body></html>";
?>
