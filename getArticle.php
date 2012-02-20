<?php
header('Content-Type: application/json');
if(isset($_GET['board']) && isset($_GET['file'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    $start = isset($_GET['start']) ? $_GET['start'] : null;
    echo $client->getArticle($_GET['board'], $_GET['file'], $start);
} else {
    echo "invalid params";
}
?>