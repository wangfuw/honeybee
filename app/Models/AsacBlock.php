<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AsacBlock extends Base
{
    use HasFactory;

    protected $table = 'asac_block';

    protected $fillable = [
       'id','number','trade_num','created_at','updated_at','is_block'
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
        $data = self::query()->select( 'id',
            'number',
            'trade_num',
            'created_at'
        ) ->orderBy('id','desc')
            ->get()
            ->forPage($page,$page_size);
        return collect([])->merge($data);
    }
}
