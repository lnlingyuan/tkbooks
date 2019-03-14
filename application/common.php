<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

//防注入安全过滤函数
function filter($data){
    //对特殊符号添加反斜杠
    $data = addslashes($data);
    //判断自动添加反斜杠是否开启
    if(get_magic_quotes_gpc()){
        //去除反斜杠
        $data = stripslashes($data);
    }
    //把'_'过滤掉
    $data = str_replace("_", "\_", $data);
    //把'%'过滤掉
    $data = str_replace("%", "\%", $data);
    //把'*'过滤掉
    $data = str_replace("*", "\*", $data);
    //回车转换
    $data = nl2br($data);
    //去掉前后空格
    $data = trim($data);
    //将HTML特殊字符转化为实体
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * @param string $code 状态码
 * @param string $msg   说明
 * @param string $res   内容数据
 * @return \think\response\Json  json返回格式
 */
function ajaxJson($code='',$msg='',$res=''){
    $data['code'] = $code;
    $data['msg'] = $msg;
    $data['res'] = $res;

    return json($data);
}


/**
 * $msg 待提示的消息
 * $url 待跳转的链接
 * $icon 这里主要有两个，5和6，代表两种表情（哭和笑）
 * $time 弹出维持时间（单位秒）
 */
function alert_success($msg='',$url='',$time=3){
    $str='<script type="text/javascript" src="/static/js/jquery-1.9.1.min.js"></script> <script type="text/javascript" src="/static/js/layer/layer.js"></script>';//加载jquery和layer
    $str.='<script>
        $(function(){
            layer.msg("'.$msg.'",{icon:"6",time:'.($time*1000).'});
            setTimeout(function(){
                   self.parent.location.href="'.$url.'"
            },2000)
        });
    </script>';//主要方法
    return $str;
}

/**
 * $msg 待提示的消息
 * $icon 这里主要有两个，5和6，代表两种表情（哭和笑）
 * $time 弹出维持时间（单位秒）
 */
function alert_error($msg='',$time=3){
    $str='<script type="text/javascript" src="/static/js/jquery-1.9.1.min.js"></script> <script type="text/javascript" src="/static/js/layer/layer.js"></script>';//加载jquery和layer
    $str.='<script>
        $(function(){
            layer.msg("'.$msg.'",{icon:"5",time:'.($time*1000).'});
            setTimeout(function(){
                   window.history.go(-1);
            },2000)
        });
    </script>';//主要方法
    return $str;
}


/**
 * @param $string
 * @param string $operation
 * @param string $key
 * @param int $expiry
 * @return bool|string
 * 加解密码算法
 * 用法示例：
 * $str = 'abcdef';
    $key = 'www.baidu.com';
    echo authcode($str,'ENCODE',$key,0); //加密
    $str = 'cee9oY4I83MZssKUT9ARpyeeUrq33KJj2Joc6spNrUA0J5A';
    echo authcode($str,'DECODE',$key,0); //解密
 */
function authcode($string, $operation = 'DECODE', $key = '', $expiry = 0)
{
    // 动态密匙长度，相同的明文会生成不同密文就是依靠动态密匙
    $ckey_length = 4;
    // 密匙
    $key = md5($key ? $key : $GLOBALS['discuz_auth_key']);
    // 密匙a会参与加解密
    $keya = md5(substr($key, 0, 16));
    // 密匙b会用来做数据完整性验证
    $keyb = md5(substr($key, 16, 16));
    // 密匙c用于变化生成的密文
    $keyc = $ckey_length ? $operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length) : '';
    // 参与运算的密匙
    $cryptkey = $keya . md5($keya . $keyc);
    $key_length = strlen($cryptkey);
    // 明文，前10位用来保存时间戳，解密时验证数据有效性，10到26位用来保存$keyb(密匙b)，
    //解密时会通过这个密匙验证数据完整性
    // 如果是解码的话，会从第$ckey_length位开始，因为密文前$ckey_length位保存 动态密匙，以保证解密正确
    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
    $string_length = strlen($string);
    $result = '';
    $box = range(0, 255);
    $rndkey = array();
    // 产生密匙簿
    for ($i = 0; $i <= 255; $i++) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }
    // 用固定的算法，打乱密匙簿，增加随机性，好像很复杂，实际上对并不会增加密文的强度
    for ($j = $i = 0; $i < 256; $i++) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }
    // 核心加解密部分
    for ($a = $j = $i = 0; $i < $string_length; $i++) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        // 从密匙簿得出密匙进行异或，再转成字符
        $result .= chr(ord($string[$i]) ^ $box[($box[$a] + $box[$j]) % 256]);
    }
    if ($operation == 'DECODE') {
        // 验证数据有效性，请看未加密明文的格式
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        // 把动态密匙保存在密文里，这也是为什么同样的明文，生产不同密文后能解密的原因
        // 因为加密后的密文可能是一些特殊字符，复制过程可能会丢失，所以用base64编码
        return $keyc . str_replace('=', '', base64_encode($result));
    }
}

/**
 * 组合成正确的书籍源地址
 * @param $url
 * @param $basename
 * @return string
 *
 */
function correct_url($url,$basename){
    $href = parse_url($url);
    $path = basename($basename);
    $res = $href['scheme'].'://'.$href['host'].$href['path'].$path;

    return $res;
}



/**
 * @param $array
 * @return array
 * 二维数组去掉重复值
 * 说明：不能只用简单的逗号等隔开，因为有些章节中会含有这些字符，会导致匹配出错
 */
function array_unique_fb($array){

    $array = array_reverse($array);
    foreach ($array as $v) {
        $v = join(",?*", $v); //降维,也可以用implode,将一维数组转换为用,?*连接的字符串
        $temp[] = $v;
    }

    $temp = array_unique($temp);//去掉重复的字符串,也就是重复的一维数组
    foreach ($temp as $k => $v) {
        $temp[$k] = explode(",?*", $v);//再将拆开的数组重新组装
    }

    return $temp;



}