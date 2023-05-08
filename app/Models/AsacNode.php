<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacNode extends Base
{
    use HasFactory;

    protected $table = 'asac_node';

    protected $fillable = [
        'id','user_id','wallet_address','private_key','number','created_at','updated_at'
    ];

    protected $hidden = [
        'private_key'
    ];

    public function user()
    {
        return $this->hasOne(User::class,'id','user_id');
    }

    public function get_list($params,$config = [])
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $list = self::query()->with(['user'=>function($query){
            return $query->select('id','coin_num');
        }])->select('user_id','wallet_address','number','updated_at')
            ->get()->map(function ($item,$items) use($config){
                if($item->id <= 4){
                    $item->money = bcmul($item->number,$config['last_price']);
                    $temp = bcdiv($item->number * 100,$config['number'],2);
                    $item ->ratio = number_format($temp,2).'%';
                }else{
                    $item->number = $item->user->coin_num;
                    var_dump($item->user);
                    $item->money = bcmul($item->user->coin_num??0,$config['last_price'])??0;
                    $temp = bcdiv($item->user->coin_num??0 * 100,$config['number'],2)??0;
                    $item ->ratio = number_format($temp,2).'%'??0;
                }
//                unset($temp,$item->user);
                return $item;
        })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }
}
