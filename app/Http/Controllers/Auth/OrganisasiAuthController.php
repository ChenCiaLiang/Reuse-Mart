<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OrganisasiAuthController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'email' => 'required|string|email',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                if ($request->expectsJson()) {
                    return response()->json(['errors' => $validator->errors()], 422);
                }
                return back()->withErrors($validator)->withInput();
            }

            $organisasi = Organisasi::where('email', $request->email)->first();
            if (!$organisasi || !Hash::check($request->password, $organisasi->password)) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Email atau password tidak valid'
                    ], 401);
                }
                return back()->withErrors(['email' => 'Email atau password tidak valid'])->withInput();
            }

            $organisasi->tokens()->delete();
            $token = $organisasi->createToken('auth_token', ['organisasi'])->plainTextToken;

            if ($request->expectsJson()) {
                return response()->json([
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                    'user' => [
                        'id' => $organisasi->idOrganisasi,
                        'email' => $organisasi->email,
                        'nama' => $organisasi->nama,
                        'userType' => 'organisasi',
                        'logo' => $organisasi->logo
                    ]
                ]);
            }

            // Set session data untuk web interface
            session([
                'access_token' => $token,
                'user_id' => $organisasi->idOrganisasi,
                'user_type' => 'organisasi',
                'user_name' => $organisasi->nama,
                'user_email' => $organisasi->email,
            ]);

            return redirect()->route('customer.homePage');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:organisasi,email',
            'password' => 'required|string|min:6',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'alamat' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            // Jika request dari API, kembalikan JSON
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Jika request dari web, redirect dengan error
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $latestOrganisasi = Organisasi::orderBy('idOrganisasi', 'desc')->first();
            $nextId = 1;
            if ($latestOrganisasi) {
                $lastId = (int) substr($latestOrganisasi->idOrganisasi, 3);
                $nextId = $lastId + 1;
            }

            $idOrganisasi = 'ORG' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

            $logoPath = null;
            if ($request->hasFile('logo')) {
                $logoFile = $request->file('logo');
                $filename = $idOrganisasi . '_' . time() . '.' . $logoFile->getClientOriginalExtension();
                $logoFile->move(public_path('organisasi'), $filename);
                $logoPath = 'user/organisasi/' . $filename;
            } else {
                $logoPath = 'organisasi/default_logo.png';
            }

            $organisasi = Organisasi::create([
                'idOrganisasi' => $idOrganisasi,
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'logo' => $logoPath,
                'alamat' => $request->alamat,
            ]);
            DB::commit();

            // Jika API request, kembalikan JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pendaftaran organisasi berhasil',
                    'user' => [
                        'id' => $organisasi->idOrganisasi,
                        'email' => $organisasi->email,
                        'nama' => $organisasi->nama,
                        'logo' => asset($organisasi->logo),
                        'userType' => 'organisasi',
                    ]
                ], 201);
            }

            // Jika web request, set session dan redirect
            session([
                'access_token' => $organisasi->createToken('auth_token', ['organisasi'])->plainTextToken,
                'user_id' => $organisasi->idOrganisasi,
                'user_type' => 'organisasi',
                'user_name' => $organisasi->nama,
                'user_email' => $organisasi->email,
            ]);

            return redirect()->route('customer.homePage')
                ->with('success', 'Pendaftaran organisasi berhasil!');
                
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Jika API request, kembalikan JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pendaftaran gagal',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // Jika web request, redirect dengan error
            return redirect()->back()
                ->with('error', 'Pendaftaran gagal: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function me(Request $request)
    {
        $user = $request->user();

        if ($user) {
            $tokenHash = hash('sha256', $request->bearerToken());
            $token = \App\Models\PersonalAccessToken::where('token', $tokenHash)->first();

            if ($token) {
                // Set token secara manual ke user
                $user->withAccessToken($token);
            }

            return response()->json($user);
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }

    public function logout(Request $request)
    {
        $token = $request->user()->currentAccessToken();
        if ($token) {
            $token->delete();
        } else {
            // Hapus token secara manual jika currentAccessToken() null
            $tokenHash = hash('sha256', $request->bearerToken());
            \App\Models\PersonalAccessToken::where('token', $tokenHash)->delete();
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Berhasil logout'
            ]);
        }

        // Untuk web, hapus session dan redirect
        session()->flush();
        return redirect()->route('login')
            ->with('success', 'Berhasil logout');
    }
}