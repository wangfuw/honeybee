<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayOrder extends Base
{
    protected $table = "pay_order";

    protected $fillable = [
        'merchant_no','order_no','amount','phone','store_id','f_trx_no',
        'cur','fre_code','trx_no','free','bank_order_no','bank_trx_no','pay_time','bank_code','openid','card_type','bank_type',
        'alt_info','pay_status','created_at','updated_at'
    ];



}
