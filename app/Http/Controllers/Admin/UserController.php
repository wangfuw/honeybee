<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Models\Notice;
use App\Models\Score;
use App\Models\User;
use App\Models\UserIdentity;
use App\Validate\Admin\NoticeValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
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
        if ($request->filled("master_id")) {
            $condition[] = ["master_id", "=", $request->master_id];
        }
        if ($request->filled("master_phone")) {
            $user = User::where("phone", $request->phone)->first();
            if (!$user) {
                $condition[] = ["master_id", "=", $user->id];
            }
        }
        if ($request->filled("leader_id")) {
            $condition[] = ["master_pos", "like", "%,$request->leader_id,%"];
        }

        if ($request->filled("leader_phone")) {
            $user = User::where("phone", $request->leader_phone)->first();
            if (!$user) {
                $condition[] = ["master_pos", "like", "%,$user->id,%"];
            }
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

    public function editUser(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("ID");
        }
        if (!$request->filled("type")) {
            return $this->error("type");
        }
        if (!$request->filled("flag")) {
            return $this->error("flag");
        }
        if (!$request->filled("num") || $request->num <= 0) {
            return $this->error("数量");
        }
        $user = User::find($request->id);
        if (!$user) {
            return $this->error("ID");
        }
        if ($request->flag == 2) {
            if ($request->type == 1) {
                $num = min($user->green_score, $request->num);
                $user->green_score = $user->green_score - $num;
            } elseif ($request->type == 2) {
                $num = min($user->sale_score, $request->num);
                $user->sale_score = $user->sale_score - $num;
            } else if ($request->type == 3) {
                $num = min($user->luck_score, $request->num);
                $user->luck_score = $user->luck_score - $num;
            } else if ($request->type == 4) {
                $num = min($user->ticket_num, $request->num);
                $user->ticket_num = $user->ticket_num - $num;
            } else if ($request->type == 5) {
                $num = min($user->money, $request->num);
                $user->money = $user->money - $num;

            } else if ($request->type == 6) {
                $num = min($user->coin_num, $request->num);
                $user->coin_num = $user->coin_num - $num;
            } else {
                $num = min($user->new_freeze, $request->num);
                $user->freeze_money = $user->freeze_money - $num;
                $user->new_freeze = $user->new_freeze - $num;
            }
        } else {
            $num = $request->num;
            if ($request->type == 1) {
                $user->green_score = $user->green_score + $num;
            } elseif ($request->type == 2) {
                $user->sale_score = $user->sale_score + $num;
            } else if ($request->type == 3) {
                $user->luck_score = $user->luck_score + $num;
            } else if ($request->type == 4) {
                $user->ticket_num = $user->ticket_num + $num;
            } else if ($request->type == 5) {
                $user->money += $num;

            } else if ($request->type == 6) {
                $user->coin_num += $num;
            } else {
                $user->freeze_money += $num;
                $user->new_freeze += $num;
            }
        }
        if ($num > 0) {
            DB::beginTransaction();
            try {
                $user->save();
                if($num > 0){
                    if ($request->type <= 5 || $request->type >= 7) {
                        if($request->type <= 5){
                            $type = $request->type;
                        }else{
                            $type = $request->type-1;
                        }
                        Score::create([
                            "user_id" => $user->id,
                            "flag" => $request->flag,
                            "num" => $num,
                            "type" => $type,
                            "f_type" => $request->flag == 1 ? Score::BACK_ADD : Score::BACK_SUB,
                        ]);
                    }
                }
                DB::commit();
                return $this->executeSuccess("操作");
            } catch (\Exception $exception) {
                DB::rollBack();
                Log::error("SCORE:" . $exception->getMessage());
                return $this->executeFail("操作");
            }
        } else {
            return $this->executeSuccess("操作");
        }
    }

    public function areaList()
    {
        $list = Area::with('children')->first()->toArray();
        return $this->executeSuccess("请求", $list["children"]);
    }

    // 修改用户身份标识
    public function editIdentity(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        if (!$request->filled("identity") || !in_array($request->identity, [0, 1, 2])) {
            return $this->error("身份");
        }
        if (!$request->filled("identity_status") || !in_array($request->identity_status, [0, 1])) {
            return $this->error("身份状态");
        }
        $user = User::find($request->id);
        if (!$user) {
            return $this->error("ID");
        }
        if ($request->identity_area) {
            $user->identity_area_code = $request->identity_area[count($request->identity_area) - 1];
            $area = '';
            foreach ($request->identity_area as $v) {
                $a = Area::where("code", $v)->first();
                $area .= $a->name;
            }
            $user->identity_area = $area;
        }
        $user->identity = $request->identity;
        $user->identity_status = $request->identity_status;
        $user->save();
        return $this->executeSuccess("操作");
    }

    // 业绩查询
    public function performance(Request $request)
    {
        $condition = [];
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $flag = $request->input("flag", 1);
        if ($flag == 1) {
            $id = $request->input("id", 1);
            $users = User::where("master_pos", "like", "%,$id,%")->select("id")->get()->toArray();
        } else {
            $code = $request->area[count($request->area) - 1];
            $area = Area::where("code", $code)->first();
            if ($area->level == 3) {
                $users = UserIdentity::where("address_code", $code)->where("status", 1)->select("user_id")->get()->toArray();
            } else {
                $areas = Area::where("pcode", $area->code)->select("code")->get()->toArray();
                $users = UserIdentity::whereIn("address_code", $areas)->where("status", 1)->select("user_id")->get()->toArray();
            }
        }

        $green = Score::where($condition)->whereIn("user_id", $users)->where("type", 1)->where("f_type", Score::TRADE_HAVE)->sum("num");
        $sale = Score::where($condition)->whereIn("user_id", $users)->where("type", 2)->where("f_type", Score::TRADE_HAVE)->sum("num");
        $per = $green / 4 + $sale / 8;
        return $this->executeSuccess("请求", ["contribute" => $per]);
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
            ->orderBy("user_identity.status")
            ->select(
                "user_identity.id",
                "user_identity.user_id",
                "users.phone",
                "username",
                "id_card",
                "address_code",
                "front_image",
                "back_image",
                "status",
                "user_identity.created_at"
            )->paginate($size)->toArray();
        foreach ($data["data"] as $k => &$v) {
            $v["address"] = city_name($v["address_code"]);
        }
        return $this->executeSuccess("请求", $data);
    }

    public function editUserAuth(Request $request)
    {
        if (!$request->filled("id")) {
            return $this->error("id");
        }
        $ua = UserIdentity::where("id", $request->id)->first();
        if (!$ua) {
            return $this->error("id");
        }
        $flag = $request->input("flag", 1);
        if ($flag == 2) {
            if (!$request->filled("note")) {
                return $this->fail("驳回原因必填");
            }
            $ua->note = $request->note;
        }
        $ua->status = $flag;
        try {
            $ua->save();
            return $this->executeSuccess("操作");
        } catch (\Exception $exception) {
            var_dump($exception);
            return $this->executeFail("操作");
        }
    }


}
