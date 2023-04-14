<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Models\Banner;

class BannerController extends BaseController
{

    protected $model;

    public function __construct(Banner $model)
    {
        $this->model = $model;
    }

    public function getBanners()
    {
        $list = $this->model->getBanners();
        return $this->success('success',$list);
    }
}
