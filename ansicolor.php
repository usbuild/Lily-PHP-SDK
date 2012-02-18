<?php
function ansi_to_html($html) {
$style = array( 
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
function callback($matches)
{
    global $style;
    if(count($matches) == 4)
        return "<span style=\"".$style[$matches[1]].";".$matches[2].";\">".$matches[3]."</span>";
    else if(count($matches) == 3)
        return "<span style=\"".$style[$matches[1]].";\">".$matches[2]."</span>";
    else if(count($matches) == 2)
        return "";
    else return false;
}
$patterns = array($regFilter1, $regFilter2, $regFilter3, $regFilter4);
$html = preg_replace_callback($patterns, "callback", $html);
return $html;
}
