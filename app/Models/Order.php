<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Base
{
    use SoftDeletes;
    protected $table = 'orders';

    protected $fillable = [
        'id','order_no','user_id','sku_id','sku_num','store_id','express_fee'
        ,'give_green_score','give_sale_score','give_lucky_score','status','address','express_no','express_name','coin_num','created_at','updated_at','address','ticket_num','price','spu_id'
    ];

    protected $hidden = ['deleted_at'];

    protected $casts = [
        'products' => 'array',
        'address'=>'array'
    ];

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function sku(){
        return $this->hasOne(MallSku::class,'id','sku_id');
    }

    public function spu(){
        return $this->hasOne(MallSpu::class,'id','spu_id');
    }

}
