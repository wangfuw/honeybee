<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseOrder extends Base
{
    protected $table = 'base_order';

    protected $fillable = [
        'id','user_id','products','money','type','created_at','updated_at'
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'products' => 'array'
    ];


}
