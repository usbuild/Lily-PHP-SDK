<?php

function getCookie($cookie_jar_name) {
    $cookie_jar = fopen($cookie_jar_name, "r");
    $cookie = fread($cookie_jar, filesize($cookie_jar_name));
    fclose($cookie_jar);
    return $cookie;
}

require_once("LilyClient.php");
$cookie = getCookie("cookie.txt");
$client = new LilyClient;
//$data = $client->ansi_to_html($client->query("http://bbs.nju.edu.cn/bbsqry?userid=comeonzqc"));
//$data = str_replace("textarea", "div", $data);
//echo $data;
// echo $client->getPersonInfo("cat810");
//phpinfo();
//echo $client->getArticle("Pictures", "M.1329568342.A");
// $client->format_date("Sat Feb 18 21:46:28 2012");
// echo $client->getArticle("Pictures", "M.1329542807.A");
// echo $client->getForums();
echo $client->getHotArticles();
// echo $client->getPosts("Pictures");
// echo $client->format_ubb("http://baidu.com 123test http://img.png");

//echo $client->postAfter("test", "M.1329404225.A", $cookie, "中文测试")
//echo $client->post("test", "无猪蹄", "没有猪蹄", $cookie)
?>
