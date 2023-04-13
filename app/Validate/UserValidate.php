<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class UserValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'id' => 'required',
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:6',
        'phone' => 'required|unique:users'
    ];
    //自定义验证信息
    protected $message = [
        'name.required'=>'用户名不能为空',
        'name.max'=>'用户名长度不能大于 255',
        'email.required'=>'邮箱必须',
        'content.email'=>'邮箱格式错误',
        'email.unique'=>'邮箱已被使用',
        'password.required'=>'请输入密码',
        'password.min'=>'密码不能少于6位有效数字',
        'phone.required'=>'电话号码必须',
        'phone.unique'=>'电话号码已注册',
    ];

    //自定义场景
    protected $scene = [
        'register'=>"name,email,password",
        'login'   =>"email,password",
    ];
}
