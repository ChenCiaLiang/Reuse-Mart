<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PegawaiController extends Controller
{
    /**
     * Menampilkan daftar pegawai
     */
    public function index(Request $request)
    {
        // Pencarian pegawai
        $search = $request->input('search');
        
        $pegawai = Pegawai::when($search, function ($query) use ($search) {
                return $query->where('nama', 'like', '%' . $search . '%')
                    ->orWhere('idPegawai', 'like', '%' . $search . '%')
                    ->orWhere('username', 'like', '%' . $search . '%');
            })
            ->orderBy('nama')
            ->paginate(10);
            
        return view('admin.pegawai.index', compact('pegawai', 'search'));
    }

    /**
     * Menampilkan form tambah pegawai
     */
    public function create()
    {
        $jabatan = Jabatan::orderBy('nama')->get();
        return view('admin.pegawai.create', compact('jabatan'));
    }

    /**
     * Menyimpan data pegawai baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'idPegawai' => 'required|string|max:10|unique:pegawai,idPegawai',
            'nama' => 'required|string|max:50',
            'username' => 'required|string|max:10|unique:pegawai,username',
            'password' => 'required|string|min:6',
            'idJabatan' => 'required|exists:jabatan,idJabatan',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Buat pegawai baru
        Pegawai::create([
            'idPegawai' => $request->idPegawai,
            'nama' => $request->nama,
            'username' => $request->username,
            'password' => Hash::make($request->password),
            'idJabatan' => $request->idJabatan,
        ]);

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil ditambahkan!');
    }

    /**
     * Menampilkan detail pegawai
     */
    public function show($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        return view('admin.pegawai.show', compact('pegawai'));
    }

    /**
     * Menampilkan form edit pegawai
     */
    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $jabatan = Jabatan::orderBy('nama')->get();
        return view('admin.pegawai.edit', compact('pegawai', 'jabatan'));
    }

    /**
     * Update data pegawai
     */
    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        
        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50',
            'username' => 'required|string|max:10|unique:pegawai,username,' . $id . ',idPegawai',
            'password' => 'nullable|string|min:6',
            'idJabatan' => 'required|exists:jabatan,idJabatan',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update pegawai
        $pegawai->nama = $request->nama;
        $pegawai->username = $request->username;
        if ($request->filled('password')) {
            $pegawai->password = Hash::make($request->password);
        }
        $pegawai->idJabatan = $request->idJabatan;
        $pegawai->save();

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Data pegawai berhasil diperbarui!');
    }

    /**
     * Hapus data pegawai
     */
    public function destroy($id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->delete();

        return redirect()->route('admin.pegawai.index')
            ->with('success', 'Pegawai berhasil dihapus!');
    }
}