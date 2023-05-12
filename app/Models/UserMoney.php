<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserMoney extends Base
{
    protected $table = 'user_money';

    protected $fillable = [
        'id','user_id','num','charge_image','money','status','admin_id','note','created_at','updated_at','coin_id'
    ];

    protected $hidden = [
        'deleted_at'
    ];


}
