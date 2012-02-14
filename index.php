<?php
    require_once("LilyClient.php");
    require_once("simple_html_dom.php");
    $client = new LilyClient;
    $data = $client->getArticle("Girls", "M.1329146109.A");
    echo $data;
?>
