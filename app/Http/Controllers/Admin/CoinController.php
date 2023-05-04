<?php

namespace App\Http\Controllers\Admin;

use App\Models\Coin;
use Illuminate\Http\Request;

class CoinController extends AdminBaseController
{
    public function coinList()
    {
        $data = Coin::all();
        return $this->executeSuccess("请求", $data);
    }

    public function addCoin(Request $request)
    {
        if (!$request->filled("name")) {
            return $this->error("币种名称");
        }
        $coin = Coin::where("name",$request->name)->first();
        if($coin){
            return  $this->fail("币种已存在");
        }
        if (!$request->filled("address")) {
            return $this->error("地址");
        }
        if (!$request->filled("money") || $request->money <= 0) {
            return $this->error("余额倍数");
        }

        Coin::create([
            "name" => $request->name,
            "address" => $request->address,
            "money" => $request->money
        ]);
        return $this->executeSuccess("添加");
    }

//    public function delCoin(Request $request){
//        $id = $request->filled("id");
//        if(!$id){
//            return $this->error("ID");
//        }
//        try{
//            Coin::destroy($id);
//            return $this->executeSuccess("删除");
//        }catch (\Exception $exception){
//            return $this->executeFail("删除");
//        }
//    }

    public function editCoin(Request $request){
        if(!$request->filled("id")){
            return $this->error("ID");
        }
        $coin = Coin::find($request->id);
        if(!$coin){
            return $this->error("ID");
        }
        if (!$request->filled("name")) {
            return $this->error("币种名称");
        }
        $coin2 = Coin::where("name",$request->name)->first();
        if($coin2 && $coin2->id != $coin->id){
            return  $this->fail("币种已存在");
        }
        $coin->name = $request->name;
        if (!$request->filled("address")) {
            return $this->error("地址");
        }
        $coin->address = $request->address;
        if (!$request->filled("money") || $request->money <= 0) {
            return $this->error("余额倍数");
        }
        $coin->money = $request->money;
        $status = $request->input("status",1);
        $coin->status = $status;
        try{
            $coin->save();
            return $this->executeSuccess("修改");
        }catch (\Exception $exception){
            return $this->executeFail("修改");
        }
    }
}
