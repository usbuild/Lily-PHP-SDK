<?php
if(isset($_REQUEST['cookie']) && isset($_REQUEST['board']) && isset($_REQUEST['file'])) {
    require_once 'LilyClient.php';
    $cookie = $_REQUEST['cookie'];
    $board = $_REQUEST['board'];
    $file = $_REQUEST['file'];
    $text = $_REQUEST['text'];
    $client = new LilyClient();
    $client->postAfter($board, $file, $cookie, $text);
} else {
    echo "invalid params";
}
?>