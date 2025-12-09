<?php

namespace App\Http\Middleware;

use Closure;

class AddSanctumToken
{
    public function handle($request, Closure $next)
    {
        if (session('access_token') && !$request->bearerToken()) {
            $request->headers->set('Authorization', session('access_token'));
            // dd($request->headers->get('Authorization'));
        }

        return $next($request);
    }
}
