<html>
<head>
<meta http-equiv="content-type" content="text/html;charset=utf-8" />
</head>
<body>
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
	//$top10 = $client->getTop10();
	//$top10 = json_decode($top10);
	$data = $client->getHotBoard();
	//$data = json_decode($data);
	//$data = $client->getPosts("test");
	//$data = $client->query("http://bbs.nju.edu.cn/bbsinfo", $cookie);
	//var_dump($data);
	echo $data;
?>
	<center><h1>全站十大</h1>
	<table><tr><td>序号</td><td>版区</td><td>标题</td>
<?php
	//foreach($data->hotBoard as $key=>$value) {
		//echo '<tr><td>No'.($key + 1) . '</td><td><a href="http://bbs.nju.edu.cn/bbstdoc?board='.$value->b.'">' .$value->b.'</a></td>';
		//echo '<td><a href="http://bbs.nju.edu.cn/bbstcon?board='.$value->b.'&file=M.'.$value->f.'.A">'.$value->t.'</a></td></tr>';
	//}
?>
	</table>
	</center> 
</body>
</html>
