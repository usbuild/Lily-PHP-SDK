<?php

//@author Usbuild <njuzhangchao@gmail.com>
require_once("config.php");
require_once("simple_html_dom.php");

class LilyClient {

    /**
     * 递归将给定的对象用url格式处理,注意本函数不应该直接调用,若要处理请使用objectEncode]
     * 
     * @param object $obj 需要递归url格式化的对象,这是一个引用类型
     * @return object 返回处理过的对象
     */
    function encode(&$obj) {
        foreach ($obj as $key => &$value) {
            if (gettype($value) == "array" || gettype($value) == "object") {
                $this->encode($value);
            }
            else
            $value = urlencode($value);
        }
    }
    /**
     *给定一个$obj对象返回该对象的json格式,无需考虑中文问题
     *
     *@param object $obj 需要处理的对象
     *@return string 返回json字符串
     */
    function objectEncode($obj) {
        $this->encode($obj);
        return urldecode(json_encode($obj));
    }
    
    
    /**
    *
    * 对百合不规范的json数据重新格式化,外部不应该直接调用
    * @param string $data
    * @return string
    */
    function getJson($data) {
    preg_match('/\[.*\]/', $data, $array);
    $data = $array[0];
    $data = str_replace("'", '"', $data);
    $data = preg_replace('/((?<=[\{\,])[\w\s]*?(?=:))/', '"$1"', $data);
    return $data;
        }


     /**
      * 将传入的$html中的ANSI颜色代码处理成HTML可读格式
      * 
      * @param string $html 需要对ANSI颜色代码处理的字符串
      * @return string 处理过可以直接在浏览器中运行的字符串
      */
    function ansi_to_html($html) {
        $GLOBALS['style'] = array(
   "30"=>"COLOR:#000000",
   "31"=>"COLOR:#e00000",
   "32"=>"COLOR:#008000",
   "33"=>"COLOR:#808000",
   "34"=>"COLOR:#0000ff",
   "35"=>"COLOR:#d000d0",
   "36"=>"COLOR:#33a0a0",
   "37"=>"COLOR:#000000",

   "40"=>"BACKGROUND-COLOR:#ffffff",
   "41"=>"BACKGROUND-COLOR:#FDE6E6",
   "42"=>"BACKGROUND-COLOR:#E7FAE7",
   "43"=>"BACKGROUND-COLOR:#FAFADC",
   "44"=>"BACKGROUND-COLOR:#E3EEFA",
   "45"=>"BACKGROUND-COLOR:#FAE7FA",
   "46"=>"BACKGROUND-COLOR:#E0F6F6",
   "47"=>"BACKGROUND-COLOR:#ffffff"

        );
        $closeTag = '(.*?)(?=\x1B\[[\d;]*?m)/i';
        $regFilter1 = '/\x1B\[[\d;]*(3\d)[\d;]*(4\d)[\d;]*m'.$closeTag;
        $regFilter2 = '/\x1B\[[\d;]*(4\d)[\d;]*(3\d)[\d;]*m'.$closeTag;
        $regFilter3 = '/\x1B\[[\d;]*(3\d|4\d)[\d;]*m'.$closeTag;
        $regFilter4 = '/\x1B\[[\d;]*(I|u|s|H|m|A|B|C|D)/i';
        
        $html = $html."\x1B[m";
        $patterns = array($regFilter1, $regFilter2, $regFilter3, $regFilter4);
        $html = preg_replace_callback($patterns, create_function('$matches', '
        	$style = $GLOBALS["style"];
            if(count($matches) == 4)
            return "<span style=\"".$style[$matches[1]].";".$matches[2].";\">".$matches[3]."</span>";
            else if(count($matches) == 3)
            return "<span style=\"".$style[$matches[1]].";\">".$matches[2]."</span>";
            else if(count($matches) == 2)
            return "";
            else return false;
        '), $html);
        return $html;
    }

    /**
     * 
     * 将UBB处理成标准HTML
     * @param string $ubb 包含$ubb的字符串
     * @return string 处理过的标准HTML
     */
    function format_ubb($ubb){
        //TODO 
        $pattern = array(
        '/(^|[^\"\'\]])(http|ftp|mms|rstp|news|https)\:\/\/([^\s\033\[\]\"\'\(\)（）。，]+)/i',
        '/\[url\]http\:\/\/(\S+\.)(gif|jpg|png|jpeg|jp)\[\/url\]/i',
        '/\[url\](.+?)\[\/url\]/i',
        '/\[img\](.+?)\[\/img\]/i',
        '/\[flash\](.+?)\[\/flash\]/i',
        '/\[wmv\](.+?\.wmv)\[\/wmv\]/i',
        '/\[wma\](.+?\.(?:wma|mp3))\[\/wma\]/i',
        
        '/\[(c|color)=([#0-9a-zA-Z]{1,10})\](.+?)\[\/\1\]/i',
        '/\[b\](.+?)\[\/b\]/i',
        '/\[brd\](.+?)\[\/brd\]/i',
        '/\[uid\]([0-9a-zA-Z]{2,12})\[\/uid\]/i',
        '/\[blog\]([0-9a-zA-Z]{2,12})\[\/blog\]/i'
        );
        $replacement = array (
        '${1}[url]${2}://${3}[/url]',
        '[img]http://${1}${2}[/img]',
        '<a href="${1}" target=_blank>${1}</a>',
        //'<img src="${1}" alt="" />',//原始版本
        '<img src="img.php?url=${1}" alt="" />',//防盗链版本
        '${1}',
        '${1}',
        '${1}',
        '<span style="color:${2};">${3}</span>',
        '<b>${1}</b>',
        '${1}',
        '${1}',
        '${1}'
        );
        return preg_replace($pattern, $replacement, $ubb);
    }

    /**
     * 
     * 将笑脸符号处理成标准HTML
     * @param string $emo 包含脸符号的字符串
     * @return string 处理过的标准HTML
     */
    function format_emotion($emo) {
        $emo_txt = array(
        "[:T]", "[;P]", "[;-D]", "[:!]", "[:L]", "[:?]", "[:Q]",
				"[:@]", "[:-|]", "[:(]", "[:)]", "[:D]", "[:P]", "[:'(]",
				"[:O]", "[:s]", "[:|]", "[:$]", "[:X]", "[:U]", "[:K]",
				"[:C-]", "[;X]", "[:H]", "[;bye]", "[;cool]", "[:-b]", "[:-8]",
				"[;PT]", "[;-C]", "[:hx]", "[;K]", "[:E]", "[:-(]", "[;hx]",
				"[:B]", "[:-v]", "[;xx]"
        );
        $emo_pics = array(
        19, 20, 21, 26, 27, 32, 18, 11, 10, 15, 14, 13, 12, 9, 0, 2, 3,
        6, 7, 16, 25, 29, 34, 36, 39, 4, 40, 41, 42, 43, 44, 47, 49,
        50, 51, 52, 53, 54
        );
        array_walk($emo_pics, create_function('&$value, $key', '
        $value = "http://bbs.nju.edu.cn/images/face/".$value.".gif";
        '));
        return str_replace($emo_txt,$emo_pics, $emo);
    }
    /**
     * 
     * ansi_to_html, format_ubb和format_emotion的结合体
     * @param string $html 原始输入字符串
     * @return string 输出标准的HTML
     */
    function format_output($html) {
        $html = str_replace(array(
        "\n"
        ), array(
        '<br />'
        ), $html);
        return trim($this->format_ubb($this->ansi_to_html($this->format_emotion($html))));
    }
    
    /**
     * 
     * 将特定格式的字符串格式化成标准时间格式
     * @param string $date 特定格式的字符串
     * @return string 格式化过的时间字符串
     */
    function format_date($date) {
        try{
            $date = date_create_from_format("D M j H:i:s Y", trim($date));
            return date_format($date, "Y-m-d H:i:s");
        } catch(Exception $e) {
            return $date;
        }
    }
/**
 * 
 * 移除所有的ANSI颜色代码
 * @param string $html
 * @return string 返回不含ANSI颜色的字符串
 */
    function removeColors($html) {
        return preg_replace('/\x1B\[(\d\d||\d\d?;\d\d?|\d|\d;\d\d?;\d\d?)m/i', '', $html);
    }

    /**
     * 
     * 获得指定用户名的cookie字符串,若输入错误则返回false.
     * 注意:由于小百合有登录次数限制,请不要执行此函数过多次数,应将得到的cookie保存起来.
     * 等到cookie失效后再调用此函数
     * @param string $username
     * @param string $password
     * @return boolean|string
     */

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
            return false;
        }
        return $cookie;
    }

    /**
     * 
     * 简单包装curl函数,用于获取网页内容
     * @param string $url 所要获取页面的url
     * @param string $cookie (可选) 用户的cookie字符串
     * @param array $fields (可选) 使用post方式提交的内容
     * @return string 所获取页面的内容
     */
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

    /**
     * 
     * 上传文件函数,以上传照片为主,文件大小不宜超过1M
     * @param string $filename 文件路径
     * @param string $exp 文件描述
     * @param string $board 文件所要上传的版面
     * @param string $cookie 用户的cookie字符串
     * @return boolean|string 成功则返回文件的服务器路径,失败返回false
     */
    function uploadFile($filename, $exp, $board, $cookie) {
        if(!file_exists($filename)) return false;
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

    /**
     * 
     * 获取今日十大
     * @return string 返回今日十大内容的json格式字符串
     */
    function getTop10() {
        $data = $this->query("http://bbs.nju.edu.cn/cache/t_top10.js");
        return '{"top10":' . $this->getJson($data) . '}';
    }

    /**
     * 
     * 获取热门板块
     * @return string 返回热门的json格式字符串
     */
    function getHotBoard() {
        $data = $this->query("http://bbs.nju.edu.cn/cache/t_hotbrd.js");
        return '{"hotBoard":' . $this->getJson($data) . '}';
    }
/**
 * 
 * 获取某一板块的帖子
 * @param string $board 板块标识,注意是英文, 下同
 * @param int $start (可选) 帖子的起始位置
 * @return string 该板块帖子的json格式字符串 
 */

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
                $objItem->time = $this->format_date(str_get_html($itemArray[4])->plaintext);
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
/**
 * 
 * 获取首页分类区的板块
 * @param int $section 版区标识,目前为0~12
 * @return string 该分类区板块的json格式字符串
 */
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
                $objItem->uptime = $this->format_date($itemArray[4]);
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

    /**
     * 
     * 获取首页分类区名称
     * @return string 所有分区标识和名称的json格式字符串
     */
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

    /**
     * 
     * 获取某一帖子的内容及回复,在回复数较多的情况下可能会引起crash
     * @param string $board 板块标识
     * @param string $file 文章标识
     * @param string $start (可选) 回帖起始位置
     * @return string 帖子内容的json格式字符串
     */
    function getArticle($board, $file, $start = -1) {
        $url = "http://bbs.nju.edu.cn/bbstcon?board=" . $board . "&file=" . $file . "&start=".$start;
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
            sscanf($item, "%*[^ ]%[^(](%[^)]%*[^:]:%*[^:]:%[^:]%*[^(](%[^)])%[^\xFF]", $objItem->author, $objItem->name, $title, $objItem->time, $objItem->text);

            if($title == "" || $objItem->author == "" || $objItem->time == "")
            {//防止不规范的内容出现
                $objItem->author = null;
                $objItem->name = null;
                $objItem->time = null;
                $objItem->text = $this->format_output(str_replace("_newline_", "\n", $item));
                array_push($objData->items, $objItem);
                continue;
            }
            
            $objItem->time = $this->format_date($objItem->time);
            $objItem->author = trim($objItem->author);
            $objItem->text = str_replace("_newline_", "\n", $objItem->text);//还原换行符
            $objItem->text = $this->format_output($objItem->text); //这里不再进行过滤了，ip地址可以过滤出来
            $objItem->name = $objItem->name;
            if ($objData->title == null) {
                $objData->title = substr($title, 0, -18);//Magic Number 
            }
            array_push($objData->items, $objItem);
        }
        return $this->objectEncode($objData);
    }

    /**
     * 
     * 发表帖子
     * @param string $board 版区标识
     * @param string $title 帖子标题
     * @param string $text 帖子正文
     * @param string $cookie 用户cookie字符串
     * @return boolean|string 成功返回true失败返回错误信息
     */
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
        return str_get_html($result)->plaintext;
    }

    /**
     * 
     * 获取个人信息
     * @param string $name 个人用户标识
     * @return string 个人信息json格式字符串
     */
    
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
        $gender = explode("上次在 [\x1B[32m", $rawData);
        $gender = substr($gender[0], -$offset, 12);
        if (strpos($gender, "座")) {
            $objData->constellation = substr($gender, 2);
            if (strpos($gender, "6m") > -1)
            $objData->gender = "male";
            else if (strpos($gender, "5m") > -1)
            $objData->gender = "female";
        }
        //$rawData = $this->removeColors($rawData);
        $info = explode($spliter, $rawData);
        $objData->sig = null; //签名
        if (count($info) > 1) {
            $objData->sig = $this->format_output(substr($rawData, strlen($info[0] . $spliter) + 5));
        }
        $info = $this->removeColors($info[0]);

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

    /**
     * 
     * 跟帖
     * @param string $board 版区标识
     * @param string $file 主贴标识
     * @param string $cookie 个人cookie字符串
     * @param string $text 回帖内容
     * @return boolean|string 成功返回true失败返回错误信息
     */
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
        return str_get_html($re);
    }

}

?>
