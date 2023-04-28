<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacBlock extends Base
{
    use HasFactory;

    protected $table = 'asac_block';

    protected $fillable = [
       'id','number','trade_num','created_at','updated_at',
    ];

    protected $hidden = [
        'updated_at'
    ];

    public function trade()
    {
        return $this->hasMany(AsacTrade::class,'block_id','id');
    }
    public function get_list($params)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $data = self::query()->with(['trade'=>function($query){
            return $query->select('block_id','num');
        }])->select( 'id',
            'number',
            'trade_num',
            'created_at'
        ) ->orderBy('id','desc')
            ->get()->map(function ($item,$items){
                $temp = 0;
                foreach ($item->trade as $value){
                    $temp += $value['num'];
                }
                $item->trade_num = $temp;
                unset($item->trade);
                return $item;
            })
            ->forPage($page,$page_size);
        return collect([])->merge($data);
    }
}
