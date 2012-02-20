<?php
header('Content-Type: application/json');
if(isset($_GET['board'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    $start = isset($_GET['start']) ? $_GET['start'] : null; 
    echo $client->getPosts($_GET['board'], $start);
} else {
    echo "invalid params";
}
?>