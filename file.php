<?php
	require_once("LilyClient.php");
	$client = new LilyClient;
	$cookie = $client->getCookie('usbuild', '19911022bbs');
	echo $cookie;
	$file = fopen("cookie.txt", 'w+');
	fwrite($file, $cookie);
	fclose($file);
?>