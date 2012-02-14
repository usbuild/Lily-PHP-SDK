<?php
	//author Usbuild
require_once("config.php");
require_once("simple_html_dom.php");
class LilyClient{
function getCookie($username, $password) {
	global $Config;
	$cookie_array = array();
	$login_url = $Config->login_url;
	$param = array(
		'type'=>'2',
		'id'=>$username,
		'pw'=>$password,
	);
	$request = http_build_query($param);
	$ch = curl_init($login_url); 
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_NOBODY, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	try{
		$tmpArray = explode("setCookie('", $data);
		$cookie_string = $tmpArray[1];
		$tmpArray = explode("')", $cookie_string);
		$cookie_string = $tmpArray[0];
		$temp_array = preg_split("/\d+/", $cookie_string);
		$cookie_array['_U_UID'] = substr($temp_array[1], 1,strlen($temp_array[1]) - 2);
		$temp_array = preg_split("/\D+/", $cookie_string);
		$cookie_array['_U_NUM'] = 2 + $temp_array[0];
		$cookie_array['_U_KEY'] = $temp_array[1] - 2;
		$cookie = '_U_NUM='.$cookie_array['_U_NUM'].'; _U_UID='.$cookie_array['_U_UID'].'; _U_KEY='.$cookie_array['_U_KEY'];
	} catch(Exception $e){}
	return $cookie;
}
function query($url, $cookie = null) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	$data = mb_convert_encoding($data, "UTF-8", "GBK");
	return $data;
}
function getTop10() {
	$data = $this->query("http://bbs.nju.edu.cn/cache/t_top10.js");
	return '{"top10":'.$this->getJson($data).'}';
}
function getHotBoard() {
	$data = $this->query("http://bbs.nju.edu.cn/cache/t_hotbrd.js");
	return '{"hotBoard":'.$this->getJson($data).'}';
}

function uploadFile($filename, $exp, $board, $cookie) {
	$post_data = array(//此处BBS进行了混淆，以下能够正常工作
		"up"=>'@'.realpath($filename),
		"board"=>$exp,
		"exp"=>"",
		"ptext"=>$board,
	);
	$curl = curl_init();
    $post_url = "http://bbs.nju.edu.cn/bbsdoupload"; 
	curl_setopt($curl, CURLOPT_URL, $post_url);
	curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	curl_setopt($curl, CURLOPT_POST, 1 );
	curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($curl);
	$error = curl_errno($curl);
	curl_close($curl);
	if($error != 0) return false;
	$start = strpos($data, "url=") + 4;
	$end = strrpos($data, "'");
	$length = $end - $start;
	$data = substr($data, $start, $length);
	$data = str_replace("\r\n", '', $data);//换行符替换掉
	$ch = curl_init("http://bbs.nju.edu.cn/".$data);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_COOKIE, $cookie);
	$data = curl_exec($ch);
	$error = curl_errno($ch);
	curl_close($ch);
	if($error != 0) return false;
	$filePath = 'http://bbs.nju.edu.cn/file/'.$board.'/';
	preg_match('/(?<=name=)\S+(?=\')/', $data, $array);
	$filePath .= $array[0];
	return $filePath;
}
function getJson($data) {
	preg_match('/\[.*\]/', $data, $array);
	$data = $array[0];
	$data = str_replace("'", '"', $data);
	$data = preg_replace('/((?<=[\{\,])[\w\s]*?(?=:))/', '"$1"', $data);
	return $data;
}
function getPosts($board, $start=null) {
    if($start == null)
        $url = $Config->board_url."?board=".$board;
    else 
        $url = $Config->board_url."?board=".$board."&start=".$start;
	$rawData = $this->query($url);
    //提取出Table中的内容
    $rawData = explode("<table", $rawData);
    $rawData = $rawData[1];
    $rawData = explode("</table>", $rawData);
    $rawData = $rawData[0];
    //end
    $isTitle = false;//判断是否是标题行
    $dataArray = explode("<tr>", $rawData);

    $objData = new stdClass;
    $objData->brd = $board;
    //这里的start是讨论区除置顶贴的第一贴序号,可以向前推算
    $objData->start = null;
    $objData->items = array();
    foreach($dataArray as $item){
        $objItem = new stdClass;
        if($isTitle)
        {
            $itemArray = explode("<td>", $item);

            $match_result = preg_match('/^\d+$/', str_get_html($itemArray[1])->plaintext, $match);
            if($match_result == 1)
                $objItem->num = $match[0];
            if($objData->start == null)
                $objData->start = $match[0];

            //TODO 可能有多种状态，当前的处理可能不妥
            if($itemArray[2] != "")
                $objItem->status = urlencode(str_get_html($itemArray[2])->plaintext);
            else
                $objItem->status = urlencode("普通");
            $objItem->author = urlencode(str_get_html($itemArray[3])->plaintext);
            $objItem->time = urlencode(str_get_html($itemArray[4])->plaintext);
            $html = str_get_html($itemArray[5]);
            $name = $html->find("a");
            $name = $name[0];
            $href = explode("file=", $name->href);
            $objItem->title = urlencode($name->plaintext);
            $objItem->file = urlencode($href[1]);

            $readreply = str_get_html($itemArray[6])->plaintext;
            $readreply = explode('/', $readreply);
            //
            if(count($readreply) > 1)
            {
                $objItem->reply = urldecode($readreply[0]);
                $objItem->read = urldecode($readreply[1]);
            }
            else {
                $objItem->reply = urlencode(0);//置顶的文章回复数置为0
                $objItem->read = urlencode($readreply[0]);
            }
            //
            array_push($objData->items, $objItem);
        } else {
            $isTitle = true;
        }
    }
    return urldecode(json_encode($objData));
}
function getBoards($section) {
    $url = "http://bbs.nju.edu.cn/bbsboa?sec=".$section;
    $rawData = $this->query($url);
    $rawData = explode("<table", $rawData);
    $rawData = $rawData[1];
    $rawData = explode("</table>", $rawData);
    $rawData = $rawData[0];
    
    $dataArray = explode("<tr>", $rawData);
    $isTitle = false;
    $objData = new stdClass;
    $objData->section = $section;
    $objData->items = array();
    foreach($dataArray as $item)
    {
        if($isTitle)
        {
            $objItem = new stdClass;
            $itemArray = explode("<td>", $item);

            $objItem->id = $itemArray[1];
            $objItem->brd = str_get_html($itemArray[3])->plaintext;
            $objItem->uptime = $itemArray[4];
            $objItem->name = urlencode(str_get_html($itemArray[6])->plaintext);
            $objItem->bm = urlencode(str_get_html($itemArray[7])->plaintext);
            preg_match('/\d+/', $itemArray[8], $match);
            $objItem->artNum = $match[0];
            //TODO 用sscanf优化，尽量少用正则表达式和explode函数
            array_push($objData->items, $objItem);
        }
        else
        {
            $isTitle = true;
        }
    }
    return urldecode(json_encode($objData));
}
function getForum() 
{
    //鉴于此处变动较小，故采用直接返回的形式
    $objData = new stdClass;
    $objData->section = urlencode("分类讨论区");
    $objData->items = array(
        array("sec"=>"0", "name"=>urlencode("本站系统")),
        array("sec"=>"1", "name"=>urlencode("南京大学")),
        array("sec"=>"2", "name"=>urlencode("乡情校谊")),
        array("sec"=>"3", "name"=>urlencode("电脑技术")),
        array("sec"=>"4", "name"=>urlencode("学术科学")),
        array("sec"=>"5", "name"=>urlencode("文化艺术")),
        array("sec"=>"6", "name"=>urlencode("体育娱乐")),
        array("sec"=>"7", "name"=>urlencode("感性休闲")),
        array("sec"=>"8", "name"=>urlencode("新闻信息")),
        array("sec"=>"9", "name"=>urlencode("百合广角")),
        array("sec"=>"10", "name"=>urlencode("校务信箱")),
        array("sec"=>"11", "name"=>urlencode("社团群体")),
        array("sec"=>"12", "name"=>urlencode("冷门讨论区"))
    );
    return urldecode(json_encode($objData));
}
function getArticle($board, $file)
{
    $url = "http://bbs.nju.edu.cn/bbstcon?board=".$board."&file=".$file."&start=-1";
    $rawData = $this->query($url);
    $rawData = str_replace("\n", '_newline_', $rawData);//simple_html_dom 的 plaintext 会将换行符过滤掉，这里先占个位
    $html = str_get_html($rawData);
    $textareas = $html->find("textarea");
    $objData = new stdClass;
    $objData->board = $board;//所在版区
    $objData->title = null;//文章标题
    $objData->items = array();
    $count = 0;
    foreach($textareas as $item)
    {
        $item = $item->plaintext;
        $objItem = new stdClass;
        $objItem->count = $count++;
        sscanf($item, "%*[^ ]%[^(](%[^)]%*[^:]:%*[^:]:%[^:]%*[^(](%[^)])%[^\a]", $objItem->author, $objItem->name, $title, $objItem->time, $objItem->text);

        $objItem->author = trim($objItem->author);
        $objItem->text = trim(urlencode($objItem->text));//这里不再进行过滤了，ip地址可以过滤出来
        $objItem->name = urlencode($objItem->name);
        if($objData->title == null)
        {
            $objData->title = urlencode(substr($title, 0, -9));
        }
        array_push($objData->items, $objItem);
    }
    $result = str_replace("_newline_", "\n", urldecode(json_encode($objData)));//还原换行符
    return $result;
}

function post($board, $title, $text, $cookie)
{
	$title = urlencode(mb_convert_encoding($title, "GBK", "UTF-8"));
	$text = urlencode(mb_convert_encoding($text, "GBK", "UTF-8"));
    $url = "http://bbs.nju.edu.cn/bbssnd?board=".$board."&text=".$text."&title=".$title;
    $result = $this->query($url, $cookie);
    if(strpos($result, 'Refresh') > 0) //如果发表成功，服务器会返回一个Refresh命令
        return true;
    return false;
}
}
?>
