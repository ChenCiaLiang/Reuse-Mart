<?php

namespace App\Http\Controllers;

use App\Models\Organisasi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class OrganisasiController extends Controller
{
    /**
     * Menampilkan daftar organisasi
     */
    public function index(Request $request)
    {
        // Pencarian organisasi
        $search = $request->input('search');

        $organisasi = Organisasi::when($search, function ($query) use ($search) {
            return $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('idOrganisasi', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        })
            ->orderBy('idOrganisasi')
            ->paginate(10);

        return view('pegawai.admin.manajemenOrganisasi..index', compact('organisasi', 'search'));
    }

    /**
     * Menampilkan detail organisasi
     */
    public function show($id)
    {
        $organisasi = Organisasi::findOrFail($id);
        // Ambil data request donasi untuk organisasi ini
        $requestDonasi = $organisasi->requestDonasis()->orderBy('tanggalRequest', 'desc')->get();
        return view('pegawai.admin.manajemenOrganisasi..show', compact('organisasi', 'requestDonasi'));
    }

    /**
     * Menampilkan form edit organisasi
     */
    public function edit($id)
    {
        $organisasi = Organisasi::findOrFail($id);
        return view('pegawai.admin.manajemenOrganisasi..edit', compact('organisasi'));
    }

    /**
     * Update data organisasi
     */
    public function update(Request $request, $id)
    {
        $organisasi = Organisasi::findOrFail($id);

        // Validasi input (hapus password dari validasi)
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:organisasi,email,' . $id . ',idOrganisasi',
            'alamat' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update organisasi
        $organisasi->nama = $request->nama;
        $organisasi->email = $request->email;
        $organisasi->alamat = $request->alamat;

        // Hapus kode reset password di bawah ini
        /*
        // Update password jika diisi
        if ($request->filled('password')) {
            $organisasi->password = Hash::make($request->password);
        }
        */

        $organisasi->save();

        return redirect()->route('admin.organisasi.index')->with('success', 'Data organisasi berhasil diperbarui!');
    }
    /**
     * Hapus data organisasi
     */
    public function destroy($id)
    {
        $organisasi = Organisasi::findOrFail($id);

        // Hapus logo jika bukan default
        if ($organisasi->logo != 'organisasi/default_logo.png') {
            Storage::disk('public')->delete($organisasi->logo);
        }

        $organisasi->delete();

        return redirect()->route('admin.organisasi.index')
            ->with('success', 'Organisasi berhasil dihapus!');
    }
}
