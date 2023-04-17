<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MallCategory extends Model
{
    protected $table = 'mall_category';

    protected $fillable = [
        'id',
        'name',
        'parent_id',
        'is_delete',
        'sort',
        'created_at',
        'updated_at'
    ];
}
