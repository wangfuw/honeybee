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
        "special_spec" => "array"
    ];

    //查询关联一条数据
    public function skp()
    {
        return $this->hasOne(MallSku::class,'spu_id','id');
    }

    public function get_welfare($data = []){
        $page = $data['page']??1;
        $page_size = $data['page_size']??3;
        $keyword = $data['keyword']??'';
        $list = $this->with(['skp'=>function($query){
            return $query->select('spu_id','price');
        }])->select('id','name','score_zone','logo','user_id')
            ->when($keyword,function ($query) use ($keyword){
                return $query->where('name','like','%'.$keyword.'%');
            })
            ->where('saleable',1)->where('score_zone',$data['score_zone'])
            ->where('game_zone',2)->get()->forPage($page,$page_size);
        return collect([])->merge($list);
    }

    public function get_preferred($data = []){
        $page = $data['page']??1;
        $page_size = $data['page_size']??3;
        $keyword = $data['keyword']??'';
        $list = $this->with(['skp'=>function($query){
            return $query->select('spu_id','price');
        }])->select('id','name','score_zone','logo')
            ->when($keyword,function ($query) use ($keyword){
                return $query->where('name','like','%'.$keyword.'%');
            })
            ->where('user_id',0)
            ->where('saleable',1)
            ->where('score_zone',$data['score_zone'])
            ->where('game_zone',2)
            ->get()->forPage($page,$page_size);
        return collect([])->merge($list);
    }

    public function get_happiness($data = []){
        $page = $data['page']??1;
        $page_size = $data['page_size']??3;
        $keyword = $data['keyword']??'';
        $list = $this->with(['skp'=>function($query){
            return $query->select('spu_id','price');
        }])->select('id','name','score_zone','logo','user_id')
            ->when($keyword,function ($query) use ($keyword){
                return $query->where('name','like','%'.$keyword.'%');
            })
            ->where('saleable',1)
            ->where('user_id',0)
            ->where('game_zone',3)->get()->forPage($page,$page_size);
        return collect([])->merge($list);
    }

    public function get_consume($data = []){
        $page = $data['page']??1;
        $page_size = $data['page_size']??3;
        $keyword = $data['keyword']??'';
        $list = $this->with(['skp'=>function($query){
            return $query->select('spu_id','price');
        }])->select('id','name','score_zone','logo','user_id')
            ->when($keyword,function ($query) use ($keyword){
                return $query->where('name','like','%'.$keyword.'%');
            })
            ->where('saleable',1)
            ->where('user_id',0)
            ->where('game_zone',4)->get()->forPage($page,$page_size);
        return collect([])->merge($list);
    }

    public function get_search_spu($params,$user_id)
    {
        $keyword = $params['keyword'] ?? '';
        $page = $params['page'] ?? 1;
        $page_size = $params['page_size'] ?? 6;
        $category_id = $params['category_id']??0;
        $store_id    = $params['store_id']??'';
        if($keyword){
            add_keyword($keyword,$user_id);
        }
        $list = $this->with(['skp' => function ($query) {
            return $query->select('spu_id', 'price');
        }])->select('id', 'name', 'score_zone', 'logo', 'user_id as store_id')
            ->when($keyword, function ($query) use ($keyword) {
                return $query->where('name', 'like', '%' . $keyword . '%');
            })
            ->when($category_id,function ($query) use($category_id){
                return $query->where('category_one',$category_id);
            })->when($store_id,function ($query) use($store_id){
                return $query->where('user_id',$store_id);
            })->where('saleable', 1)
            ->get()->map(function ($item,$items){
                $item->price = $item->skp->price;
                unset($item->skp);
                return $item;
            })->forPage($page, $page_size);
        return collect([])->merge($list)->toArray();
    }

    //商品详情
    public function getInfo($params)
    {
        $id = $params["id"];
        return  $this->with(['skp'=>function($query){
            return $query->select('spu_id','price','stock','indexes');
        }])->select('id','name','score_zone','logo','user_id as store_id','details','banners','special_spec','fee')
            ->where('id',$id)->first()->toArray();
    }

    public function get_category($store_id)
    {
        $cates = self::query()->where('user_id',$store_id)->pluck('category_one');
        if(empty($cates)) return [];
        $cates = array_unique($cates->toArray());
        $cate_names = MallCategory::get_first();
        $arr = [];
        foreach ($cates as $key=>$value){
            $arr[$key]['category_id'] = $value;
            $arr[$key]['category_name'] = $cate_names[$value];
        }
        array_unshift($arr,['category_id'=>'','category_name'=>'全部']);
        return  $arr;
    }
}
