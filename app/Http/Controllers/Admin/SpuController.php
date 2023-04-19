<?php

namespace App\Http\Controllers\Admin;

use App\Models\MallSku;
use App\Models\MallSpu;
use App\Models\User;
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
        $params = $request->only('area', 'category', 'name', 'logo', 'banner_imgs', 'detail_imgs', 'special_spec', 'skus', 'saleable');

        if (!$this->validate->scene('add')->check($params)) {
            return $this->fail($this->validate->getError());
        }

        DB::beginTransaction();
        try {
            $spu = MallSpu::create([
                "name" => $params["name"],
                "category_one" => $params["category"][0],
                "category_two" => $params["category"][1] ?? 0,
                "saleable" => $params["saleable"],
                "logo" => $params["logo"],
                "banners" => $params["banner_imgs"],
                "details" => $params["detail_imgs"],
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
            DB::rollBack();
            return $this->executeFail("上传");
        }
    }

    public function spuList(Request $request)
    {
        $condition = [];
        $size = $request->size ?? $this->size;
        if ($request->name) {
            $condition[] = ["name", "like", "%$request->name%"];
        }
        if ($request->game_zone) {
            $condition[] = ["game_zone", "=", $request->game_zone];
        }
        if ($request->score_zone) {
            $condition[] = ["score_zone", "=", $request->score_zone];
        }
        if ($request->category) {
            $condition[] = ["category_one", "=", $request->category[0]];
            if (count($request->category) >= 2) {
                $condition[] = ["category_two", "=", $request->category[1]];
            }
        }
        $condition[] = ["user_id", '=', 0];
        $spus = MallSpu::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $spus);
    }

    public function shopSpuList(Request $request)
    {
        $condition = [];
        $size = $request->size ?? $this->size;
        if ($request->phone) {
            $user = User::where("phone", $request->phone)->first();
            if (!$user) {
                $condition[] = ["mall_spu.id", "=", -1];
            } else {
                $condition[] = ["mall_spu.user_id", "=", $user->id];
            }
        }
        if ($request->name) {
            $condition[] = ["mall_spu.name", "like", "%$request->name%"];
        }
        if ($request->saleable) {
            $condition[] = ["mall_spu.saleable", "=", $request->saleable];
        }
        if ($request->category) {
            $condition[] = ["category_one", "=", $request->category[0]];
            if (count($request->category) >= 2) {
                $condition[] = ["category_two", "=", $request->category[1]];
            }
        }
        $condition[] = ["mall_spu.user_id", ">=", 1];
        $condition[] = ["mall_spu.saleable", "<=", 2];
        $spus = MallSpu::join("users", "users.id", "=", "mall_spu.user_id")
            ->where($condition)
            ->orderByDesc("saleable")
            ->select("mall_spu.*,users.phone")
            ->paginate($size);

        return $this->executeSuccess("请求", $spus);

    }

    public function spuDetail(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $spu = MallSpu::find($request->id)->toArray();
        $skus = MallSku::where("spu_id", $request->id)->get()->toArray();
        $spu["skus"] = $skus;
        return $this->executeSuccess("请求", $spu);
    }

    public function editSpu(Request $request)
    {
        $params = $request->only('id', 'area', 'category', 'name', 'logo', 'banner_imgs', 'detail_imgs', 'special_spec', 'skus', 'saleable');

        if (!$this->validate->scene('modify')->check($params)) {
            return $this->fail($this->validate->getError());
        }
        $spu = MallSpu::find($request->id);
        if (!$spu || $spu->user_id > 0) {
            return $this->error("ID");
        }
        DB::beginTransaction();
        try {
            MallSpu::where("id", $params["id"])->update([
                "name" => $params["name"],
                "category_one" => $params["category"][0],
                "category_two" => $params["category"][1] ?? 0,
                "saleable" => $params["saleable"],
                "logo" => $params["logo"],
                "banners" => $params["banner_imgs"],
                "details" => $params["detail_imgs"],
                "special_spec" => $params["special_spec"],
                "user_id" => 0,
                "game_zone" => $params["area"][0],
                "score_zone" => $params["area"][1] ?? 0,
            ]);
            foreach ($params["skus"] as $k) {
                $sku = MallSku::where(["spu_id" => $params["id"], "indexes" => $k["indexes"]])->first();
                if ($sku) {
                    $sku->stock = $k["stock"];
                    $sku->price = $k["price"];
                    $sku->enable = $k["enable"];
                    $sku->save();
                } else {
                    MallSku::create([
                        "spu_id" => $spu->id,
                        "stock" => $k["stock"],
                        "price" => $k["price"],
                        "indexes" => $k["indexes"],
                        "enable" => $k["enable"],
                    ]);
                }
            }
            DB::commit();
            return $this->executeSuccess("修改");
        } catch (\Exception $exception) {
            var_dump($exception);
            DB::rollBack();
            return $this->executeFail("修改");
        }
    }
}
