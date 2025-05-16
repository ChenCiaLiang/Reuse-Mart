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
                            'nama' => $user->nama,
                            'email' => $user->email,
                            'userType' => 'penitip',
                            'poin' => $user->poin,
                            'bonus' => $user->bonus,
                            'komisi' => $user->komisi,
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
                            'nama' => $user->nama,
                            'email' => $user->email,
                            'userType' => 'organisasi',
                            'logo' => $user->logo,
                            'alamat' => $user->alamat,
                        ]
                    ]);
                }
            } else {
                $user = Pegawai::where('username', $identifier)->first();
                if ($user && Hash::check($password, $user->password)) {
                    $user->tokens()->delete();
                    $jabatan = Jabatan::find($user->idJabatan);
                    $role = strtolower($jabatan->nama);
                    $token = $user->createToken('auth_token', ['pegawai', $role])->plainTextToken;

                    return response()->json([
                        'access_token' => $token,
                        'token_type' => 'Bearer',
                        'user' => [
                            'id' => $user->idPegawai,
                            'username' => $user->username,
                            'nama' => $user->nama,
                            'userType' => 'pegawai',
                            'role' => $role,
                            'jabatan' => $jabatan->nama,
                            'foto_profile' => $user->foto_profile
                        ]
                    ]);
                }
            }
            return response()->json([
                'message' => 'Username/email atau password tidak valid'
            ], 401);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function logout(Request $request)
    {
        dd($request->all());
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
