@extends('layouts.cs')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Pengelolaan Klaim Merchandise
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

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-blue-500 text-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">Total Klaim</h3>
                    <p class="text-3xl font-bold">{{ $stats['total'] }}</p>
                </div>
                <div class="ml-4">
                    <i class="fas fa-gift text-3xl opacity-75"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-500 text-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">Belum Diambil</h3>
                    <p class="text-3xl font-bold">{{ $stats['belum_diambil'] }}</p>
                </div>
                <div class="ml-4">
                    <i class="fas fa-clock text-3xl opacity-75"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-green-500 text-white p-6 rounded-lg shadow">
            <div class="flex items-center">
                <div class="flex-1">
                    <h3 class="text-lg font-semibold">Sudah Diambil</h3>
                    <p class="text-3xl font-bold">{{ $stats['sudah_diambil'] }}</p>
                </div>
                <div class="ml-4">
                    <i class="fas fa-check-circle text-3xl opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <div class="flex justify-between items-center mb-4">
            <div class="flex space-x-2">
                <!-- Filter Status -->
                <form action="{{ route('cs.merchandise.index') }}" method="GET" class="flex items-center space-x-2">
                    <select name="status" class="border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-600" onchange="this.form.submit()">
                        <option value="semua" {{ ($status ?? 'semua') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                        <option value="belum_diambil" {{ ($status ?? '') == 'belum_diambil' ? 'selected' : '' }}>Belum Diambil</option>
                        <option value="sudah_diambil" {{ ($status ?? '') == 'sudah_diambil' ? 'selected' : '' }}>Sudah Diambil</option>
                    </select>
                    @if($search)
                        <input type="hidden" name="search" value="{{ $search }}">
                    @endif
                </form>
            </div>
            <div>
                <!-- Search Form -->
                <form action="{{ route('cs.merchandise.index') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari pembeli/merchandise..." 
                           class="border rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600 w-64">
                    @if($status && $status != 'semua')
                        <input type="hidden" name="status" value="{{ $status }}">
                    @endif
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
                        <th class="py-3 px-6 text-left">ID Klaim</th>
                        <th class="py-3 px-6 text-left">Nama Pembeli</th>
                        <th class="py-3 px-6 text-left">Email</th>
                        <th class="py-3 px-6 text-left">Merchandise</th>
                        <th class="py-3 px-6 text-center">Poin</th>
                        <th class="py-3 px-6 text-center">Tanggal Klaim</th>
                        <th class="py-3 px-6 text-center">Status</th>
                        <th class="py-3 px-6 text-center">Tanggal Ambil</th>
                        <th class="py-3 px-6 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($klaimMerchandise as $klaim)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6 text-left">
                            <span class="font-semibold">MER-{{ str_pad($klaim->idPenukaran, 3, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td class="py-3 px-6 text-left">{{ $klaim->namaPembeli }}</td>
                        <td class="py-3 px-6 text-left">{{ $klaim->email }}</td>
                        <td class="py-3 px-6 text-left">{{ $klaim->namaMerchandise }}</td>
                        <td class="py-3 px-6 text-center">
                            <span class="bg-blue-100 text-blue-800 text-xs font-semibold px-2 py-1 rounded">
                                {{ $klaim->jumlahPoin }} poin
                            </span>
                        </td>
                        <td class="py-3 px-6 text-center">
                            {{ \Carbon\Carbon::parse($klaim->tanggalPengajuan)->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($klaim->statusPenukaran == 'belum diambil')
                                <span class="bg-yellow-100 text-yellow-800 text-xs font-semibold px-2 py-1 rounded">
                                    <i class="fas fa-clock mr-1"></i>Belum Diambil
                                </span>
                            @elseif($klaim->statusPenukaran == 'sudah diambil')
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2 py-1 rounded">
                                    <i class="fas fa-check mr-1"></i>Sudah Diambil
                                </span>
                            @elseif($klaim->statusPenukaran == 'dibatalkan')
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2 py-1 rounded">
                                    <i class="fas fa-times mr-1"></i>Dibatalkan
                                </span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs font-semibold px-2 py-1 rounded">
                                    <i class="fas fa-question mr-1"></i>{{ ucfirst($klaim->statusPenukaran) }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            @if($klaim->tanggalPenerimaan)
                                {{ \Carbon\Carbon::parse($klaim->tanggalPenerimaan)->format('d/m/Y H:i') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3 px-6 text-center">
                            <div class="flex item-center justify-center">
                                <!-- Detail -->
                                <a href="{{ route('cs.merchandise.show', $klaim->idPenukaran) }}" class="text-blue-600 hover:text-blue-900 mx-1" title="Detail">
                                    <i class="fas fa-eye"></i>
                                </a>
                                
                                @if($klaim->statusPenukaran == 'belum diambil')
                                    <!-- Konfirmasi Pengambilan -->
                                    <a href="{{ route('cs.merchandise.konfirmasi.form', $klaim->idPenukaran) }}" 
                                       class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs mx-1" 
                                       title="Konfirmasi Pengambilan">
                                        <i class="fas fa-check mr-1"></i>Konfirmasi
                                    </a>
                                @else
                                    <span class="text-gray-400 text-xs">Selesai</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr class="border-b border-gray-200">
                        <td colspan="9" class="py-10 px-6 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-gift text-5xl mb-4"></i>
                                <p class="text-lg">Tidak ada data klaim merchandise</p>
                                @if($search)
                                    <p class="text-sm mt-1">Tidak ditemukan hasil untuk "{{ $search }}"</p>
                                    <a href="{{ route('cs.merchandise.index') }}" class="text-blue-500 hover:underline mt-2">
                                        Tampilkan semua klaim
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
            {{ $klaimMerchandise->appends(request()->query())->links() }}
        </div>
    </div>
</div>
@endsection