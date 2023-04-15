<?php

namespace App\Http\Middleware;

use Closure;

class AdminResponse
{
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        if($request->rule_type == 2 && $response->original["status"] == 1){
            var_dump($request->all());
        }
        return $response;
    }
}
