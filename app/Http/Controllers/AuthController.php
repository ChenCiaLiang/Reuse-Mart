<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Pembeli;
use App\Models\Penitip;
use App\Models\Pegawai;
use App\Models\Organisasi;
use App\Models\Jabatan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function registerPembeli(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi,email|unique:pegawai,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $user = Pembeli::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'poin' => 0,
            ]);
            DB::commit();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pembeli berhasil terdaftar',
                    'data' => $user,
                ], 201);
            }

            $token = $user->createToken('auth_token', ['pembeli'])->plainTextToken;
            Auth::guard('pembeli')->login($user);
            session([
                'user' => $user,
                'role' => 'pembeli',
            ]);

            return redirect()->route('homePage')->with('success', 'Registrasi berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal melakukan registrasi'
                ], 401);
            }
            return redirect()->route('register.pembeli')->with('error', 'Gagal melakukan registrasi');
        }
    }

    public function registerOrganisasi(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:organisasi,email|unique:pembeli,email|unique:penitip,email|unique:pegawai,email',
            'password' => 'required|string|min:6',
            'alamat' => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return redirect()->back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        try {
            $user = Organisasi::create([
                'nama' => $request->nama,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'alamat' => $request->alamat,
            ]);
            DB::commit();

            $token = $user->createToken('auth_token', ['organisasi'])->plainTextToken;
            Auth::guard('organisasi')->login($user);
            session([
                'user' => $user,
                'role' => 'organisasi',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Organisasi berhasil terdaftar',
                    'data' => $user,
                ], 201);
            }

            return redirect()->route('customer.homePage')->with('success', 'Registrasi berhasil!');
        } catch (\Exception $e) {
            DB::rollBack();

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Gagal melakukan registrasi'
                ], 401);
            }
            return redirect()->route('register.organisasi')->with('error', 'Gagal melakukan registrasi');
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = Pembeli::where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('auth_token', ["pembeli"])->plainTextToken;
            Auth::guard('pembeli')->login($user);
            session([
                'user' => $user,
                'role' => 'pembeli',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ],
                ]);
            }
            return redirect()->route('homePage')->with('success', 'Login successful');
        }

        $user = Penitip::where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('auth_token', ["penitip"])->plainTextToken;
            Auth::guard('penitip')->login($user);
            session([
                'user' => $user,
                'role' => 'penitip',
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ],
                ]);
            }
            return redirect()->route('homePage')->with('success', 'Login successful');
        }

        $user = pegawai::where('email', $credentials['email'])->first();

        if ($user && Hash::check($credentials['password'], $user->password)) {
            $jabatan = Jabatan::find($user->idJabatan);
            $role = strtolower($jabatan->nama);

            $token = $user->createToken('auth_token', [$role])->plainTextToken;
            if (!$role) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Jabatan tidak ditemukan'
                    ], 404);
                }
            } else {
                $token = $user->createToken('auth_token', [$role])->plainTextToken;
                Auth::guard('pegawai')->login($user);
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Login successful',
                        'data' => [
                            'user' => $user,
                            'token' => $token,
                        ],
                    ]);
                }
                session([
                    'user' => $user,
                    'role' => $role,
                ]);
                return redirect()->route($role . '.dashboard')->with('success', 'Login successful');
            }
            return redirect()->route('loginPage')->with('success', 'Login successful');
        }

        $user = Organisasi::where('email', $credentials['email'])->first();
        if ($user && Hash::check($credentials['password'], $user->password)) {
            $token = $user->createToken('auth_token', ["organisasi"])->plainTextToken;
            Auth::guard('organisasi')->login($user);
            session([
                'user' => $user,
                'role' => 'organisasi',
            ]);
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Login successful',
                    'data' => [
                        'user' => $user,
                        'token' => $token,
                    ],
                ]);
            }
            return redirect()->route('homePage')->with('success', 'Login successful');
        }

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Email atau password salah'
            ], 401);
        }
        return redirect()->route('loginPage')->with('error', 'Email atau password salah');
    }

    public function logout(Request $request)
    {
        $guard = Auth::getDefaultDriver();

        // Logout dari guard yang aktif
        Auth::guard($guard)->logout();

        // Invalidate session
        $request->session()->invalidate();

        // Regenerate CSRF token
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            $user = $request->user();
            $user->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout successful'
            ]);
        }

        return redirect()->route('loginPage')->with('success', 'Logout successful');
    }

    // Fungsi untuk reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $resetRecord = DB::table('password_resets')->where('token', $request->token)->first();

        if (!$resetRecord) {
            return response()->json(['message' => 'Token tidak valid atau sudah kadaluarsa.'], 400);
        }

        // Coba cari pembeli dulu
        $user = Pembeli::where('email', $resetRecord->email)->first();
        if ($user) {
            $user->password = Hash::make($request->password);
            $user->save();
        } else {
            // Coba cari penitip
            $user = Penitip::where('email', $resetRecord->email)->first();
            if ($user) {
                $user->password_penitip = Hash::make($request->password);
                $user->save();
            } else {
                $user = Organisasi::where('email', $resetRecord->email)->first();
                if ($user) {
                    $user->password_organisasi = Hash::make($request->password);
                    $user->save();
                } else {
                    return response()->json(['message' => 'Email tidak ditemukan.'], 404);
                }
            }
        }

        // Hapus token setelah digunakan
        DB::table('password_resets')->where('email', $resetRecord->email)->delete();

        return response()->json(['message' => 'Password berhasil diperbarui.']);
    }

    // public function loginMobile(Request $request)
    // {
    //     $credentials = $request->validate([
    //         'email' => 'required|email',
    //         'password' => 'required'
    //     ]);

    //     // Cek Pembeli
    //     $user = Pembeli::where('email', $credentials['email'])->first();
    //     if ($user && Hash::check($credentials['password'], $user->password)) {
    //         $token = $user->createToken('auth_token', ['pembeli'])->plainTextToken;
    //         return response()->json([
    //             'message' => 'Login successful',
    //             'token' => $token,
    //             'role' => 'pembeli',
    //             'user' => $user
    //         ]);
    //     }

    //     // Cek Penitip
    //     $user = Penitip::where('email', $credentials['email'])->first();
    //     if ($user && Hash::check($credentials['password'], $user->password_penitip)) {
    //         $token = $user->createToken('auth_token', ['penitip'])->plainTextToken;
    //         return response()->json([
    //             'message' => 'Login successful',
    //             'token' => $token,
    //             'role' => 'penitip',
    //             'user' => $user
    //         ]);
    //     }

    //     // Cek Kurir
    //     $user = Pegawai::where('email', $credentials['email'])->first();
    //     if ($user && Hash::check($credentials['password'], $user->password_pegawai)) {
    //         if ($user->jabatan->id_jabatan == 4) {
    //             $token = $user->createToken('auth_token', ['kurir'])->plainTextToken;
    //             return response()->json([
    //                 'message' => 'Login successful',
    //                 'token' => $token,
    //                 'role' => 'kurir',
    //                 'user' => $user
    //             ]);
    //         }
    //     }

    //     return response()->json([
    //         'message' => 'Email atau password salah, atau role tidak diizinkan di mobile.'
    //     ], 401);
    // }
}
