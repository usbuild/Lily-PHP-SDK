<?php
function getCookie($cookie_jar_name) {
	$cookie_jar = fopen($cookie_jar_name, "r");
	$cookie = fread($cookie_jar, filesize($cookie_jar_name));
	fclose($cookie_jar);
	return $cookie;
}
?>
<?php
	require_once("LilyClient.php");
	$client = new LilyClient;
	$cookie = getCookie("cookie.txt");
    //$data = $client->post('test', null, '测试', $cookie);
	var_dump($data);
?>
