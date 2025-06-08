@extends('layouts.owner')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-lg p-6 text-white">
        <h1 class="text-2xl font-bold mb-2">Laporan Donasi Barang</h1>
        <p class="text-green-100">Monitor semua barang yang telah didonasikan kepada organisasi</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        @php
            $totalDonations = $donations->total();
            $thisMonth = $donations->filter(function($donation) {
                return \Carbon\Carbon::parse($donation->tanggalPemberian)->isCurrentMonth();
            })->count();
            $totalOrganizations = $donations->pluck('requestDonasi.organisasi.nama')->unique()->count();
            $averagePerMonth = $totalDonations > 0 ? round($totalDonations / 12, 1) : 0;
        @endphp
        
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-green-100 rounded-lg">
                    <i class="fas fa-gift text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Donasi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalDonations }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-blue-100 rounded-lg">
                    <i class="fas fa-calendar-month text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Bulan Ini</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $thisMonth }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-purple-100 rounded-lg">
                    <i class="fas fa-building text-purple-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Organisasi</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $totalOrganizations }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="p-3 bg-yellow-100 rounded-lg">
                    <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Rata-rata/Bulan</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $averagePerMonth }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Card -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200">
        <!-- Card Header -->
        <div class="border-b border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Donasi Barang</h2>
                
                <!-- Search and Actions -->
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <!-- Search Form -->
                    <form action="{{ route('owner.laporan.donasi-barang') }}" method="GET" class="flex">
                        <div class="relative">
                            <input 
                                type="text" 
                                name="search" 
                                value="{{ request('search') }}"
                                placeholder="Cari produk atau organisasi..."
                                class="w-full sm:w-80 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500 text-sm"
                            >
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i class="fas fa-search text-gray-400"></i>
                            </div>
                        </div>
                        <button type="submit" class="ml-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition duration-200 text-sm">
                            Cari
                        </button>
                    </form>

                    <!-- Download PDF Button -->
                    <a href="{{ route('owner.laporan.donasi-barang.pdf', ['search' => request('search')]) }}" 
                       class="inline-flex items-center px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-200 text-sm font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Body -->
        <div class="p-6">
            @if($donations->count() > 0)
                <!-- Search Results Info -->
                @if(request('search'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg">
                        <p class="text-sm text-green-700">
                            <i class="fas fa-info-circle mr-1"></i>
                            Menampilkan {{ $donations->count() }} hasil untuk pencarian "<strong>{{ request('search') }}</strong>"
                        </p>
                    </div>
                @endif

                <!-- Table -->
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Kode Produk
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produk
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Penitip
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Organisasi Penerima
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Penerima
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal Donasi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($donations as $donasi)
                                @php
                                    $penitip = null;
                                    $detailPenitipan = $donasi->produk->detailTransaksiPenitipan->first();
                                    if ($detailPenitipan && $detailPenitipan->transaksiPenitipan) {
                                        $penitip = $detailPenitipan->transaksiPenitipan->penitip;
                                    }
                                @endphp
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-box text-green-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">
                                                    {{ strtoupper(substr($donasi->produk->deskripsi, 0, 1)) }}{{ $donasi->produk->idProduk }}
                                                </p>
                                                <p class="text-xs text-gray-500">Kode Produk</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                                                    <i class="fas fa-box text-blue-600"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900 line-clamp-1">{{ $donasi->produk->deskripsi }}</p>
                                                <p class="text-xs text-gray-500">Kategori: {{ $donasi->produk->kategori->nama }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-indigo-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-indigo-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                @if($penitip)
                                                    <p class="text-sm font-medium text-gray-900">{{ $penitip->nama }}</p>
                                                    <p class="text-xs text-gray-500">T{{ str_pad($penitip->idPenitip, 2, '0', STR_PAD_LEFT) }}</p>
                                                @else
                                                    <p class="text-sm text-gray-500">Tidak diketahui</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-building text-purple-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $donasi->requestDonasi->organisasi->nama }}</p>
                                                <p class="text-xs text-gray-500 line-clamp-1">{{ Str::limit($donasi->requestDonasi->organisasi->alamat, 30) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0">
                                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                                    <i class="fas fa-user text-yellow-600 text-sm"></i>
                                                </div>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-gray-900">{{ $donasi->namaPenerima }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($donasi->tanggalPemberian)->format('d M Y') }}</div>
                                        <div class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($donasi->tanggalPemberian)->diffForHumans() }}</div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6 flex items-center justify-between border-t border-gray-200 pt-6">
                    <div class="flex items-center text-sm text-gray-500">
                        Menampilkan {{ $donations->firstItem() }} - {{ $donations->lastItem() }} dari {{ $donations->total() }} donasi
                    </div>
                    <div class="flex items-center space-x-2">
                        {{ $donations->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                        <i class="fas fa-hand-holding-heart text-gray-400 text-2xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">
                        @if(request('search'))
                            Tidak ada hasil pencarian
                        @else
                            Belum ada donasi barang
                        @endif
                    </h3>
                    <p class="text-gray-500 mb-6">
                        @if(request('search'))
                            Coba ubah kata kunci pencarian atau hapus filter.
                        @else
                            Riwayat donasi barang akan muncul di sini setelah ada barang yang didonasikan.
                        @endif
                    </p>
                    @if(request('search'))
                        <a href="{{ route('owner.laporan.donasi-barang') }}" 
                           class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-200">
                            <i class="fas fa-arrow-left mr-2"></i>
                            Lihat Semua Donasi
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('owner.donasi.request') }}" 
               class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition duration-200">
                <div class="p-2 bg-blue-500 rounded-lg mr-3">
                    <i class="fas fa-clipboard-list text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-blue-900">Lihat Request Donasi</p>
                    <p class="text-sm text-blue-600">Kelola permintaan donasi</p>
                </div>
            </a>

            <a href="{{ route('owner.donasi.barang') }}" 
               class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition duration-200">
                <div class="p-2 bg-green-500 rounded-lg mr-3">
                    <i class="fas fa-box text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-green-900">Barang untuk Donasi</p>
                    <p class="text-sm text-green-600">Kelola barang siap donasi</p>
                </div>
            </a>

            <a href="{{ route('owner.donasi.history') }}" 
               class="flex items-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition duration-200">
                <div class="p-2 bg-purple-500 rounded-lg mr-3">
                    <i class="fas fa-history text-white"></i>
                </div>
                <div>
                    <p class="font-medium text-purple-900">History Donasi</p>
                    <p class="text-sm text-purple-600">Lihat riwayat lengkap</p>
                </div>
            </a>
        </div>
    </div>
</div>

<style>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
@endsection