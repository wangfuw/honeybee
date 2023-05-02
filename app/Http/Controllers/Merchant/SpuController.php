<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\BaseController;
use App\Models\MallCategory;
use App\Models\MallSku;
use App\Models\MallSpu;
use App\Models\User;
use App\Validate\Admin\SpuValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class SpuController extends MerchantBaseController
{
    private $validate;

    public function __construct(SpuValidate $validate)
    {
        $this->validate = $validate;
    }


    public function categoryList(Request $request)
    {
        $cate = MallCategory::where(["parent_id" => 0, "is_delete" => 0])->get()->toArray();
        foreach ($cate as $k => &$v) {
            $v["children"] = MallCategory::where(["parent_id" => $v["id"], "is_delete" => 0])->get()->toArray();
        }
        return $this->executeSuccess("请求", $cate);
    }

    public function addSpu(Request $request)
    {
        $params = $request->only('area', 'category', 'name', 'logo', 'banner_imgs', 'detail_imgs', 'special_spec', 'skus', 'saleable','fee');

        if (!$this->validate->scene('add')->check($params)) {
            return $this->fail($this->validate->getError());
        }
        if($params["fee"] < 0){
            return  $this->fail("运费不能为负数");
        }
        $user = auth("merchant")->user();
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
                "user_id" => $user->id,
                "game_zone" => 1,
                "score_zone" => $params["score_zone"] ?? 1,
                'fee'=>$params['fee'],
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

        if ($request->score_zone) {
            $condition[] = ["score_zone", "=", $request->score_zone];
        }
        if ($request->category) {
            $condition[] = ["category_one", "=", $request->category[0]];
            if (count($request->category) >= 2) {
                $condition[] = ["category_two", "=", $request->category[1]];
            }
        }
        $user = auth("merchant")->user();
        $condition[] = ["user_id", '=', $user->id];
        $spus = MallSpu::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $spus);
    }


    public function spuDetail(Request $request)
    {
        if (!$request->id) {
            return $this->error("ID");
        }
        $spu = MallSpu::find($request->id)->toArray();
        $user = auth("merchant")->user();
        if($spu["user_id"] != $user->id){
            return $this->executeSuccess("请求",[]);
        }
        $skus = MallSku::where("spu_id", $request->id)->get()->toArray();
        $spu["skus"] = $skus;
        return $this->executeSuccess("请求", $spu);
    }

    public function editSpu(Request $request)
    {
        $params = $request->only('id', 'area', 'category', 'name', 'logo', 'banner_imgs', 'detail_imgs', 'special_spec', 'skus', 'saleable','fee');

        if (!$this->validate->scene('modify')->check($params)) {
            return $this->fail($this->validate->getError());
        }
        $spu = MallSpu::find($request->id);
        $user = auth("merchant")->user();
        if (!$spu || $spu->user_id != $user->id) {
            return $this->error("ID");
        }
        if($params["fee"] < 0){
            return  $this->fail("运费不能为负数");
        }
        DB::beginTransaction();
        try {
            MallSpu::where("id", $params["id"])->update([
                "name" => $params["name"],
                "category_one" => $params["category"][0],
                "category_two" => $params["category"][1] ?? 0,
                "saleable" => 4,
                "logo" => $params["logo"],
                "banners" => $params["banner_imgs"],
                "details" => $params["detail_imgs"],
                "special_spec" => $params["special_spec"],
                "user_id" => $user->id,
                "game_zone" => 1,
                "score_zone" => $params["score_zone"] ?? 1,
                'fee'=>$params['fee'],
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
        $user = auth("merchant")->user();
        if ($spu->user_id != $user->id) {
            $this->error("ID");
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
