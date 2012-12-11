<?php

/**
 * @author Usbuild <njuzhangchao@gmail.com>
 * @link https://github.com/usbuild/Lily-PHP-SDK
 */
require_once("config.php");
require_once("simple_html_dom.php");

class LilyClient {

    /**
     * 递归将给定的对象用url格式处理,注意本函数不应该直接调用,若要处理请使用objectEncode
     *
     * @param object $obj 需要递归url格式化的对象,这是一个引用类型
     * @return object 返回处理过的对象
     */
    function encode(&$obj) {
        foreach ($obj as $key => &$value) {
            if (gettype($value) == "array" || gettype($value) == "object") 	
                $this->encode($value);
          else {
          $value = str_replace(array("\\", "\""), array("\\\\", "\\\""), $value);
            $value = urlencode($value);
          }
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
        $data = preg_replace(array('/((?<=[\{\,])[\w\s]*?(?=:))/', '/(?<=:)(\d+)/'), '"$1"', $data);
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
   "30"=>"#000000",
   "31"=>"#e00000",
   "32"=>"#008000",
   "33"=>"#808000",
   "34"=>"#0000ff",
   "35"=>"#d000d0",
   "36"=>"#33a0a0",
   "37"=>"#000000",

   "40"=>"#ffffff",
   "41"=>"#FDE6E6",
   "42"=>"#E7FAE7",
   "43"=>"#FAFADC",
   "44"=>"#E3EEFA",
   "45"=>"#FAE7FA",
   "46"=>"#E0F6F6",
   "47"=>"#ffffff"

        );
        $closeTag = '(.*?)(?=\x1B\[[\d;]*?m)/i';
        $regFilter1 = '/\x1B\[[\d;]*(3\d)[\d;]*(4\d)[\d;]*m'.$closeTag;
        $regFilter2 = '/\x1B\[[\d;]*(4\d)[\d;]*(3\d)[\d;]*m'.$closeTag;
        $regFilter3 = '/\x1B\[[\d;]*(3\d)[\d;]*m'.$closeTag;
        $regFilter4 = '/\x1B\[[\d;]*(4\d)[\d;]*m'.$closeTag;
        $regFilter5 = '/\x1B\[[\d;]*(I|u|s|H|m|A|B|C|D)/i';

        $html = $html."\x1B[m";
        $patterns = array($regFilter1, $regFilter2, $regFilter3, $regFilter4, $regFilter5);
        $html = preg_replace_callback($patterns, create_function('$matches', '
        $style = $GLOBALS["style"];
        if(count($matches) == 4) {
            if(substr($matches[1], 0, 1) == "3")
            return "<span style=\"background-color:".$style[$matches[2]].";\"><font color=\"".$style[$matches[1]]."\">".$matches[3]."</font></span>";
            else
            return "<span style=\"background-color:".$style[$matches[1]].";\"><font color=\"".$style[$matches[2]]."\">".$matches[3]."</font></span>";
        }
        else if(count($matches) == 3) {
            if(substr($matches[1], 0, 1) == 3)
            return "<font color=\"".$style[$matches[1]]."\">".$matches[2]."</font>";
            else
            return "<span style=\"background-color:".$style[$matches[1]].";\">".$matches[2]."</span>";
        }
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
        global $Config;
        $pattern = array(
        '/(^|[^\"\'\]])(http|ftp|mms|rstp|news|https)\:\/\/([^<\n\s\033\[\]\"\'\(\)（）。，]+)/i',
          '/\[url\]http\:\/\/(\S+\.)(gif|jpg|png|jpeg|jp)\[\/url\]/i',
          '/\[url\](.+?)\[\/url\]/i',

        '/\[img\](.+?)\[\/img\]/i',
        '/http\:\/\/(bbs.nju.edu\S+\.|www.lilybbs\S+\.)(gif|jpg|png|jpeg|jp)/i',
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
        '<img src="${1}" alt="" />',//原始版本
          $Config->install_dir.'/img.php?url=http://${1}${2}',//防盗链版本
        '${1}',
        '${1}',
        '${1}',
        '<font color="${2}">${3}</font>',
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
        $value = " http://bbs.nju.edu.cn/images/face/".$value.".gif ";
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
      return date("m-d H:i", strtotime($date));
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
        $cookie_array = array();
        $login_url = "http://bbs.nju.edu.cn/bbslogin";
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
            if(count($tmpArray) < 2) return false;
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
     * 登出
     * @param string 用户cookie
     */
    function logout($cookie) {
        $url = "http://bbs.nju.edu.cn/bbslogout";
        $this->query($url, $cookie);
    }

    //以下三个函数是为了保证传输的安全性,目前还没有实现
    /**
    *
    * 根据用户名密码获得唯一$token
    * @param string $username 用户标识
    * @param string $password 密码
    * @return string 唯一标识token
    */
    function getToken($username, $password) {
        $cookie = getCookie($username, $password);
        //请保存上面的$cookie字符串,实现可根据情况.返回唯一对应字符串
    }
    /**
     *
     * 根据唯一token获得cookie
     * @param string $token 标识
     * @param string $cookie字符串
     */
    function getCookieString($token) {
        //根据上面的唯一字符串($token)获得$cookie字符串
    }
    /**
     *
     * 使cookie无效化
     * @param string $token 标识
     */
    function invalideCookie($token) {

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
        curl_setopt($curl, CURLOPT_REFERER, "http://bbs.nju.edu.cn");
        curl_setopt($curl, CURLOPT_COOKIE, $cookie);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
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
      $data = preg_replace('/&name=.*?([\w\.-]+?)&/', '&name=${1}&', $data);//去掉冗余文件名
      
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
        if(count($array) == 0) return false;
        $filePath .= $array[0];
        return $filePath;

    }

    /**
     *
     * 获取今日十大
     * @return string 返回今日十大内容的json格式字符串
     */
    function getTop10() {
        $url = "http://bbs.nju.edu.cn/bbstop10";
        $rawData = $this->query($url);
        preg_match_all('/(?<=bbstcon\?board=)(\w+)&file=([\w\.]+)">(.+?)\n.+?userid=(\w+).+?>(\d+)/', $rawData, $matches);
        $objData = new stdClass;
        $objData->title = "今日十大";
        $objData->items = array();
        for($i = 0; $i < count($matches[0]); $i++) {
            $objItem = new stdClass;
            $objItem->title = $matches[3][$i];
            $objItem->board = $matches[1][$i];
            $objItem->file = $matches[2][$i];
            $objItem->author = $matches[4][$i];
            $objItem->reply = $matches[5][$i];
            array_push($objData->items, $objItem);
        }
        return $this->objectEncode($objData);
    }

    /**
     *
     * 获取热门板块
     * @return string 返回热门的json格式字符串
     */
    function getHotBoard() {
        $data = $this->query("http://bbs.nju.edu.cn/cache/t_hotbrd.js");
        $data = '{"title":"热门板块", "hotBoard":' . $this->getJson($data) . '}';
        $data = str_replace(array('"bm"', '"n"', '"brd"'), array('"manager"', '"name"', '"board"'), $data);
        echo $data;
    }


    /**
     *
     * 获取热门板块
     * @return string 返回热门的json格式字符串
     */
    function getRecBoard() {
        $data = $this->query("http://bbs.nju.edu.cn/cache/t_recbrd.js");
        $data = '{"title":"推荐版块", "recBoard":' . $this->getJson($data) . '}';
        $data = str_replace(array('"bm"', '"brd"'), array('"manager"', '"board"'), $data);
        echo $data;
    }

    /**
     *
     * 获取某一板块的帖子
     * @param string $board 板块标识,注意是英文, 下同
     * @param int $start (可选) 帖子的起始位置
     * @return string 该板块帖子的json格式字符串
     */

    function getPosts($board, $start = null) {
        $url = "http://bbs.nju.edu.cn/bbstdoc";
        if ($start == null)
        $url = $url . "?board=" . $board;
        else
        $url = $url . "?board=" . $board . "&start=" . $start;
        $rawData = $this->query($url);
        
      
        
        
        //提取出Table中的内容
        $rawData = explode("<table", $rawData);
        if(count($rawData) < 2) return "{}";
        $rawData = $rawData[1];
        $rawData = explode("</table>", $rawData);
        
      	$footer = $rawData[1];
      
        $rawData = $rawData[0];
        //end
        $isTitle = false; //判断是否是标题行
        $dataArray = explode("<tr>", $rawData);

        $objData = new stdClass;
        $objData->brd = $board;
        //这里的start是讨论区除置顶贴的第一贴序号,可以向前推算
        $objData->start = null;
        
        $prev = preg_match("/(?<=start\=)\d+(?=>上一页)/", $footer, $matches);//提取上一页        
        if($prev > 0)
        	$objData->prev = $matches[0];
        else 
        	$objData->prev = -1;
        $next = preg_match("/(?<=start\=)\d+(?=>下一页)/", $footer, $matches);//提取下一页
        if( $next > 0 )
        	$objData->next = $matches[0];
        else
        	$objData->next = -1;
        
        
        
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
                $objItem->title = substr($name->plaintext, 4);
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
        $objData->name = $this->getForumBySec($section);
        $objData->items = array();
        foreach ($dataArray as $item) {
            if ($isTitle) {
                $objItem = new stdClass;
                $itemArray = explode("<td>", $item);

                $objItem->id = $itemArray[1];
                $objItem->brd = str_get_html($itemArray[3])->plaintext;
                $objItem->uptime = $this->format_date($itemArray[4]);
                $objItem->name = substr(str_get_html($itemArray[6])->plaintext, 5);
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

        $objData->items = array();
        for($i = 0; $i < 13; $i++ ) {
            array_push($objData->items, array("sec"=> $i, "name"=>$this->getForumBySec($i)));
        }
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
        $rawData = str_replace("\n", "_newline_", $rawData); //simple_html_dom 的 plaintext 会将换行符过滤掉，这里先占个位
        $html = str_get_html($rawData);
      
        $textareas = $html->find("textarea");
      
      
        $objData = new stdClass;
        $objData->board = $board; //所在版区
        $objData->title = null; //文章标题
        $objData->prev = -1;
        $objData->next = -1;
        $count = preg_match('/&start=(\d+)>本主题上30篇/', $rawData, $matches);
        if($count > 0) {
            $objData->prev = $matches[1];
        }
        $count = preg_match('/&start=(\d+)>本主题下30篇/', $rawData, $matches);
        if($count > 0) {
            $objData->next = $matches[1];
        }




        $objData->items = array();
        $count = $start  < 0 ?0 : $start;
        if(count($textareas) == 0) return "{}";
      
        foreach ($textareas as $item) {
            $item = $item->plaintext;
          
            $item = str_replace("_newline_", "\n", $item);

            $objItem = new stdClass;
            $objItem->count = $count++;
          
            sscanf($item, "%*[^:]:%[^(](%[^)?]%*[^\n]\n%[^\n]\n%*[^(](%[^)]%*[^\n]\n%[^\xFF]", $objItem->author, $objItem->name, $title, $objItem->time, $objItem->text);

          
            if($title == "" || $objItem->author == "" || $objItem->time == "")
            {
                //防止不规范的内容出现
                $objItem->author = null;
                $objItem->name = null;
                $objItem->time = null;
                //$objItem->text = $this->format_output($item);
                $objItem->text = $this->format_output(substr($item, 0, strrpos($item, "--")));
                array_push($objData->items, $objItem);
                continue;
            }

            $objItem->time = $this->format_date($objItem->time);
          
            $objItem->author = trim($objItem->author);
          
            $objItem->text = $this->format_output(substr($objItem->text, 0, strrpos($objItem->text, "--"))); //这里不再进行过滤了，ip地址可以过滤出来
            $objItem->name = $objItem->name;
            if ($objData->title == null) {
                $objData->title = substr($title, 10);//Magic Number
            }
          
            array_push($objData->items, $objItem);
        }
        return $this->objectEncode($objData);
    }

  //send SomeBody an @
  function at_somebody($uid, $cookie, $board, $title) {
    	$msg = "我在 ".$board." 区的《".$title." 》中@了您";
		$msg = urlencode(mb_convert_encoding($msg, "GBK", "UTF-8"));
		$url = "bbs.nju.edu.cn/bbssendmsg?msg=".$msg."&destid=".$uid;
		$this->query($url, $cookie);
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
		//提交文本前段处理
		//处理提交文本中的@
		$pattern = '/(?<=@)(\w+)/';
		preg_match_all($pattern, $text, $matches);
		$uids = array_unique($matches[1]);

		$text = preg_replace($pattern, '[uid]$1[/uid]', $text);
		//等到后来才正式处理@信息
		//得到@中的uid列表
		//前段处理结束
	
        $title = mb_convert_encoding($title, "GBK", "UTF-8");
        $text = mb_convert_encoding($text, "GBK", "UTF-8");
        $fields = array(
            "board"=>$board,
            "text"=>$text,
            "title"=>$title
        );
        $url = "http://bbs.nju.edu.cn/bbssnd";
        $result = $this->query($url, $cookie, $fields);
        if (strpos($result, 'Refresh') > 0) {//如果发表成功，服务器会返回一个Refresh命令
			//正式处理@信息
			foreach($uids as $uid)
				$this->at_somebody($uid, $cookie, $board, $title);
			return true;
		}
        return false;
    }

    /**
     *
     * 获取个人信息
     * @param string $name 个人用户标识
     * @return string 个人信息json格式字符串
     */

    function getPersonInfo($name) {/*{{{*/
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
    
        $offset = -1;//防止没有星座信息
        if (strpos($gender, "座")) {
            $objData->constellation = substr($gender, 2, -1);
            if (strpos($gender, "6m") > -1)
            $objData->gender = "male";
            else if (strpos($gender, "5m") > -1)
            $objData->gender = "female";
            $offset = 0;
        } else if(strpos($gender, "不"))//貌似有不详的信息
        {
            $offset = 0;
        }
        else {}
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
        /*
        if ($objData->constellation == null)
            $offset = -1;
        else
            $offset = 0;
         */
        $objData->lastDate = $this->format_date(substr($match[0][1 + $offset], 1, -1));
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
        echo str_replace("\n", '', urldecode(json_encode($objData)));
        return null;
    }/*}}}*/

    /**
     *
     * 跟帖
     * @param string $board 版区标识
     * @param string $file 主贴标识
     * @param string $cookie 个人cookie字符串
     * @param string $text 回帖内容
     * @return boolean|string 成功返回true失败返回错误信息
     */
    function postAfter($board, $file, $cookie, $text) {/*{{{*/


		$pattern = '/(?<=@)(\w+)/';
		preg_match_all($pattern, $text, $matches);
		$uids = array_unique($matches[1]);
		$text = preg_replace($pattern, '[uid]$1[/uid]', $text);


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
			foreach($uids as $uid)
				$this->at_somebody($uid, $cookie, $board, $title);
            return true;
        } else
        return false;
    }/*}}}*/

    /**
     *
     * 根据sec获得
     */
    function  getForumBySec($sec) {

        $forumList = array("本站系统", "南京大学", "乡情校谊", "电脑技术", "学术科学", "文化艺术", "体育娱乐", "感性休闲", "新闻信息", "百合广角", "校务信箱", "社团群体", "冷门讨论区");
        return $forumList[$sec];
    }
    /**
     *
     * 获取今日各区热门话题
     * @return string 包含各区热门帖子的json字符串
     */
    function getHotArticles() {/*{{{*/
        $url = "http://bbs.nju.edu.cn/bbstopall";
        $rawData = $this->query($url);
        $spliter = "<tr><td colspan=2>";
        $rawArray = explode($spliter, $rawData);

        $objData = new stdClass();
        $objData->title = "今日各区热门话题";
        $objData->items = array();
        $count = 0;
        array_shift($rawArray);
        foreach ($rawArray as $item) {
            preg_match_all('/(?<=file\=)(.*?)">(.+?)\n.{10,}?>(\w+)?</', $item, $matches);
            $objItem = new stdClass;
            $objItem->sec = $count;
            $objItem->name = $this->getForumBySec($count++);
            $objItem->items = array();
            for($i = 0; $i < count($matches[1]); $i++) {
                $objArt = new stdClass;
                $objArt->title = $matches[2][$i];
                $objArt->board = $matches[3][$i];
                $objArt->file = $matches[1][$i];
                array_push($objItem->items, $objArt);

            }
            array_push($objData->items, $objItem);
        }
        return $this->objectEncode($objData);
    }/*}}}*/
	/**
     * search
	*/
	function format_search($search_field) {/*{{{*/
		$url = "http://bbs.nju.edu.cn/bbsfind?&flag=1&";
		foreach($search_field as &$item) {
			$item = mb_convert_encoding($item, "GBK", "UTF-8");
		}
		$url = $url.http_build_query($search_field);
		$rawData = $this->query($url);
		
		$html = str_get_html($rawData);
		$tables = $html->find("table");
		foreach($tables as $item) {
			$plaintext = trim($item->plaintext);
			if($plaintext != "") echo $plaintext."<hr />";
		}
	}/*}}}*/
}