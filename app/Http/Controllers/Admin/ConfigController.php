<?php

namespace App\Http\Controllers\Admin;

use App\Models\Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ConfigController extends AdminBaseController
{
    public function getConfig(Request $request)
    {
        $data = Config::first();
        return $this->executeSuccess("请求", $data);
    }

    public function editConfig(Request $request)
    {
        if (!$request->filled("green_one_allowance") || $request->green_one_allowance <= 0 || $request->green_one_allowance >= 100) {
            return $this->error("1倍绿色积分区商家让利比例");
        }
        if (!$request->filled("green_twice_allowance") || $request->green_twice_allowance <= 0 || $request->green_twice_allowance >= 100) {
            return $this->error("2倍绿色积分区商家让利比例");
        }
        if (!$request->filled("green_threefold_allowance") || $request->green_threefold_allowance <= 0 || $request->green_threefold_allowance >= 100) {
            return $this->error("3倍绿色积分区商家让利比例");
        }

        if (!$request->filled("green_free_before_rate") || $request->green_free_before_rate <= 0 || $request->green_free_before_rate >= 1000) {
            return $this->error("绿色积分释放比例(回本前)");
        }
        if (!$request->filled("green_free_next_rate") || $request->green_free_next_rate <= 0 || $request->green_free_next_rate >= 1000) {
            return $this->error("绿色积分释放比例(回本后)");
        }

        if (!$request->filled("consume_one_allowance") || $request->consume_one_allowance <= 0 || $request->consume_one_allowance >= 100) {
            return $this->error("1倍消费积分区商家让利比例");
        }
        if (!$request->filled("consume_twice_allowance") || $request->consume_twice_allowance <= 0 || $request->consume_twice_allowance >= 100) {
            return $this->error("2倍消费积分区商家让利比例");
        }
        if (!$request->filled("consume_threefold_allowance") || $request->consume_threefold_allowance <= 0 || $request->consume_threefold_allowance >= 100) {
            return $this->error("3倍消费积分区商家让利比例");
        }

        if (!$request->filled("consume_free_rate") || $request->consume_free_rate <= 0 || $request->consume_free_rate >= 1000) {
            return $this->error("消费积分释放比例");
        }

        if (!$request->filled("lucky_base") || $request->lucky_base <= 0) {
            return $this->error("幸运专区初级获得幸运值倍数");
        }
        if (!$request->filled("lucky_middle") || $request->lucky_middle <= 0) {
            return $this->error("幸运专区中级获得幸运值倍数");
        }
        if (!$request->filled("lucky_last") || $request->lucky_last <= 0) {
            return $this->error("幸运专区高级获得幸运值倍数");
        }

        if (!$request->filled("lucky_base_reward_coin") || $request->lucky_base_reward_coin <= 0 || $request->lucky_base_reward_coin >= 100) {
            return $this->error("幸运专区初级奖励比例");
        }
        if (!$request->filled("lucky_middle_reward_coin") || $request->lucky_middle_reward_coin <= 0 || $request->lucky_middle_reward_coin >= 100) {
            return $this->error("幸运专区中级奖励比例");
        }
        if (!$request->filled("lucky_last_reward_coin") || $request->lucky_last_reward_coin <= 0 || $request->lucky_last_reward_coin >= 100) {
            return $this->error("幸运专区高级奖励比例");
        }

        if (!$request->filled("register_give_lucky") || $request->register_give_lucky <= 0) {
            return $this->error("注册赠送幸运值数量");
        }

        if (!$request->filled("burn_give_green") || $request->burn_give_green <= 0) {
            return $this->error("商家燃烧asac获得绿色节分倍数");
        }

        if (!$request->filled("free_green_ratio_lucky") || $request->free_green_ratio_lucky <= 0) {
            return $this->error("释放绿色积分消耗幸运值比");
        }

        if (!$request->filled("leader_average_rate") || $request->leader_average_rate <= 0 || $request->leader_average_rate >= 100) {
            return $this->error("领导释放给直推人加速比例");
        }

        if (!$request->filled("rank_rate") || $request->rank_rate <= 0 || $request->rank_rate >= 100) {
            return $this->error("公排拿下面2人的加速比例");
        }

        $config = Config::first();
        $param = $request->all();
        unset($param["rule_type"]);
        unset($param["created_at"]);
        unset($param["updated_at"]);
        try{
            Config::where("id",$config->id)->update($param);
            return $this->executeSuccess("修改");
        }catch (\Exception $exception){
            Log::error($exception->getMessage());
            return $this->executeFail("修改");
        }
//        $config->green_one_allowance = $request->green_one_allowance;
//        $config->green_twice_allowance = $request->green_twice_allowance;
//        $config->green_threefold_allowance = $request->green_threefold_allowance;
//        $config->green_free_before_rate = $request->green_free_before_rate;
//        $config->green_free_next_rate = $request->green_free_next_rate;
//        $config->consume_one_allowance = $request->consume_one_allowance;
//        $config->consume_twice_allowance = $request->consume_twice_allowance;

    }
}
