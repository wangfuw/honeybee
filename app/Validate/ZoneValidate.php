<?php
namespace App\Validate;

/**
 * 文章验证器
 */
class ZoneValidate extends BaseValidate {
    //验证规则
    protected $rule =[
        'keyword'  => 'min:1|max:10',
       'score_zone'=>'required|numeric',
       'page'=>'numeric',
       'page_size'=>'numeric',
    ];
    //自定义验证信息
    protected $message = [
        'score_zone.required'=>'专区不能为空',
        'page.numeric'=>'页码数字',
        'page_size.numeric'=>'分页数字',
        'score_zone.numeric'=>'专区数字',
    ];

    //自定义场景
    protected $scene = [
       'welfare'=>['keyword','score_zone','page','page_size'],
       'preferred'=>['keyword','score_zone','page','page_size'],
       'happiness'=>['keyword','page','page_size'],
       'consume'=>['keyword','page','page_size'],
    ];
}
