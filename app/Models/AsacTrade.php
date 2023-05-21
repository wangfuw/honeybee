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


    //转入
    const CHANG_IN = 8;
    //转出
    const CHANG_OUT = 9;
    //商家让利
    const STORE = 10;
    const FEE = 11;

    const ADD = 12;
    const BUS = 13;
    protected $table = 'asac_trade';

    const typeData = [
        self::BUY => '购买',
        self::SELL => '出售获得',
        self::RECHARGE => '充值获得',
        self::WITHDRAW => '提现',
        self::REWARD => '奖励获得',
        self::FREE_HAVED => '释放获得',
        self::FREE_USED => '释放消耗',
        self::CHANG_IN  => '转入',
        self::CHANG_OUT => '转出',
        self::STORE => '商品利润质押',
        self::FEE => '提现手续费',
        self::ADD => '后台增加',
        self::FEE => '后台扣除',
    ];
    protected $fillable = [
        'id','from_address','to_address','num','trade_hash','block_id','created_at','updated_at','type','game_zone'
    ];

    protected $hidden = [
        'updated_at'
    ];



}
