<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Base extends Model
{
    use HasFactory;

    //关闭自动时间管理 默认值true
//    public $timestamps = false;

    protected $dateFormat = 'U';
}
