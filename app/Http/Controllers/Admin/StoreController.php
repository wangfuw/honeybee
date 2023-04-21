<?php

namespace App\Http\Controllers\Admin;

use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StoreController extends AdminBaseController
{

    public function storeList(Request $request)
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
                $condition[] = ["store.id", "=", "-1"];
            }
        }
        $data = Store::join("users", "users.id", "=", "store.user_id")
            ->where($condition)
            ->orderBy("store.type")
            ->select("store.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function editStore(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = Store::find($request->id);
        if ($request->filled('status')) {
            $store->status = $request->status;
            $store->save();
            return $this->executeSuccess("操作");
        }
        if ($request->type) {
            $store->type = $request->type;
            if ($request->type == 1) {
                $user = User::find($store->user_id);
                DB::beginTransaction();
                try {
                    $store->save();
                    User::where("id", $store->user_id)->update(["is_shop" => 1]);
                    DB::commit();
                    // 发送短信提醒，店铺通过，附带登录链接
                    $url = config("app.merchant");
                    $content = "【商城】您的开店申请已通过，请前往 $url 管理您的店铺，登录的账号为您的手机号，密码与APP端的密码一致";
                    send_sms($user->phone, $content);
                    return $this->executeSuccess("操作");
                } catch (\Exception $exception) {
                    DB::rollBack();
                    Log::error($exception->getMessage());
                    return $this->executeFail("操作");
                }
            } else {
                if (!$request->filled("note")) {
                    return $this->fail("请填写不通过的原因");
                }
                $store->note = $request->note;
                $store->save();
                return $this->executeSuccess("操作");
            }
        }
        return $this->executeSuccess("操作");
    }
}
