<?php

namespace App\Http\Controllers\Admin;

use App\Models\AdminAction;
use App\Models\AdminGroup;
use App\Models\AdminRule;
use App\Models\AdminUser;
use App\Validate\Admin\AdminUserValidate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends AdminBaseController
{
    private $validate;

    public function __construct(AdminUserValidate $validate)
    {
        $this->validate = $validate;
    }

    public function admins(Request $request)
    {
        $groups = AdminGroup::orderBy("id")->select("id", "name")->get()->toArray();
        foreach ($groups as $k => &$v) {
            $v["group"] = 1;
            $v["index"] = $v["id"];
            $users = AdminUser::where("group_id", $v["id"])->select('id', 'username as name', 'status')->get()->toArray();
            foreach ($users as $m => &$n) {
                $n["index"] = $v["id"] . "_" . $n["id"];
                $n["group"] = 2;
            }
            $v["children"] = $users;
        }
        return $this->success("请求成功", $groups);
    }

    public function groups(Request $request)
    {
        $data = AdminGroup::orderBy("id")->select("id", "name")->get();
        return $this->success('请求', $data);
    }

    public function addGroup(Request $request)
    {
        $name = $request->only('name');
        if (!$name['name']) {
            return $this->fail("组名不能为空");
        }
        $adminGroup = AdminGroup::where($name)->first();
        if ($adminGroup) {
            return $this->fail("组名已存在");
        }
        try {
            AdminGroup::insert([
                "name" => $name["name"],
                "rules" => ","
            ]);
            return $this->success("添加成功");
        } catch (\Exception $exception) {
            return $this->fail('添加失败');
        }
    }

    public function delGroup(Request $request)
    {
        $id = $request->input("id");
        if (!$id) {
            return $this->fail("管理组ID");
        }
        $group = AdminGroup::find($id);
        if (!$group) {
            return $this->fail(0, '管理组不存在');
        }
        $admin = auth("admin")->user();

        if ($admin->group_id == $id) {
            return $this->fail(0, '不能删除自己所在的组');
        }
        DB::beginTransaction();
        try {
            AdminGroup::destroy($id);
            AdminUser::where('group_id', $id)->delete();
            DB::commit();
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            DB::rollback();
            return $this->fail('删除失败');
        }
    }

    public function banUser(Request $request)
    {
        $id = $request->input("id");;
        if (!$id) {
            return $this->fail("ID错误");
        }
        if ($id == $request->adminId) {
            return $this->fail("不能禁用自己");
        }
        $au = AdminUser::find($id);
        $au->status = 3 - $au->status;
        try {
            $au->save();
            return $this->success('禁用成功');
        } catch (\Exception $exception) {
            var_dump($exception);
            return $this->fail('禁用失败');
        }
    }

    public function addUser(Request $request)
    {
        $param = $request->only('username', 'password', 'password_confirm', 'group_id');;

        if (!$this->validate->scene('add')->check($param)) {
            return $this->fail($this->validate->getError());
        }
        if ($request->password != $request->password_confirm) {
            return $this->fail("两次输入的密码不一致");
        }
        $group = AdminGroup::find($request->group_id);
        if (!$group) {
            return $this->fail('管理组不存在');
        }
        $au = AdminUser::where(['username' => $request->username])->first();
        if ($au) {
            return $this->fail('用户名已存在');
        }
        try {
            AdminUser::insert([
                'username' => $param['username'],
                'password' => Hash::make($request->password),
                'group_id' => $param['group_id']
            ]);
            return $this->success('添加成功');
        } catch (\Exception$exception) {
            return $this->fail('添加');
        }
    }

    public function delUser(Request $request)
    {
        $id = $request->input("id");
        if (!$id) {
            return $this->fail('用户错误');
        }
        $au = AdminUser::find($id);
        if (!$au) {
            return $this->fail('用户错误');
        }
        $admin = auth("admin")->user();
        if ($id == $admin->id) {
            return $this->fail('不能删除自己');
        }
        try {
            AdminUser::destroy($id);
            return $this->success('删除成功');
        } catch (\Exception $exception) {
            return $this->fail('删除失败');
        }
    }

    public function actionLog(Request $request)
    {
        $size = $request->size ?? $this->size;

        $condition = [];
        $admin_id = $request->admin_id;
        if ($admin_id) {
            $condition["admin_id"] = $admin_id;
        }
        $rule_id = $request->rule_id;
        if ($rule_id) {
            $condition["rule_id"] = $rule_id;
        }
        $rules = AdminRule::where("rule_type",2)->get()->toArray();
        $notices = AdminAction::join("admin_rule", "admin_action.rule_id", "=", "admin_rule.id")->where($condition)->orderByDesc("admin_admin.id")->select("admin_action.id,admin_id,admin_rule.title,ip,created_at")->paginate($size);
        return$this->executeSuccess("请求", ["data"=>$notices,"rules"=>$rules]);
    }
}
