<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Daily Podcast</title>
<style type="text/css">
body {
	background-color: #fff;
	color: #000;
	font-family: Trebuchet MS, Arial, Helvetica, sans-serif;
	margin: 16px;
	padding: 0;
}
.thumbwrap {
	border: 1px solid #999;
	padding: 15px 8px 0 8px;
	background-color: #f4f4f4;
	margin: 0;
}
.thumbwrap li {
	display: -moz-inline-box;
	display: inline-block;
	/*\*/ vertical-align: top; /**/
	margin: 0 7px 15px 7px;
	border: 1px solid #999;
	padding: 0;
}
/*  Moz: NO border qui altrimenti difficolta' con width, table altrimenti problemi a text resize (risolubili con refresh) */
.thumbwrap li>div {
	/*\*/ display: table; /**/
	width: 199px;
}
.thumbwrap a {
	display: block;
	text-decoration: none;
	color: #000;
	background-color: #ffe;
	cursor: pointer;
}
/*\*/
.thumbwrap>li .wrimg {
	display: table-cell;
	vertical-align: middle;
	width: 199px;
	height: 199px;
}
/**/
.thumbwrap img {
	border: solid 1px #66f;
	vertical-align: middle;
}
.thumbwrap a:hover {
	background-color: #dfd;
}
/*\*//*/
* html .thumbwrap li .wrimg {
	display: block;
	font-size: 1px;
}
* html .thumbwrap .wrimg span {
	display: inline-block;
	vertical-align: middle;
	height: 199px;
	width: 1px;
}
/**/
.thumbwrap .caption {
	display: block;
	padding: .3em 5px;
	font-size: .9em;
	line-height: 1.1;
	border-top: 1px solid #ccc;
	w\idth: 189px;  /* Moz, IE6 */
}
/* top ib e hover Op < 9.5 */
@media all and (min-width: 0px) {
	html:first-child .thumbwrap a {
		display: inline-block;
		vertical-align: top;
	}
	html:first-child .thumbwrap {
		border-collapse: collapse;
		display: inline-block; /* non deve avere margin */
	}
}
</style>
</head>
<body>
        <?php 
        #http://www.brunildo.org/test/ImgThumbIBL2.html
        function __autoload($class_name) {
            include $class_name . '.php';
        }
        function dump_var($prmFile, $prmLine, $prmName){
            $file = preg_replace('/.*\\\/', '', $prmFile);
            foreach($prmName as $key => $value){
                print "<pre>\n";
                print "$file line $prmLine \$$key\n";
                var_dump($value);
                print "\n";
                print "</pre>\n";
            }
        }
        if(isset($_GET['class'])){
            list($class, $path) = explode('::', stripcslashes($_GET['class']));
            $menu = new $class($path);
        } else {
            $menu = new DailyPodcastMenu();
        }
        $html = $menu->asHTML;
        print "<ul class=\"thumbwrap\" style=\"text-align: center\">\n";
        foreach ($html as $anchor){
            printf("<li><div><a href=\"%s\"><span class=\"wrimg\"><span></span>", $anchor['href']);
            printf("<img src=\"%s\" width=\"110\"/>",  $anchor['img']);
            printf("</span><span class=\"caption\">%s</span></a></div></li>\n", $anchor['text']);
        }
        print "</ul>\n";
        ?>
</body>
</html>

