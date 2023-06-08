<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PayController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['to_pey']]);
    }
    //发起支付
    public function to_pey(){

        return $this->success('请求成功',[]);
    }
}
