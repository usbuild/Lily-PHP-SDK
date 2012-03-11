<?php
header('Content-Type: application/json');
if(isset($_REQUEST['username'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    echo $client->getPersonInfo($_REQUEST['username']);
} else {
    echo "invalid params";
}
?>