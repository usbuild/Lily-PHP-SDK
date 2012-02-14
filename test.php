<?php
$ch = curl_init("http://bbs.nju.edu.cn/bbstcon?board=S_Atmosphere&file=M.1325224249.A");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$data = curl_exec($ch);
curl_close($ch);
$data = str_replace("textarea", "div", $data);
//preg_match_all('/http:\/\/bbs(\S+)?\.jpg/', $data, $array);
$data = preg_replace('/(http:\/\/bbs(\S+)?\.jpg)/', '<img src=img.php?url=$1>', $data);
//preg_replace('/(http:)/', '<img>', $data);
echo $data;
//echo $data;
?>

