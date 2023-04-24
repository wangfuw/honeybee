<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Asaconfig extends Base
{
    use HasFactory;

    protected $table = 'asac_config';

    protected $fillable = [
        'id','name','contract_address','destruction_address','accuracy','number','price','flow_num',
        'precat_num','flux','dest_num','owner_num','trans_num','old_price','last_price','username',
        'ip','created_at','updated_at'
    ];

    protected $hidden = [];


    public static function get_price()
    {
        return self::query()->value('last_price');
    }

}
