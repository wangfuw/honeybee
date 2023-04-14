<?php

namespace App\Http\Middleware;

use App\Traits\AdminResponse;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
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
            return $this->jsonResponse(ResponseEnum::TOKEN_EXPIRED, '长时间未操作,请重新登录');
        }
        try {
            $user = auth("admin")->user();
            auth("admin")->invalidate();
            $token = auth("admin")->tokenById($user->id);
            $response = $next($request);
            $response->headers->set('Authorization', $token);
            return $response;
        } catch (\Exception $e) {
            return $this->jsonResponse(ResponseEnum::TOKEN_EXPIRED, '长时间未操作,请重新登录');
        }

        return $next($request);
    }
}
