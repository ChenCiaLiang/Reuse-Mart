@extends('layouts.admin')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Dashboard
    </h2>

    <!-- Cards -->
    <div class="grid gap-6 mb-8 md:grid-cols-2 xl:grid-cols-4">
        <!-- Card Pengguna -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <i class="fas fa-users text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">
                    Total Pegawai
                </p>
                <p class="text-lg font-semibold text-gray-700">
                    {{ $totalPegawai }}
                </p>
            </div>
        </div>
        
        <!-- Card Penitip -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <i class="fas fa-store text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">
                    Total Penitip
                </p>
                <p class="text-lg font-semibold text-gray-700">
                    {{ $totalPenitip }}
                </p>
            </div>
        </div>
        
        <!-- Card Pembeli -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <i class="fas fa-shopping-bag text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">
                    Total Pembeli
                </p>
                <p class="text-lg font-semibold text-gray-700">
                    {{ $totalPembeli }}
                </p>
            </div>
        </div>
        
        <!-- Card Organisasi -->
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                <i class="fas fa-building text-xl"></i>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">
                    Total Organisasi
                </p>
                <p class="text-lg font-semibold text-gray-700">
                    {{ $totalOrganisasi }}
                </p>
            </div>
        </div>
    </div>

    <!-- Produk Card -->
    <div class="grid gap-6 mb-8 md:grid-cols-4">
        <!-- Total Produk -->
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-600">Total Produk</p>
                <div class="text-green-500 bg-green-100 rounded-full p-2">
                    <i class="fas fa-box text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-700">{{ $totalProduk }}</p>
        </div>
        
        <!-- Produk Tersedia -->
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-600">Tersedia</p>
                <div class="text-blue-500 bg-blue-100 rounded-full p-2">
                    <i class="fas fa-check text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-blue-600">{{ $produkTersedia }}</p>
        </div>
        
        <!-- Produk Terjual -->
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-600">Terjual</p>
                <div class="text-yellow-500 bg-yellow-100 rounded-full p-2">
                    <i class="fas fa-shopping-cart text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-yellow-600">{{ $produkTerjual }}</p>
        </div>
        
        <!-- Produk Didonasikan -->
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-gray-600">Didonasikan</p>
                <div class="text-purple-500 bg-purple-100 rounded-full p-2">
                    <i class="fas fa-hand-holding-heart text-lg"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-purple-600">{{ $produkDidonasikan }}</p>
        </div>
    </div>

    <!-- Statistik Bulan Ini -->
    <h3 class="mb-4 text-lg font-semibold text-gray-700">
        Statistik Bulan Ini ({{ Carbon\Carbon::now()->format('F Y') }})
    </h3>
    <div class="grid gap-6 mb-8 md:grid-cols-3">
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-600">Penjualan</p>
                <div class="text-green-500">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-700">{{ $penjualanBulanIni }}</p>
            <p class="text-sm text-gray-500">Transaksi</p>
        </div>
        
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-600">Penitipan</p>
                <div class="text-blue-500">
                    <i class="fas fa-box-open"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-700">{{ $penitipanBulanIni }}</p>
            <p class="text-sm text-gray-500">Barang</p>
        </div>
        
        <div class="p-4 bg-white rounded-lg shadow-md">
            <div class="flex items-center justify-between mb-2">
                <p class="text-sm font-medium text-gray-600">Donasi</p>
                <div class="text-purple-500">
                    <i class="fas fa-gift"></i>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-700">{{ $donasiBulanIni }}</p>
            <p class="text-sm text-gray-500">Barang</p>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Grafik Penjualan -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h4 class="mb-4 font-semibold text-gray-800">
                Penjualan Per Bulan ({{ Carbon\Carbon::now()->format('Y') }})
            </h4>
            <canvas id="salesChart" class="w-full" height="300"></canvas>
        </div>
        
        <!-- Produk Terlaris -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <h4 class="mb-4 font-semibold text-gray-800">
                Produk Terlaris
            </h4>
            <div class="overflow-hidden">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3 text-center">Terjual</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse($produkTerlaris as $produk)
                        <tr class="text-gray-700 hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center text-sm">
                                    <p class="font-semibold truncate">{{ Str::limit($produk->deskripsi, 30) }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $produk->idProduk }}
                            </td>
                            <td class="px-4 py-3 text-sm text-center">
                                <span class="px-2 py-1 font-semibold leading-tight text-green-700 bg-green-100 rounded-full">
                                    {{ $produk->total_terjual }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">
                                Belum ada produk yang terjual
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Two Tables -->
    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Transaksi Terakhir -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-semibold text-gray-800">
                    Transaksi Penjualan Terakhir
                </h4>
                <a href="#" class="text-sm font-medium text-green-600 hover:underline">
                    Lihat Semua
                </a>
            </div>
            <div class="overflow-auto max-h-60">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Pembeli</th>
                            <th class="px-4 py-3">Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse($transaksiTerakhir as $transaksi)
                        <tr class="text-gray-700 hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">
                                {{ $transaksi->idTransaksi }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $transaksi->pembeli->nama }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d/m/Y') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">
                                Belum ada transaksi
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Barang Hampir Habis Masa Penitipan -->
        <div class="bg-white p-4 rounded-lg shadow-md">
            <div class="flex justify-between items-center mb-4">
                <h4 class="font-semibold text-gray-800">
                    Masa Penitipan Hampir Habis
                </h4>
                <a href="#" class="text-sm font-medium text-green-600 hover:underline">
                    Lihat Semua
                </a>
            </div>
            <div class="overflow-auto max-h-60">
                <table class="w-full whitespace-nowrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Produk</th>
                            <th class="px-4 py-3">Penitip</th>
                            <th class="px-4 py-3">Berakhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse($barangHampirHabis as $penitipan)
                        <tr class="text-gray-700 hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm">
                                @if(isset($penitipan->detailTransaksiPenitipan[0]->produk))
                                    {{ Str::limit($penitipan->detailTransaksiPenitipan[0]->produk->deskripsi, 20) }}
                                @else
                                    -
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm">
                                {{ $penitipan->penitip->nama }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 font-semibold leading-tight {{ Carbon\Carbon::parse($penitipan->tanggalAkhirPenitipan)->isPast() ? 'text-red-700 bg-red-100' : 'text-yellow-700 bg-yellow-100' }} rounded-full">
                                    {{ Carbon\Carbon::parse($penitipan->tanggalAkhirPenitipan)->format('d/m/Y') }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">
                                Tidak ada barang dengan masa penitipan hampir habis
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Chart data
        var chartData = @json($chartData);
        
        // Extract labels and data
        var labels = chartData.map(function(item) {
            return item.bulan;
        });
        
        var data = chartData.map(function(item) {
            return item.total_penjualan;
        });
        
        // Draw chart
        var ctx = document.getElementById('salesChart').getContext('2d');
        var salesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Jumlah Penjualan',
                    data: data,
                    backgroundColor: 'rgba(34, 197, 94, 0.5)',
                    borderColor: 'rgb(34, 197, 94)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    });
</script>
@endsection