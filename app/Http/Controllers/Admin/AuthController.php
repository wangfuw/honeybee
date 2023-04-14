<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminGroup;
use App\Models\AdminNav;
use App\Models\AdminRule;
use Illuminate\Http\Request;

class AuthController extends AdminBaseController
{

    public function authList(Request $request)
    {
        $groups = AdminGroup::orderBy("id")->select("id", "name", "rules")->get()->toArray();
        foreach ($groups as $k => &$v) {
            $v["auth"] = $this->authRule(explode(",", $v["rules"]));
            unset($v["rules"]);
        }
        return $this->executeSuccess("请求",$groups);
    }

    private function authRule($rules)
    {
        $menu = AdminNav::select("id", "title")->get()->toArray();
        foreach ($menu as $m => &$n) {
            $rule = AdminRule::where("nav_id", $n["id"])->select("id", "title")->get();
            if ($rule->isEmpty()) {
                unset($menu[$m]);
            } else {
                $n["have"] = false;
                $rule = $rule->toArray();
                foreach ($rule as $k => &$v) {
                    if (in_array($v["id"], $rules)) {
                        $v["have"] = true;
                        $n["have"] = true;
                    }
                    $v["key"] = $n["id"] . "_" . $v["id"];
                }
                $n["children"] = $rule;
                $n["key"] = $n["id"];
            }
        }
        sort($menu);
        return $menu;
    }

    public function addAuth(Request $request)
    {
        $param = $request->only("groupId","menuId","authId");
        if(!$param["groupId"]){
            return $this->fail("必须选择组");
        }
        $group = AdminGroup::find($param["groupId"]);
        if(!$group){
            return $this->error("组");
        }
        $ruleList = explode(",", $group->rules);
        if ($param['authId']) {
            $authId = $param['authId'];
            $auth = AdminRule::find($authId);
            if (!$auth) {
                return $this->error('auth id');
            }
            if (in_array($authId, $ruleList)) {
                return $this->fail( '权限已添加');
            }
            $group->rules = $group->rules . $authId . ",";
            try {
                $group->save();
                return $this->executeSuccess('添加');
            } catch (\Exception $exception) {
                return $this->executeFail('添加');
            }
        }
        if($param['menuId']){
            $menuId = $param['menuId'];
            $auths = AdminRule::where('nav_id',$menuId)->select("id")->get()->toArray();
            $newRule = $group->rules;
            foreach ($auths as $v){
                if(!in_array($v,$ruleList)){
                    $newRule = $newRule.$v.",";
                }
            }
            $group->rules = $newRule;
            try{
                $group->save();
                return $this->executeSuccess('添加');
            }catch (\Exception $exception){
                return $this->executeFail('添加');
            }
        }
        return $this->executeSuccess('添加');
    }

    public function delAuth(Request  $request){
        $param = $request->only('groupId', 'menuId', 'authId');
        if (!$param['groupId']) {
            return $this->error('组ID');
        }
        if($param['groupId'] == 1){
            return $this->fail('超级管理员不能删除自己的权限');
        }
        $group = AdminGroup::find($param['groupId']);
        if (!$group) {
            return $this->error('组ID');
        }
        $ruleList = explode(",", $group->rules);
        if($param['authId']){
            $authId = $param['authId'];
            if(!in_array($authId,$ruleList)){
                return $this->fail('权限已删除');
            }
            $group->rules = str_replace(",".$authId.",",",",$group->rules);
            try{
                $group->save();
                return $this->executeSuccess('删除');
            }catch (\Exception $exception){
                return $this->executeFail('删除');
            }
        }
        if($param['menuId']){
            $menuId = $param['menuId'];
            $auths = AdminRule::where('nav_id',$menuId)->select("id")->get()->toArray();
            $newRule = $group->rules;
            foreach ($auths as $v){
                if(in_array($v,$ruleList)){
                    $newRule = str_replace(",".$v.",",",",$newRule);
                }
            }
            $group->rules = $newRule;
            try{
                $group->save();
                return $this->executeSuccess('删除');
            }catch (\Exception $exception){
                return $this->executeFail('删除');
            }
        }
        return $this->executeSuccess('删除');
    }
}
