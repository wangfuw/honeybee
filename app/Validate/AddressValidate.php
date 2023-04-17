<?php

namespace App\Validate;

class AddressValidate extends BaseValidate
{
    protected $rule =[
        "id"  => 'required|numeric',
        "address_detail" => 'required|min:4|max:20',
        "exp_person"  => 'required',
        "exp_phone"  =>'required',
        "is_def"     => 'numeric',
        "area"       => 'required|numeric',
        'page'=>'numeric',
        'page_size'=>'numeric',
        ];
    //自定义验证信息
    protected $message = [
        'id.required'  => 'ID必须',
        'id.numeric'   => 'ID必须是数字',
        'address_detail.required' => '收件地址不能为空',
        'address_detail.min'  => '收件地址不合法',
        'address_detail.max'  => '收件地址不合法',
        'exp_person.required'=> '收件人不能为空',
        'exp_phone.required'=> '收件人电话不能为空',
        "is_def.numeric"    => '默认地址参数错误',
        "area.required"      => '地区不能为空',
        "area.numeric"      => '数字类型',
        'page.numeric'      => '页码是数字',
        'page_size.numeric'      => '每页显示条数是数字',
    ];

    //自定义场景
    protected $scene = [
        'add'=>"address_detail,exp_person,is_def,area",
        'id' => "id",
        'page' => 'page,page_size'
    ];
}
