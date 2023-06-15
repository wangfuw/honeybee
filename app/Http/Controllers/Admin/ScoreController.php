<?php

namespace App\Http\Controllers\Admin;

use App\Models\AsacNode;
use App\Models\AsacTrade;
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

        if($request->help_phone){
            $condition[] = ["score.help_phone","=",$request->help_phone];
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

    public function asacLogType()
    {
        $ts = [];
        foreach (AsacTrade::typeData as $k => $v) {
            $ts[] = ["id" => $k, "name" => $v];
        }
        return $this->executeSuccess("请求", $ts);
    }

    public function asacLog(Request  $request)
    {
        $size = $request->size ?? $this->size;
        $wallet_address = "";
        if ($request->user_id) {
            //获取我的地址
            $wallet_address = AsacNode::query()->where('user_id', $request->user_id)->value('wallet_address');
        }
        if ($request->address) {
            if(strlen($request->address) < 12){
                $user_id = User::query()->where('phone',$request->address)->value('id');
                $wallet_address = AsacNode::query()->where('user_id', $user_id)->value('wallet_address');
            }else{
                $wallet_address = $request->address;
            }
        }

        $condition = [];
        if($request->type){
            $condition[] = ["type","=",$request->type];
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }

        if ($wallet_address) {
            $data = AsacTrade::where($condition)
                ->where(function ($query) use ($wallet_address) {
                    $query->where("to_address", $wallet_address)
                        ->orWhere("from_address",$wallet_address);
                })
                ->orderByDesc("id")
                ->paginate($size);
        } else {
            $data = AsacTrade::where($condition)
                ->orderByDesc("id")
                ->paginate($size);
        }
        if(!empty($data)){
            foreach ($data as &$value){
                $user_id_from = AsacNode::query()->where('wallet_address',$value['from_address'])->value('user_id');
                $user_id_to = AsacNode::query()->where('wallet_address',$value['to_address'])->value('user_id');
                $value['from_phone'] = User::query()->where('id',$user_id_from)->value('phone');
                $value['to_phone'] = User::query()->where('id',$user_id_to)->value('phone');
            }
        }
        return $this->executeSuccess("request", $data);
    }
}
