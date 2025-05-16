@extends('layouts.customer')

@section('content')
<div class="container px-6 mx-auto py-8">
    <h1 class="text-2xl font-semibold text-gray-900 mb-6">Daftar Alamat Saya</h1>
    
    @if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif
    
    <div class="flex justify-between items-center mb-6">
        <a href="{{ route('alamat.create') }}" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md">
            <i class="fas fa-plus mr-2"></i> Tambah Alamat Baru
        </a>
        
        <form action="{{ route('alamat.search') }}" method="GET" class="flex">
            <input type="text" name="search" placeholder="Cari alamat..." value="{{ $search ?? '' }}" 
                   class="border rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600 w-64">
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-r-md">
                <i class="fas fa-search"></i>
            </button>
        </form>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse ($alamat as $a)
        <div class="bg-white shadow-md rounded-lg overflow-hidden border {{ $a->statusDefault ? 'border-green-500' : 'border-gray-200' }}">
            <div class="p-4">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="font-semibold text-lg text-gray-800">{{ $a->jenis }}</h3>
                        @if ($a->statusDefault)
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Default</span>
                        @endif
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('alamat.edit', $a->idAlamat) }}" class="text-yellow-600 hover:text-yellow-800">
                            <i class="fas fa-edit"></i>
                        </a>
                        <form action="{{ route('alamat.destroy', $a->idAlamat) }}" method="POST" class="inline-block"
                              onsubmit="return confirm('Yakin ingin menghapus alamat ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-800">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                
                <p class="text-gray-700 mt-2">{{ $a->alamatLengkap }}</p>
            </div>
        </div>
        @empty
        <div class="col-span-2 bg-gray-100 rounded-lg p-8 text-center">
            <i class="fas fa-map-marker-alt text-4xl text-gray-400 mb-4"></i>
            <p class="text-gray-500">Anda belum memiliki alamat yang tersimpan.</p>
            <a href="{{ route('alamat.create') }}" class="mt-4 inline-block bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md">
                Tambah Alamat Pertama Anda
            </a>
        </div>
        @endforelse
    </div>
</div>
@endsection