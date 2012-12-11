<?php

if (isset($_GET['url']) && !empty($_GET['url'])){
  
  if(array_key_exists("HTTP_IF_MODIFIED_SINCE",$_SERVER))
    {
        header("HTTP/1.0 304 Not Modified");
        return;
    }
  
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $_GET['url']);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
    array(
    'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*',
    'Referer: http://bbs.nju.edu.cn',
    'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
    'Host: bbs.nju.edu.cn'
));
    curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1); 
    $image = curl_exec ($ch);
  if(!$image) return;
  $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE );
  curl_close ($ch);
  
  header('Last-Modified: ' . date("r"));
  if($content_type == 'image/gif') {
    header('Content-Type: '.$content_type);
    echo $image;
    return;
  } else {
    header('Content-Type: image/png');
  }
   $image = imagecreatefromstring($image);
   $width = imagesx($image);
   $height = imagesy($image);
    $percent = 1;
   $maxWidth = 400;
    if($width > $maxWidth) {
     $percent = $maxWidth / $width;
   
$new_width = $width * $percent;
$new_height = $height * $percent;
   $image_p = imagecreatetruecolor($new_width, $new_height);
  imagecopyresampled($image_p, $image, 0, 0, 0, 0, $new_width, $new_height, $width, $height);
  imagepng($image_p);
   imagedestroy($image_p);
    } else {
    imagepng($image);
    }
  imagedestroy($image);
}
?>