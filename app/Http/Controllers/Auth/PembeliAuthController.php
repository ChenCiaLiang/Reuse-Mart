<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Pembeli;
use App\Models\Penitip;
use App\Models\Organisasi;
use App\Models\Pegawai;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PembeliAuthController extends Controller
{
    // public function login(Request $request)
    // {
    //     try {
    //         $validator = Validator::make($request->all(), [
    //             'email' => 'required|string|email',
    //             'password' => 'required|string',
    //         ]);

    //         if ($validator->fails()) {
    //             return response()->json(['errors' => $validator->errors()], 422);
    //         }

    //         $pembeli = Pembeli::where('email', $request->email)->first();
    //         if (!$pembeli || !Hash::check($request->password, $pembeli->password)) {
    //             return response()->json([
    //                 'message' => 'Email atau password tidak valid'
    //             ], 401);
    //         }

    //         $pembeli->tokens()->delete();
    //         $token = $pembeli->createToken('auth_token', ['pembeli'])->plainTextToken;

    //         return response()->json([
    //             'access_token' => $token,
    //             'token_type' => 'Bearer',
    //             'user' => [
    //                 'id' => $pembeli->idPembeli,
    //                 'nama' => $pembeli->nama,
    //                 'email' => $pembeli->email,
    //                 'foto_profile' => $pembeli->foto_profile,
    //                 'poin' => $pembeli->poin,
    //                 'userType' => 'pembeli',
    //             ]
    //         ]);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'message' => 'Error: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi',
            'password' => 'required|string|min:6|confirmed',
            'foto_profile' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($validator->fails()) {
            // Jika request mengharapkan JSON, kembalikan respons JSON
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            // Jika request dari web form, kembalikan ke halaman dengan error
            return redirect()->back()->withErrors($validator)->withInput();
        }
        
        DB::beginTransaction();
        try {
            $fotoPath = null;
            if ($request->hasFile('foto_profile')) {
                $foto_profile = $request->file('foto_profile');
                $filename = 'pembeli_' . time() . '.' . $foto_profile->getClientOriginalExtension();
                $foto_profile->move(public_path('user/pembeli'), $filename);
                $fotoPath = 'user/pembeli/' . $filename;
            } else {
                $fotoPath = 'pembeli/default_foto_profile.png';
            }

            $pembeli = Pembeli::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'foto_profile' => $fotoPath,
                'poin' => 0,
            ]);
            DB::commit();
            
            // Jika request mengharapkan JSON, kembalikan respons JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pendaftaran pembeli berhasil',
                    'user' => [
                        'id' => $pembeli->idPembeli,
                        'email' => $pembeli->email,
                        'nama' => $pembeli->nama,
                        'foto_profile' => asset($pembeli->foto_profile),
                        'poin' => $pembeli->poin,
                        'userType' => 'pembeli',
                    ]
                ], 201);
            }
            
            // Untuk web form, buat token dan set session
            $token = $pembeli->createToken('auth_token', ['pembeli'])->plainTextToken;
            
            session([
                'access_token' => $token,
                'user_id' => $pembeli->idPembeli, 
                'user_type' => 'pembeli',
                'user_name' => $pembeli->nama,
                'user_email' => $pembeli->email,
                'user_foto_profile' => $pembeli->foto_profile,
                'user_poin' => $pembeli->poin,
            ]);
            
            return redirect()->route('customer.homePage')->with('success', 'Registrasi berhasil!');
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            // Jika request mengharapkan JSON, kembalikan respons JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pendaftaran gagal',
                    'error' => $e->getMessage()
                ], 500);
            }
            
            // Untuk web form, kembalikan ke halaman dengan error
            return redirect()->back()->withErrors(['error' => 'Pendaftaran gagal: ' . $e->getMessage()])->withInput();
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
