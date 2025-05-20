@extends('layouts.owner')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Update Informasi Donasi
    </h2>

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <form action="{{ route('owner.donasi.update', $donasi->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tanggalPemberian" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Donasi</label>
                    <input type="date" id="tanggalPemberian" name="tanggalPemberian" 
                           value="{{ old('tanggalPemberian', $donasi->tanggalPemberian->format('Y-m-d')) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('tanggalPemberian')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <div>
                    <label for="namaPenerima" class="block text-sm font-medium text-gray-700 mb-1">Nama Penerima</label>
                    <input type="text" id="namaPenerima" name="namaPenerima" 
                           value="{{ old('namaPenerima', $donasi->namaPenerima) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md">
                    @error('namaPenerima')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h3 class="text-lg font-medium mb-4">Informasi Barang</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">ID Produk</span>
                        <span class="block px-3 py-2 bg-gray-100 rounded-md">{{ $donasi->produk->idProduk }}</span>
                    </div>
                    
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</span>
                        <span class="block px-3 py-2 bg-gray-100 rounded-md">{{ $donasi->produk->deskripsi }}</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h3 class="text-lg font-medium mb-4">Informasi Organisasi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Organisasi</span>
                        <span class="block px-3 py-2 bg-gray-100 rounded-md">{{ $donasi->request->organisasi->nama }}</span>
                    </div>
                    
                    <div>
                        <span class="block text-sm font-medium text-gray-700 mb-1">Request</span>
                        <span class="block px-3 py-2 bg-gray-100 rounded-md">{{ $donasi->request->request }}</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('owner.donasi.history') }}" class="px-4 py-2 bg-gray-300 rounded-md text-gray-800">Batal</a>
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection