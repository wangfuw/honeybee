<?php

namespace App\Http\Controllers\Admin;

use App\Models\Notice;
use App\Models\User;
use App\Models\UserIdentity;
use App\Validate\Admin\NoticeValidate;
use Illuminate\Http\Request;
use PHPUnit\Exception;

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

    public function banUser(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("ID");
        }
        if (!$request->filled("is_ban")) {
            return $this->error("状态");
        }

        try {
            User::where("id", $request->id)->update(["is_ban" => $request->is_ban]);
            return $this->executeSuccess("操作");
        } catch (\Exception $exception) {
            return $this->executeFail("操作");
        }
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

    public function userAuthList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("id")) {
            $condition["user_id"] = $request->id;
        }
        if ($request->filled("phone")) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition["user_id"] = $request->id;
            } else {
                $condition["user_id"] = -1;
            }
        }
        if ($request->filled("status")) {
            $condition["status"] = $request->status;
        }
        $data = UserIdentity::join('users', 'users.id', '=', 'user_identity.user_id')
            ->where($condition)
            ->orderByDesc("user_identity.id")
            ->select(
                "users.id",
                "users.phone",
                "username",
                "id_card",
                "address",
                "front_image",
                "back_image",
                "status",
                "user_identity.created_at"
            )->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editUserAuth(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("id");
        }
        $flag = $request->input("flag", 1);
        try {
            UserIdentity::where("id", $request->id)->update(["status" => $flag]);
            return $this->executeSuccess("操作");
        } catch (\Exception $exception) {
            return $this->executeFail("操作");
        }
    }
}
