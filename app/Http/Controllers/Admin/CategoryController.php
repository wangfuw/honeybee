<?php

namespace App\Http\Controllers\Admin;

use App\Models\MallCategory;
use Illuminate\Http\Request;

class CategoryController extends AdminBaseController
{

    public function categoryList(Request $request)
    {
        $cate = MallCategory::where(["parent_id" => 0, "is_delete" => 0])->get()->toArray();
        foreach ($cate as $k => &$v) {
            $v["children"] = MallCategory::where(["parent_id" => $v["id"], "is_delete" => 0])->get()->toArray();
        }
        return $this->executeSuccess("请求", $cate);
    }

    public function addCategory(Request $request)
    {
        if (!$request->filled("name")) {
            return $this->error("分类名");
        }

        $parent_id = $request->input("parent_id");
        if($parent_id == null){
            $parent_id = 0;
        }
        $cate = MallCategory::where("name", $request->name)->first();
        if ($cate) {
            return $this->fail("分类已经存在了");
        }
        try {
            MallCategory::create([
                "parent_id" => $parent_id,
                "name" => $request->name,
            ]);
            return $this->executeSuccess("添加");
        } catch (\Exception $exception) {
            var_dump($exception);
            return $this->executeFail("添加");
        }
    }

    public function editCategory(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("id");
        }
        if (!$request->filled("parent_id")) {
            return $this->error("父分类id");
        }
        if (!$request->filled("name")) {
            return $this->error("分类名");
        }
        try {
            MallCategory::where("id", $request->id)->update(["parent_id" => $request->parent_id, "name" => $request->name]);
            return $this->executeSuccess("修改");
        } catch (\Exception $exception) {
            return $this->fail("修改");
        }
    }

    public function delCategory(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("ID");
        }
        try {
            MallCategory::where("id", $request->id)->update(["is_delete" => 1]);
            return $this->executeSuccess("删除");
        } catch (\Exception $exception) {
            return $this->executeFail("删除");
        }
    }
}
