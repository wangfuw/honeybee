<?php

namespace App\Http\Middleware;

use App\Traits\AdminResponse;
use Closure;
use App\Traits\ResponseEnum;

class AdminToken
{
    use AdminResponse;

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function handle($request, Closure $next)
    {
        //检测会员是否已登录
        $token = $request->header('Authorization');

        if (!$token) {
            return $this->jsonResponse(ResponseEnum::TOKEN_EXPIRED, '登录令牌缺失,请重新登录');
        }

        try {
            auth("admin")->authenticate($token);
        } catch (\Exception $e) {

            return $this->jsonResponse(ResponseEnum::TOKEN_EXPIRED, '登录令牌失效,请重新登录');
        }

        return $next($request);
    }
}
