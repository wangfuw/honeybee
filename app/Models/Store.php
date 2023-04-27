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
        'area','address','status','created_at','updated_at','longitude','latitude'
    ];

    protected $hidden = [
        'deleted_at'
    ];

    public function get_info($user_id)
    {

        if (!self::query()->where('user_id', $user_id)->exists()) {
            return [];
        } else {
            $list = self::query()->select('id', 'store_name', 'business_type', 'mobile', 'store_image','note', 'master', 'images', 'area', 'address', 'on_line', 'type')->where('user_id', $user_id)->first();
            $list->area_china = city_name((string)$list->area);
            $list->business = MallCategory::query()->where('id',$list->business_type)->value('name')??'';
            return $list->toArray();
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

    public function get_near_store($data){
        $page = $data['page']??1;
        $page_size = $data['page_size']??5;
        $longitude = $data['longitude'];
        $latitude  = $data['latitude'];
        $list = self::query()->where('on_line',2)->get();
        if(!$list) return [];
        $new = [];
        foreach ($list as $l){
            if(getdistance($longitude,$latitude,$l->longitude,$l->latitude) < 5000){
                $distance = getdistance($longitude,$latitude,$l->longitude,$l->latitude);
                $l->distance = floor($distance*100)/100;
                $l->door_phote = $l->images['door_phote'];
                array_push($new,$l);
            }else{
                continue;
            }
        }
        return collect([])->merge($new)->toArray();
    }

}
