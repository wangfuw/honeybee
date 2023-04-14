<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends AdminBaseController
{

    public function bannerList()
    {
        $banners = Banner::all();
        return$this->executeSuccess("请求", $banners);
    }

    public function addBanner(Request $request){
        $filePath = $request->file_path;
        if(!$filePath){
            return $this->error("文件路径");
        }
        try {
            Banner::insert(["path"=>$filePath]);
            return $this->executeSuccess("添加");
        }catch (\Exception $exception){
            return $this->executeFail("添加");
        }
    }

    public function delBanner(Request $request){
        $id = $request->id;
        if(!$id){
            return $this->error("ID");
        }
        try{
            Banner::destroy($id);
            return $this->executeSuccess("删除");
        }catch (\Exception $exception){
            return $this->executeFail("删除");
        }
    }
}
