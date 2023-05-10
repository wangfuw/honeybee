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
        $params = $request->only('area', 'category', 'name', 'logo', 'banner_imgs', 'detail_imgs', 'special_spec', 'skus', 'saleable','fee','score_zone');

        if (!$this->validate->scene('add')->check($params)) {
            return $this->fail($this->validate->getError());
        }
        if($params["fee"] < 0){
            return  $this->fail("运费不能为负数");
        }

        if($params["area"] > 2){
            $score_zone = 0;
        }else{
            $score_zone = $params["score_zone"] ?? 1;
        }

        if(empty($params["special_spec"])){
            return $this->fail("规格参数不能为空");
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
                "game_zone" => $params["area"],
                "score_zone" => $score_zone,
                'fee'=>$params['fee']
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
            if($request->score_zone <= 2){
                $condition[] = ["score_zone", "=", $request->score_zone];
            }else{
                $condition[] = ["score_zone",">=",$request->score_zone];
            }

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
        if ($request->id) {
            $condition[] = ["mall_spu.user_id", "=", $request->id];
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
        $condition[] = ["mall_spu.saleable", ">=", 1];
        $spus = MallSpu::join("users", "users.id", "=", "mall_spu.user_id")
            ->where($condition)
            ->orderByDesc("mall_spu.saleable")
            ->select("mall_spu.*", "users.phone")
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
        $params = $request->only('id', 'area', 'category', 'name', 'logo', 'banner_imgs', 'detail_imgs', 'special_spec', 'skus', 'saleable','fee','score_zone');

        if (!$this->validate->scene('modify')->check($params)) {
            return $this->fail($this->validate->getError());
        }
        $spu = MallSpu::find($request->id);
        if (!$spu || $spu->user_id > 0) {
            return $this->error("ID");
        }

        if($params["area"] > 2){
            $score_zone = 0;
        }else{
            $score_zone = $params["score_zone"] ?? 1;
        }

        if(empty($params["special_spec"])){
            return $this->fail("规格参数不能为空");
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
                "game_zone" => $params["area"],
                "score_zone" => $score_zone,
                'fee'=>$params['fee'] ?? 0
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

    public function editSaleable(Request $request)
    {
        $params = $request->only('id', 'saleable');

        if (!$this->validate->scene('sale')->check($params)) {
            return $this->fail($this->validate->getError());
        }
        $spu = MallSpu::find($params["id"]);
        if (!$spu) {
            return $this->error("ID");
        }
        if ($spu->user_id == 0) {
            $this->error("ID");
        }
        if($params["saleable"] == 3){
            if(!$request->reason){
                $this->fail("驳回原因必传");
            }
            $spu->reason = $request->reason;
        }
        try {
            $spu->saleable = $params["saleable"];
            $spu->save();
            return $this->executeSuccess("操作");
        } catch (\Exception $exception) {
            return $this->fail("操作");
        }
    }
}
