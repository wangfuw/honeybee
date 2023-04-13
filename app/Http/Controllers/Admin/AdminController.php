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
            $users = AdminUser::where("group_id", $v["id"])->select('id', 'username as name', 'status')->get()->toArray();
            foreach ($users as $m => &$n) {
                $n["index"] = $v["id"] . "_" . $n["id"];
                $n["group"] = 2;
            }
            $v["children"] = $users;
        }
        return $this->success("请求成功", $groups);
    }

    public function groups(Request  $request){
        $data = AdminGroup::orderByDesc("id")->select("id","name")->get();
        return $this->success('请求',$data);
    }
}
