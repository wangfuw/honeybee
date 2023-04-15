<?php

namespace App\Http\Controllers\Admin;

use App\Models\News;
use App\Validate\Admin\NoticeValidate;
use Illuminate\Http\Request;

class NewsController extends AdminBaseController
{
    private $validate;

    public function __construct(NoticeValidate $validate)
    {
        $this->validate = $validate;
    }

    public function newsList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $type = $request->type;
        $condition = [];
        if ($type) {
            $condition["type"] = $type;
        }
        $notices = News::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $notices);
    }

    public function addNews(Request $request)
    {
        $param = $request->only("title", "text", 'face', 'type');
        if (!$this->validate->scene('addNews')->check($param)) {
            return $this->fail($this->validate->getError());
        }
        try {
            News::create($param);
            return $this->executeSuccess("添加");
        } catch (\Exception $exception) {
            return $this->executeFail("添加");
        }
    }

    public function editNews(Request $request)
    {
        $param = $request->only("title", "text", "id", "face", "type");
        if (!$this->validate->scene('modifyNews')->check($param)) {
            return $this->fail($this->validate->getError());
        }
        try {
            News::where("id", $param["id"])->update(["title" => $param["title"], "text" => $param["text"], "face" => $param["face"], "type" => $param["type"]]);
            return $this->executeSuccess("修改");
        } catch (\Exception $exception) {
            return $this->executeFail("修改");
        }
    }

    public function delNews(Request  $request){
        if (!$request->id) {
            return $this->error("id");
        }
        try {
            News::destroy($request->id);
            return $this->executeSuccess("删除");
        } catch (\Exception $exception) {
            return $this->executeFail("删除");
        }
    }
}
