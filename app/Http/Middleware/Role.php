<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Role
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect()->route('loginPage');
        }

        // Cara mendapatkan role berbeda untuk web dan API
        $roles = '';
        if (method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            // API route dengan token
            $tokenAbilities = $user->currentAccessToken()->abilities;
            $roles = $tokenAbilities[0] ?? '';
        }

        foreach ($role as $allowedRole) {
            if ($roles === $allowedRole) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Redirect berdasarkan role
        if ($roles === 'admin') {
            return redirect('/dashboard');
        } elseif ($roles === 'customer service') {
            return redirect()->route('cs.index');
        } elseif ($roles === 'gudang') {
            return redirect()->route('gudang.dashboard');
        }

        return redirect()->route('unAuthorized');
    }
}
