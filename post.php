<?php
if(isset($_REQUEST['title']) && isset($_REQUEST['cookie']) && isset($_REQUEST['board'])) {
    require_once 'LilyClient.php';
    $cookie = $_REQUEST['cookie'];
    $client = new LilyClient();
    $board = $_REQUEST['board'];
    $title = $_REQUEST['title'];
    $text = $_REQUEST['text'];
    $text = str_replace("<br />", "\n", $text);
    $text = preg_replace('/<img src=\"(.*?)\".*?>/', '${1}', $text);
    if($client->post($board, $title, $text, $cookie))
        echo "success";
    else 
        echo "fail";
    
}
?>