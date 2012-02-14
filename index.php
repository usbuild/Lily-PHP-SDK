<?php
    require_once("LilyClient.php");
    $client = new LilyClient;
    echo $client->getArticle("Pictures", "M.1329110963.A");
?>
