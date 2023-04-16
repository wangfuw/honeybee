<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class UserValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'id' => 'required',
        'invite_code'=>'required|min:6',
        'code'=>'required|min:4',
        'password' => 'required|string|min:6',
        'phone' => 'required',
        're_password' => 'required|string|min:6',
    ];
    //自定义验证信息
    protected $message = [
        'invite_code.required'=>'邀请码不能为空',
        'invite_code.min'=>'邀请码错误',
        'code.required'=>'短信验证码必须',
        'code.min'=>'短信验证码错误',
        'password.required'=>'请输入密码',
        'password.min'=>'密码不能少于6位有效数字',
        're_password.required'=>'请输入密码',
        're_password.min'=>'密码不能少于6位有效数字',
        'phone.required'=>'电话号码必须',
    ];

    //自定义场景
    protected $scene = [
        'register'=>"invite_code,password,phone,code,re_password", //短信验证码验证
        'register_no_code'=>"invite_code,password,phone", //无需短信验证码验证
        'login'   =>"phone,password",
        'change'  =>"phone,code,password,re_password",
        'change_sale' => "phone,code,sale_password,re_sale_phone"
    ];
}
