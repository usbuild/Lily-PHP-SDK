<?php
function getCookie($cookie_jar_name) {
	$cookie_jar = fopen($cookie_jar_name, "r");
	$cookie = fread($cookie_jar, filesize($cookie_jar_name));
	fclose($cookie_jar);
	return $cookie;
}
?>

<?php
if(isset($_REQUEST['title'])) {
    require_once 'LilyClient.php';
    $cookie = getCookie("cookie.txt");
    $client = new LilyClient();
    $board = $_REQUEST['board'];
    $title = $_REQUEST['title'];
    $text = $_REQUEST['content'];
    echo $text;
    $text = str_replace("<br />", "\n", $text);
    $text = preg_replace('/<img src=\"(.*?)\".*?>/', '${1}', $text);
    $client->post($board, $title, $text, $cookie);
    //echo $text;
    
}
?>