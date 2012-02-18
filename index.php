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
echo $client->query("http://www.baidu.com");
//phpinfo();
// echo $client->getArticle("Pictures","M.1329391194.A");
//echo $client->postAfter("test", "M.1329404225.A", $cookie, "中文测试")
//echo $client->post("test", "无猪蹄", "没有猪蹄", $cookie)
?>
