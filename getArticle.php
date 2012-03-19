<?php
header('Content-Type: application/json');
if(isset($_REQUEST['board']) && isset($_REQUEST['file'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    $start = isset($_REQUEST['start']) ? $_REQUEST['start'] : -1;
    echo $client->getArticle($_REQUEST['board'], $_REQUEST['file'], $start);
} else {
    echo "invalid params";
}
?>
