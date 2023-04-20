<?php

namespace App\Http\Controllers\Admin;

use App\Models\Score;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScoreController extends AdminBaseController
{

    public function scoreTypes()
    {
        $ts = [];
        foreach (Score::F_TYPES as $k => $v) {
            $ts[] = ["id" => $k, "name" => $v];
        }
        return $this->executeSuccess("请求", $ts);
    }

    public function scoreList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->id) {
            $condition[] = ["user_id", "=", $request->id];
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $condition[] = ["user_id", "=", $user->id];
            } else {
                $condition[] = ["score.id", "=", "-1"];
            }
        }
        if ($request->flag) {
            $condition[] = ["score.flag", "=", $request->flag];
        }

        if ($request->type) {
            $condition[] = ["score.type", "=", $request->type];
        }

        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["score.created_at", ">=", strtotime($start)];
            $condition[] = ["score.created_at", "<", strtotime($end)];
        }

        $db = Score::join("users", "users.id", "=", "score.user_id")
            ->where($condition);
        if ($request->f_type) {
            $db = $db->whereIn("score.f_type", $request->f_type);
        }
        $data = $db->orderByDesc("score.id")
            ->select("score.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }
}
