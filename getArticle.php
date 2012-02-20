<?php
header('Content-Type: application/json');
if(isset($_GET['board']) && isset($_GET['file'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    echo $client->getArticle($_GET['board'], $_GET['file']);
} else {
    echo "invalid params";
}
?>