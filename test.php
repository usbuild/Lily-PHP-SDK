<?php
require_once 'LilyClient.class.php';
$client=new LilyClient();

$field = array(
	"user"=>"nuanbing",
	"day"=>"0",
	"day2"=>"10"
);
echo $client->format_search($field);
?>