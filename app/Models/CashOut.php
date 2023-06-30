<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CashOut extends Base
{
    protected $table = "cash_out";

    protected $fillable = [
        'id','user_id','bank_name','bank_card','fax_name','amount','payment_image','note','status','created_at','created_at'
    ];
}
