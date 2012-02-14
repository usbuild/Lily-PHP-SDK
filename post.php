<?php
    function getCookie($cookie_jar_name) {
        $cookie_jar = fopen($cookie_jar_name, "r");
        $cookie = fread($cookie_jar, filesize($cookie_jar_name));
        fclose($cookie_jar);
        return $cookie;
    }
if(isset($_GET['node']))
{
	require_once("LilyClient.php");
	$client = new LilyClient;
	//$cookie = getCookie("cookie.txt");
    if(preg_match('/\d+/', $_GET['node']) > 0)
        $data = $client->getBoards($_GET['node']);
    else
        $data = $client->getForum();
} else {
    $data = $client->getForum();
}
	echo $data;
?>
