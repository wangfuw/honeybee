<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacNode extends Model
{
    use HasFactory;

    protected $table = 'asac_node';

    protected $fillable = [
        'id','user_id','wallet_address','private_key','number','created_at','updated_at'
    ];

    protected $hidden = [
        'private_key'
    ];

    public function get_list($params,$config = [])
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $list = self::query()->select('wallet_address','number','updated_at')
            ->orderBy('number','desc')
            ->get()->map(function ($item,$items) use($config){
                $item['money'] = bcmul($item['number'],$config['last_price']);
                $temp = bcdiv($item['number'],$config['number'])*100;
                $item['ratio'] = number_format($temp,2).'%';
                unset($temp);
                return $item;
            })->forPage($page,$page_size);
        return collect([])->merge($list);
    }
}
