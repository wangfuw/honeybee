<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Admin\SupplyController;
use App\Http\Controllers\Controller;
use App\Models\PayOrder;
use App\Models\StoreSupply;
use Illuminate\Http\Request;

class PayOrderController extends MerchantBaseController
{
    public function payOrderList(){
        $size = $request->size ?? $this->size;
        $user = auth("merchant")->user();
        $data = PayOrder::query()->where('store_id',$user->id)
            ->orderByDesc("id")
            ->select("*")
            ->paginate($size)
            ->toArray();
        $alt_mch_no = StoreSupply::query()->where('user_id',$user->id)->value('alt_mch_no');
        foreach ($data["data"] as $k => &$v) {
            $v["alt_mch_no"] = $alt_mch_no;
        }
        return $this->executeSuccess("请求", $data);
    }

}
