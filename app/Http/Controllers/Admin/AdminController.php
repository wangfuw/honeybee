<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminGroup;
use App\Models\AdminUser;
use Illuminate\Http\Request;

class AdminController extends AdminBaseController
{

    public function admins(Request $request)
    {
        $groups = AdminGroup::orderByDesc("id")->select("id", "name")->get()->toArray();
        foreach ($groups as $k => &$v) {
            $v["group"] = 1;
            $v["index"] = $v["id"];
            $users = AdminUser::where("group_id", $v["id"])->select('id', 'username as name', 'status');
            foreach ($users as $m => &$n) {
                $n["index"] = $v["id"] . "_" . $n["id"];
                $n["group"] = 2;
            }
            $v["children"] = $users;
        }
        return $this->success("请求成功", $groups);
    }
}
