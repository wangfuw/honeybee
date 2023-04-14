<?php


namespace App\Http\Middleware;

use App\Common\Rsa;
use App\Models\AdminGroup;
use App\Models\AdminRule;
use App\Models\AdminUser;
use App\Traits\AdminResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class AdminAuth
{
    use AdminResponse;

    public function handle($request, Closure $next)
    {
        $uri = $request->path();
        $admin = auth("admin")->user();
        var_dump($admin);
        $rule = AdminRule::where("uri", "/" . $uri)->first();
        $group = AdminGroup::find($admin->group_id);
        $rules = explode(",", $group->rules);
        if (!in_array($rule->id, $rules)) {
            return $this->fail( "权限不足");
        }
        return $next($request);
    }


}
