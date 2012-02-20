<?php
if(isset($_REQUEST['cookie']) && isset($_REQUEST['board'])) {
    require_once 'LilyClient.class.php';
    $cookie = $_REQUEST['cookie'];
    $board = $_REQUEST['board'];
    $file = $_REQUEST['file'];
    $text = isset($_REQUEST['text']) ? $_REQUEST['text'] : "";
    $client = new LilyClient();
    $client->postAfter($board, $file, $cookie, $text);
} else {
    echo "invalid params";
}
?>