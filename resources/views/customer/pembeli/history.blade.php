@extends('layouts.customer')

@section('content')
<div class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-green-500 px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-white">Riwayat Transaksi Penjualan</h1>
                <a href="{{ route('pembeli.profile') }}" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Profil
                </a>
            </div>
            
            <!-- Filter -->
            <div class="p-6 border-b border-gray-200">
                <form method="GET" action="{{ route('pembeli.history') }}" class="flex flex-wrap items-end gap-4">
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                            class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            class="shadow-sm focus:ring-green-500 focus:border-green-500 block w-full sm:text-sm border-gray-300 rounded-md">
                    </div>
                    
                    <div>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                            Filter
                        </button>
                    </div>
                </form>
            </div>
            
            <!-- Transactions Table -->
            <div class="p-6">
                @if(count($transaksiPenjualan) > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID Transaksi</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga Jual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($transaksiPenjualan as $transaksi)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $transaksi->idTransaksi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $transaksi->deskripsi }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Rp {{ number_format($transaksi->hargaJual, 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Selesai
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <a href="{{ route('pembeli.transaksi.detail', $transaksi->idTransaksi) }}" class="text-green-600 hover:text-green-900">
                                            Detail
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-4">
                        {{ $transaksiPenjualan->links() }}
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg>
                        <p class="text-gray-500 mb-4">Tidak ada transaksi penjualan dalam periode yang dipilih</p>
                        <a href="{{ route('pembeli.profile') }}" class="text-green-600 hover:text-green-900">
                            Kembali ke profil
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection