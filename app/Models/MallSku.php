<?php

namespace App\Models;


class MallSku extends Base
{
    protected $table = 'mall_sku';


    protected $fillable = [
        'id',
        'spu_id',
        'stock',
        'price',
        'indexes',
        'enable',
        'created_at',
        'updated_at',
        'deleted_at',
    ];
    public function spu()
    {
        return $this->hasOne(MallSpu::class,'id','spu_id');
    }
    public function get_sku($data)
    {
        $spu_id = $data['spu_id'];
        $indexes = $data['indexes'];
        $list =  self::query()->select('id','spu_id','price','stock')->where('spu_id',$spu_id)->where('indexes',$indexes)->first();
        if(empty($list)) return [];
        return $list->toArray();
    }
}
