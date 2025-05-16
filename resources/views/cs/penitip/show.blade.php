@extends('layouts.cs')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Detail Penitip
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">{{ $penitip->nama }}</h3>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('cs.penitip.edit', $penitip->idPenitip) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form action="{{ route('cs.penitip.destroy', $penitip->idPenitip) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus penitip ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-4 gap-y-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID Penitip</dt>
                    <dd class="mt-1 text-gray-900">{{ $penitip->idPenitip }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                    <dd class="mt-1 text-gray-900">{{ $penitip->nama }}</dd>
                </div>
                
                <div class="md:row-span-3 flex items-center justify-center md:col-start-3 md:row-start-1">
                    <img class="object-cover w-full h-auto max-h-64 rounded-lg shadow-md" 
                        src="{{ asset($penitip->foto_ktp) }}" alt="{{$penitip->foto_ktp}}">
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-gray-900">{{ $penitip->email }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                    <dd class="mt-1 text-gray-900">{{ $penitip->alamat }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Ditambahkan</dt>
                    <dd class="mt-1 text-gray-900">{{ $penitip->created_at->format('d F Y H:i') }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Terakhir Diperbarui</dt>
                    <dd class="mt-1 text-gray-900">{{ $penitip->updated_at->format('d F Y H:i') }}</dd>
                </div>
            </dl>
        </div>
        
        <div class="mt-6">
            <a href="{{ route('cs.penitip.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar penitip
            </a>
        </div>
    </div>
</div>
@endsection