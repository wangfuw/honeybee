<?php

namespace App\Http\Controllers\Api;

use App\Common\Rsa;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DownController extends BaseController
{
    public function update(Request $request){
        $version_r = $request->version;
        $version = DB::table('down')->value('version');
        if($version_r != $version){
            $url =  "http://".$_SERVER['SERVER_NAME']."/storage/YYT_".$version.".wgt";
        }else{
            $url = "";
        }
        return $this->success("请求成功",['wgt_url'=>$url]);
    }

    public function download(){
        $down = DB::table("down")->first();
        return $this->success("请求成功",$down);
    }

    public function url_asac()
    {
        $url = env('INVITE_RUL',"http://register.yuyuantong.shop");
        return $this->success("请求成功",compact('url'));
    }
}
