<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Base
{
    use HasFactory;

    protected $table = 'store';

    protected $casts = [
        'images' => 'array'
    ];

    protected $fillable = [
        'id','user_id','store_name','business_type','desc','mobile','images',
        'store_image','sale_volume','stock','class_num','star_level','master','on_line','type',
        'area','address','status','created_at','updated_at'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function get_info($user_id)
    {

        if (!self::query()->where('user_id', $user_id)->exists()) {
            return [];
        } else {
            return self::query()->select('id', 'store_name', 'business_type', 'mobile', 'store_image', 'master', 'images', 'area', 'address', 'on_line', 'type')->where('user_id', $user_id)->first()->toArray();
        }
    }

    public function spu()
    {
        return $this->hasMany('MallSpu','store_id','id');
    }

    public function get_store_info($store_id)
    {
        if(self::query()->select('id','store_name','store_image')->where('id',$store_id)->exists()){
            return self::query()->select('id','store_name','store_image')->where('id',$store_id)->first()->toArray();
        }else{
            return  [];
        }

    }


}
