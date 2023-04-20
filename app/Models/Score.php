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


    public function get_list($data = [],$type = 1,$user_id)
    {
        $used_num = 0;
        switch ($type){
            case 1:
                $used_num = self::query()->where('user_id',$user_id)->where('type',self::FREE_USED)->where('type',1)->count('num');
                break;
            case 2:
                $used_num = self::query()->where('user_id',$user_id)->where('type',self::FREE_USED)->where('type',2)->count('num');
                break;
            case 3:
                $used_num = self::query()->where('user_id',$user_id)->where('type',self::LUCKY_FREE_USED)->where('type',3)->count('num');
                break;
            case 4:
                $used_num = self::query()->where('user_id',$user_id)->where('type',self::TRADE_USED)->where('type',4)->count('num');
                break;
        }
        $page = $page??1;
        $page_size = $page_size??8;
        $list = self::query()->select('id','flag','created_at','num','f_type')->where('user_id',$user_id)
            ->where('type',$type)
            ->orderBy('created_at','desc')->get()->forPage($page,$page_size);
        $data =  collect([])->merge($list);
        return compact('used_num','data');
    }
}
