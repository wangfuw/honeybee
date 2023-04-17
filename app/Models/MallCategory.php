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
}
