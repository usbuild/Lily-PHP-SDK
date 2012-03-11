<?php
if (isset($_REQUEST['cookie'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    echo $client->logout($_REQUEST[$cookie]);
} else {
    echo "invalid params";
}
?>