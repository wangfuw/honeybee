<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayOrder extends Base
{
    protected $table = "pay_order";

    protected $fillable = [
        'merchant_no','order_no','amount','cur','fre_code','trx_no','code','code_msg','result','hmac','pay_status','created_at','updated_at'
    ];



}
