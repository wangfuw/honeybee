<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\BaseController;
use App\Models\Area;
use Illuminate\Support\Facades\Redis;

class AreaController extends BaseController
{
    public function get_area()
    {
        $list = unserialize(Redis::get('AREA'));
        if(empty($list)){
            $list = Area::with('allChildren')->first()->toArray();
            Redis::set('AREA',serialize($list));
        }
        return $this->success('请求成功',$list);
    }

    public function get_info(){
        $list =  unserialize(Redis::get('AREZ'));
        return $this->success('请求成功',$list);
    }
}
