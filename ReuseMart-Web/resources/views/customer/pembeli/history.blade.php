@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 via-purple-600 to-indigo-600 px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-history mr-3"></i>
                            Histori Transaksi Lengkap
                        </h1>
                        <p class="text-blue-100">Kelola dan lihat semua riwayat pembelian Anda</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <a href="{{ route('pembeli.profile') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all backdrop-blur-sm border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Profil
                        </a>
                        <a href="{{ route('produk.index') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-shopping-cart mr-2"></i> Belanja Lagi
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards - UPDATED untuk semua status -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4 mb-8">
            <!-- Total Transaksi -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-shopping-bag text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['total'] }}</p>
                    <p class="text-xs text-gray-600">Total</p>
                </div>
            </div>

            <!-- Menunggu Pembayaran -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-clock text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['menunggu_pembayaran'] }}</p>
                    <p class="text-xs text-gray-600">Bayar</p>
                </div>
            </div>

            <!-- Verifikasi -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-indigo-500 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-search text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['menunggu_verifikasi'] }}</p>
                    <p class="text-xs text-gray-600">Verifikasi</p>
                </div>
            </div>

            <!-- Disiapkan -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-purple-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-box text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['disiapkan'] }}</p>
                    <p class="text-xs text-gray-600">Siapkan</p>
                </div>
            </div>

            <!-- Dikirim -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-indigo-400 to-blue-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-truck text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['kirim'] }}</p>
                    <p class="text-xs text-gray-600">Kirim</p>
                </div>
            </div>

            <!-- Selesai -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-green-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-check-circle text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['terjual'] + $statistics['diambil'] }}</p>
                    <p class="text-xs text-gray-600">Selesai</p>
                </div>
            </div>

            <!-- Dibatalkan -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-red-400 to-red-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-times-circle text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ $statistics['batal'] }}</p>
                    <p class="text-xs text-gray-600">Batal</p>
                </div>
            </div>

            <!-- Total Belanja -->
            <div class="bg-white rounded-xl shadow-lg p-4 border border-gray-100">
                <div class="text-center">
                    <div class="w-10 h-10 bg-gradient-to-r from-emerald-400 to-teal-600 rounded-xl flex items-center justify-center mx-auto mb-2">
                        <i class="fas fa-wallet text-white text-sm"></i>
                    </div>
                    <p class="text-lg font-bold text-gray-800">{{ number_format($statistics['total_belanja'] / 1000000, 1) }}M</p>
                    <p class="text-xs text-gray-600">Total</p>
                </div>
            </div>
        </div>

        <!-- Filter Section - UPDATED dengan filter status -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
            <div class="flex items-center mb-4">
                <i class="fas fa-search text-blue-500 mr-3 text-xl"></i>
                <h2 class="text-lg font-semibold text-gray-800">Filter Transaksi</h2>
            </div>
            
            <form method="GET" action="{{ route('pembeli.history') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Filter Tanggal -->
                    <div>
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Dari Tanggal</label>
                        <input type="date" id="start_date" name="start_date" value="{{ $startDate->format('Y-m-d') }}" 
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>
                    
                    <div>
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">Sampai Tanggal</label>
                        <input type="date" id="end_date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                    </div>

                    <!-- Filter Status - BARU -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status Transaksi</label>
                        <select id="status" name="status" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                            <option value="semua" {{ ($statusFilter ?? 'semua') == 'semua' ? 'selected' : '' }}>Semua Status</option>
                            <option value="menunggu_pembayaran" {{ ($statusFilter ?? '') == 'menunggu_pembayaran' ? 'selected' : '' }}>Menunggu Pembayaran</option>
                            <option value="menunggu_verifikasi" {{ ($statusFilter ?? '') == 'menunggu_verifikasi' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                            <option value="disiapkan" {{ ($statusFilter ?? '') == 'disiapkan' ? 'selected' : '' }}>Disiapkan</option>
                            <option value="kirim" {{ ($statusFilter ?? '') == 'kirim' ? 'selected' : '' }}>Dikirim</option>
                            <option value="diambil" {{ ($statusFilter ?? '') == 'diambil' ? 'selected' : '' }}>Diambil</option>
                            <option value="terjual" {{ ($statusFilter ?? '') == 'terjual' ? 'selected' : '' }}>Selesai</option>
                            <option value="batal" {{ ($statusFilter ?? '') == 'batal' ? 'selected' : '' }}>Dibatalkan</option>
                        </select>
                    </div>
                    
                    <!-- Quick Filter Buttons -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Filter Cepat</label>
                        <div class="flex space-x-2">
                            <button type="button" onclick="setQuickFilter('week')" class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                7 Hari
                            </button>
                            <button type="button" onclick="setQuickFilter('month')" class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                1 Bulan
                            </button>
                            <button type="button" onclick="setQuickFilter('year')" class="px-3 py-2 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                                1 Tahun
                            </button>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">&nbsp;</label>
                        <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-lg shadow-sm text-white bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all">
                            <i class="fas fa-search mr-2"></i>
                            Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Transactions Section -->
        <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100">
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center">
                        <i class="fas fa-list mr-3 text-gray-500"></i>
                        Daftar Transaksi
                        <span class="ml-2 px-3 py-1 bg-blue-100 text-blue-800 text-sm rounded-full">
                            {{ $transaksiPenjualan->total() }} transaksi
                        </span>
                    </h2>
                    
                    <!-- View Toggle -->
                    <div class="flex bg-gray-200 rounded-lg p-1">
                        <button onclick="toggleView('grid')" id="gridViewBtn" class="px-3 py-1 text-sm rounded-md transition-colors bg-white text-gray-700 shadow-sm">
                            <i class="fas fa-th-large mr-1"></i> Grid
                        </button>
                        <button onclick="toggleView('list')" id="listViewBtn" class="px-3 py-1 text-sm rounded-md transition-colors text-gray-500 hover:text-gray-700">
                            <i class="fas fa-list mr-1"></i> List
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="p-6">
                @if(count($transaksiPenjualan) > 0)
                    <!-- Grid View -->
                    <div id="gridView" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($transaksiPenjualan as $transaksi)
                        @php
                        $statusConfig = [
                            'menunggu_pembayaran' => ['color' => 'yellow', 'bg' => 'yellow', 'icon' => 'fa-clock', 'text' => 'Menunggu Pembayaran'],
                            'menunggu_verifikasi' => ['color' => 'blue', 'bg' => 'blue', 'icon' => 'fa-search', 'text' => 'Verifikasi'],
                            'disiapkan' => ['color' => 'purple', 'bg' => 'purple', 'icon' => 'fa-box', 'text' => 'Disiapkan'],
                            'kirim' => ['color' => 'indigo', 'bg' => 'indigo', 'icon' => 'fa-truck', 'text' => 'Dikirim'],
                            'diambil' => ['color' => 'green', 'bg' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Diambil'],
                            'terjual' => ['color' => 'green', 'bg' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Selesai'],
                            'batal' => ['color' => 'red', 'bg' => 'red', 'icon' => 'fa-times-circle', 'text' => 'Dibatalkan'],
                        ];
                        $currentStatus = $statusConfig[$transaksi->status] ?? ['color' => 'gray', 'bg' => 'gray', 'icon' => 'fa-question', 'text' => 'Unknown'];
                        @endphp
                        
                        <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl border border-gray-200 hover:border-{{ $currentStatus['color'] }}-300 transition-all duration-300 overflow-hidden group hover:shadow-lg">
                            <!-- Card Header -->
                            <div class="bg-gradient-to-r from-{{ $currentStatus['bg'] }}-500 to-{{ $currentStatus['bg'] }}-600 px-4 py-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-white font-semibold text-sm">
                                        #{{ $transaksi->idTransaksiPenjualan }}
                                    </span>
                                    <span class="bg-white/20 backdrop-blur-sm text-white text-xs px-2 py-1 rounded-full">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y') }}
                                    </span>
                                </div>
                            </div>
                            
                            <!-- Card Content -->
                            <div class="p-4">
                                <h3 class="font-semibold text-gray-800 mb-2 group-hover:text-{{ $currentStatus['color'] }}-700 transition-colors">
                                    {{ Str::limit($transaksi->deskripsi, 50) }}
                                </h3>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Harga:</span>
                                        <span class="font-semibold text-green-600">
                                            Rp {{ number_format($transaksi->hargaJual, 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">Status:</span>
                                        <span class="px-2 py-1 bg-{{ $currentStatus['color'] }}-100 text-{{ $currentStatus['color'] }}-800 text-xs rounded-full font-medium flex items-center">
                                            <i class="fas {{ $currentStatus['icon'] }} mr-1"></i>
                                            {{ $currentStatus['text'] }}
                                        </span>
                                    </div>

                                    <!-- Tanggal berdasarkan status -->
                                    <div class="flex items-center justify-between">
                                        <span class="text-sm text-gray-600">
                                            @if($transaksi->status === 'menunggu_pembayaran')
                                                Batas Bayar:
                                            @elseif($transaksi->tanggalLunas)
                                                Tgl Lunas:
                                            @else
                                                Tgl Pesan:
                                            @endif
                                        </span>
                                        <span class="text-xs text-gray-500">
                                            @if($transaksi->status === 'menunggu_pembayaran' && $transaksi->tanggalBatasLunas)
                                                {{ \Carbon\Carbon::parse($transaksi->tanggalBatasLunas)->format('d M H:i') }}
                                            @elseif($transaksi->tanggalLunas)
                                                {{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d M H:i') }}
                                            @else
                                                {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M H:i') }}
                                            @endif
                                        </span>
                                    </div>
                                </div>
                                
                                <a href="{{ route('pembeli.transaksi.detail', $transaksi->idTransaksiPenjualan) }}" 
                                   class="w-full inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-{{ $currentStatus['bg'] }}-500 to-{{ $currentStatus['bg'] }}-600 hover:from-{{ $currentStatus['bg'] }}-600 hover:to-{{ $currentStatus['bg'] }}-700 text-white text-sm font-medium rounded-lg transition-all">
                                    <i class="fas fa-eye mr-2"></i>
                                    Lihat Detail
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- List View -->
                    <div id="listView" class="space-y-4 hidden">
                        @foreach($transaksiPenjualan as $transaksi)
                        @php
                        $statusConfig = [
                            'menunggu_pembayaran' => ['color' => 'yellow', 'bg' => 'yellow', 'icon' => 'fa-clock', 'text' => 'Menunggu Pembayaran'],
                            'menunggu_verifikasi' => ['color' => 'blue', 'bg' => 'blue', 'icon' => 'fa-search', 'text' => 'Verifikasi'],
                            'disiapkan' => ['color' => 'purple', 'bg' => 'purple', 'icon' => 'fa-box', 'text' => 'Disiapkan'],
                            'kirim' => ['color' => 'indigo', 'bg' => 'indigo', 'icon' => 'fa-truck', 'text' => 'Dikirim'],
                            'diambil' => ['color' => 'green', 'bg' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Diambil'],
                            'terjual' => ['color' => 'green', 'bg' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Selesai'],
                            'batal' => ['color' => 'red', 'bg' => 'red', 'icon' => 'fa-times-circle', 'text' => 'Dibatalkan'],
                        ];
                        $currentStatus = $statusConfig[$transaksi->status] ?? ['color' => 'gray', 'bg' => 'gray', 'icon' => 'fa-question', 'text' => 'Unknown'];
                        @endphp
                        
                        <div class="bg-gradient-to-r from-white to-gray-50 rounded-xl border border-gray-200 hover:border-{{ $currentStatus['color'] }}-300 transition-all duration-300 p-6 group hover:shadow-lg">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-6">
                                    <!-- Transaction ID Badge -->
                                    <div class="w-16 h-16 bg-gradient-to-r from-{{ $currentStatus['bg'] }}-500 to-{{ $currentStatus['bg'] }}-600 rounded-xl flex items-center justify-center">
                                        <span class="text-white font-bold text-sm">#{{ $transaksi->idTransaksiPenjualan }}</span>
                                    </div>
                                    
                                    <!-- Transaction Info -->
                                    <div>
                                        <h3 class="font-semibold text-gray-800 mb-1 group-hover:text-{{ $currentStatus['color'] }}-700 transition-colors">
                                            {{ $transaksi->deskripsi }}
                                        </h3>
                                        <div class="flex items-center space-x-4 text-sm text-gray-600">
                                            <span class="flex items-center">
                                                <i class="fas fa-calendar mr-2"></i>
                                                {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }}
                                            </span>
                                            <span class="flex items-center">
                                                <i class="fas fa-money-bill-wave mr-2"></i>
                                                Rp {{ number_format($transaksi->hargaJual, 0, ',', '.') }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="flex items-center space-x-4">
                                    <span class="px-3 py-1 bg-{{ $currentStatus['color'] }}-100 text-{{ $currentStatus['color'] }}-800 text-sm rounded-full font-medium flex items-center">
                                        <i class="fas {{ $currentStatus['icon'] }} mr-2"></i>
                                        {{ $currentStatus['text'] }}
                                    </span>
                                    
                                    <a href="{{ route('pembeli.transaksi.detail', $transaksi->idTransaksiPenjualan) }}" 
                                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-{{ $currentStatus['bg'] }}-500 to-{{ $currentStatus['bg'] }}-600 hover:from-{{ $currentStatus['bg'] }}-600 hover:to-{{ $currentStatus['bg'] }}-700 text-white text-sm font-medium rounded-lg transition-all">
                                        <i class="fas fa-eye mr-2"></i>
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    
                    <!-- Pagination -->
                    <div class="mt-8 flex justify-center">
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-2">
                            {{ $transaksiPenjualan->appends(request()->query())->links('pagination::tailwind') }}
                        </div>
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-16">
                        <div class="w-32 h-32 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-shopping-bag text-gray-400 text-4xl"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-3">Tidak Ada Transaksi</h3>
                        <p class="text-gray-600 mb-8 max-w-md mx-auto">
                            Tidak ada transaksi dalam periode yang dipilih. Coba ubah filter tanggal atau mulai berbelanja sekarang!
                        </p>
                        <div class="flex flex-col sm:flex-row gap-4 justify-center">
                            <a href="{{ route('produk.index') }}" class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Mulai Belanja
                            </a>
                            <button onclick="resetFilter()" class="inline-flex items-center px-6 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all">
                                <i class="fas fa-undo mr-2"></i>
                                Reset Filter
                            </button>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
// Quick filter functions
function setQuickFilter(period) {
    const endDate = new Date();
    let startDate = new Date();
    
    switch(period) {
        case 'week':
            startDate.setDate(endDate.getDate() - 7);
            break;
        case 'month':
            startDate.setMonth(endDate.getMonth() - 1);
            break;
        case 'year':
            startDate.setFullYear(endDate.getFullYear() - 1);
            break;
    }
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
}

// Reset filter to default (last 3 months)
function resetFilter() {
    const endDate = new Date();
    const startDate = new Date();
    startDate.setMonth(endDate.getMonth() - 3);
    
    document.getElementById('start_date').value = startDate.toISOString().split('T')[0];
    document.getElementById('end_date').value = endDate.toISOString().split('T')[0];
    document.getElementById('status').value = 'semua';
    
    // Submit form
    document.querySelector('form').submit();
}

// Toggle between grid and list view
function toggleView(viewType) {
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const gridBtn = document.getElementById('gridViewBtn');
    const listBtn = document.getElementById('listViewBtn');
    
    if (viewType === 'grid') {
        gridView.classList.remove('hidden');
        listView.classList.add('hidden');
        gridBtn.classList.add('bg-white', 'text-gray-700', 'shadow-sm');
        gridBtn.classList.remove('text-gray-500');
        listBtn.classList.remove('bg-white', 'text-gray-700', 'shadow-sm');
        listBtn.classList.add('text-gray-500');
    } else {
        listView.classList.remove('hidden');
        gridView.classList.add('hidden');
        listBtn.classList.add('bg-white', 'text-gray-700', 'shadow-sm');
        listBtn.classList.remove('text-gray-500');
        gridBtn.classList.remove('bg-white', 'text-gray-700', 'shadow-sm');
        gridBtn.classList.add('text-gray-500');
    }
    
    // Save preference to localStorage
    localStorage.setItem('transactionViewType', viewType);
}

// Load saved view preference
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('transactionViewType') || 'grid';
    toggleView(savedView);
});
</script>

<style>
/* Custom animations */
.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Improved hover effects */
.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
    transform: translateY(-2px);
}

/* Status color variations */
.bg-yellow-500 { background: linear-gradient(135deg, #f59e0b, #d97706); }
.bg-blue-500 { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
.bg-purple-500 { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
.bg-indigo-500 { background: linear-gradient(135deg, #6366f1, #4f46e5); }
.bg-green-500 { background: linear-gradient(135deg, #10b981, #059669); }
.bg-red-500 { background: linear-gradient(135deg, #ef4444, #dc2626); }
.bg-gray-500 { background: linear-gradient(135deg, #6b7280, #4b5563); }

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%);
}
</style>

@endsection