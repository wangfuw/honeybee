<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Score extends Base
{
    use HasFactory;

    protected $table = 'score';

    protected $guarded = [];
    public $fillable = [
        'id','user_id','help_phone','flag','num','type','f_type','amount','created_at','updated_at', 'game_zone'
    ];
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

    const TRADE_REWARD = 8;
    const REGISTER_REWARD = 9;
    const FREE_HAVE = 10;
    const DICT_FREE_USED = 11;
    const TEAM_FREE_USED = 12;
    const SORT_FREE_USED = 13;


    const BUY_USED = 14;

    //分享直推
    const DICT_FREE = 15;
    //分享间接
    const J_DICT_FREE = 16;
    const SALE_DICT_FREE = 17;
    const SALE_J_DICT_FREE = 18;

    // 平级
    const LEVEL_FREE_USED = 19;

    // 形象店加速
    const XX_USED = 20;
    const XX_TEAM_USED = 22;
    // 旗舰店加速
    const QJ_USED = 21;
    const QJ_TEAM_USED = 23;
    const DOWN_LINE_BUY_HAVE = 24;
    const DOWN_LINE_SALE_HAVE = 25;

    const F_TYPES = [
        self::BACK_ADD => "后台增加",
        self::BACK_SUB => "后台扣除",
        self::FREE_USED => "释放消耗",
        self::BURN_HAVE => "燃烧所得",
        self::LUCKY_FREE_USED => "释放消耗",
        self::TRADE_HAVE => "购买奖励",
        self::TRADE_USED => "出售消耗",
        self::TRADE_REWARD=>"消费发奖励",
        self::REGISTER_REWARD=>"注册赠送幸运值",
        self::FREE_HAVE=>"释放获得",
        self::DICT_FREE_USED=>"上层加速释放",
        self::TEAM_FREE_USED=>"团队加速释放",
        self::SORT_FREE_USED=>"全网公排加速",
        self::BUY_USED => '购买消耗',
        self::DICT_FREE => '绿色积分分享直推',
        self::J_DICT_FREE => '绿色积分分享间推',
        self::SALE_DICT_FREE => '消费积分分享直推',
        self::SALE_J_DICT_FREE => '消费积分分享间推',
        self::LEVEL_FREE_USED => '平级加速',
        self::XX_USED => '形象店加速',
        self::QJ_USED => '旗舰店加速',
        self::XX_TEAM_USED => '形象店团队加速',
        self::QJ_TEAM_USED => '旗舰店团队加速',
        self::DOWN_LINE_BUY_HAVE => '线下购买获得',
        self::DOWN_LINE_SALE_HAVE => '线下销售获得',
    ];


    public function get_list($data = [],$type = 1,$user_id)
    {
        $used_num = 0;
        switch ($type){
            case 1:
                $used_num = self::query()->where('user_id',$user_id)->whereIn('f_type',[self::LUCKY_FREE_USED,Score::SORT_FREE_USED,
                    self::FREE_USED,self::DICT_FREE_USED,self::TEAM_FREE_USED,
                    self::DICT_FREE,self::J_DICT_FREE,self::SALE_DICT_FREE,
                    self::SALE_J_DICT_FREE,self::XX_USED,self::XX_TEAM_USED,self::QJ_TEAM_USED,self::QJ_USED])->where('type',1)->sum('num');
                break;
            case 2:
                $used_num = self::query()->where('user_id',$user_id)->where('f_type', self::FREE_USED)->where('type',2)->sum('num');
                break;
            case 3:
                $used_num = 0;
                break;
            case 4:
                $used_num = self::query()->where('user_id',$user_id)->where('f_type',self::BUY_USED)->where('type',4)->sum('num');
                break;
        }
        $page = $data['page']??1;
        $page_size = $data['page_size']??8;
        $types = self::F_TYPES;
        $list = self::query()->select('id','flag','created_at','num','f_type','amount','game_zone','help_phone')->where('user_id',$user_id)
            ->where('type',$type)
            ->orderBy('created_at','desc')->forPage($page,$page_size)->get()->map(function ($item,$items) use($types){
                $item->note = $this->get_name($item->game_zone).$types[$item->f_type];
                return $item;
            });
        $data =  collect([])->merge($list);
        return compact('used_num','data');
    }

    protected function get_name($game_zone){
        switch ($game_zone){
            case 1:
                return '福利专区';
            case 2:
                return '优选专区';
            case 3:
                return '幸运专区';
            case 4:
                return '消费专区';
        }
    }
}
