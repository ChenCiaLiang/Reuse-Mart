<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\PersonalAccessToken;
use Illuminate\Support\Facades\Log;

class AttachTokenToUser
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user() && $request->bearerToken()) {
            $tokenHash = hash('sha256', $request->bearerToken());
            $token = PersonalAccessToken::where('token', $tokenHash)->first();

            if ($token) {
                $request->user()->withAccessToken($token);
                Log::info('Token attached to user', ['user' => $request->user()->idPegawai]);
            }
        }

        return $next($request);
    }
}
