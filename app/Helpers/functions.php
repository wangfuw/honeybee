<?php

use \Illuminate\Support\Facades\Redis;
use App\Models\Area;
use Illuminate\Support\Env;
/**
 * 生成邀请码
 */
if (!function_exists('inviteCode')) {
    function inviteCode($phone)
    {
        static $codeArr = [
            'L', '1', '2', 'C', '4', 'U',
            '6', '7', '8', '9', 'Y', 'Z',
            'A', 'B', '3', 'D', 'E', 'F',
            'G', 'H', 'I', '0', 'J', 'K',
            'M', 'N', 'O', 'P', 'Q', 'R',
            'S', 'T', '5', 'V', 'W', 'X',
        ];
        $code = '';
        while ($phone) {
            $mod = $phone % 36;
            $phone = (int)($phone / 36);
            $code = $codeArr[$mod] . $code;
        }
        return $code;
    }
}
/**
 * 电话号码验证
 */
   if(!function_exists('check_phone')){
       function check_phone($phone)
       {
           $isMob="/^1[3456789]{1}\d{9}$/";
           $isTel="/^([0-9]{3,4}-)?[0-9]{7,8}$/";
           if(!preg_match($isMob,$phone) && !preg_match($isTel,$phone)) return false;
           return true;
       }
   }
/**
 * 验证身份证
 * @param $idCard
 * @return bool
 * @author centphp.com
 * @date 2020/5/1
 */
   if(!function_exists("checkIdentityCard")){
      function checkIdentityCard($idCard)
       {
           // 只能是18位
           if (strlen($idCard) != 18) {
               return false;
           }
           // 取出本体码
           $idcard_base = substr($idCard, 0, 17);
           // 取出校验码
           $verify_code = substr($idCard, 17, 1);
           // 加权因子
           $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
           // 校验码对应值
           $verify_code_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
           // 根据前17位计算校验码
           $total = 0;
           for ($i = 0; $i < 17; $i++) {
               $total += substr($idcard_base, $i, 1) * $factor[$i];
           }
           // 取模
           $mod = $total % 11;
           // 比较校验码
           if ($verify_code == $verify_code_list[$mod]) {
               return true;
           } else {
               return false;
           }
       }
   }

/**
 * 生成随机字符串 40 地址 64 交易hash 64 密钥
 */
   if(!function_exists("rand_str_pay")){
       function rand_str_pay($length=40) {
           $rand='';
           $randstr= 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
           $max = strlen($randstr)-1;
           mt_srand((double)microtime()*1000000);
           for($i=0;$i<$length;$i++) {
               $rand.=$randstr[mt_rand(0,$max)];
           }
           return $rand;
       }
   }

   //搜索关键词写入redis
   if(!function_exists("add_keyword")){
       function add_keyword($key,$user_id)
       {
           Redis::rpush("SHANGTAO_".$user_id,$key);
       }
   }


if (!function_exists('send_sms')) {
    function send_sms($phone, $content, $om = "+86")
    {
        return send_sms_real($phone,$content,$om);
    }
}
if(!function_exists('make_code')){
    function make_code()
    {
        return rand('1000','9999');
    }
}

if (!function_exists("send_sms_real")) {
    function send_sms_real($phone,$content,$om)
    {
        $statusStr = array(
            "0" => "短信发送成功",
            "-1" => "参数不全",
            "-2" => "服务器空间不支持,请确认支持curl或者fsocket，联系您的空间商解决或者更换空间！",
            "30" => "密码错误",
            "40" => "账号不存在",
            "41" => "余额不足",
            "42" => "帐户已过期",
            "43" => "IP地址限制",
            "50" => "内容含有敏感词"
        );
        $sendurl = env('SEND_URL',"http://api.smsbao.com/") . "sms?u=" . env("SEND_USER","Szr180862") . "&p=" . env("SEND_PASS","59fe186b6a63fa80c816a86ed6303c25") . "&m=" . $phone . "&c=" . urlencode($content);
        $result = file_get_contents($sendurl);
        return ['status' => 1, 'info' => $statusStr[$result], 'data' => ''];
    }
}

/**
 * 通过区ID 查询完整省市区数据
 */
if (!function_exists('city_name')) {
    function city_name($code) {
        $city = Area::where('code', $code)->with('parent.parent.parent')->first();
        $str = [
            //$city['parent']['parent']['parent']['name'] ?? '',
            $city['parent']['parent']['name'] ?? '',
            $city['parent']['name'] ?? '',
            $city['name'] ?? ''
        ];
        return trim(implode('', $str));
    }
}

if(!function_exists("regex")){
    function regex($value, $rule)
    {
        $validate = [
            'email' => '/^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/',
            'phone' => '#^13[\d]{9}$|^14[5,7]{1}\d{8}$|^15[^4]{1}\d{8}$|^17[0,6,7,8]{1}\d{8}$|^18[\d]{9}$#',
            'interphone' => '/^[0-9]{6,11}$/',
            'username' => '/^[a-zA-Z][A-Za-z0-9]{5,17}$/',
            'nickname' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_]{1,12}$/u',  // 昵称，2-12位的汉字，字母或者数字
            'theme' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_ ]{2,50}$/u', // 反馈主题
            'password' => '/^[A-Za-z0-9@#!_-~\.]{6,18}$/',  // 密码，6-18位字母或者数字
            'double' => '/^[-\+]?\d+(\.\d+)?$/',
            'bankcard' => '/^(\d{16,19})$/',
            'card' => '/^[A-Za-z0-9]{4,20}$/',
            'qqNum' => '/^[0-9]{4,15}/',
            'qq' => '/^[1-9][0-9]{4,15}$/',
            'passport' => '/^[a-zA-Z0-9]{6,12}$/',
            'inviteCode' => '/^[0-9]{6,8}/',
            'cueWords' => '/^[\x{4e00}-\x{9fa5}A-Za-z0-9_ \,\，]{1,200}$/u',
            "trxAddress" => '/^[A-Za-z0-9]{34,34}$/',
            "ethAddress" => '/^0x[A-Fa-f0-9]{40,40}/',
            "num" => '/^[1-9][0-9]*$/',
            "url" => '/^[A-Za-z0-9\.]{1,30}$/',
            'payAccount'=>'/[a-zA-Z0-9]+/'
        ];
        $rule = $validate[$rule];
        $sb = preg_match($rule, $value);
        if ($sb === 1) {
            return 1;
        } else {
            return 0;
        }
    }
}

if(!function_exists('make_phone')){
    function make_phone($phone){
        return substr($phone, 0, 3).'*****'.substr($phone, 8);
    }
}

if(!function_exists('grade')){
    function grade($number,$base_num = 0)
    {
        $grade = 0;
        switch ($number){
            case $number >= 150000 && $number < 1000000:
                $grade = 1;
                break;
            case $number >= 1000000 && $number < 5000000:
                $grade = 2;
                break;
            case $number >= 5000000 && $number < 20000000:
                $grade = 3;
                break;
            case $number >= 20000000 && $number < 60000000:
                $grade = 4;
                break;
            case $number >= 60000000 && $base_num >= 2:
                $grade = 5;
                break;
            case $number >= 60000000 && $base_num < 2:
                $grade = 4;
                break;
            default:
                $grade = 0;
        }
        return $grade;
    }
}

//计算距离
if(!function_exists('getdistance')){
    function getdistance($lng1,$lat1,$lng2,$lat2){

        $radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
        $radLat2=deg2rad($lat2);
        $radLng1=deg2rad($lng1);
        $radLng2=deg2rad($lng2);
        $a=$radLat1-$radLat2;
        $b=$radLng1-$radLng2;
        $s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
        return $s;
    }
}

if(!function_exists('get_up_area')){
    function get_up_area($code)
    {
        return Area::query()->where('code',$code)->value('pcode');
    }
}

/**
 * Curl 提币
 */
//

if(!function_exists('post_url')){
    /**
     * PHP发送Json对象数据
     * @param $url 请求url
     * @param $data 发送的json字符串/数组
     * @return array
     */
    function post_url($url, $data = NULL)
    {

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if(!$data){
            return 'data is null';
        }
        if(is_array($data))
        {
            $data = json_encode($data,JSON_UNESCAPED_UNICODE);
        }
        dd($data);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER,array(
            'Content-Type: application/json; charset=utf-8',
            'Content-Length:' . strlen($data),
            'Cache-Control: no-cache',
            'Pragma: no-cache'
        ));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $res = curl_exec($curl);
        $errorno = curl_errno($curl);
        if ($errorno) {
            return $errorno;
        }
        curl_close($curl);
        return $res;
    }
}

if(!function_exists('curl_get')){
    function curl_get($appid,$secret,$code,$type = "authorization_code",$method){
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid =".$appid."&secret=".$secret."&code=".$code.'&grant_type='.$type;
        $ch = curl_init();//1.初始化
        curl_setopt($ch, CURLOPT_URL, $url);//2.请求地址
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);//3.请求方式
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $tmpInfo = curl_Exec($ch);

        if (curl_errno($ch)) {
            return curl_errno($ch);
        }
        curl_close($ch);//关闭

        return  $data = json_decode($tmpInfo);
    }
}

//排序
function formatBizQueryParaMap($paraMap, $urlencode)
{
    $buff = "";
    ksort($paraMap);
    foreach ($paraMap as $k => $v)
    {
        if($urlencode)
        {
            $v = urlencode($v);
        }
        $buff .= $k . "=" . $v . "&";
    }
    $reqPar = '';
    if (strlen($buff) > 0)
    {
        $reqPar = substr($buff, 0, strlen($buff)-1);
    }
    return $reqPar;
}

function formatBizQueryParaMapVal($paraMap,$urlencode){
    $buff = "";
    foreach ($paraMap as $k=>$v){
        if($urlencode){
            $v = urlencode($v);
        }
        $buff .= $v;
    }
    return $buff;
}

/**
 * RSA签名
 * @param $data 待签名数据
 * @param $private_key 私钥字符串
 * return 签名结果
 */
function rsaSign($data, $private_key) {

    $search = [
        "-----BEGIN RSA PRIVATE KEY-----",
        "-----END RSA PRIVATE KEY-----",
        "\n",
        "\r",
        "\r\n"
    ];
    $private_key = str_replace($search,"",$private_key);
    $private_key = $search[0] . PHP_EOL . wordwrap($private_key, 64, "\n", true) . PHP_EOL . $search[1];
    $private_key_resource_id=openssl_get_privatekey($private_key);
    if($private_key_resource_id)
    {
        openssl_sign($data, $sign,$private_key_resource_id,OPENSSL_ALGO_MD5);
        openssl_free_key($private_key_resource_id);
    }else {
        exit("私钥格式有误");
    }
    $sign = base64_encode($sign);
    return $sign;
}

function generateOrderNumber($length=4){
    //14位的日期（年月日时分秒）
    $date=date('Ymdhis ',time());
    //初始化变量为0
    $connt = 0;
    //建一个新数组
    $temp = array();
    while($connt < $length){
        //在一定范围内随机生成一个数放入数组中
        $temp[] = mt_rand(0, 9);
        //$data = array_unique($temp);
//去除数组中的重复值用了“翻翻法”，就是用array_flip()把数组的key和value交换两次。这种做法比用 array_unique() 快得多。
        $data = array_flip(array_flip($temp));
        //将数组的数量存入变量count中
        $connt = count($data);
    }
    //为数组赋予新的键名
    shuffle($data);
    //数组转字符串
    $str=implode(",", $data);
    //替换掉逗号
    $number=str_replace(',', '', $str);
    return str_replace(' ','',$date.$number);
}

/**
 * 生成签名
 * @param $params
 * @param $key
 * @param string $encryptType
 * @return string
 */
function hmacRequest($params="dasdas", $key, $encryptType = "1")
{
    if ("1" == $encryptType) {
        return md5(implode("", $params) . $key);
    } else {
        $private_key = openssl_pkey_get_private($key);
        $params = implode("", $params);
        openssl_sign($params, $sign, $private_key, OPENSSL_ALGO_MD5);
        openssl_free_key($private_key);
        $sign = base64_encode($sign);
        return $sign;
    }
}

function sign_ru_zhu($params = '',$key)
{
    $string = $params.'&key='.$key;
    return md5($string);
}

