<?php

namespace App\Models;


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
        return $this->hasOne('MallSku','spu_id','id');
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

    public function get_search_spu($params,$asac_price)
    {
        $keyword = $params['keyword']??'';
        $page = $params['page']??1;
        $page_size = $params['page_size']??6;
        $list = $this->with(['skp'=>function($query){
            return $query->select('spu_id','price');
        }])->select('id','name','score_zone','logo','user_id')
            ->when($keyword,function ($query) use ($keyword){
                return $query->where('name','like','%'.$keyword.'%');
            })
            ->where('saleable',1)
            ->get()->forPage($page,$page_size)->map(function ($item,$items) use($asac_price){
                $item->price = $item->skp['price'];
                $item->asac_price = bcdiv($item->skp['price'] , $asac_price,2);
            });
        return collect([])->merge($list);
    }

    public function getInfo($params)
    {
        $id = $params["id"];
        return  $this->with(['skp'=>function($query){
            return $query->select('spu_id','price');
        }])->select('id','name','score_zone','logo','user_id','details','banners')
            ->where('id',$id)->first()->toArray();

    }
}
