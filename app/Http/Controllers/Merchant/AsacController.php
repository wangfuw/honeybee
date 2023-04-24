<?php

namespace App\Http\Controllers\Merchant;

use App\Models\AsacDestory;
use App\Models\AsacNode;
use App\Models\AsacTrade;
use App\Models\Config;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AsacController extends MerchantBaseController
{
    public function info()
    {
        $user = auth("merchant")->user();
        return $this->executeSuccess("请求", $user);
    }

    public function config()
    {
        $config = Config::select("id", "burn_give_green")->first();
        return $this->executeSuccess("请求", $config);
    }

    public function burn(Request $request)
    {
        $num = $request->input("num", 1);
        if (!is_numeric($num) || $num <= 0) {
            return $this->error("数量");
        }
        $config = Config::first();
        $user = auth("merchant")->user();
        if ($user->coin_num < $num) {
            return $this->fail("您的asac余额不足");
        }
        $user->coin_num -= $num;
        $user->green_score += $num * $config->burn_give_green;

        $node = AsacNode::where("user_id", $user->id)->first();
        $node_pre = AsacNode::find(2);

        $node_pre->number += $num;
        DB::beginTransaction();
        try {
            $user->save();
            Score::create([
                "user_id" => $user->id,
                "flag" => 1,
                "num" => $num * $config->burn_give_green,
                "type" => 1,
                "f_type" => Score::BURN_HAVE,
                "amount" => 0
            ]);
            AsacDestory::create([
                "user_id" => $user->id,
                "dest_address" => $node->wallet_address,
                "number" => $num,
                "green_num" => $num * $config->burn_give_green,
            ]);
            DB::commit();
            return $this->executeSuccess("燃烧");
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error($exception->getMessage());
            return $this->executeFail("燃烧");
        }
    }
}
