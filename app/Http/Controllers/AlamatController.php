<?php

namespace App\Http\Controllers;

use App\Models\Alamat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AlamatController extends Controller
{
    /**
     * Menampilkan daftar alamat pembeli
     */
    public function index(Request $request)
    {
        // Cek apakah request dari API atau web
        if ($request->expectsJson()) {
            $alamat = Alamat::where('idPembeli', $request->user()->idPembeli)->get();
            return response()->json(['alamat' => $alamat]);
        }

        // Ambil ID pembeli dari session untuk web
        $idPembeli = session('user')['id'];
        $alamat = Alamat::where('idPembeli', $idPembeli)->get();
        return view('customer.alamat.index', ['alamat' => $alamat]);
    }

    /**
     * Menampilkan form tambah alamat
     */
    public function create()
    {
        return view('customer.alamat.create');
    }

    /**
     * Menyimpan data alamat baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'alamatLengkap' => 'required|string|max:255',
            'jenis' => 'required|string|max:25',
            'statusDefault' => 'boolean',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Dapatkan ID pembeli
        if ($request->expectsJson()) {
            $idPembeli = $request->user()->idPembeli;
        } else {
            $idPembeli = session('user')['id'];
        }

        // Jika alamat ini diset sebagai default, ubah semua alamat lain menjadi non-default
        if ($request->statusDefault) {
            Alamat::where('idPembeli', $idPembeli)
                ->update(['statusDefault' => false]);
        }

        // Jika ini alamat pertama, jadikan default
        $alamatCount = Alamat::where('idPembeli', $idPembeli)->count();
        $isDefault = $alamatCount === 0 ? true : ($request->statusDefault ?? false);

        // Buat alamat baru
        $alamat = Alamat::create([
            'alamatLengkap' => $request->alamatLengkap,
            'jenis' => $request->jenis,
            'statusDefault' => $isDefault,
            'idPembeli' => $idPembeli,
        ]);

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Alamat berhasil ditambahkan',
                'alamat' => $alamat
            ], 201);
        }

        return redirect()->route('alamat.index')
            ->with('success', 'Alamat berhasil ditambahkan');
    }

    /**
     * Menampilkan detail alamat
     */
    public function show($id, Request $request)
    {
        $alamat = Alamat::findOrFail($id);

        // Cek apakah alamat ini milik pembeli yang sedang login
        if ($request->expectsJson()) {
            if ($request->user()->idPembeli != $alamat->idPembeli) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            return response()->json(['alamat' => $alamat]);
        } else {
            if (session('user')['id'] != $alamat->idPembeli) {
                return redirect()->route('unauthorized');
            }
            return view('customer.alamat.show', ['alamat' => $alamat]);
        }
    }

    /**
     * Menampilkan form edit alamat
     */
    public function edit($id)
    {
        $alamat = Alamat::findOrFail($id);

        // Cek apakah alamat ini milik pembeli yang sedang login
        if (session('user')['id'] != $alamat->idPembeli) {
            return redirect()->route('unauthorized');
        }

        return view('customer.alamat.edit', ['alamat' => $alamat]);
    }

    /**
     * Update data alamat
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'alamatLengkap' => 'required|string|max:255',
            'jenis' => 'required|string|max:25',
            'statusDefault' => 'boolean',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $alamat = Alamat::findOrFail($id);

        // Cek apakah alamat ini milik pembeli yang sedang login
        if ($request->expectsJson()) {
            if ($request->user()->idPembeli != $alamat->idPembeli) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            $idPembeli = $request->user()->idPembeli;
        } else {
            if (session('user')['id'] != $alamat->idPembeli) {
                return redirect()->route('unauthorized');
            }
            $idPembeli = session('user')['id'];
        }

        // Jika alamat ini diset sebagai default, ubah semua alamat lain menjadi non-default
        if ($request->statusDefault) {
            Alamat::where('idPembeli', $idPembeli)
                ->where('idAlamat', '!=', $id)
                ->update(['statusDefault' => false]);
        }

        // Update alamat
        $alamat->alamatLengkap = $request->alamatLengkap;
        $alamat->jenis = $request->jenis;
        $alamat->statusDefault = $request->statusDefault ?? false;
        $alamat->save();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Alamat berhasil diperbarui',
                'alamat' => $alamat
            ]);
        }

        return redirect()->route('alamat.index')
            ->with('success', 'Alamat berhasil diperbarui');
    }

    /**
     * Menghapus alamat
     */
    public function destroy($id, Request $request)
    {
        $alamat = Alamat::findOrFail($id);

        // Cek apakah alamat ini milik pembeli yang sedang login
        if ($request->expectsJson()) {
            if ($request->user()->idPembeli != $alamat->idPembeli) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
        } else {
            if (session('user')['id'] != $alamat->idPembeli) {
                return redirect()->route('unauthorized');
            }
        }

        // Jika alamat yang dihapus adalah default, pilih alamat lain menjadi default
        if ($alamat->statusDefault) {
            $anotherAddress = Alamat::where('idPembeli', $alamat->idPembeli)
                ->where('idAlamat', '!=', $id)
                ->first();
            if ($anotherAddress) {
                $anotherAddress->statusDefault = true;
                $anotherAddress->save();
            }
        }

        $alamat->delete();

        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Alamat berhasil dihapus'
            ]);
        }

        return redirect()->route('alamat.index')
            ->with('success', 'Alamat berhasil dihapus');
    }

    /**
     * Mencari alamat
     */
    public function search(Request $request)
    {
        $search = $request->input('search');

        if ($request->expectsJson()) {
            $idPembeli = $request->user()->idPembeli;
        } else {
            $idPembeli = session('user')['id'];
        }

        $alamat = Alamat::where('idPembeli', $idPembeli)
            ->where(function ($query) use ($search) {
                $query->where('alamatLengkap', 'like', "%{$search}%")
                    ->orWhere('jenis', 'like', "%{$search}%");
            })
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['alamat' => $alamat]);
        }

        return view('customer.alamat.index', ['alamat' => $alamat, 'search' => $search]);
    }
}
