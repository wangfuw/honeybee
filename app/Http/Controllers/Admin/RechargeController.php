<?php

namespace App\Http\Controllers\Admin;

use App\Models\AsacNode;
use App\Models\AsacTrade;
use App\Models\Recharge;
use App\Models\User;
use App\Models\Withdraw;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RechargeController extends AdminBaseController
{

    const APPID = "1120122425175191552";
    const PUBLIC_KEY ="MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC5GnfqIyYaaat8MLYofU49fjghLR2X5j4Dy/FQMu+IAWVKtum/yUOFwTWI1lFD6qGLTFAPdFwaVhpLeZqnpIyuwcgnTGfEONKYUg+NSUMwMe2QhE/KqoyexL1xmB3O5V99xGxUPrGqFKU092AjLzYiWhvWNuUOFvSyVvmXduGgHQIDAQAB";
    const PRIVATE_KEY =
        <<<EOF
-----BEGIN PRIVATE KEY-----
MIICeAIBADANBgkqhkiG9w0BAQEFAASCAmIwggJeAgEAAoGBALkad+ojJhppq3ww
tih9Tj1+OCEtHZfmPgPL8VAy74gBZUq26b/JQ4XBNYjWUUPqoYtMUA90XBpWGkt5
mqekjK7ByCdMZ8Q40phSD41JQzAx7ZCET8qqjJ7EvXGYHc7lX33EbFQ+saoUpTT3
YCMvNiJaG9Y25Q4W9LJW+Zd24aAdAgMBAAECgYB4/UEGTKU6PHm3ekuGmakLbrYX
kVq3j+pXJvX7et+wYWEo/fg5wL8e7VQlthh2MSYYW/A0udT97evQC5M4IslE0Ie8
Ar7M/Cwi2LozAxsZK3W4zR+N4ZoKet/zzf2wzArI+yGmVHZc5Y6sIoNjKZDEmCxd
nETZP6yJx1cvju3aDQJBAN5UTYxxpbVxzjFDT17vww1KjN3nPbfHVc/VR/tXYxDY
9QXJ1BlfriL+OHasWhqTtvqLtXGBjZ/A8pZrBmyk/5sCQQDVIusen+crFflgVfzN
rnuxEh8Cfh5SsbTne1zhzvr122IfEWi2cNSy+Jq2ygrlaVEBxIcyxfMe6vmPZDJB
98anAkEAve2iueG0QAbSsH7h5SZJqKcRI9gRf1gIVJ3M+kgy1wegeatrR6nXJwmp
zqd56c5auDp1bFvSUrEQC7OuL03dFQJBAIQC7L47LGNzaNJScBK1T8eNAcf5da6i
gvodXpo+KRK+nze/AKx/lj6D3M/6tGUDpjkCEPtRwBQWVhyKYtaZMWECQQCCgkkE
KipOi9vkYPAR3U1cW6hUCQYZdLP4nBTq0sD3+OVZ7QSEfMqYv65kzxT5zG+JAwzT
BsdxwxYuy+e+4hXm
-----END PRIVATE KEY-----
EOF;
    private $url = "http://4619p19v09.qicp.vip/app/token/transferAccounts";
    public function rechargeList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->id) {
            $an = AsacNode::where("user_id", $request->id)->first();
            if ($an) {
                $condition[] = ["from_address", "=", $an->wallet_address];
            } else {
                $condition[] = ["id", "=", -1];
            }
        }
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if ($user) {
                $an = AsacNode::where("user_id", $request->id)->first();
                if ($an) {
                    $condition[] = ["from_address", "=", $an->wallet_address];
                } else {
                    $condition[] = ["id", "=", -1];
                }
            } else {
                $condition[] = ["id", "=", -1];
            }
        }
        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["created_at", ">=", strtotime($start)];
            $condition[] = ["created_at", "<", strtotime($end)];
        }
        $condition[] = ["type", "=", 3];
        $data = AsacTrade::where($condition)
            ->orderByDesc("id")
            ->paginate($size)->toArray();
        foreach ($data["data"] as $k => &$v) {
            $an = AsacNode::where("wallet_address", $v["from_address"])->first();
            $user = User::find($an->user_id);
            $v["user"] = $user->toArray();
        }
        return $this->executeSuccess("请求", $data);
    }

    public function withdrawList(Request $request)
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
                $condition[] = ["withdraw.id", "=", "-1"];
            }
        }

        if ($request->filled("status")) {
            $condition[] = ["withdraw.status", "=", $request->status];
        }

        if ($request->filled("create_at")) {
            $start = $request->input("create_at.0");
            $end = $request->input("create_at.1");
            $condition[] = ["withdraw.created_at", ">=", strtotime($start)];
            $condition[] = ["withdraw.created_at", "<", strtotime($end)];
        }
        $data = Withdraw::join("users", "users.id", "=", "withdraw.user_id")
            ->where($condition)
            ->orderBy("status")
            ->select("withdraw.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editWithdraw(Request $request)
    {
        $id = $request->id;
        if (!$id) {
            return $this->error("ID");
        }
        $flag = $request->flag;
        if ($flag != 1 && $flag != 2) {
            return $this->fail("操作");
        }
        $withdraw = Withdraw::query()->where('id',$request->id)->first();
        if (!$withdraw) {
            return $this->error("ID");
        }
        $user = User::find($withdraw->user_id);
        if ($flag == 2) {
                $user->coin_num += $withdraw->amount;
                $withdraw->status = $flag;
                $withdraw->err = $request->err;
                DB::beginTransaction();
                try {
                    $user->save();
                    $withdraw->save();
                    DB::commit();
                    return $this->executeSuccess("驳回");
                } catch (\Exception $exception) {
                    DB::rollBack();
                    return $this->executeFail("驳回");
                }
        }
        $user_address = AsacNode::query()->where('user_id',$user->id)->first();
        if ($flag == 1) {
            // 接三方 ,写 币的交易记录
            $data = [
                "address"=>$withdraw->withdraw_address,
                "amount"=> $withdraw->actual,
                "appId"=> self::APPID,
                "publicKey"=> self::PUBLIC_KEY,
                "timestamp"=>time(),
            ];
            $str = formatBizQueryParaMap($data,false);
            $data["sign"] = rsaSign($str,self::PRIVATE_KEY);
            $ret = post_url($this->url,$data);
            if($ret["orderSn"]){
                AsacTrade::query()->create([
                    'from_address'=>$user_address->wallet_address,
                    'to_address' => $withdraw->withdraw_address,
                    'num'        => $withdraw->amonut,
                    'type'       => AsacTrade::WITHDRAW,
                    'trade_hash' => rand_str_pay(64),
                ]);
                return $this->executeSuccess("提现成功");
            }else{
                return $this->executeFail("提现失败,服务访问错误");
            }
        }


    }
}
