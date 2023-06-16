<?php

namespace App\Http\Controllers\Admin;

use App\Models\StoreSupply;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SupplyController extends AdminBaseController
{

    public function supplyList(Request $request)
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
                $condition[] = ["store_supply.id", "=", "-1"];
            }
        }

        $data = StoreSupply::join("users", "users.id", "=", "store_supply.user_id")
            ->where($condition)
            ->orderBy("store_supply.status")
            ->select("store_supply.*", "users.phone")
            ->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function apply(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $store = StoreSupply::find($request->id);
        if ($request->filled('status')) {
            $store->status = $request->status;
            $store->save();
            return $this->executeSuccess("操作");
        }
        return $this->executeSuccess("操作");
    }
}
