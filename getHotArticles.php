<?php
header('Content-Type: application/json');
require_once 'LilyClient.class.php';
$client=new LilyClient();
echo $client->getHotArticles();
?>