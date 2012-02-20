<?php
header('Content-Type: application/json');
if(isset($_GET['username'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    echo $client->getPersonInfo($_GET['username']);
} else {
    echo "invalid params";
}
?>