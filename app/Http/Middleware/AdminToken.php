<?php
namespace App\Http\Middleware;
use App\Traits\AdminResponse;
use Closure;
use Tymon\JWTAuth\Exceptions\JWTException;


class AdminToken{
    use AdminResponse;
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    public function handle($request,Closure $next)
    {
        //检测会员是否已登录
        $token = $request->token = $request->header('Authorization');

        if (!$token) {
            return $this->fail('请求token缺失');
        }
        try {
            //重新设置请求头把token修改成
            $request->headers->set('Authorization',"{$token}");

            $user = auth("admin")->user();

        } catch (JWTException $e) {
            if($e->getMessage() == 'Wrong number of segments') {
                return $this->fail('签名令牌不合法,请重新登录');
            }

            if($e->getMessage() == 'Token has expired') {
                return $this->fail('长时间未操作,请重新登录');
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
