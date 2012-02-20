<?php
if(isset($_GET['username']) && isset($_GET['password'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    echo $client->getCookie($_GET['username'], $_GET['password']);
} else {
    echo "invalid params";
}
?>