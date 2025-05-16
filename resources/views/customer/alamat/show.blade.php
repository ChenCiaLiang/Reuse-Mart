@extends('layouts.customer')

@section('content')
<div class="container px-6 mx-auto py-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Detail Alamat</h1>
    
    <div class="bg-white shadow-md rounded-lg p-6 max-w-xl mx-auto">
        <div class="flex justify-between items-start mb-4">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">{{ $alamat->jenis }}</h2>
                @if ($alamat->statusDefault)
                <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Alamat Utama</span>
                @endif
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('alamat.edit', $alamat->idAlamat) }}" class="px-3 py-1 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form action="{{ route('alamat.destroy', $alamat->idAlamat) }}" method="POST" class="inline-block"
                      onsubmit="return confirm('Yakin ingin menghapus alamat ini?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-3 py-1 bg-red-500 hover:bg-red-600 text-white rounded-md text-sm">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
        
        <div class="border-t border-gray-200 pt-4">
            <p class="text-gray-700">
                {{ $alamat->alamatLengkap }}
            </p>
        </div>
        
        <div class="mt-6">
            <a href="{{ route('alamat.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar alamat
            </a>
        </div>
    </div>
</div>
@endsection