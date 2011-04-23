<?php

header("content-type: text/xml");

# Import functions
include('funcs-upnp.php');
# Import $myMediaItems array
include('media-items.php');

# Build DIDL-XML from $myMediaItems array
$tmpDOM = _createDIDL($myMediaItems);
echo $tmpDOM->saveXML();
?>
