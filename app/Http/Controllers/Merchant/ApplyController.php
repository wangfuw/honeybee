<?php

namespace App\Http\Controllers\Merchant;

use App\Models\StoreSupply;
use Illuminate\Http\Request;

class ApplyController extends MerchantBaseController
{
    public function applyInfo()
    {
        $user = auth("merchant")->user();
        $info = StoreSupply::query()->where('user_id',$user->user_id)->first();
        return $this->executeSuccess("请求", $info);
    }


    public function apply(Request $request)
    {
        $data = $request->only(['']);
        StoreSupply::query()->create($data);
    }

    protected function check($data)
    {
        return $data;
    }

}
