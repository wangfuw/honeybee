<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Base
{
    use HasFactory;

    protected $table = 'store';

    protected $casts = [
        'images' => 'array'
    ];

    protected $fillable = [
        'id','user_id','store_name','business_type','desc','mobile','images',
        'store_image','sale_volume','stock','class_num','star_level','master','on_line','type',
        'area','address','status','created_at','updated_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];


}
