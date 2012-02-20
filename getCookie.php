<?php
if(isset($_GET['username']) && isset($_GET['password'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    $re = $client->getCookie($_GET['username'], $_GET['password']);
    if($re == false)
        echo "";
    else echo $re;
} else {
    echo "invalid params";
}
?>