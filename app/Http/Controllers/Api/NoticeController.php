<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\BaseController;
use App\Http\Controllers\Controller;
use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends BaseController
{
    protected $model;

    public function __construct(Notice $model)
    {
        $this->model = $model;
    }

    public function getNotices()
    {
        $data = $this->model->getNotices();
        return $this->successPaginate($data);
    }
}
