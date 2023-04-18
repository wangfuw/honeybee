<?php

namespace App\Models;


class MallSpu extends Base
{
    protected $table = 'mall_spu';


    protected $fillable = [
        'id',
        'name',
        'sub_title',
        'description',
        'category_one',
        'category_two',
        'saleable',
        'logo',
        'banners',
        'details',
        'special_spec',
        'user_id',
        'game_zone',
        'score_zone',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        "banners" => "array",
        "details" => "array",
        "special_spec" => "array"
    ];
}
