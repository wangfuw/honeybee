<?php

namespace App\Http\Middleware;

use App\Models\AdminAction;
use App\Models\AdminRule;
use Closure;

class AdminResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
//        if ($request->rule_type == 2) {
//            $ip = $request->header('X-Real-IP');
//            $uri = $request->path();
//            $admin = auth("admin")->user();
//            $rule = AdminRule::where("uri", "/" . $uri)->first();
//            $param = $request->all();
//            unset($param["rule_type"]);
//            date_default_timezone_set("Asia/Shanghai");
//            AdminAction::create([
//                "admin_id" => $admin->id,
//                "rule_id" => $rule->id,
//                "param" => json_encode($param),
//                "ip" => $ip,
//                "created_at" => date("Y-m-d H:i:s", time())
//            ]);
//        }
        return $response;
    }
}
