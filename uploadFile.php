<?php
$board = "test";
$exp = urlencode("Upload By Lily PHP-SDK");
if(isset($_POST["board"])) $board = $_POST["board"];
if(isset($_POST["exp"])) $exp = $_POST["exp"];
if(!empty($_FILES["file"]) && isset($_POST['cookie']))
{
   require_once("LilyClient.php");
   $client = new LilyClient;
   $cookie = $_POST['cookie'];
   $file = $_FILES["file"]["tmp_name"];
   $pathpart = pathinfo($file);
   $newname = $pathpart['dirname'].'/'.rand(100000, 999999).str_replace(" ", "-",$_FILES["file"]["name"]);
   rename($file, $newname);
   $file = $newname;
   $url = $client->uploadFile($file, $exp, $board, $cookie);
   if($url) echo $url;
   else echo "Upload Failed";
} else echo "invalid params"
?>
