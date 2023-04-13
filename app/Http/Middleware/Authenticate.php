<?php

namespace App\Http\Middleware;
use Closure;
use App\Traits\ApiResponse;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
class Authenticate
{
    use ApiResponse;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request,Closure $next)
    {
//        if (! $request->expectsJson()) {
//            return route('login');
//        }
        //检测会员是否已登录
        $token = $request->token = $request->header('Authorization');

        if (!$token) {
            return $this->fail('请求token缺失');
        }
        try {
            //重新设置请求头把token修改成j
            $request->headers->set('Authorization',"{$token}");

            $user = JWTAuth::parseToken()->touser();
        } catch (JWTException $e) {
            if($e->getMessage() == 'Wrong number of segments') {
                return $this->fail('签名令牌不合法,请重新登录');
            }

            if($e->getMessage() == 'Token has expired') {
                return $this->fail('令牌已过期,请重新登录');
            }

            if($e->getMessage() == 'Token Signature could not be verified.') {
                return $this->fail('无法验证令牌签名,请重新登录',);
            }

            return $this->fail('token验证意外错误：' . $e->getMessage());
        }

        $request->setUserResolver(
            function () use ($user) {
                return $user;
            }
        );

        return $next($request);
    }
}
