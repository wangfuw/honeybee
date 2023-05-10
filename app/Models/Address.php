<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Address extends Base
{
    use HasFactory,SoftDeletes;
    protected $table = 'address';

    protected $fillable = [
      'id','user_id','address_detail','area','is_def','exp_person','exp_phone','created_at','updated_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];
    public function getList($params,$user_id)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??8;
        $data = self::query()->select( 'id',
            'id','address_detail','area','is_def','exp_person','exp_phone','created_at',
        )->where('user_id',$user_id)
            ->orderBy('is_def','desc')
            ->forPage($page,$page_size)
            ->get()->map(function ($item,$items){
                $item->area_china = city_name($item->area);
                return $item;
            });
        return collect([])->merge($data);
    }
}
