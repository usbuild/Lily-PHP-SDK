<?php
if(isset($_REQUEST['username']) && isset($_REQUEST['password'])) {
    require_once 'LilyClient.class.php';
    $client=new LilyClient();
    $re = $client->getCookie($_REQUEST['username'], $_REQUEST['password']);
    if($re == false)
        echo "";
    else echo $re;
} else {
    echo "invalid params";
}
?>