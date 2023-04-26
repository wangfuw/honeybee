<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
class ShopCart extends Base
{
    use HasFactory,SoftDeletes;

    protected $table = 'shop_cart';

    protected $fillable = ["id",'store_id','user_id','sku_id','spu_id','number','order_money','created_at','updated_at'];

    protected $hidden = ['deleted_at'];

    public function store()
    {
        return $this->hasOne(Store::class,'id','store_id');
    }

    public function sku()
    {
        return $this->hasOne(MallSku::class,'id','sku_id');
    }

    public function spu()
    {
        return $this->hasOne(MallSpu::class,'id','spu_id');
    }
    public  function shops($params,$user_id)
    {
        $page = $params['page']??1;
        $page_size = $params['page_size']??6;
        $user_id = $user_id;

        $list = $this->with(['store'=>function($query){
                return $query->select('id','store_name');
        },'sku'=>function($query){
            return $query->select('id','price','indexes');
        },'spu'=>function($query){
            return $query->select('id','name','logo','game_zone','score_zone','special_spec');
        }])->select('id','store_id','sku_id','spu_id','number','order_money')
            ->where('user_id',$user_id)->get();
        if(empty($list)) return [];
        return $list->map(function ($item,$items){
            $item->price = $item->sku->price;
            $item->logo  = $item->spu->logo;
            $item->game_zone = $item->spu->game_zone;
            $item->score_zone = $item->spu->score_zone;
            $item->special_spec = $item->spu->special_spec;
            $item->indexex = $item->sku->indexes;
            $item->name = $item->spu->name;
            if($item->store != null){
                $item->store_name = $item->store->name;
            }else{
                $item->store_name = '自营';
            }
            unset($item->spu);
            unset($item->sku);
            unset($item->store);
            return $item;
        })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }


}
