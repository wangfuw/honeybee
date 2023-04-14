<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use Illuminate\Http\Request;

class NoticeController extends AdminBaseController
{
    public function noticeList(Request $request)
    {
        $page = $request->page ?? $this->page;
        $size = $request->size ?? $this->size;
        $notices = Notice::orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $notices);
    }
}
