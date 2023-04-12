<?php
namespace App\Http\Middleware;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Closure;

class AdminSign {
    public function handle($request, Closure $next)
    {
        $sign = $request->header('sign');

        return $next($request);
    }
}
