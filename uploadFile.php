<?php
$exp = urlencode("Upload By Lily PHP-SDK");
if(isset($_REQUEST["board"])) $board = $_REQUEST["board"];
if(isset($_REQUEST["exp"])) $exp = $_REQUEST["exp"];
if(!empty($_FILES["file"]) && isset($_REQUEST['cookie']))
{
   require_once("LilyClient.class.php");
   $client = new LilyClient;
   $cookie = $_REQUEST['cookie'];
   $file = $_FILES["file"]["tmp_name"];

  
  $pathpart = pathinfo($file);
   $newname = str_replace(' ', "_", $_FILES["file"]["name"]);
   if(strlen($newname) > 15)
       $newname = substr($newname, strlen($newname) - 10, 10);
    
  $newname = $pathpart['dirname'].'/'.$newname;
  rename($file, $newname);
  $file = $newname;
   $url = $client->uploadFile($file, $exp, $board, $cookie);
   if($url) echo $url;
   else echo "Upload Failed";
} else echo "invalid params"
?>
