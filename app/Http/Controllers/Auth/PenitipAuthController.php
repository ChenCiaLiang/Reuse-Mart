<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Penitip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PenitipAuthController extends Controller
{
    // Login untuk penitip
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

        // Cari penitip berdasarkan email
        $penitip = Penitip::where('email', $request->email)->first();
        if (!$penitip || !Hash::check($request->password, $penitip->password)) {
            return response()->json([
                'message' => 'Email atau password tidak valid'
            ], 401);
        }

        // Hapus token lama dengan nama yang sama jika ada
        $penitip->tokens()->where('name', $deviceName)->delete();

        // Buat token baru
        $token = $penitip->createToken($deviceName, ['penitip', 'penitip']);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $penitip->idPenitip,
                'email' => $penitip->email,
                'nama' => $penitip->nama,
                'userType' => 'penitip',
                'role' => 'penitip',
                'poin' => $penitip->poin,
                'saldo' => $penitip->saldo,
                'rating' => $penitip->rating,
                'foto_ktp' => $penitip->foto_ktp
            ]
        ]);
    }

    // Register untuk penitip (oleh CS)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:penitip,email',
            'password' => 'required|string|min:6',
            'alamat' => 'required|string|max:200',
            'nik' => 'required|string|max:16|unique:penitip,nik',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Validasi apakah yang mendaftarkan adalah CS
        $user = $request->user();
        $tokenAbilities = $user->currentAccessToken()->abilities;
        $role = $tokenAbilities[1] ?? '';

        if ($role !== 'customer service') {
            return response()->json([
                'message' => 'Tidak memiliki akses untuk mendaftarkan penitip'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Generate ID Penitip
            $latestPenitip = Penitip::orderBy('idPenitip', 'desc')->first();
            $nextId = 1;

            if ($latestPenitip) {
                $lastId = (int) substr($latestPenitip->idPenitip, 1);
                $nextId = $lastId + 1;
            }

            $idPenitip = 'T' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

            // Buat Penitip
            $penitip = Penitip::create([
                'idPenitip' => $idPenitip,
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'alamat' => $request->alamat,
                'nik' => $request->nik,
                'foto_ktp' => '',
                'poin' => 0,
                'bonus' => 0,
                'komisi' => 0,
                'saldo' => 0,
                'rating' => 0,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran penitip berhasil',
                'user' => [
                    'id' => $penitip->idPenitip,
                    'email' => $penitip->email,
                    'nama' => $penitip->nama,
                    'userType' => 'penitip',
                    'role' => 'penitip',
                    'nik' => $penitip->nik
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

    // Ubah password penitip
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $penitip = $request->user();

        if (!Hash::check($request->old_password, $penitip->password)) {
            return response()->json([
                'message' => 'Password lama tidak valid'
            ], 400);
        }

        $penitip->password = Hash::make($request->password);
        $penitip->save();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }

    // Mendapatkan data penitip yang login
    public function me(Request $request)
    {
        $penitip = $request->user();

        return response()->json([
            'user' => [
                'id' => $penitip->idPenitip,
                'email' => $penitip->email,
                'nama' => $penitip->nama,
                'userType' => 'penitip',
                'role' => 'penitip',
                'alamat' => $penitip->alamat,
                'poin' => $penitip->poin,
                'saldo' => $penitip->saldo,
                'rating' => $penitip->rating,
                'foto_ktp' => $penitip->foto_ktp
            ]
        ]);
    }

    // Logout penitip
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
