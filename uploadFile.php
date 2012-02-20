<?php
$board = "test";
$exp = urlencode("Upload By Lily PHP-SDK");
var_dump($_POST);
if(isset($_POST["board"])) $board = $_POST["board"];
if(isset($_POST["exp"])) $exp = $_POST["exp"];
if(!empty($_FILES["file"]) && isset($_POST['cookie']))
{
   require_once("LilyClient.class.php");
   $client = new LilyClient;
   $cookie = $_POST['cookie'];
   $file = $_FILES["file"]["tmp_name"];
   $pathpart = pathinfo($file);
   $newname = str_replace(' ', "_", $_FILES["file"]["name"]);
   if(strlen($newname) > 15)
       $newname = substr($newname, strlen($newname) - 10);
   $newname = $pathpart['dirname'].'/'.rand(10, 99).$newname;
   rename($file, $newname);
   $file = $newname;
   $url = $client->uploadFile($file, $exp, $board, $cookie);
   if($url) echo $url;
   else echo "Upload Failed";
} else echo "invalid params"
?>
