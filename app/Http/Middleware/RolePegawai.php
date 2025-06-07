<?php

namespace App\Http\Middleware;

use App\Models\Jabatan;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RolePegawai
{
    public function handle($request, $next)
    {
        if (Auth::guard('pegawai')->check()) {
            $pegawai = Auth::guard('pegawai')->user();
            $role = Jabatan::find($pegawai->idJabatan);
            $jabatan = strtolower($role->nama);

            $routeName = $request->route()->getName();
            $routePrefix = explode('.', $routeName)[0];

            if ($jabatan === 'owner' && $routePrefix === 'owner') {
                return $next($request);
            }

            if ($jabatan === 'admin' && $routePrefix === 'admin') {
                return $next($request);
            }

            if ($jabatan === 'cs' && $routePrefix === 'cs') {
                return $next($request);
            }

            if ($jabatan === 'gudang' && $routePrefix === 'gudang') {
                return $next($request);
            }

            if ($jabatan === 'hunter' && $routePrefix === 'hunter') {
                return $next($request);
            }

            return redirect()->route('unAuthorized');
        }

        return redirect()->route('loginPage');
    }
}
