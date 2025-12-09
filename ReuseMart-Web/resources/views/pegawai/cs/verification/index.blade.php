{{-- FILE: resources/views/pegawai/cs/verification/index.blade.php --}}
@extends('layouts.cs')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-500 px-6 py-4">
                <h1 class="text-xl font-bold text-white">Verifikasi Pembayaran</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Daftar transaksi yang menunggu verifikasi pembayaran
                </p>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <div class="flex">
                        <i class="fas fa-check-circle mr-3 mt-0.5"></i>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5"></i>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-2xl text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-600">Menunggu Verifikasi</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ $transaksiList->total() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-600">Total Nilai</p>
                                <p class="text-2xl font-bold text-blue-900">
                                    Rp {{ number_format($transaksiList->sum(function($t) { 
                                        return $t->detailTransaksiPenjualan->sum(function($d) { 
                                            return $d->produk->hargaJual; 
                                        }); 
                                    }), 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-600">Pembeli Unik</p>
                                <p class="text-2xl font-bold text-green-900">{{ $transaksiList->unique('idPembeli')->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-images text-2xl text-purple-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-purple-600">Bukti Upload</p>
                                <p class="text-2xl font-bold text-purple-900">{{ $transaksiList->where('buktiPembayaran', '!=', null)->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction List -->
                @if(count($transaksiList) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaksi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pembeli
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Bukti Upload
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transaksiList as $transaksi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            #{{ $transaksi->idTransaksiPenjualan }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $transaksi->detailTransaksiPenjualan->count() }} item
                                        </div>
                                        <div class="text-xs text-gray-400">
                                            Pesan: {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M H:i') }}
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $transaksi->pembeli->nama }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $transaksi->pembeli->email }}
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp {{ number_format($transaksi->detailTransaksiPenjualan->sum(function($detail) { 
                                            return $detail->produk->hargaJual; 
                                        }), 0, ',', '.') }}
                                    </div>
                                    @if($transaksi->poinDigunakan > 0)
                                    <div class="text-xs text-yellow-600">
                                        Poin digunakan: {{ number_format($transaksi->poinDigunakan) }}
                                    </div>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($transaksi->buktiPembayaran)
                                    <div class="flex items-center">
                                        <i class="fas fa-check-circle text-green-500 mr-2"></i>
                                        <div>
                                            <div class="text-sm text-green-700 font-medium">Sudah Upload</div>
                                            <div class="text-xs text-gray-500">
                                                {{ \Carbon\Carbon::parse($transaksi->tanggalUploadBukti)->format('d M H:i') }}
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                    <div class="flex items-center">
                                        <i class="fas fa-times-circle text-red-500 mr-2"></i>
                                        <span class="text-sm text-red-700">Belum Upload</span>
                                    </div>
                                    @endif
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @if($transaksi->buktiPembayaran)
                                    <a href="{{ route('cs.verification.show', $transaksi->idTransaksiPenjualan) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors inline-flex items-center">
                                        <i class="fas fa-eye mr-2"></i>
                                        Verifikasi
                                    </a>
                                    @else
                                    <span class="bg-gray-300 text-gray-500 px-4 py-2 rounded-lg text-sm cursor-not-allowed">
                                        <i class="fas fa-clock mr-2"></i>
                                        Menunggu Upload
                                    </span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $transaksiList->links() }}
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-check-circle text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Verifikasi Pending</h3>
                    <p class="text-gray-500">Semua pembayaran sudah diverifikasi.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection