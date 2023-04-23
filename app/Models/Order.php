<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Base
{
    protected $table = 'orders';

    protected $fillable = [
        'id','base_id','order_no','user_id','type','created_at','updated_at','address'
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'products' => 'array',
        'address'=>'array'
    ];

    public function getUser()
    {
        return $this->hasOne('User','id','user_id');
    }

    public function getSku(){
        return $this->hasOne('MallSku','id','sku_id');
    }

}
