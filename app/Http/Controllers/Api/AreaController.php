<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Area;
use Illuminate\Support\Facades\Redis;

class AreaController extends BaseController
{
    public function get_area()
    {
        $list = unserialize(Redis::get('AREA'));
        if(empty($list)){
            $list = Area::with('children')->first()->toArray();
            Redis::set('AREA',serialize($list));
        }
        return $this->success('请求成功',$list);
    }

}
