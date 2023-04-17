<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use App\Models\User;
use App\Validate\Admin\NoticeValidate;
use Illuminate\Http\Request;

class UserController extends AdminBaseController
{
    public function userList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("id")) {
            $condition[] = ["id", "=", $request->id];
        }
        if ($request->filled("phone")) {
            $condition[] = ["phone", "=", $request->phone];
        }
        if ($request->filled("nickname")) {
            $condition[] = ["nickname", "like", "%$request->nickname%"];
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }

        $data = User::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function teamTree(Request $request)
    {
        if ($request->filled("id")) {
            $user = User::find($request->id);
        } else {
            $user = User::where("phone", $request->input("phone", ""))->first();
        }
        if (!$user) {
            return $this->executeSuccess("请求", []);
        }
        $subs = User::where("master_pos", "like", "%," . $user->id . ",%")->get()->toArray();
        $data = [];
        $data["name"] = $user->phone;
        $data["desc"] = $user->id;
        $data["children"] = $this->getTeam($subs, $user->id);

        return $this->executeSuccess("请求", $data);
    }

    private function getTeam($users, $p_id)
    {
        $tree = [];
        foreach ($users as $u) {
            if ($u["master_id"] == $p_id) {
                $child = $this->getTeam($users, $u["id"]);
                $data = ["name" => $u["phone"], "desc" => $u["id"]];
                if ($child) {
                    $data["children"] = $child;
                }
                array_push($tree, $data);
            }
        }
        return $tree;
    }
}
