@extends('layouts.owner')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between mb-6">
        <div class="mb-4 lg:mb-0">
            <h2 class="text-2xl font-bold text-gray-800">Laporan Transaksi Penitip</h2>
            <div class="mt-2 text-sm text-gray-600">
                <div class="flex flex-wrap items-center gap-4">
                    <div class="flex items-center">
                        <i class="fas fa-user-circle text-green-600 mr-2"></i>
                        <span><strong>Penitip:</strong> {{ $penitip->nama }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-envelope text-blue-600 mr-2"></i>
                        <span><strong>Email:</strong> {{ $penitip->email }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-calendar text-yellow-600 mr-2"></i>
                        <span><strong>Tahun:</strong> {{ $tahun }}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-3">
            <a href="{{ route('owner.laporan.transaksi-penitip') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 text-gray-700 bg-white rounded-md hover:bg-gray-50 text-sm">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Filter
            </a>
            
            <form action="{{ route('owner.laporan.transaksi-penitip.generate') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="idPenitip" value="{{ $penitip->idPenitip }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit" name="download_pdf" value="true" 
                        class="inline-flex items-center px-4 py-2 border border-transparent text-white bg-red-600 rounded-md hover:bg-red-700 text-sm">
                    <i class="fas fa-file-pdf mr-2"></i>
                    Download PDF
                </button>
            </form>
        </div>
    </div>

    <!-- Info Header -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-green-800">
            Laporan Transaksi {{ $penitip->nama }} - Tahun {{ $tahun }}
        </h3>
        <p class="text-sm text-green-600 mt-1">
            Tanggal cetak: {{ \Carbon\Carbon::now()->format('d F Y') }}
        </p>
    </div>

    <!-- Summary Cards -->
    @php
        $totalTransaksi = $transaksi->count();
        $totalHargaJual = $totals['harga_jual'];
        $totalKomisi = $totals['komisi'];
        $totalPendapatan = $totals['pendapatan'];
        $rataTransaksi = $totalTransaksi > 0 ? $totalPendapatan / $totalTransaksi : 0;
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-500 rounded-lg">
                    <i class="fas fa-list-ol text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Transaksi</p>
                    <p class="text-lg font-bold text-blue-800">{{ $totalTransaksi }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-500 rounded-lg">
                    <i class="fas fa-money-bill-wave text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-600">Total Harga Jual</p>
                    <p class="text-lg font-bold text-yellow-800">Rp {{ number_format($totalHargaJual, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-red-500 rounded-lg">
                    <i class="fas fa-percentage text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-red-600">Total Komisi</p>
                    <p class="text-lg font-bold text-red-800">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-500 rounded-lg">
                    <i class="fas fa-wallet text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Total Pendapatan</p>
                    <p class="text-lg font-bold text-green-800">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Metrics -->
    @if($totalTransaksi > 0)
    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
        <h4 class="text-md font-semibold text-gray-800 mb-3">Analisis Performa</h4>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
            <div class="text-center">
                <p class="text-gray-600">Rata-rata Pendapatan per Transaksi</p>
                <p class="text-lg font-bold text-gray-800">Rp {{ number_format($rataTransaksi, 0, ',', '.') }}</p>
            </div>
            <div class="text-center">
                <p class="text-gray-600">Persentase Komisi Rata-rata</p>
                <p class="text-lg font-bold text-gray-800">{{ $totalHargaJual > 0 ? number_format(($totalKomisi / $totalHargaJual) * 100, 1) : 0 }}%</p>
            </div>
            <div class="text-center">
                <p class="text-gray-600">Frekuensi Transaksi per Bulan</p>
                <p class="text-lg font-bold text-gray-800">{{ number_format($totalTransaksi / 12, 1) }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Table -->
    <div class="overflow-x-auto">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Transaksi</h3>
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Produk
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Masuk
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Terjual
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Harga Jual
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Komisi
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Pendapatan Bersih
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Durasi Penjualan
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($transaksi as $item)
                @php
                    $komisiTotal = $item->komisiReuse + $item->komisiHunter;
                    $pendapatanBersih = $item->komisiPenitip;
                    $tanggalMasuk = \Carbon\Carbon::parse($item->tanggalMasukPenitipan);
                    $tanggalLaku = \Carbon\Carbon::parse($item->tanggalLaku);
                    $durasiPenjualan = $tanggalMasuk->diffInDays($tanggalLaku);
                @endphp
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $item->nama_produk }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tanggalMasuk->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $tanggalLaku->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($item->hargaJual, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($komisiTotal, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                        Rp {{ number_format($pendapatanBersih, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $durasiPenjualan }} hari
                        @if($durasiPenjualan <= 7)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                                Cepat
                            </span>
                        @elseif($durasiPenjualan <= 30)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                                Normal
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 ml-2">
                                Lama
                            </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                            <p class="text-lg font-medium">Tidak ada transaksi</p>
                            <p class="text-sm">Tidak ada transaksi untuk {{ $penitip->nama }} pada tahun {{ $tahun }}</p>
                        </div>
                    </td>
                </tr>
                @endforelse
                
                @if($transaksi->count() > 0)
                <tr class="bg-gray-100 font-bold">
                    <td colspan="3" class="px-4 py-4 text-right text-sm text-gray-900">
                        <strong>Total</strong>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        <strong>Rp {{ number_format($totalHargaJual, 0, ',', '.') }}</strong>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        <strong>Rp {{ number_format($totalKomisi, 0, ',', '.') }}</strong>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-green-700">
                        <strong>Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</strong>
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        <strong>{{ $totalTransaksi }} transaksi</strong>
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($transaksi->count() > 0)
    <!-- Legend -->
    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-800 mb-2">Keterangan:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-gray-600">
            <p>• <strong>Komisi:</strong> Total komisi yang diambil oleh ReUseMart (termasuk komisi hunter jika ada)</p>
            <p>• <strong>Pendapatan Bersih:</strong> Jumlah yang diterima penitip setelah dipotong komisi</p>
            <p>• <strong>Durasi Penjualan:</strong> Waktu dari barang masuk hingga terjual</p>
            <p>• <strong>Status Cepat:</strong> Terjual dalam 7 hari (mendapat bonus)</p>
        </div>
    </div>
    @endif
</div>
@endsection