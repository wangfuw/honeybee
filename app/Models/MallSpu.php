<?php

namespace App\Models;


use Illuminate\Support\Facades\Redis;

class MallSpu extends Base
{
    protected $table = 'mall_spu';


    protected $fillable = [
        'id',
        'name',
        'sub_title',
        'description',
        'category_one',
        'category_two',
        'saleable',
        'logo',
        'banners',
        'details',
        'special_spec',
        'user_id',
        'game_zone',
        'score_zone',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected $casts = [
        "banners" => "array",
        "details" => "array",
//        "special_spec" => "array"
    ];

    //查询关联一条数据
    public function skp()
    {
        return $this->hasOne(MallSku::class, 'spu_id', 'id');
    }

    public function getSpecialSpecAttribute($value)
    {
        if ($value == '') return $value;
        return json_decode($value);
    }

    public function get_welfare($data = [])
    {
        $coin_price = Asaconfig::get_price();
        $page = $data['page'] ?? 1;
        $page_size = $data['page_size'] ?? 3;
        $keyword = $data['keyword'] ?? '';
        $score_zone = $data['score_zone']??'';
        $handler = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price');
        }])->select('id', 'name', 'score_zone', 'logo', 'user_id')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->where('saleable', 1);
        if(in_array($score_zone,[1,2])){
            $handler->where('score_zone', $data['score_zone']);
        }else if ($score_zone>=3){
            $handler->where('score_zone','>=',$data['score_zone']);
        }else{
            $handler->where('score_zone','>',0);
        }

       $list = $handler->where('game_zone', 1)->forPage($page, $page_size)->get()->map(function ($item, $items) use ($coin_price) {
                $item->prcie = $item->skp->price;
                $item->coin_num = bcdiv($item->skp->price, $coin_price, 2);
                unset($item->skp);
                return $item;
            });
        return collect([])->merge($list);
    }

    public function get_preferred($data = [])
    {
        $coin_price = Asaconfig::get_price();
        $page = $data['page'] ?? 1;
        $page_size = $data['page_size'] ?? 3;
        $keyword = $data['keyword'] ?? '';
        $score_zone = $data['score_zone']??'';
        $handler = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price');
        }])->select('id', 'name', 'score_zone', 'logo')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->where('user_id', 0)
            ->where('saleable', 1);
        if(in_array($score_zone,[1,2])){
            $handler->where('score_zone', $data['score_zone']);
        }else if ($score_zone>=3){
            $handler->where('score_zone','>=',$data['score_zone']);
        }else{
            $handler->where('score_zone','>',0);
        }
       $list = $handler->where('game_zone', 2)->forPage($page, $page_size)
        ->get()->map(function ($item, $items) use ($coin_price) {
            $item->prcie = $item->skp->price;
            $item->coin_num = bcdiv($item->skp->price, $coin_price, 2);
            unset($item->skp);
            return $item;
        });
        return collect([])->merge($list);
    }

    public function get_happiness($data = [])
    {
        $page = $data['page'] ?? 1;
        $page_size = $data['page_size'] ?? 3;
        $keyword = $data['keyword'] ?? '';
        $coin_price = Asaconfig::get_price();
        $list = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price');
        }])->select('id', 'name', 'score_zone', 'logo', 'user_id')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->where('saleable', 1)
            ->where('user_id', 0)
            ->where('game_zone', 3)->forPage($page, $page_size)->get()->map(function ($item, $items) use ($coin_price) {
                $item->prcie = $item->skp->price;
                $item->coin_num = bcdiv($item->skp->prcie, $coin_price, 2);
                unset($item->skp);
                return $item;
            });
        return collect([])->merge($list);
    }

    public function get_consume($data = [])
    {
        $coin_price = Asaconfig::get_price();
        $page = $data['page'] ?? 1;
        $page_size = $data['page_size'] ?? 3;
        $keyword = $data['keyword'] ?? '';
        $list = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price');
        }])->select('id', 'name', 'score_zone', 'logo', 'user_id')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->where('saleable', 1)
            ->where('user_id', 0)
            ->where('game_zone', 4)->forPage($page, $page_size)->get()->map(function ($item, $items) use ($coin_price) {
                $item->prcie = $item->skp->price;
                $item->coin_num = bcdiv($item->skp->price, $coin_price, 2);
                unset($item->skp);
                return $item;
            });
        return collect([])->merge($list);
    }

    public function get_search_spu($params, $user_id)
    {
        $coin_price = Asaconfig::get_price();
        $keyword = $params['keyword'] ?? '';
        $page = $params['page'] ?? 1;
        $page_size = $params['page_size'] ?? 6;
        $category_id = $params['category_id'] ?? 0;
        $store_id = $params['store_id'] ?? '';
        if ($keyword) {
            add_keyword($keyword, $user_id);
        }
        $list = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price');
        }])->select('id', 'name', 'score_zone', 'logo', 'user_id as store_id', 'game_zone', 'score_zone')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->when($category_id, function ($query) use ($category_id) {
                return $query->where('category_one', $category_id);
            })->when($store_id, function ($query) use ($store_id) {
                return $query->where('user_id', $store_id);
            })->where('saleable', 1)->forPage($page, $page_size)
            ->get()->map(function ($item, $items) use ($coin_price) {
                $item->price = $item->skp->price;
                $item->coin_num = bcdiv($item->skp->price, $coin_price, 2);
                unset($item->skp);
                return $item;
            });
        return collect([])->merge($list)->toArray();
    }

    //商品详情
    public function getInfo($params)
    {
        $coin_price = Asaconfig::get_price();
        $id = $params["id"];
        $info = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price', 'stock', 'indexes', 'id');
        }])->select('id', 'name', 'score_zone', 'logo', 'user_id as store_id', 'details', 'banners', 'special_spec', 'fee', 'game_zone')
            ->where('id', $id)->first();
        if (empty($info)) return [];
        $info->price = $info->skp->price;
        $info->coin_num = bcdiv($info->skp->price, $coin_price, 2);
        $info->stock = $info->skp->stock;
        $info->indexes = $info->skp->indexes;
        $info->sku_id = $info->skp->id;
        $info->spu_id = $info->skp->spu_id;
        unset($info->skp);
        return $info->toArray();
    }

    public function get_category($store_id)
    {
        $cates = self::query()->where('user_id', $store_id)->pluck('category_one');
        if (empty($cates)) return [];
        $cates = array_unique($cates->toArray());
        $cate_names = MallCategory::get_first();
        $arr = [];
        foreach ($cates as $key => $value) {
            $arr[$key]['category_id'] = $value;
            $arr[$key]['category_name'] = $cate_names[$value];
        }
        array_unshift($arr, ['category_id' => '', 'category_name' => '全部']);
        return $arr;
    }
}
