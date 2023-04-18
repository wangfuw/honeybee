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
}
