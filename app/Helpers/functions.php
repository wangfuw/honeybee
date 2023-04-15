<?php

/**
 * 生成邀请码
 */
   if(!function_exists('inviteCode')){
       function inviteCode($phone)
       {
           static  $codeArr = [
               'L','1','2','C','4','U',
               '6','7','8','9','Y','Z',
               'A','B','3','D','E','F',
               'G','H','I','0','J','K',
               'M','N','O','P','Q','R',
               'S','T','5','V','W','X',
           ];
           $code = '';
           while($phone)
           {
               $mod = $phone % 36;
               $phone = (int)($phone / 36);
               $code = $codeArr[$mod].$code;
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
           $pattern = '/^1[3456789]{1}\d{9}$/';
           $res = preg_match($pattern, $phone);
           if(!$res) return false;
           return true;
       }
   }

