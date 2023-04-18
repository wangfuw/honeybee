<?php

namespace App\Http\Controllers\Admin;

use App\Models\MallSku;
use App\Models\MallSpu;
use App\Validate\Admin\SpuValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SpuController extends AdminBaseController
{
    private $validate;

    public function __construct(SpuValidate $validate)
    {
        $this->validate = $validate;
    }


    public function addSpu(Request $request)
    {
        $params = $request->only('area', 'category', 'name', 'logo', 'banners', 'details', 'special_spec', 'skus', 'saleable');

        if (!$this->validate->scene('add')->check($params)) {
            return $this->fail($this->validate->getError());
        }

        DB::beginTransaction();
        try {
            $spu = MallSpu::create([
                "name" => $params["name"],
                "category_one" => $params["category"][0],
                "category_two" => $params["category"][1] ?? 0,
                "sale_able" => $params["saleable"],
                "logo" => $params["logo"],
                "banners" => $params["banners"],
                "details" => $params["details"],
                "special_spec" => $params["special_spec"],
                "user_id" => 0,
                "game_zone" => $params["area"][0],
                "score_zone" => $params["area"][1] ?? 0,
            ]);
            foreach ($params["skus"] as $k) {
                MallSku::create([
                    "spu_id" => $spu->id,
                    "stock" => $k["stock"],
                    "price" => $k["price"],
                    "indexes" => $k["indexes"],
                    "enable" => $k["enable"],
                ]);
            }
            DB::commit();
            return $this->executeSuccess("上传");
        } catch (\Exception $exception) {
            var_dump($exception);
            return $this->executeFail("上传");
        }
    }
}
