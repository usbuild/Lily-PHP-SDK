<?php
function getCookie($cookie_jar_name) {
	$cookie_jar = fopen($cookie_jar_name, "r");
	$cookie = fread($cookie_jar, filesize($cookie_jar_name));
	fclose($cookie_jar);
	return $cookie;
}
?>
<?php
$board = "test";
$exp = urlencode("Upload By Lily PHP-SDK");
if(isset($_POST["board"])) $board = $_POST["board"];
if(isset($_POST["exp"])) $exp = $_POST["exp"];
if(!empty($_FILES["file"]))
{
	require_once("LilyClient.php");
	$client = new LilyClient;
	$cookie = getCookie("cookie.txt");
    $file = $_FILES["file"]["tmp_name"];
    $pathpart = pathinfo($file);
    $newname = $pathpart['dirname'].'/'.rand(100000, 999999).$_FILES["file"]["name"];
    rename($file, $newname);
    $file = $newname;
    $url = $client->uploadFile($file, $exp, $board, $cookie);
    if($url) echo $url;
    else echo "Upload Failed";
}
?>
