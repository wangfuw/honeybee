<?php

namespace App\Validate\Admin;

use App\Validate\BaseValidate;

class SpuValidate extends BaseValidate
{
    //验证规则
    protected $rule = [
        'id' => 'required',
        'area' => 'required',
        'category' => 'required',
        'name' => 'required|string',
        'logo' => 'required|string',
        'banner_imgs' => 'required',
        'detail_imgs' => 'required',
        'special_spec' => 'required|string',
        'skus'=>'required'
    ];
    //自定义验证信息
    protected $message = [
        'area.required' => '商品分区必选',
        'category.required' => '商品分类必选',
        'name.required' => '商品名必填',
        'logo.required' => '商品logo必传',
        'banner_imgs.required' => '商品轮播图必传',
        'detail_imgs.required' => '商品详情图必传',
        'special_spec.required' => '商品规格',
        'skus.required' => '价格信息',
    ];

    //自定义场景
    protected $scene = [
        'add'=>"area,category,name,logo,banner_imgs,detail_imgs,special_spec,skus",
    ];
}
