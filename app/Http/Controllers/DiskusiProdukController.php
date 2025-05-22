<?php

namespace App\Http\Controllers;

use App\Models\DiskusiProduk;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DiskusiProdukController extends Controller
{
    public function index(Request $request, $idProduk)
    {
        $produk = Produk::findOrFail($idProduk);

        $diskusi = DiskusiProduk::where('idProduk', $idProduk)
            ->with(['pembeli', 'pegawai'])
            ->orderBy('tanggalDiskusi', 'asc')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['diskusi' => $diskusi]);
        }

        return view('produk.diskusi', [
            'produk' => $produk,
            'diskusi' => $diskusi
        ]);
    }

    public function store(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'pesan' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        if ((!in_array(session('role'), ['pembeli', 'cs']))) {
            return redirect()->route('loginPage')->with('error', 'Anda harus login terlebih dahulu untuk mengirim diskusi.');
        }

        $produk = Produk::findOrFail($id);

        $diskusiData = [
            'pesan' => $request->pesan,
            'tanggalDiskusi' => now(),
            'idProduk' => $id,
        ];

        if (session('role') === 'pembeli') {
            $diskusiData['idPembeli'] = session('user')['idPembeli'];
            $diskusiData['idPegawai'] = null;
        } elseif (session('role') === 'cs') {
            $diskusiData['idPegawai'] = session('user')['idPegawai'];
            $diskusiData['idPembeli'] = null;
        } else {
            return redirect()->back()->with('error', 'Anda tidak memiliki izin untuk mengirim diskusi.');
        }

        $diskusi = DiskusiProduk::create($diskusiData);

        return redirect()->route('produk.show', $id)->with('success', 'Pesan diskusi berhasil ditambahkan');
    }
}
