<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Base
{
    use HasFactory;

    protected $table = 'score';

    protected $guarded = [];

    protected $hidden = [
        'deleted_at'
    ];


    const BACK_ADD = 1;
    const BACK_SUB = 2;

    const FREE_USED     = 3;
    const BURN_HAVE     = 4;

    const LUCKY_FREE_USED  = 5;
    const TRADE_HAVE = 6;
    const TRADE_USED = 7;

    const F_TYPES = [
        self::BACK_ADD => "后台增加",
        self::BACK_SUB => "后台扣除",
        self::FREE_USED => "释放消耗",
        self::BURN_HAVE => "燃烧所得",
        self::LUCKY_FREE_USED => "释放消耗幸运值",
        self::TRADE_HAVE => "交易得到",
        self::TRADE_USED => "交易消耗",
    ];
}
