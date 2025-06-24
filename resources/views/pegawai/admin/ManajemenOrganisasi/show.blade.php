@extends('layouts.admin')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Detail Organisasi
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">{{ $organisasi->nama }}</h3>
                <p class="text-sm text-gray-600">{{ $organisasi->idOrganisasi }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.organisasi.edit', $organisasi->idOrganisasi) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <form action="{{ route('admin.organisasi.destroy', $organisasi->idOrganisasi) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus organisasi ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Logo Organisasi -->
            <!-- <div class="md:col-span-1 flex flex-col items-center">
                <img src="{{ asset($organisasi->logo) }}" alt="{{ $organisasi->nama }}" class="w-full max-w-[200px] rounded-lg mb-4">
                <p class="text-sm text-gray-500">Logo Organisasi</p>
            </div> -->
            
            <!-- Informasi Organisasi -->
            <div class="md:col-span-2">
                <div class="border-t border-gray-200 pt-4">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Organisasi</dt>
                            <dd class="mt-1 text-gray-900">{{ $organisasi->nama }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="mt-1 text-gray-900">{{ $organisasi->email }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                            <dd class="mt-1 text-gray-900">{{ $organisasi->alamat }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Tanggal Pendaftaran</dt>
                            <dd class="mt-1 text-gray-900">{{ $organisasi->created_at}}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>
        
        <!-- Request Donasi Organisasi -->
        <div class="mt-8 border-t pt-6">
            <h4 class="text-lg font-semibold mb-4">Riwayat Request Donasi</h4>
            
            @if(count($requestDonasi) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                <th class="py-3 px-4 border-b">Tanggal Request</th>
                                <th class="py-3 px-4 border-b">Request</th>
                                <th class="py-3 px-4 border-b">Status</th>
                                <th class="py-3 px-4 border-b">Penerima</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm">
                            @foreach($requestDonasi as $request)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="py-3 px-4">{{ $request->tanggalRequest}}</td>
                                    <td class="py-3 px-4">{{ $request->request }}</td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-1 rounded-full text-xs 
                                            {{ $request->status == 'Terpenuhi' ? 'bg-green-100 text-green-800' : 
                                              ($request->status == 'Ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">{{ $request->penerima }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4 text-gray-500">
                    <p>Belum ada request donasi dari organisasi ini.</p>
                </div>
            @endif
        </div>
        
        <div class="mt-6">
            <a href="{{ route('admin.organisasi.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar organisasi
            </a>
        </div>
    </div>
</div>
@endsection