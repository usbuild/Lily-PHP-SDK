<?php
header('Content-Type: application/json');
if(isset($_REQUEST['board'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : null; 
    echo $client->getPosts($_REQUEST['board'], $start);
} else {
    echo "invalid params";
}
?>