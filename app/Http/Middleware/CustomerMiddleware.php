<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CustomerMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        // Cek tipe user dari session
        $userType = session('user')['userType'];

        // Pastikan user adalah customer (pembeli, penitip, atau organisasi)
        if (!in_array($userType, ['pembeli', 'penitip', 'organisasi'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return redirect()->route('unauthorized');
        }

        return $next($request);
    }
}
