<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            return redirect('/login');
        }

        $tokenAbilities = $user->currentAccessToken()->abilities;
        $role = $tokenAbilities[1] ?? '';

        // Cek apakah role sesuai
        foreach ($roles as $allowedRole) {
            if ($role === $allowedRole) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        // Redirect sesuai role
        if ($role === 'admin') {
            return redirect('/admin/dashboard');
        } elseif ($role === 'customer service') {
            return redirect('/cs/dashboard');
        } elseif ($role === 'pegawai gudang' || $role === 'gudang') {
            return redirect('/gudang/dashboard');
        } elseif ($role === 'hunter') {
            return redirect('/hunter/dashboard');
        } elseif ($role === 'pembeli') {
            return redirect('/pembeli/dashboard');
        } elseif ($role === 'penitip') {
            return redirect('/penitip/dashboard');
        } elseif ($role === 'organisasi') {
            return redirect('/organisasi/dashboard');
        }

        return redirect('/unauthorized');
    }
}
