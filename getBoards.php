<?php
if (isset($_REQUEST['sec'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    echo $client->getBoards($_REQUEST['sec']);
} else {
    echo "invalid params";
}
?>