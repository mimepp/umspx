<?php
        header ("Content-type: image/png");
        $img_handle = ImageCreate (230, 20) or die ("Cannot Create image");
        $back_color = ImageColorAllocate ($img_handle, 0, 10, 10);
        $txt_color = ImageColorAllocate ($img_handle, 233, 114, 191);
        ImageString ($img_handle, 31, 5, 5,  "My first Program with GD", $txt_color);
        ImagePng ($img_handle);
    ?> 