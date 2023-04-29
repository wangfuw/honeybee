<?php

namespace App\Http\Controllers\Api;

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
            $url =  "http://".$_SERVER['SERVER_NAME']."storage/app/public/st".$version.".wgt";
        }else{
            $url = "";
        }
        return $this->success("请求成功",['wgt_url'=>$url]);
    }
}
