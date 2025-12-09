@extends('layouts.customer')

@section('content')
<div class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-green-500 px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-white">Detail Transaksi</h1>
                <a href="{{ route('penitip.history') }}" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>
            
            <!-- Transaksi Info -->
            <div class="p-6 border-b border-gray-200">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">ID Transaksi</p>
                        <p class="text-lg font-semibold">{{ $transaksi->idTransaksi }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Transaksi</p>
                        <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                            Selesai
                        </span>
                    </div>
                    @if($transaksi->tanggalKirim)
                    <div>
                        <p class="text-sm text-gray-500">Tanggal Kirim</p>
                        <p class="text-lg font-semibold">{{ \Carbon\Carbon::parse($transaksi->tanggalKirim)->format('d/m/Y') }}</p>
                    </div>
                    @endif
                </div>
            </div>
            
            <!-- Pembeli Info -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold mb-3">Informasi Pembeli</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Nama Pembeli</p>
                        <p class="text-base">{{ $pembeli->nama }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Email</p>
                        <p class="text-base">{{ $pembeli->email }}</p>
                    </div>
                </div>
            </div>
            
            <!-- Produk Info -->
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold mb-3">Detail Produk</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Produk
                                </th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Harga
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($detailTransaksi as $detail)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            @php
                                                $gambarArray = $detail->produk->gambar ? explode(',', $detail->produk->gambar) : ['default.jpg'];
                                                $thumbnail = $gambarArray[0];
                                            @endphp
                                            <img class="h-10 w-10 rounded-full object-cover" 
                                                src="{{ asset('images/produk/' . $thumbnail) }}" 
                                                alt="{{ $detail->produk->deskripsi }}"
                                                onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $detail->produk->deskripsi }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ID: {{ $detail->produk->idProduk }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm text-gray-500">
                                    Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Komisi Info -->
            @if($komisi)
            <div class="p-6 border-b border-gray-200">
                <h2 class="text-lg font-semibold mb-3">Rincian Pendapatan</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Harga Jual</p>
                        <p class="text-base">Rp {{ number_format($detailTransaksi[0]->produk->hargaJual, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Komisi ReUseMart</p>
                        <p class="text-base">Rp {{ number_format($komisi->komisiReuse, 0, ',', '.') }}</p>
                    </div>
                    @if($komisi->komisiHunter > 0)
                    <div>
                        <p class="text-sm text-gray-500">Komisi Hunter</p>
                        <p class="text-base">Rp {{ number_format($komisi->komisiHunter, 0, ',', '.') }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-sm text-gray-500">Bonus</p>
                        <p class="text-base">Rp {{ number_format($komisi->komisiPenitip, 0, ',', '.') }}</p>
                    </div>
                    <div class="col-span-2 pt-4 border-t border-gray-200 mt-4">
                        <p class="text-sm text-gray-500">Total Pendapatan</p>
                        <p class="text-xl font-bold text-green-600">
                            Rp {{ number_format($detailTransaksi[0]->produk->hargaJual - $komisi->komisiReuse - $komisi->komisiHunter + $komisi->komisiPenitip, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection