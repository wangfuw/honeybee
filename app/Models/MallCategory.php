<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MallCategory extends Base
{
    protected $table = 'mall_category';


    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'is_delete',
        'created_at',
        'updated_at'
    ];

    //获取一级
    public static function get_first()
    {
        return self::query()->where('parent_id',0)->pluck('name','id')->toArray();
    }
}
