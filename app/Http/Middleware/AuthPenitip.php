<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthPenitip
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // Cek apakah user sudah login dan merupakan penitip
        if (!session('user')['id'] || session('user')['userType'] !== 'penitip') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 401);
            }
            return redirect()->route('login');
        }

        return $next($request);
    }
}
