<?php
header('Content-Type: image/jpeg');
if (isset($_GET['url']) && !empty($_GET['url'])){
    $ch = curl_init();
    curl_setopt ($ch, CURLOPT_URL, $_GET['url']);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
    array(
    'Accept: image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, application/vnd.ms-excel, application/vnd.ms-powerpoint, application/msword, */*',
    'Referer: http://bbs.nju.edu.cn',
    'User-Agent: Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; InfoPath.1)',
    'Host: bbs.nju.edu.cn'
));

    $pic = curl_exec ($ch);
    curl_close ($ch);
    echo $pic;
}
?>
