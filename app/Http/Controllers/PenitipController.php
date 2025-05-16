<?php

namespace App\Http\Controllers;

use App\Models\Penitip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class PenitipController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $penitip = Penitip::when($search, function ($query) use ($search) {
            return $query->where('nama', 'like', '%' . $search . '%')
                ->orWhere('idPenitip', 'like', '%' . $search . '%')
                ->orWhere('email', 'like', '%' . $search . '%');
        })
            ->orderBy('nama')
            ->paginate(10);

        return view('cs.penitip.index', compact('penitip', 'search'));
    }

    public function create()
    {
        return view('cs.penitip.create');
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'idPenitip' => 'required|string|max:10|unique:penitip,idPenitip',
            'nama' => 'required|string|max:50',
            'email' => 'required|string|email|max:50|unique:pembeli,email|unique:penitip,email|unique:organisasi,email',
            'password' => 'required|string|min:6',
            'alamat' => 'required|string|max:200',
            'nik' => 'required|string|max:16|unique:penitip,nik',
            'foto_ktp' => 'required|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $fotoPath = null;
        if ($request->hasFile('foto_ktp')) {
            $foto_ktp = $request->file('foto_ktp');
            $filename = 'penitip_' . $request->nama . '.' . $foto_ktp->getClientOriginalExtension();
            $foto_ktp->move(public_path('user/penitip'), $filename);
            $fotoPath = 'user/penitip/' . $filename;
        } else {
            $fotoPath = 'penitip/default_foto_ktp.png';
        }

        Penitip::create([
            'idPenitip' => $request->idPenitip,
            'nama' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'alamat' => $request->alamat,
            'nik' => $request->nik,
            'foto_ktp' => $fotoPath,
            'poin' => 0,
            'bonus' => 0.0,
            'komisi' => 0.0,
            'saldo' => 0.0,
            'rating' => 0.0,
        ]);

        return redirect()->route('cs.penitip.index')
            ->with('success', 'Penitip berhasil ditambahkan!');
    }

    public function show($id)
    {
        $penitip = Penitip::findOrFail($id);
        return view('cs.penitip.show', compact('penitip'));
    }

    public function edit($id)
    {
        $penitip = Penitip::findOrFail($id);
        return view('cs.penitip.edit', compact('penitip'));
    }

    public function update(Request $request, $id)
    {
        $penitip = Penitip::findOrFail($id);

        // Validasi input
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:50|unique:penitip,nama,' . $id . ',idPenitip',
            'email' => 'required|string|email|max:50|unique:penitip,email,' . $id . ',idPenitip|unique:pembeli,email|unique:organisasi,email',
            'alamat' => 'required|string|max:200',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Update pegawai
        $penitip->nama = $request->nama;
        $penitip->email = $request->email;
        $penitip->save();

        return redirect()->route('cs.penitip.index')
            ->with('success', 'Data penitip berhasil diperbarui!');
    }

    /**
     * Hapus data pegawai
     */
    public function destroy($id)
    {
        $penitip = Penitip::findOrFail($id);
        $penitip->delete();

        return redirect()->route('cs.penitip.index')
            ->with('success', 'Penitip berhasil dihapus!');
    }
}
