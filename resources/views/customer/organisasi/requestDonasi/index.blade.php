@extends('layouts.customer')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Request Donasi
    </h2>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <a href="{{ route('organisasi.requestDonasi.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-plus mr-2"></i>Tambah Request Donasi
                </a>
            </div>
            <div>
                <!-- Search Form -->
                <form action="{{ route('organisasi.requestDonasi.index') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari request..." 
                           class="border rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600 w-64">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-r-md">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead>
                    <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                        <th class="py-3 px-6 text-left">Tanggal Request Donasi</th>
                        <th class="py-3 px-6 text-left">Request</th>
                        <th class="py-3 px-6 text-left">Status</th>
                        <th class="py-3 px-6 text-left">Penerima</th>
                        <th class="py-3 px-6 text-center">ID Organisasi</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($requestDonasi as $r)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left">{{ $r->tanggalRequest }}</td>
                        <td class="py-3 px-6 text-left">{{ $r->request }}</td>
                        <td class="py-3 px-6 text-left">{{ $r->status }}</td>
                        <td class="py-3 px-6 text-left">{{ $r->penerima }}</td>
                        <td class="py-3 px-6 text-left">{{ $r->idOrganisasi }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <!-- Detail -->
                                <a href="{{ route('organisasi.requestDonasi.show', $r->idRequest) }}" class="text-blue-600 hover:text-blue-900 mx-1" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <!-- Edit -->
                                <a href="{{ route('organisasi.requestDonasi.edit', $r->idRequest) }}" class="text-yellow-600 hover:text-yellow-900 mx-1" title="Edit">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <!-- Delete -->
                                <form action="{{ route('organisasi.requestDonasi.destroy', $r->idRequest) }}" method="POST" class="inline-block mx-1" onsubmit="return confirm('Apakah Anda yakin ingin menghapus request ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="border-b border-gray-200">
                        <td colspan="5" class="py-10 px-6 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-slash text-5xl mb-4"></i>
                                <p class="text-lg">Tidak ada data request</p>
                                @if($search)
                                    <p class="text-sm mt-1">Tidak ditemukan hasil untuk "{{ $search }}"</p>
                                    <a href="{{ route('organisasi.requestDonasi.index') }}" class="text-blue-500 hover:underline mt-2">
                                        Tampilkan semua request
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="mt-4">
            {{ $requestDonasi->links() }}
        </div>
    </div>
</div>
@endsection