@extends('layouts.customer')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Daftar Penitipan
    </h2>

    <!-- Alert Success -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
        <p>{{ session('success') }}</p>
    </div>
    @endif

    <!-- Alert Error -->
    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <p>{{ session('error') }}</p>
    </div>
    @endif

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <div class="flex justify-between items-center mb-4">
            <div>
                <!-- Search Form -->
                <form action="{{ route('penitip.penitipan.index') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari penitipan..." 
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
                        <th class="py-3 px-6 text-left">ID Penitipan</th>
                        <th class="py-3 px-6 text-left">Tanggal Masuk Penitipan</th>
                        <th class="py-3 px-6 text-left">Tanggal Akhir Penitipan</th>
                        <th class="py-3 px-6 text-left">Status Penitipan</th>
                        <th class="py-3 px-6 text-left">Status Perpanjangan</th>
                        <th class="py-3 px-6 text-left">Pendapatan</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($penitipan as $p)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left">{{ $p->idTransaksiPenitipan }}</td>
                        <td class="py-3 px-6 text-left">{{ $p->tanggalMasukPenitipan }}</td>
                        <td class="py-3 px-6 text-left">{{ $p->tanggalAkhirPenitipan }}</td>
                        <td class="py-3 px-6 text-left">{{ $p->statusPenitipan }}</td>
                        <td class="py-3 px-6 text-left">{{ $p->statusPerpanjangan }}</td>
                        <td class="py-3 px-6 text-left">{{ $p->pendapatan }}</td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <!-- Detail -->
                                <a href="{{ route('penitip.penitipan.show', $p->idTransaksiPenitipan) }}" class="text-blue-600 hover:text-blue-900 mx-1" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="border-b border-gray-200">
                        <td colspan="5" class="py-10 px-6 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-user-slash text-5xl mb-4"></i>
                                <p class="text-lg">Tidak ada data transaksi Penitipan</p>
                                @if($search)
                                    <p class="text-sm mt-1">Tidak ditemukan hasil untuk "{{ $search }}"</p>
                                    <a href="{{ route('penitip.penitipan.index') }}" class="text-blue-500 hover:underline mt-2">
                                        Tampilkan semua Penitipan
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
            {{ $penitipan->links() }}
        </div>
    </div>
</div>
@endsection