<?php
if(isset($_REQUEST['cookie']) && isset($_REQUEST['board'])) {
    require_once 'LilyClient.class.php';
    $cookie = $_REQUEST['cookie'];
    $board = $_REQUEST['board'];
    $file = $_REQUEST['file'];
    $text = isset($_REQUEST['text']) ? $_REQUEST['text'] : "";
    $client = new LilyClient();
    $re = $client->postAfter($board, $file, $cookie, $text);
    if(!$re)
    	echo "fail";
} else {
    echo "invalid params";
}
?>