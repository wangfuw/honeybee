<?php

namespace App\Http\Controllers\Admin;

use App\Models\Banner;

class BannerController extends AdminBaseController
{

    public function bannerList()
    {
        $banners = Banner::all();
        return$this->executeSuccess("请求", $banners);
    }
}
