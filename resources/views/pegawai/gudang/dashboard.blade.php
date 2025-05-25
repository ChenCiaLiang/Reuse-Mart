@extends('layouts.gudang')

@section('title', 'Dashboard Gudang')

@section('content')
<div class="space-y-6">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 rounded-lg shadow-sm p-6 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-2xl font-bold">Selamat Datang, {{ session('user')['nama'] ?? 'Pegawai Gudang' }}!</h2>
                <p class="text-green-100 mt-1">Dashboard sistem penitipan ReUseMart</p>
                <p class="text-green-200 text-sm mt-2">{{ now()->format('l, d F Y') }}</p>
            </div>
            <div class="hidden md:block">
                <i class="fa-solid fa-warehouse text-6xl text-green-200 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Transaksi -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fa-solid fa-file-invoice text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalTransaksi) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Semua waktu</p>
                </div>
            </div>
        </div>

        <!-- Transaksi Aktif -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fa-solid fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Transaksi Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($transaksiAktif) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Sedang berjalan</p>
                </div>
            </div>
        </div>

        <!-- Total Pendapatan -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fa-solid fa-money-bill text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                    <p class="text-xl font-semibold text-gray-900">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">Semua waktu</p>
                </div>
            </div>
        </div>

        <!-- Transaksi Hari Ini -->
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fa-solid fa-calendar-day text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Transaksi Hari Ini</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($transaksiHariIni) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ now()->format('d/m/Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Two Column Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Transaksi Akan Expired -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-exclamation-triangle text-red-500 mr-2"></i>
                        Akan Expired (7 Hari)
                    </h3>
                    <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded-full">
                        {{ $transaksiExpiringSoon->count() }}
                    </span>
                </div>
            </div>
            <div class="p-6">
                @if($transaksiExpiringSoon->count() > 0)
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @foreach($transaksiExpiringSoon as $expired)
                            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-md">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-red-800">{{ $expired->namaPenitip }}</p>
                                        <p class="text-sm text-red-600 mt-1">
                                            <i class="fa-solid fa-clock mr-1"></i>
                                            Batas: {{ \Carbon\Carbon::parse($expired->batasAmbil)->format('d/m/Y H:i') }}
                                        </p>
                                    </div>
                                    <div class="text-right ml-4">
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                            @if(\Carbon\Carbon::parse($expired->batasAmbil)->isPast()) 
                                                bg-red-100 text-red-800
                                            @else 
                                                bg-yellow-100 text-yellow-800 
                                            @endif">
                                            @if(\Carbon\Carbon::parse($expired->batasAmbil)->isPast()) 
                                                Expired 
                                            @else 
                                                {{ \Carbon\Carbon::parse($expired->batasAmbil)->diffForHumans() }} 
                                            @endif
                                        </span>
                                        <div class="mt-2">
                                            <a href="{{ route('gudang.transaksi.show', $expired->idTransaksiPenitipan) }}" 
                                               class="text-red-600 hover:text-red-800 text-xs">
                                                <i class="fa-solid fa-eye mr-1"></i>Lihat
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-4 text-center">
                        <a href="{{ route('gudang.transaksi.index', ['status' => 'Aktif']) }}" 
                           class="text-red-600 hover:text-red-800 text-sm font-medium">
                            Lihat semua transaksi aktif →
                        </a>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-3">
                            <i class="fa-solid fa-check-circle text-4xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Tidak ada transaksi yang akan expired dalam 7 hari ke depan.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Transaksi Terbaru -->
        <div class="bg-white rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-clock text-blue-500 mr-2"></i>
                        Transaksi Terbaru
                    </h3>
                    <a href="{{ route('gudang.transaksi.index') }}" 
                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Lihat Semua
                    </a>
                </div>
            </div>
            <div class="p-6">
                @if($transaksiTerbaru->count() > 0)
                    <div class="space-y-4 max-h-80 overflow-y-auto">
                        @foreach($transaksiTerbaru as $transaksi)
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition duration-200">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-3">
                                        <div class="flex-shrink-0">
                                            <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center">
                                                <i class="fa-solid fa-user text-green-600 text-sm"></i>
                                            </div>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-gray-900 truncate">{{ $transaksi->namaPenitip }}</p>
                                            <p class="text-sm text-gray-500">{{ $transaksi->namaPegawai }}</p>
                                            <p class="text-xs text-gray-400">
                                                <i class="fa-solid fa-calendar mr-1"></i>
                                                {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <p class="text-sm font-medium text-gray-900">Rp {{ number_format($transaksi->pendapatan, 0, ',', '.') }}</p>
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($transaksi->statusPenitipan == 'Aktif') bg-green-100 text-green-800
                                        @elseif($transaksi->statusPenitipan == 'Selesai') bg-blue-100 text-blue-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ $transaksi->statusPenitipan }}
                                    </span>
                                    <div class="mt-1">
                                        <a href="{{ route('gudang.transaksi.show', $transaksi->idTransaksiPenitipan) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-xs">
                                            <i class="fa-solid fa-eye mr-1"></i>Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-3">
                            <i class="fa-solid fa-file-invoice text-4xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Belum ada transaksi.</p>
                        <div class="mt-4">
                            <a href="{{ route('gudang.transaksi.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Buat Transaksi Pertama
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
            <i class="fa-solid fa-bolt text-yellow-500 mr-2"></i>
            Aksi Cepat
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <a href="{{ route('gudang.transaksi.create') }}" 
               class="flex items-center p-4 border-2 border-dashed border-green-300 rounded-lg hover:border-green-400 hover:bg-green-50 transition duration-200">
                <div class="p-3 bg-green-100 rounded-full mr-4">
                    <i class="fa-solid fa-plus text-green-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Transaksi Baru</p>
                    <p class="text-xs text-gray-500">Buat transaksi penitipan</p>
                </div>
            </a>
            
            <a href="{{ route('gudang.transaksi.index', ['status' => 'Aktif']) }}" 
               class="flex items-center p-4 border-2 border-dashed border-blue-300 rounded-lg hover:border-blue-400 hover:bg-blue-50 transition duration-200">
                <div class="p-3 bg-blue-100 rounded-full mr-4">
                    <i class="fa-solid fa-list text-blue-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Transaksi Aktif</p>
                    <p class="text-xs text-gray-500">Lihat yang sedang berjalan</p>
                </div>
            </a>
            
            <a href="{{ route('gudang.transaksi.index') }}" 
               class="flex items-center p-4 border-2 border-dashed border-purple-300 rounded-lg hover:border-purple-400 hover:bg-purple-50 transition duration-200">
                <div class="p-3 bg-purple-100 rounded-full mr-4">
                    <i class="fa-solid fa-chart-bar text-purple-600"></i>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-900">Semua Transaksi</p>
                    <p class="text-xs text-gray-500">Lihat semua data</p>
                </div>
            </a>
        </div>
    </div>
</div>
@endsection