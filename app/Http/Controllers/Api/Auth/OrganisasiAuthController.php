<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class OrganisasiAuthController extends Controller
{
    // Login untuk organisasi
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

        // Cari organisasi berdasarkan email
        $organisasi = Organisasi::where('email', $request->email)->first();
        if (!$organisasi || !Hash::check($request->password, $organisasi->password)) {
            return response()->json([
                'message' => 'Email atau password tidak valid'
            ], 401);
        }

        // Hapus token lama dengan nama yang sama jika ada
        $organisasi->tokens()->where('name', $deviceName)->delete();

        // Buat token baru
        $token = $organisasi->createToken($deviceName, ['organisasi', 'organisasi']);

        return response()->json([
            'access_token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $organisasi->idOrganisasi,
                'email' => $organisasi->email,
                'nama' => $organisasi->nama,
                'userType' => 'organisasi',
                'role' => 'organisasi',
                'alamat' => $organisasi->alamat,
                'logo' => $organisasi->logo
            ]
        ]);
    }

    // Register untuk organisasi (self-register)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:organisasi,email',
            'password' => 'required|string|min:6|confirmed',
            'alamat' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            // Generate ID Organisasi
            $latestOrganisasi = Organisasi::orderBy('idOrganisasi', 'desc')->first();
            $nextId = 1;

            if ($latestOrganisasi) {
                $lastId = (int) substr($latestOrganisasi->idOrganisasi, 3);
                $nextId = $lastId + 1;
            }

            $idOrganisasi = 'ORG' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

            // Buat Organisasi
            $organisasi = Organisasi::create([
                'idOrganisasi' => $idOrganisasi,
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'logo' => '',
                'alamat' => $request->alamat,
            ]);

            DB::commit();

            // Buat token
            $token = $organisasi->createToken('registration', ['organisasi', 'organisasi']);

            return response()->json([
                'message' => 'Pendaftaran organisasi berhasil',
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $organisasi->idOrganisasi,
                    'email' => $organisasi->email,
                    'nama' => $organisasi->nama,
                    'userType' => 'organisasi',
                    'role' => 'organisasi',
                    'alamat' => $organisasi->alamat
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

    // Ubah password organisasi
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $organisasi = $request->user();

        if (!Hash::check($request->old_password, $organisasi->password)) {
            return response()->json([
                'message' => 'Password lama tidak valid'
            ], 400);
        }

        $organisasi->password = Hash::make($request->password);
        $organisasi->save();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }

    // Mendapatkan data organisasi yang login
    public function me(Request $request)
    {
        $organisasi = $request->user();

        return response()->json([
            'user' => [
                'id' => $organisasi->idOrganisasi,
                'email' => $organisasi->email,
                'nama' => $organisasi->nama,
                'userType' => 'organisasi',
                'role' => 'organisasi',
                'alamat' => $organisasi->alamat,
                'logo' => $organisasi->logo
            ]
        ]);
    }

    // Logout organisasi
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
