<?php

//author Usbuild
require_once("config.php");
require_once("simple_html_dom.php");

class LilyClient {

    //-------Utils
    function encode(&$obj) {
        foreach ($obj as $key => &$value) {
            if (gettype($value) == "array" || gettype($value) == "object") {
                $this->encode($value);
            }
            else
                $value = urlencode($value);
        }
    }
     function objectEncode($obj) {
        $this->encode($obj);
        return urldecode(json_encode($obj));
    }


    function removeColors($var) {
        return preg_replace('/\[(\d\d||\d\d?;\d\d?|\d|\d;\d\d?;\d\d?)m/', '', $var);
    }

    //-------



    function getCookie($username, $password) {
        global $Config;
        $cookie_array = array();
        $login_url = $Config->login_url;
        $param = array(
            'type' => '2',
            'id' => $username,
            'pw' => $password,
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
        try {
            $tmpArray = explode("setCookie('", $data);
            $cookie_string = $tmpArray[1];
            $tmpArray = explode("')", $cookie_string);
            $cookie_string = $tmpArray[0];
            $temp_array = preg_split("/\d+/", $cookie_string);
            $cookie_array['_U_UID'] = substr($temp_array[1], 1, strlen($temp_array[1]) - 2);
            $temp_array = preg_split("/\D+/", $cookie_string);
            $cookie_array['_U_NUM'] = 2 + $temp_array[0];
            $cookie_array['_U_KEY'] = $temp_array[1] - 2;
            $cookie = '_U_NUM=' . $cookie_array['_U_NUM'] . '; _U_UID=' . $cookie_array['_U_UID'] . '; _U_KEY=' . $cookie_array['_U_KEY'];
        } catch (Exception $e) {
            
        }
        return $cookie;
    }

    function query($url, $cookie = null, $fields = null) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($fields != null) {
            curl_setopt($ch, CURLOPT_POST, count($fields));
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        }
        $data = curl_exec($ch);
        curl_close($ch);
        $data = mb_convert_encoding($data, "UTF-8", "GBK");
        return $data;
    }

    function uploadFile($filename, $exp, $board, $cookie) {
        $post_data = array(//此处BBS进行了混淆，以下能够正常工作
            "up" => '@' . realpath($filename),
            "board" => $exp,
            "exp" => "",
            "ptext" => $board,
        );
        $curl = curl_init();
        $post_url = "http://bbs.nju.edu.cn/bbsdoupload";
        curl_setopt($curl, CURLOPT_URL, $post_url);
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $data = curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);
        if ($error != 0)
            return false;
        $start = strpos($data, "url=") + 4;
        $end = strrpos($data, "'");
        $length = $end - $start;
        $data = substr($data, $start, $length);
        $data = str_replace("\r\n", '', $data); //换行符替换掉
        $ch = curl_init("http://bbs.nju.edu.cn/" . $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        $data = curl_exec($ch);
        $error = curl_errno($ch);
        curl_close($ch);
        if ($error != 0)
            return false;
        $filePath = 'http://bbs.nju.edu.cn/file/' . $board . '/';
        preg_match('/(?<=name=)\S+(?=\')/', $data, $array);
        $filePath .= $array[0];
        return $filePath;
    }
    
    function getTop10() {
        $data = $this->query("http://bbs.nju.edu.cn/cache/t_top10.js");
        return '{"top10":' . $this->getJson($data) . '}';
    }

    function getHotBoard() {
        $data = $this->query("http://bbs.nju.edu.cn/cache/t_hotbrd.js");
        return '{"hotBoard":' . $this->getJson($data) . '}';
    }

    function getJson($data) {
        preg_match('/\[.*\]/', $data, $array);
        $data = $array[0];
        $data = str_replace("'", '"', $data);
        $data = preg_replace('/((?<=[\{\,])[\w\s]*?(?=:))/', '"$1"', $data);
        return $data;
    }

    function getPosts($board, $start = null) {
        global $Config;
        if ($start == null)
            $url = $Config->board_url . "?board=" . $board;
        else
            $url = $Config->board_url . "?board=" . $board . "&start=" . $start;
        $rawData = $this->query($url);
        //提取出Table中的内容
        $rawData = explode("<table", $rawData);
        $rawData = $rawData[1];
        $rawData = explode("</table>", $rawData);
        $rawData = $rawData[0];
        //end
        $isTitle = false; //判断是否是标题行
        $dataArray = explode("<tr>", $rawData);

        $objData = new stdClass;
        $objData->brd = $board;
        //这里的start是讨论区除置顶贴的第一贴序号,可以向前推算
        $objData->start = null;
        $objData->items = array();
        foreach ($dataArray as $item) {
            $objItem = new stdClass;
            if ($isTitle) {
                $itemArray = explode("<td>", $item);

                $match_result = preg_match('/^\d+$/', str_get_html($itemArray[1])->plaintext, $match);
                if ($match_result == 1) {
                    $objItem->num = $match[0];
                if ($objData->start == null)
                    $objData->start = $match[0];
                }

                //TODO 可能有多种状态，当前的处理可能不妥
                if ($itemArray[2] != "")
                    $objItem->status = str_get_html($itemArray[2])->plaintext;
                else
                    $objItem->status = "普通";
                $objItem->author = str_get_html($itemArray[3])->plaintext;
                $objItem->time = str_get_html($itemArray[4])->plaintext;
                $html = str_get_html($itemArray[5]);
                $name = $html->find("a");
                $name = $name[0];
                $href = explode("file=", $name->href);
                $objItem->title = $name->plaintext;
                $objItem->file = $href[1];

                $readreply = str_get_html($itemArray[6])->plaintext;
                $readreply = explode('/', $readreply);
                //
                if (count($readreply) > 1) {
                    $objItem->reply = urldecode($readreply[0]);
                    $objItem->read = urldecode($readreply[1]);
                } else {
                    $objItem->reply = "-1"; //置顶的文章回复数置为0
                    $objItem->read = $readreply[0];
                }
                //
                array_push($objData->items, $objItem);
            } else {
                $isTitle = true;
            }
        }
        return $this->objectEncode($objData);
    }

    function getBoards($section) {
        $url = "http://bbs.nju.edu.cn/bbsboa?sec=" . $section;
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
        foreach ($dataArray as $item) {
            if ($isTitle) {
                $objItem = new stdClass;
                $itemArray = explode("<td>", $item);

                $objItem->id = $itemArray[1];
                $objItem->brd = str_get_html($itemArray[3])->plaintext;
                $objItem->uptime = $itemArray[4];
                $objItem->name = str_get_html($itemArray[6])->plaintext;
                $objItem->bm = str_get_html($itemArray[7])->plaintext;
                preg_match('/\d+/', $itemArray[8], $match);
                $objItem->artNum = $match[0];
                //TODO 用sscanf优化，尽量少用正则表达式和explode函数
                array_push($objData->items, $objItem);
            } else {
                $isTitle = true;
            }
        }
        return $this->objectEncode($objData);
    }

    function getForums() {
        //鉴于此处变动较小，故采用直接返回的形式
        $objData = new stdClass;
        $objData->section = "分类讨论区";
        $objData->items = array(
            array("sec" => "0", "name" => "本站系统"),
            array("sec" => "1", "name" => "南京大学"),
            array("sec" => "2", "name" => "乡情校谊"),
            array("sec" => "3", "name" => "电脑技术"),
            array("sec" => "4", "name" => "学术科学"),
            array("sec" => "5", "name" => "文化艺术"),
            array("sec" => "6", "name" => "体育娱乐"),
            array("sec" => "7", "name" => "感性休闲"),
            array("sec" => "8", "name" => "新闻信息"),
            array("sec" => "9", "name" => "百合广角"),
            array("sec" => "10", "name" => "校务信箱"),
            array("sec" => "11", "name" => "社团群体"),
            array("sec" => "12", "name" => "冷门讨论区")
        );
        return $this->objectEncode($objData);
    }

    function getArticle($board, $file) {
        $url = "http://bbs.nju.edu.cn/bbstcon?board=" . $board . "&file=" . $file . "&start=-1";
        $rawData = $this->query($url);
        $rawData = str_replace("\n", '_newline_', $rawData); //simple_html_dom 的 plaintext 会将换行符过滤掉，这里先占个位
        $html = str_get_html($rawData);
        $textareas = $html->find("textarea");
        $objData = new stdClass;
        $objData->board = $board; //所在版区
        $objData->title = null; //文章标题
        $objData->items = array();
        $count = 0;
        foreach ($textareas as $item) {
            $item = $item->plaintext;
            $objItem = new stdClass;
            $objItem->count = $count++;
            sscanf($item, "%*[^ ]%[^(](%[^)]%*[^:]:%*[^:]:%[^:]%*[^(](%[^)])%[^\a]", $objItem->author, $objItem->name, $title, $objItem->time, $objItem->text);

            $objItem->author = trim($objItem->author);
            $objItem->text = trim($objItem->text); //这里不再进行过滤了，ip地址可以过滤出来
            $objItem->name = $objItem->name;
            if ($objData->title == null) {
                $objData->title = substr($title, 0, -9);
            }
            array_push($objData->items, $objItem);
        }
        $result = str_replace("_newline_", "\n", $this->objectEncode($objData)); //还原换行符
        return $result;
    }

    function post($board, $title, $text, $cookie) {
        $title = mb_convert_encoding($title, "GBK", "UTF-8");
        $text = mb_convert_encoding($text, "GBK", "UTF-8");
        $fields = array(
            "board"=>$board,
            "text"=>$text,
            "title"=>$title
        );
        $url = "http://bbs.nju.edu.cn/bbssnd";
        $result = $this->query($url, $cookie, $fields);
        if (strpos($result, 'Refresh') > 0) //如果发表成功，服务器会返回一个Refresh命令
            return true;
        return false;
    }

    function getPersonInfo($name) {
        $url = "http://bbs.nju.edu.cn/bbsqry?userid=" . $name;
        $spliter = "个人说明档如下";
        $offset = 15;
        $objData = new stdClass;
        $html = $this->query($url);

        $objData->id = null;
        $objData->name = null;
        $objData->gender = null;
        $objData->constellation = null; //星座
        $objData->upCounts = null; //上站次数
        $objData->articles = null;
        $objData->exp = null;
        $objData->appearance = null;
        $objData->life = null;
        $objData->lastDate = null;
        $objData->lastIp = null;
        $objData->manager = null;
        $objData->status = null;
        $objData->action = null;
        $objData->sig = null;

        $nextline = "_nextline_";
        $html = str_replace("\n", $nextline, $html);
        $rawData = str_get_html($html)->find("textarea");
        if (count($rawData) == 0)
            return json_encode(new stdClass);
        $rawData = $rawData[0]->plaintext;
        $rawData = str_replace($nextline, "\n", $rawData);
        $gender = explode("上次在 [[32m", $rawData);
        $gender = substr($gender[0], -$offset, 12);
        if (strpos($gender, "座")) {
            $objData->constellation = substr($gender, 2);
            if (strpos($gender, "6m") > -1)
                $objData->gender = "male";
            else if (strpos($gender, "5m") > -1)
                $objData->gender = "female";
        }
        $rawData = $this->removeColors($rawData);
        $info = explode($spliter, $rawData);
        $objData->sig = null; //签名
        if (count($info) > 1) {
            $objData->sig = trim(substr($rawData, strlen($info[0] . $spliter) + 2));
        }
        $info = $info[0];

        $tempArray = explode("共上站", $info);
        $nameid = trim($tempArray[0]);
        sscanf($nameid, "%[^(]", $objData->id);
        $objData->id = trim($objData->id);
        if (preg_match('/(?<=\().*()(?=\))/', $nameid, $match) > 0)
            $objData->name = $match[0];

        $info = substr($info, strpos($info, "共上站"));
        preg_match_all('/\d+/', $info, $match);
        $objData->upCounts = $match[0][0];
        $objData->articles = $match[0][1];

        $info = str_replace("不告诉你", '[未知](未知)', $info);
        preg_match_all('/\[[^\[]+?\]\(.+?\)/', $info, $match); //取得经验值
        $objData->exp = $match[0][0];
        $objData->appearance = $match[0][1];

        $tempArray = explode("生命力：", $info);
        preg_match('/\[.+?\]/', $tempArray[1], $match);
        $objData->life = $match[0];

        preg_match_all('/(?<=\[brd\])\w+(?=\[\/brd\])/', $info, $match); //获取版主
        if (count($match[0]) > 0) {
            $objData->manager = $match[0];
        }


        preg_match_all('/\[.*?\]/', $info, $match);
        if ($objData->constellation == null)
            $offset = -1;
        else
            $offset = 0;
        $objData->lastDate = substr($match[0][1 + $offset], 1, -1);
        $objData->lastIp = substr($match[0][2 + $offset], 1, -1);

        $spliter = "目前在站上, 状态如下:";
        $tempArray = explode($spliter, $info);
        if (count($tempArray) > 1) {
            $objData->status = "online";
            $objData->action = str_replace("没有个人说明档", "", $tempArray[1]);
        } else {
            $objData->status = "offline";
        }

        $this->encode($objData);
        echo urldecode(json_encode($objData));
        return null;
    }

    function postAfter($board, $file, $cookie, $text) {
        $text = mb_convert_encoding($text, "GBK", "UTF-8");
        $url1 = "http://bbs.nju.edu.cn/bbspst?board=" . $board . "&file=" . $file;
        $html = str_get_html($this->query($url1, $cookie));
        $html = $html->find("input");
        $pid = 0;
        $title = "";
        foreach ($html as $item) {
            if ($item->name == "pid")
                $pid = $item->value;
            if ($item->name == "title")
                $title = mb_convert_encoding($item->value, "GBK", "UTF-8");;
        }
        $fields = array(
            "title"=>$title,
            "text"=>$text,
            "pid"=>$pid,
            "board"=>$board
        );
        $url2 = "http://bbs.nju.edu.cn/bbssnd";
        $re = $this->query($url2, $cookie, $fields);
        if (strpos($re, 'Refresh') > -1) {
            return true;
        } else
            return false;
    }

}

?>
