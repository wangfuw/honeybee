<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacTrade extends Base
{
    use HasFactory;
    //买卖花费
    const BUY = 1;
    //买卖获得
    const SELL = 2;
    //充值获得
    const RECHARGE = 3;
    //提现花费
    const WITHDRAW = 4;
    //奖励获得
    const REWARD = 5;
    //释放获得
    const FREE_HAVED = 6;
    //释放花费
    const FREE_USED = 7;
    protected $table = 'asac_trade';

    const typeData = [
        self::BUY => '买卖花费',
        self::SELL => '买卖获得',
        self::RECHARGE => '充值获得',
        self::WITHDRAW => '提现花费',
        self::REWARD => '奖励获得',
        self::FREE_HAVED => '释放获得',
        self::FREE_USED => '释放花费',
    ];
    protected $fillable = [
        'id','from_address','to_address','num','trade_hash','block_id','created_at','updated_at','type'
    ];

    protected $hidden = [
        'updated_at'
    ];

}
