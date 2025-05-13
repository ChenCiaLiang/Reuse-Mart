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
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $organisasi = Organisasi::where('email', $request->email)->first();
            if (!$organisasi || !Hash::check($request->password, $organisasi->password)) {
                return response()->json([
                    'message' => 'Email atau password tidak valid'
                ], 401);
            }

            $organisasi->tokens()->delete();
            $token = $organisasi->createToken('auth_token', ['organisasi'])->plainTextToken;

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
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
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
            return response()->json(['errors' => $validator->errors()], 422);
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
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Pendaftaran gagal',
                'error' => $e->getMessage()
            ], 500);
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

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
