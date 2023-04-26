<?php

namespace App\Validate;

class StoreValidate extends BaseValidate
{
    protected $rule =[
        'id'=>'required|numeric',
        'store_name' => 'required|max:20',
        'business_type' => 'required|numeric',
        'master'=> 'required|min:2|max:20',
        'mobile' => 'required',
        'images' => 'array',
        'area'   => 'required|numeric',
        'address' => 'max:50',
        'store_image' => '',
        'on_line' => 'required|numeric',

    ];

    //自定义验证信息
    protected $message = [
        'id.required'  => 'ID不能为空',
        'id.numeric'   => 'ID类型为数字',
        'store_name.required' => '店铺名称不能为空',
        'store_name.max'  => '店铺名称最多20个长度',
        'business_type.required'=> '营业范围不能为空',
        'business_type.numeric'=> '营业范围不能为空',
        'master.required'=> '负责人不能为空',
        'master.min'=> '负责人不合法',
        'master.max'=> '负责人不合法',
        'mobile.required'=> '联系电话不能为空',
        'images.array'=> '文件类型错误',
        'area.required' => '请选择地区',
        'area.numeric'  => '请选择正确地区',
        'address.max'   => '详细地址过长',
        'on_line.required'   => '选择申请店铺类型',
        'on_line.numeric'   => '请正确选择申请店铺类型',
    ];

    //自定义场景
    protected $scene = [
        'add'   => "store_name,business_type,master,mobile,images,area,address,store_image,on_line",
        'info'  => "id"
    ];
}
