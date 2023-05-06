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
        $last_price = Asaconfig::get_price();
        $page = $params['page']??1;
        $page_size = $params['page_size']??6;

        $list = $this->with(['store'=>function($query){
                return $query->select('id','store_name');
        },'sku'=>function($query){
            return $query->select('id','price','indexes');
        },'spu'=>function($query){
            return $query->select('id','name','logo','game_zone','score_zone','special_spec','fee');
        }])->select('id','store_id','sku_id','spu_id','number','order_money')
            ->where('user_id',$user_id)->get();
        if(empty($list)) return [];
        return $list->map(function ($item,$items) use($last_price){
            $item->price = $item->sku->price;
            $item->logo  = $item->spu->logo;
            $item->game_zone = $item->spu->game_zone;
            $item->score_zone = $item->spu->score_zone;
            $item->special_spec = $item->spu->special_spec;
            $item->indexex = $item->sku->indexes;
            $item->money = 0;
            $item->ticket_num = 0;
            if($item->game_zone == 3){
                $item->money = $item->order_money;
            }
            if($item->game_zone == 4){
                $item->ticket_num = $item->order_money;
            }
            $item->name = $item->spu->name;
            $item->fee = $item->spu->fee;
            $item->coin_num = bcdiv($item->order_money,$last_price,4);
            if($item->store != null){
                $item->store_name = $item->store->name;
            }else{
                $item->store_name = '源宇通自营';
            }
            unset($item->spu);
            unset($item->sku);
            unset($item->store);
            return $item;
        })->forPage($page,$page_size);
        return collect([])->merge($list)->toArray();
    }


}
