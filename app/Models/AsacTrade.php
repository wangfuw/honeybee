<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacTrade extends Base
{
    use HasFactory;
    //买
    const BUY = 1;
    //卖
    const SELL = 2;
    //充值
    const RECHARGE = 3;
    //提现
    const WITHDRAW = 4;
    //奖励
    const REWARD = 5;

    protected $table = 'asac_trade';

    protected $fillable = [
        'id','from_address','to_address','num','trade_hash','block_id','created_at','updated_at','type'
    ];

    protected $hidden = [
        'updated_at'
    ];

}
