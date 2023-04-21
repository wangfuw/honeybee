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
            ->select("store.*","users.phone")
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
                DB::beginTransaction();
                try {
                    $store->save();
                    User::where("id", $store->user_id)->update(["is_shop" => 1]);
                    DB::commit();
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
