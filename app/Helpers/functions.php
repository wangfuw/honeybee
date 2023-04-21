<?php

use \Illuminate\Support\Facades\Redis;
use App\Models\Area;
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
           $isMob="/^1[34578]{1}\d{9}$/";
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

   if(!function_exists('get_order_no')){
       function get_order_no()
       {
           $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
           return $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf('%02d', rand(0, 99));
       }
   }


if (!function_exists('send_sms')) {
    function send_sms($phone, $content, $om = "+86")
    {
        return send_sms_real();
    }
}

if (!function_exists("send_sms_real")) {
    function send_sms_real()
    {
        return true;
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
            //$city['parent']['parent']['name'] ?? '',
            $city['parent']['name'] ?? '',
            $city['name'] ?? ''
        ];
        return trim(implode('', $str));
    }
}


