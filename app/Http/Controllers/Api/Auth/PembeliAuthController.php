<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pembeli;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PembeliAuthController extends Controller
{
    // Login untuk pembeli
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $deviceName = $request->device_name ?? 'default';

        // Cari pembeli berdasarkan email
        $pembeli = Pembeli::where('email', $request->email)->first();
        if (!$pembeli || !Hash::check($request->password, $pembeli->password)) {
            return response()->json([
                'message' => 'Email atau password tidak valid'
            ], 401);
        }

        // Hapus token lama dengan nama yang sama jika ada
        $pembeli->tokens()->where('name', $deviceName)->delete();

        // Buat token baru
        $token = $pembeli->createToken($deviceName, ['pembeli', 'pembeli']);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $pembeli->idPembeli,
                'email' => $pembeli->email,
                'nama' => $pembeli->nama,
                'userType' => 'pembeli',
                'role' => 'pembeli',
                'poin' => $pembeli->poin,
                'foto_profile' => $pembeli->foto_profile
            ]
        ]);
    }

    // Register untuk pembeli (self-register)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Buat Pembeli
            $pembeli = Pembeli::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'foto_profile' => '',
                'poin' => 0,
            ]);

            DB::commit();

            // Buat token
            $token = $pembeli->createToken('registration', ['pembeli', 'pembeli']);

            return response()->json([
                'message' => 'Pendaftaran pembeli berhasil',
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $pembeli->idPembeli,
                    'email' => $pembeli->email,
                    'nama' => $pembeli->nama,
                    'userType' => 'pembeli',
                    'role' => 'pembeli',
                    'poin' => $pembeli->poin
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

    // Ubah password pembeli
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pembeli = $request->user();

        if (!Hash::check($request->old_password, $pembeli->password)) {
            return response()->json([
                'message' => 'Password lama tidak valid'
            ], 400);
        }

        $pembeli->password = Hash::make($request->password);
        $pembeli->save();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }

    // Mendapatkan data pembeli yang login
    public function me(Request $request)
    {
        $pembeli = $request->user();

        return response()->json([
            'user' => [
                'id' => $pembeli->idPembeli,
                'email' => $pembeli->email,
                'nama' => $pembeli->nama,
                'userType' => 'pembeli',
                'role' => 'pembeli',
                'poin' => $pembeli->poin,
                'foto_profile' => $pembeli->foto_profile
            ]
        ]);
    }

    // Logout pembeli
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
