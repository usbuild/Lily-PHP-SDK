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
if(isset($_REQUEST["board"])) $board = $_REQUEST["board"];
$rData = new stdClass();
if(!empty($_FILES["imgFile"]))
{
	require_once("LilyClient.php");
	$client = new LilyClient;
	$cookie = getCookie("cookie.txt");
    $file = $_FILES["imgFile"]["tmp_name"];
    $pathpart = pathinfo($file);
    $newname = $pathpart['dirname'].'/'.rand(100000, 999999).$_FILES["imgFile"]["name"];
    rename($file, $newname);
    $file = $newname;
    $url = $client->uploadFile($file, $exp, $board, $cookie);
    if($url) {
        $rData->error = 0;
        $rData->url = $url;
    } else {
        $rData->error = 1;
        $rData->message = "";
    }
    echo json_encode($rData);
    return json_encode($rData);
    
}
?>
