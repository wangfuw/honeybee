<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use App\Validate\Admin\NoticeValidate;
use Illuminate\Http\Request;

class NoticeController extends AdminBaseController
{
    private $validate;

    public function __construct(NoticeValidate $validate)
    {
        $this->validate = $validate;
    }

    public function noticeList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("type")) {
            $condition["type"] = $request->type;
        }
        $notices = Notice::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $notices);
    }

    public function delNotice(Request $request)
    {
        if (!$request->id) {
            return $this->error("id");
        }
        try {
            Notice::destroy($request->id);
            return $this->executeSuccess("删除");
        } catch (\Exception $exception) {
            return $this->executeFail("删除");
        }
    }

    public function addNotice(Request $request)
    {
        $param = $request->only("title", "text", "type");
        if (!$this->validate->scene('add')->check($param)) {
            return $this->fail($this->validate->getError());
        }
        try {
            Notice::create($param);
            return $this->executeSuccess("添加");
        } catch (\Exception $exception) {
            return $this->executeFail("添加");
        }
    }

    public function editNotice(Request $request)
    {
        $param = $request->only("title", "text", "id", "type");
        if (!$this->validate->scene('modify')->check($param)) {
            return $this->fail($this->validate->getError());
        }
        try {
            Notice::where("id", $param["id"])->update(["title" => $param["title"], "text" => $param["text"]]);
            return $this->executeSuccess("修改");
        } catch (\Exception $exception) {
            return $this->executeFail("修改");
        }
    }
}
