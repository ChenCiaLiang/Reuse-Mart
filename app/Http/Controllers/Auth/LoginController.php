<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pegawai;
use App\Models\Penitip;
use App\Models\Pembeli;
use App\Models\Organisasi;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function login(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'loginID' => 'required|string',
                'password' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $identifier = $request->loginID;
            $password = $request->password;
            // Cek apakah format identifier adalah email
            if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
                // Coba login sebagai Pembeli
                $user = Pembeli::where('email', $identifier)->first();
                if ($user && Hash::check($password, $user->password)) {
                    $user->tokens()->delete();
                    $token = $user->createToken('auth_token', ['pembeli'])->plainTextToken;

                    return response()->json([
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user' => [
                            'id' => $user->idPembeli,
                            'nama' => $user->nama,
                            'email' => $user->email,
                            'foto_profile' => $user->foto_profile,
                            'poin' => $user->poin,
                            'userType' => 'pembeli',
                        ]
                    ]);
                }

                // Coba login sebagai Penitip
                $user = Penitip::where('email', $identifier)->first();
                if ($user && Hash::check($password, $user->password)) {
                    $user->tokens()->delete();
                    $token = $user->createToken('auth_token', ['penitip'])->plainTextToken;

                    return response()->json([
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user' => [
                            'id' => $user->idPenitip,
                            'email' => $user->email,
                            'nama' => $user->nama,
                            'userType' => 'penitip',
                            'poin' => $user->poin,
                            'saldo' => $user->saldo,
                            'rating' => $user->rating,
                        ]
                    ]);
                }

                // Coba login sebagai Organisasi
                $user = Organisasi::where('email', $identifier)->first();
                if ($user && Hash::check($password, $user->password)) {
                    $user->tokens()->delete();
                    $token = $user->createToken('auth_token', ['organisasi'])->plainTextToken;

                    return response()->json([
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user' => [
                            'id' => $user->idOrganisasi,
                            'email' => $user->email,
                            'nama' => $user->nama,
                            'userType' => 'organisasi',
                            'logo' => $user->logo
                        ]
                    ]);
                }
            }
            // Cek apakah format identifier adalah ID Penitip (Txx)
            // else if (preg_match('/^T\d+/', $identifier)) {
            //     $user = Penitip::where('idPenitip', $identifier)->first();
            //     if ($user && Hash::check($password, $user->password)) {
            //         $user->tokens()->delete();
            //         $token = $user->createToken('auth_token', ['penitip'])->plainTextToken;

            //         return response()->json([
            //             'access_token' => $token,
            //             'token_type' => 'Bearer',
            //             'user' => [
            //                 'id' => $user->idPenitip,
            //                 'email' => $user->email,
            //                 'nama' => $user->nama,
            //                 'userType' => 'penitip',
            //                 'poin' => $user->poin,
            //                 'saldo' => $user->saldo,
            //                 'rating' => $user->rating,
            //             ]
            //         ]);
            //     }
            // }
            // // Cek apakah format identifier adalah ID Organisasi (ORGxx)
            // else if (preg_match('/^ORG\d+/', $identifier)) {
            //     $user = Organisasi::where('idOrganisasi', $identifier)->first();
            //     if ($user && Hash::check($password, $user->password)) {
            //         $user->tokens()->delete();
            //         $token = $user->createToken('auth_token', ['organisasi'])->plainTextToken;

            //         return response()->json([
            //             'access_token' => $token,
            //             'token_type' => 'Bearer',
            //             'user' => [
            //                 'id' => $user->idOrganisasi,
            //                 'email' => $user->email,
            //                 'nama' => $user->nama,
            //                 'userType' => 'organisasi',
            //                 'logo' => $user->logo
            //             ]
            //         ]);
            //     }
            // }
            // // Cek apakah format identifier adalah ID Pegawai (Pxx) atau username
            // else {
            //     // Coba dengan ID Pegawai
            //     if (preg_match('/^P\d+/', $identifier)) {
            //         $user = Pegawai::where('idPegawai', $identifier)->first();
            //     } else {
            //         // Coba dengan username
            //         $user = Pegawai::where('username', $identifier)->first();
            //     }

            //     if ($user && Hash::check($password, $user->password)) {
            //         $user->tokens()->delete();

            //         $jabatan = Jabatan::find($user->idJabatan);
            //         $role = strtolower($jabatan->nama);

            //         $token = $user->createToken('auth_token', ['pegawai', $role])->plainTextToken;

            //         return response()->json([
            //             'access_token' => $token,
            //             'token_type' => 'Bearer',
            //             'user' => [
            //                 'id' => $user->idPegawai,
            //                 'username' => $user->username,
            //                 'nama' => $user->nama,
            //                 'userType' => 'pegawai',
            //                 'role' => $role,
            //                 'jabatan' => $jabatan->nama,
            //                 'foto_profile' => $user->foto_profile
            //             ]
            //         ]);
            //     }
            // }

            // Jika semua percobaan login gagal
            return response()->json([
                'message' => 'Username/email atau password tidak valid'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}
