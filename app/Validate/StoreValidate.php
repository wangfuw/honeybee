<?php

namespace App\Validate;

class StoreValidate extends BaseValidate
{
    protected $rule =[
       'store_name' => 'required|min:6|max:20',
        'business_type' => 'required|numeric',
        'master'=> 'required|min:5|max:10',
        'mobile' => 'required',
        'images' => 'array',
        'area'   => 'required|numeric',
        'address' => '',
        'store_image' => 'required',
        '' => '',

    ];
    //自定义验证信息
    protected $message = [
        'username.required' => '姓名不能为空',
        'username.min'  => '姓名不合法',
        'username.max'  => '姓名不合法',
        'id_card.required'=> '身份证不能为空',
        'address.required'=> '地址不能为空',
        'front_image.required'=> '身份证正面不能为空',
        'back_image.required'=> '身份证背面不能为空',

    ];

    //自定义场景
    protected $scene = [
        'identity'=>"username,id_card,address,front_image,back_image",
    ];
}
