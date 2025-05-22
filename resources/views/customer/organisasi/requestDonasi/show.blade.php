@extends('layouts.cs')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Detail Request
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">{{ $requestDonasi->nama }}</h3>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('organisasi.requestDonasi.edit', $requestDonasi->idRequest) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form action="{{ route('organisasi.requestDonasi.destroy', $requestDonasi->idRequest) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus request ini?');">
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
                    <dt class="text-sm font-medium text-gray-500">ID Request</dt>
                    <dd class="mt-1 text-gray-900">{{ $requestDonasi->idRequest }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Request Donasi</dt>
                    <dd class="mt-1 text-gray-900">{{ $requestDonasi->tanggalRequest }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Request</dt>
                    <dd class="mt-1 text-gray-900">{{ $requestDonasi->request }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1 text-gray-900">{{ $requestDonasi->status }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Penerima</dt>
                    <dd class="mt-1 text-gray-900">{{ $requestDonasi->penerima }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID Organisasi</dt>
                    <dd class="mt-1 text-gray-900">{{ $requestDonasi->idOrganisasi }}</dd>
                </div>
            </dl>
        </div>
        
        <div class="mt-6">
            <a href="{{ route('organisasi.requestDonasi.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar request
            </a>
        </div>
    </div>
</div>
@endsection