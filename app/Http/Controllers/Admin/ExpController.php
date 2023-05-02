<?php

namespace App\Http\Controllers\Admin;
use App\Models\Exp;
use Illuminate\Http\Request;

class ExpController extends AdminBaseController{
    public function expList(Request $request)
    {
        $size = $request->size ?? $this->size;
        $condition = [];
        if ($request->filled("name")) {
            $condition[] = ["name", "like", "%$request->name%"];
        }

        $data = Exp::where($condition)->orderByDesc("id")->paginate($size);
        return $this->executeSuccess("请求", $data);
    }

    public function expAll(Request $request)
    {
        $data = Exp::orderByDesc("id")->all();
        return $this->executeSuccess("请求", $data);
    }

    public function editExp(Request $request){
        $exp = Exp::find($request->id);
        if(!$exp){
            return $this->error("ID");
        }
        $exp2 = Exp::where("name",$request->name)->first();
        if($exp2 && $exp2->id != $exp->id){
            return $this->fail("该快递公司已添加");
        }
        $exp->name = $request->name;
        $exp->save();
        return $this->executeSuccess("修改");
    }

    public function addExp(Request $request){
        $exp = Exp::where("name",$request->name)->first();
        if($exp){
            return $this->fail("该快递公司已添加");
        }
        try {
            Exp::create([
                "name"=>$request->name
            ]);
            return $this->executeSuccess("添加");
        }catch (\Exception$exception){
            return $this->executeFail("添加");
        }
    }

    public function delExp(Request $request){
        $id = $request->id;
        if(!$id){
            return $this->error("ID");
        }
        try{
            Exp::destroy($id);
            return $this->executeSuccess("删除");
        }catch (\Exception $exception){
            return $this->executeFail("删除");
        }
    }
}
