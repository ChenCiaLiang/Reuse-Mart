<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Jabatan;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PegawaiAuthController extends Controller
{
    // Login untuk pegawai (admin, CS, pegawai gudang, hunter)
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'username' => 'required|string',
                'password' => 'required|string',
                // 'device_name' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // $deviceName = $request->device_name ?? 'default';

            // Cari pegawai berdasarkan username
            $pegawai = Pegawai::where('username', $request->username)->first();
            if (!$pegawai || !Hash::check($request->password, $pegawai->password)) {
                return response()->json([
                    'message' => 'Username atau password tidak valid'
                ], 401);
            }

            $pegawai->tokens()->delete();

            $jabatan = Jabatan::find($pegawai->idJabatan);
            $role = strtolower($jabatan->nama);

            // Hapus token lama dengan nama yang sama jika ada
            // $pegawai->tokens()->where('name', $deviceName)->delete();

            // Buat token baru dengan abilities sesuai role
            // $token = $pegawai->createToken($deviceName, ['pegawai', $role]);
            $token = $pegawai->createToken('auth_token', ['pegawai', $role]);

            return response()->json([
                'access_token' => $token->plainTextToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $pegawai->idPegawai,
                    'username' => $pegawai->username,
                    'nama' => $pegawai->nama,
                    'userType' => 'pegawai',
                    'role' => $role,
                    'jabatan' => $jabatan->nama,
                    'foto_profile' => $pegawai->foto_profile
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    // Register untuk Pegawai (hanya oleh Admin)
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'username' => 'required|string|max:10|unique:pegawai,username',
            'password' => 'required|string|min:6',
            'idJabatan' => 'required|exists:jabatan,idJabatan',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }
        // Validasi apakah yang mendaftarkan adalah Admin
        $user = $request->user();
        $role = $user->getJabatanNama();

        if ($role !== 'admin') {
            return response()->json([
                'message' => 'Tidak memiliki akses sebagai admin'
            ], 403);
        }

        DB::beginTransaction();
        try {
            // Generate ID Pegawai
            $latestPegawai = Pegawai::orderBy('idPegawai', 'desc')->first();
            $nextId = 1;

            if ($latestPegawai) {
                $lastId = (int) substr($latestPegawai->idPegawai, 1);
                $nextId = $lastId + 1;
            }

            $idPegawai = 'P' . str_pad($nextId, 2, '0', STR_PAD_LEFT);

            // Buat Pegawai
            $pegawai = Pegawai::create([
                'idPegawai' => $idPegawai,
                'nama' => $request->nama,
                'username' => $request->username,
                'password' => Hash::make($request->password),
                'foto_profile' => '',
                'idJabatan' => $request->idJabatan,
            ]);

            $jabatan = Jabatan::find($request->idJabatan);
            $role = strtolower($jabatan->nama);

            DB::commit();

            return response()->json([
                'message' => 'Pendaftaran pegawai berhasil',
                'user' => [
                    'id' => $pegawai->idPegawai,
                    'username' => $pegawai->username,
                    'nama' => $pegawai->nama,
                    'userType' => 'pegawai',
                    'role' => $role,
                    'jabatan' => $jabatan->nama
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

    // Ubah password pegawai
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'old_password' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $pegawai = $request->user();

        if (!Hash::check($request->old_password, $pegawai->password)) {
            return response()->json([
                'message' => 'Password lama tidak valid'
            ], 400);
        }

        $pegawai->password = Hash::make($request->password);
        $pegawai->save();

        return response()->json([
            'message' => 'Password berhasil diubah'
        ]);
    }

    // Mendapatkan data pegawai yang login
    public function me(Request $request)
    {
        $pegawai = $request->user();

        if (!$pegawai) {
            return response()->json(['message' => 'Bukan Pegawai'], 401);
        }

        $jabatan = Jabatan::find($pegawai->idJabatan);
        $tokenAbilities = $pegawai->currentAccessToken()->abilities ?? [];
        $role = $tokenAbilities[1] ?? '';

        return response()->json([
            'user' => [
                'id' => $pegawai->idPegawai,
                'username' => $pegawai->username,
                'nama' => $pegawai->nama,
                'userType' => 'pegawai',
                'role' => $role,
                'jabatan' => $jabatan->nama
            ]
        ]);
    }

    // Logout pegawai
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Berhasil logout'
        ]);
    }
}
