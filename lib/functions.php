<?php
/**
 * 新建目录
 * @param string $path 目录
 * @param int $mode 访问权
 */
function mkpath ($path, $mode = 0777)
{
    $dirs = preg_split('/[\/\]/', $path);
    $path = $dirs[0];
    for ($i = 1; $i < count($dirs); $i++) {
        $path .= '/' . $dirs[$i];
        @mkdir($path, $mode);
    }
    mkdir();
}

/**
 * 复制文件
 * @param string $src 原始路径
 * @param string $new 目标路径
 */
function copyfiles ($src, $new)
{
    $d = dir($src);
    while (($entry = $d->read())) {
        if (is_file($src . $entry)) {
            copy("$src . $entry", "$new . $entry");
        }
    }
    $d->close();
}

/**
 * 计算包含中文的字符串长度
 * @param $str
 * @return int
 */
function mstrlen($str){
    if (empty($str)){
        return 0;
    }elseif (function_exists("mb_strlen")){
        return mb_strlen($str, 'utf-8');
    }else {
        preg_match_all("/./us", $str, $matches);
        return count($matches[0]);
    }
}

/**
 * 截取含中文的字符串
 * @param string $str 截取的字符串
 * @param int $start 起始
 * @param int $length 截取数量
 * @param string $charset 字符编码
 * @param bool $suffix 后缀
 * @return string
 */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true) {
    if (mstrlen($str) <= $length){
        return $str;
    }
    if(function_exists("mb_substr")){
        $slice = mb_substr($str, $start, $length, $charset);
    }elseif(function_exists('iconv_substr')) {
        $slice = iconv_substr($str, $start, $length, $charset);
    }else{
        $re['utf-8']   = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
        $re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
        $re['gbk']    = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
        $re['big5']   = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
        preg_match_all($re[$charset], $str, $match);
        $slice = join("", array_slice($match[0], $start, $length));
    }
    return $suffix ? $slice.'...' : $slice;
}

/**
 * 整数转换为罗马数字
 * @param int $number 1~3999之间的数字，
 * @return string 将数字转换为小写罗马数字
 * @throws Exception
 */
function int_to_roman($number)
{
    $units      = array('', 'i', 'ii', 'iii', 'iv', 'v', 'vi', 'vii', 'viii', 'ix');
    $tens       = array('', 'x', 'xx', 'xxx', 'xl', 'l', 'lx', 'lxx', 'lxxx', 'xc');
    $hundreds   = array('', 'c', 'cc', 'ccc', 'cd', 'd', 'dc', 'dcc', 'dccc', 'cm');
    $thousands  = array('', 'm', 'mm', 'mmm');

    if (!is_integer($number) || $number < 1 || $number > 3999) {
        throw new Exception('Only integers between 0 and 3999 can be ' . 'converted to roman numerals.', $number);
    }

    return $thousands[$number / 1000 % 10] . $hundreds[$number / 100 % 10] . $tens[$number / 10 % 10] . $units[$number % 10];
}

/**
 * 数字样式转换
 * @param int $num
 * @param string $style
 * @return string
 */
function number_in_style($num, $style)
{
    switch($style) {
        case 'abc':
            $number = chr(ord('a') + $num);
            break;
        case 'ABCD':
            $number = chr(ord('A') + $num);
            break;
        case '123':
            $number = $num + 1;
            break;
        case 'iii':
            $number = int_to_roman($num + 1);
            break;
        case 'IIII':
            $number = strtoupper(int_to_roman($num + 1));
            break;
        case 'none':
            return '';
        default:
            return 'ERR';
    }
    return $number;
}

/**
 * 数字转换为大写或货币大写
 * @param $i 数字
 * @param int $s 默认转换为货币大写
 * @param bool $lt20 原始数字的是否小于20，如13应转为十三而非一十三
 * @return mixed|string
 */
function Chinese_Money_Max($i, $s = 1, $lt20 = false)
{
    $c_digIT_min = array('零', '十', '百', '千', '万', '亿');
    $c_num_min = array('零', '一', '二', '三', '四', '五', '六', '七', '八', '九', '十');

    $c_digIT_max = array('零', '拾', '佰', '仟', '万', '亿');
    $c_num_max = array('零', '壹', '贰', '叁', '肆', '伍', '陆', '柒', '捌', '玖', '拾');

    if ($s == 1) {
        $c_digIT = $c_digIT_max;
        $c_num = $c_num_max;
    } else {
        $c_digIT = $c_digIT_min;
        $c_num = $c_num_min;
    }

    if ($i < 0) {
        // - $i 则为正数
        return '负' . Chinese_Money_Max(- $i, $s);
    }
    if ($i < 11) {
        return $c_num[$i];
    }
    if ($i < 20) {
        if ($lt20) {
            return $c_digIT[1] . $c_num[$i - 10];
        } else {
            return $c_num[1] . $c_digIT[1] . $c_num[$i - 10];
        }
    }
    if ($i < 100) {
        if ($i % 10) {
            return $c_num[$i / 10] . $c_digIT[1] . $c_num[$i % 10];
        } else {
            return $c_num[$i / 10] . $c_digIT[1];
        }
    }
    if ($i < 1000) {
        if ($i % 100 == 0) {
            return $c_num[$i / 100] . $c_digIT[2];
        } elseif ($i % 100 < 10) {
            return $c_num[$i / 100] . $c_digIT[2] . $c_num[0] . Chinese_Money_Max($i % 100, $s);
        } elseif ($i % 100 < 10) {
            return $c_num[$i / 100] . $c_digIT[2] . $c_num[1] . Chinese_Money_Max($i % 100, $s);
        } else {
            return $c_num[$i / 100] . $c_digIT[2] . Chinese_Money_Max($i % 100, $s);
        }
    }
    if ($i < 10000) {
        if ($i % 1000 == 0) {
            return $c_num[$i / 1000] . $c_digIT[3];
        } elseif ($i % 1000 < 100) {
            return $c_num[$i / 1000] . $c_digIT[3] . $c_num[0] . Chinese_Money_Max($i % 1000, $s);
        } else {
            return $c_num[$i / 1000] . $c_digIT[3] . Chinese_Money_Max($i % 1000, $s);
        }
    }
    if ($i < 100000000) {
        if ($i % 10000 == 0) {
            return Chinese_Money_Max($i / 10000, $s) . $c_digIT[4];
        } elseif ($i % 10000 < 1000) {
            return Chinese_Money_Max($i / 10000, $s) . $c_digIT[4] . $c_num[0] . Chinese_Money_Max($i % 10000, $s);
        } else {
            return Chinese_Money_Max($i / 10000, $s) . $c_digIT[4] . Chinese_Money_Max($i % 10000, $s);
        }
    }
    if ($i < 1000000000000) {
        if ($i % 100000000 == 0) {
            return Chinese_Money_Max($i / 100000000, $s) . $c_digIT[5];
        } elseif ($i % 100000000 < 1000000) {
            return Chinese_Money_Max($i / 100000000, $s) . $c_digIT[5] . $c_num[0] . Chinese_Money_Max($i % 100000000, $s);
        } else {
            return Chinese_Money_Max($i / 100000000, $s) . $c_digIT[5] . Chinese_Money_Max($i % 100000000, $s);
        }
    }
}

/**
 * json_encode中文处理
 * @param $data
 * @return mixed|string
 */
function json_encode_cn($data)
{
    if (version_compare(PHP_VERSION,'5.4.0','<')) {
        $data = json_encode($data);
        return preg_replace("/\\\u([0-9a-f]{4})/ie", "iconv('UCS-2BE', 'UTF-8', pack('H*', '$1'));", $data);
    }else {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

/**
 * 随机生成字符串
 * @param int $length
 * @return string
 */
function createNonceStr($length = 16)
{
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }

    return $str;
}

/**
 * 使用CURL发送GET请求
 * @param $url
 * @return bool|mixed
 */
function http_get($url)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus ["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}

/**
 * 使用CURL发送POST请求
 * @param string $url
 * @param $params
 * @return bool|mixed
 */
function http_post($url, $params)
{
    $oCurl = curl_init();
    if (stripos($url, "https://") !== FALSE) {
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
    }
    if (is_string($params)) {
        $strPOST = $params;
    } else {
        if (isset($params['media'])) {
            $strPOST = $params;
        } else {
            $aPOST = array();
            foreach ($params as $key => $val) {
                $aPOST[] = $key . "=" . urlencode($val);
            }
            $strPOST = explode("&", $aPOST);
        }
    }
    curl_setopt($oCurl, CURLOPT_URL, $url);
    curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($oCurl, CURLOPT_POST, true);
    curl_setopt($oCurl, CURLOPT_POSTFIELDS, $strPOST);
    $sContent = curl_exec($oCurl);
    $aStatus = curl_getinfo($oCurl);
    curl_close($oCurl);
    if (intval($aStatus["http_code"]) == 200) {
        return $sContent;
    } else {
        return false;
    }
}
