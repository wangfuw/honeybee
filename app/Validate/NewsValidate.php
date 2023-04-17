<?php

namespace App\Validate;

class NewsValidate extends BaseValidate
{
    protected $rule =[
        'id' => 'required|numeric',
        'type'=>'numeric',
        'page'=>'numeric',
        'page_size'=>'numeric',
    ];
    //自定义验证信息
    protected $message = [
        'id.required' => '正确选择资讯',
        'id.numeric'  => '资讯ID为数字',
        'type.numeric'=> '资讯类型为数字',
        'page'        => '页码为数字',
        'page_size'   => '每页显示条数为数字',
        'type'        => '查询的公告类型'
    ];

    //自定义场景
    protected $scene = [
        'getNews'=>"type,page,page_size,type",
        'getInfo'   =>"id",
        'notice' => "page,page_size"
    ];
}
