<?
//dummy to redirect to the true URL since player does not handle off-site redirection sometimes.
$stream = $_GET['stream'];
$file = get_headers($stream);
 if (strpos($file[13],"http"))
  {
   $url = ltrim($file[13],"Location: ");
    } # end if
     else
      {
       foreach ($file as $key => $value)
        {
         if (strstr($value,"http://"))
          {
           $url = ltrim($value,": ");
       } # end else
         } # end for
          } # end if
//print_r $file;
//
// $url = ltrim($value,"Location: ");
//
header("Location: $url");
?>
