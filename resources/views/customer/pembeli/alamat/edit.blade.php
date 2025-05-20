@extends('layouts.customer')

@section('content')
<div class="container px-6 mx-auto py-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Edit Alamat</h1>
    
    <div class="bg-white shadow-md rounded-lg p-6 max-w-xl mx-auto">
        <form action="{{ route('pembeli.alamat.update', $alamat->idAlamat) }}" method="POST">
            @csrf
            @method('PUT')
            
            @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            
            <div class="mb-4">
                <label for="jenis" class="block text-sm font-medium text-gray-700 mb-1">Jenis Alamat</label>
                <input type="text" id="jenis" name="jenis" required
                       value="{{ old('jenis', $alamat->jenis) }}" 
                       placeholder="Contoh: Rumah, Kantor, Apartemen, dll"
                       class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500">
            </div>
            
            <div class="mb-4">
                <label for="alamatLengkap" class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap</label>
                <textarea id="alamatLengkap" name="alamatLengkap" required
                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                          rows="4" placeholder="Masukkan alamat lengkap termasuk kecamatan, kota/kabupaten, dan kode pos">{{ old('alamatLengkap', $alamat->alamatLengkap) }}</textarea>
            </div>
            
            <div class="mb-4">
                <label class="flex items-center">
                    <input type="checkbox" name="statusDefault" value="1" {{ old('statusDefault', $alamat->statusDefault) ? 'checked' : '' }}
                           class="rounded text-green-600 focus:ring-green-500 h-4 w-4">
                    <span class="ml-2 text-sm text-gray-700">Jadikan sebagai alamat utama</span>
                </label>
            </div>
            
            <div class="flex justify-end space-x-3">
                <a href="{{ route('pembeli.alamat.index') }}" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">
                    Batal
                </a>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                    Perbarui Alamat
                </button>
            </div>
        </form>
    </div>
</div>
@endsection