<?php
namespace App\Models;

class Withdraw extends Base{

    protected $table = "withdraw";

    protected $guarded = [];
    protected $fillable = [
        'id','user_id','withdraw_address','amount','status','actual','fee','out_order_no','err','created_at','updated_at'
    ];
    protected $hidden = [];
}
