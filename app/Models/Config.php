<?php

namespace App\Models;

class Config extends Base
{
    protected $table = "config";
    protected $guarded = [];

    public static function green_one_allowance(){
        return self::query()->value('green_one_allowance');
    }
    public static function green_twice_allowance(){
        return self::query()->value('green_twice_allowance');
    }
    public static function green_threefold_allowance(){
        return self::query()->value('green_threefold_allowance');
    }
    public static function consume_one_allowance(){
        return self::query()->value('consume_one_allowance');
    }
    public static function consume_twice_allowance(){
        return self::query()->value('consume_twice_allowance');
    }
    public static function consume_threefold_allowance(){
        return self::query()->value('consume_threefold_allowance');
    }
    public static function lucky_base(){
        return self::query()->value('lucky_base');
    }
    public static function lucky_middle(){
        return self::query()->value('lucky_middle');
    }
    public static function lucky_last(){
        return self::query()->value('lucky_last');
    }
    public static function lucky_base_reward_coin(){
        return self::query()->value('lucky_base_reward_coin');
    }
    public static function lucky_middle_reward_coin(){
        return self::query()->value('lucky_middle_reward_coin');
    }
    public static function lucky_last_reward_coin(){
        return self::query()->value('lucky_last_reward_coin');
    }

    public static function ticket_ratio_rmb(){
        return self::query()->value('ticket_ratio_rmb');
    }

    public static function register_give_lucky(){
        return self::query()->value('register_give_lucky');
    }
    public static function free_green_ratio_lucky(){
        return self::query()->value('free_green_ratio_lucky');
    }
    public static function leader_average_rate(){
        return self::query()->value('leader_average_rate');
    }

    public static function rank_rate(){
        return self::query()->value('rank_rate');
    }

    public static function money_rate(){
        return self::query()->value('money_rate');
    }
    public static function freeze_free(){
        return self::query()->value('freeze_free');
    }

}
