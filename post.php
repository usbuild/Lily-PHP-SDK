<?php
if(isset($_REQUEST['title']) && isset($_REQUEST['cookie']) && isset($_REQUEST['board'])) {
    require_once 'LilyClient.class.php';
    $cookie = $_REQUEST['cookie'];
    $client = new LilyClient();
    $board = $_REQUEST['board'];
    $title = $_REQUEST['title'];
    $text = isset($_REQUEST['text']) ? $_REQUEST['text'] : "";
    $text = str_replace("<br />", "\n", $text);
    $text = preg_replace('/<img src=\"(.*?)\".*?>/', '${1}', $text);
    $re = $client->post($board, $title, $text, $cookie);
    if(!$re)
        echo "fail";
} else {
    echo "invalid params";
}
?>