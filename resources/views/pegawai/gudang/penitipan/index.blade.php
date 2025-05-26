@extends('layouts.gudang')

@section('title', 'Daftar Transaksi Penitipan')

@section('breadcrumb')
<nav class="text-sm text-gray-500 mt-1">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('gudang.dashboard') }}" class="hover:text-green-600">Dashboard</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="text-gray-600">Transaksi Penitipan</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Daftar Transaksi Penitipan</h2>
                <p class="text-gray-600 mt-1">Kelola semua transaksi penitipan barang</p>
            </div>
            <div class="flex space-x-3">
                <button onclick="toggleAdvancedSearch()" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-search-plus"></i>
                    <span>Pencarian Advanced</span>
                </button>
                <a href="{{ route('gudang.penitipan.create') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-plus"></i>
                    <span>Tambah Transaksi</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fa-solid fa-file-invoice text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->total() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fa-solid fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Transaksi Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->where('statusPenitipan', 'Aktif')->count() }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fa-solid fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Akan Expired</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        {{ $transaksi->filter(function($item) {
                            return \Carbon\Carbon::parse($item->batasAmbil)->isBetween(now(), now()->addDays(7)) && $item->statusPenitipan == 'Aktif';
                        })->count() }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fa-solid fa-money-bill text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pendapatan</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($transaksi->sum('pendapatan'), 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Filters -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form method="GET" action="{{ route('gudang.penitipan.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">
                    <i class="fa-solid fa-search mr-1"></i>
                    Pencarian Cepat
                </label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" 
                       placeholder="Nama penitip, ID transaksi"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select name="status" id="status" class="border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    <option value="">Semua Status</option>
                    <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                    <option value="Selesai" {{ request('status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                    <option value="Expired" {{ request('status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                </select>
            </div>
            <div class="flex space-x-2">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                    <i class="fa-solid fa-search"></i>
                    <span>Cari</span>
                </button>
                <a href="{{ route('gudang.penitipan.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                    <i class="fa-solid fa-refresh"></i>
                    <span>Reset</span>
                </a>
            </div>
        </form>
    </div>

    <!-- Advanced Search Panel (Hidden by default) -->
    <div id="advanced-search" class="bg-white rounded-lg shadow-sm p-6 hidden">
        <div class="border-l-4 border-blue-500 pl-4 mb-6">
            <h3 class="text-lg font-medium text-gray-900 flex items-center">
                <i class="fa-solid fa-search-plus text-blue-600 mr-2"></i>
                Pencarian Advanced
            </h3>
            <p class="text-sm text-gray-600 mt-1">Cari berdasarkan semua field yang tersedia</p>
        </div>
        
        <form method="GET" action="{{ route('gudang.penitipan.index') }}" class="space-y-6">
            <input type="hidden" name="advanced_search" value="1">
            
            <!-- Row 1: Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="adv_id" class="block text-sm font-medium text-gray-700 mb-2">ID Transaksi</label>
                    <input type="text" name="adv_id" id="adv_id" value="{{ request('adv_id') }}" 
                           placeholder="Cth: 001, 123"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="adv_nama_penitip" class="block text-sm font-medium text-gray-700 mb-2">Nama Penitip</label>
                    <input type="text" name="adv_nama_penitip" id="adv_nama_penitip" value="{{ request('adv_nama_penitip') }}" 
                           placeholder="Nama lengkap penitip"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="adv_nama_pegawai" class="block text-sm font-medium text-gray-700 mb-2">Nama Pegawai</label>
                    <input type="text" name="adv_nama_pegawai" id="adv_nama_pegawai" value="{{ request('adv_nama_pegawai') }}" 
                           placeholder="Nama pegawai"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
            </div>

            <!-- Row 2: Dates -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="adv_tanggal_masuk_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masuk (Dari)</label>
                    <input type="date" name="adv_tanggal_masuk_dari" id="adv_tanggal_masuk_dari" value="{{ request('adv_tanggal_masuk_dari') }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="adv_tanggal_masuk_sampai" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Masuk (Sampai)</label>
                    <input type="date" name="adv_tanggal_masuk_sampai" id="adv_tanggal_masuk_sampai" value="{{ request('adv_tanggal_masuk_sampai') }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="adv_status" class="block text-sm font-medium text-gray-700 mb-2">Status Penitipan</label>
                    <select name="adv_status" id="adv_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua Status</option>
                        <option value="Aktif" {{ request('adv_status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="Selesai" {{ request('adv_status') == 'Selesai' ? 'selected' : '' }}>Selesai</option>
                        <option value="Expired" {{ request('adv_status') == 'Expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
            </div>

            <!-- Row 3: Financial & Special -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label for="adv_pendapatan_min" class="block text-sm font-medium text-gray-700 mb-2">Pendapatan Min</label>
                    <input type="number" name="adv_pendapatan_min" id="adv_pendapatan_min" value="{{ request('adv_pendapatan_min') }}" 
                           placeholder="0" min="0"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="adv_pendapatan_max" class="block text-sm font-medium text-gray-700 mb-2">Pendapatan Max</label>
                    <input type="number" name="adv_pendapatan_max" id="adv_pendapatan_max" value="{{ request('adv_pendapatan_max') }}" 
                           placeholder="0" min="0"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div>
                    <label for="adv_perpanjangan" class="block text-sm font-medium text-gray-700 mb-2">Status Perpanjangan</label>
                    <select name="adv_perpanjangan" id="adv_perpanjangan" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua</option>
                        <option value="0" {{ request('adv_perpanjangan') === '0' ? 'selected' : '' }}>Tidak Diperpanjang</option>
                        <option value="1" {{ request('adv_perpanjangan') === '1' ? 'selected' : '' }}>Diperpanjang</option>
                    </select>
                </div>
                <div>
                    <label for="adv_expired_only" class="block text-sm font-medium text-gray-700 mb-2">Filter Khusus</label>
                    <select name="adv_expired_only" id="adv_expired_only" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Semua</option>
                        <option value="akan_expired" {{ request('adv_expired_only') == 'akan_expired' ? 'selected' : '' }}>Akan Expired (7 hari)</option>
                        <option value="sudah_expired" {{ request('adv_expired_only') == 'sudah_expired' ? 'selected' : '' }}>Sudah Expired</option>
                        <option value="hari_ini" {{ request('adv_expired_only') == 'hari_ini' ? 'selected' : '' }}>Dibuat Hari Ini</option>
                    </select>
                </div>
            </div>

            <!-- Search Actions -->
            <div class="flex justify-between items-center pt-4 border-t border-gray-200">
                <div class="text-sm text-gray-500">
                    <i class="fa-solid fa-info-circle mr-1"></i>
                    Kosongkan field untuk mengabaikan kriteria pencarian
                </div>
                <div class="flex space-x-3">
                    <button type="button" onclick="clearAdvancedSearch()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md flex items-center space-x-2">
                        <i class="fa-solid fa-eraser"></i>
                        <span>Clear</span>
                    </button>
                    <button type="submit" 
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md flex items-center space-x-2">
                        <i class="fa-solid fa-search"></i>
                        <span>Cari Advanced</span>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Search Results Info -->
    @if(request()->filled('search') || request()->filled('status') || request()->filled('advanced_search'))
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-info-circle text-blue-600 mr-2"></i>
                    <span class="text-blue-800 font-medium">
                        Hasil pencarian: {{ $transaksi->total() }} transaksi ditemukan
                    </span>
                </div>
                <a href="{{ route('gudang.penitipan.index') }}" 
                   class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                    <i class="fa-solid fa-times mr-1"></i>
                    Hapus Filter
                </a>
            </div>
            @if(request()->filled('advanced_search'))
                <div class="mt-2 text-sm text-blue-700">
                    <i class="fa-solid fa-search-plus mr-1"></i>
                    Pencarian menggunakan filter advanced
                </div>
            @endif
        </div>
    @endif

    <!-- Transactions Table -->
    <div class="bg-white rounded-lg shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Daftar Transaksi</h3>
                <div class="flex items-center space-x-2">
                    <!-- Export Buttons -->
                    <button onclick="exportData('excel')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded text-xs flex items-center space-x-1">
                        <i class="fa-solid fa-file-excel"></i>
                        <span>Excel</span>
                    </button>
                    <button onclick="exportData('pdf')" 
                            class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded text-xs flex items-center space-x-1">
                        <i class="fa-solid fa-file-pdf"></i>
                        <span>PDF</span>
                    </button>
                </div>
            </div>
        </div>
        
        @if($transaksi->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                ID &amp; Penitip
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pegawai
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Tanggal
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Pendapatan
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($transaksi as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">#{{ $item->idTransaksiPenitipan }}</div>
                                        <div class="text-sm text-gray-500">{{ $item->namaPenitip }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $item->namaPegawai }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div>Masuk: {{ \Carbon\Carbon::parse($item->tanggalMasukPenitipan)->format('d/m/Y') }}</div>
                                        <div class="text-xs text-gray-500">
                                            Batas: {{ \Carbon\Carbon::parse($item->batasAmbil)->format('d/m/Y H:i') }}
                                        </div>
                                        @if(\Carbon\Carbon::parse($item->batasAmbil)->isPast() && $item->statusPenitipan == 'Aktif')
                                            <div class="text-xs text-red-500 font-medium">Expired!</div>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center space-x-2">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($item->statusPenitipan == 'Aktif') bg-green-100 text-green-800
                                            @elseif($item->statusPenitipan == 'Selesai') bg-blue-100 text-blue-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ $item->statusPenitipan }}
                                        </span>
                                        @if($item->statusPerpanjangan)
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                                Diperpanjang
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">Rp {{ number_format($item->pendapatan, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <div class="flex items-center space-x-2">
                                        <a href="{{ route('gudang.penitipan.show', $item->idTransaksiPenitipan) }}" 
                                           class="text-green-600 hover:text-green-900" title="Lihat Detail">
                                            <i class="fa-solid fa-eye"></i>
                                        </a>
                                        <a href="{{ route('gudang.penitipan.edit', $item->idTransaksiPenitipan) }}" 
                                           class="text-blue-600 hover:text-blue-900" title="Edit">
                                            <i class="fa-solid fa-edit"></i>
                                        </a>
                                        <form action="{{ route('gudang.penitipan.destroy', $item->idTransaksiPenitipan) }}" 
                                              method="POST" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-900" 
                                                    title="Hapus"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus transaksi ini?')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                {{ $transaksi->appends(request()->query())->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <div class="mx-auto h-24 w-24 text-gray-400">
                    <i class="fa-solid fa-file-invoice text-6xl"></i>
                </div>
                <h3 class="mt-4 text-sm font-medium text-gray-900">
                    @if(request()->hasAny(['search', 'status', 'advanced_search']))
                        Tidak ada transaksi yang sesuai dengan kriteria pencarian
                    @else
                        Belum ada transaksi
                    @endif
                </h3>
                <p class="mt-1 text-sm text-gray-500">
                    @if(request()->hasAny(['search', 'status', 'advanced_search']))
                        Coba ubah kriteria pencarian atau reset filter
                    @else
                        Mulai dengan membuat transaksi penitipan baru.
                    @endif
                </p>
                <div class="mt-6">
                    @if(request()->hasAny(['search', 'status', 'advanced_search']))
                        <a href="{{ route('gudang.penitipan.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                            <i class="fa-solid fa-refresh mr-2"></i>
                            Reset Filter
                        </a>
                    @else
                        <a href="{{ route('gudang.penitipan.create') }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700">
                            <i class="fa-solid fa-plus mr-2"></i>
                            Tambah Transaksi Baru
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Toggle Advanced Search Panel
    function toggleAdvancedSearch() {
        const panel = document.getElementById('advanced-search');
        const isHidden = panel.classList.contains('hidden');
        
        if (isHidden) {
            panel.classList.remove('hidden');
            panel.style.display = 'block';
            // Smooth animation
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                panel.style.transition = 'all 0.3s ease-out';
                panel.style.opacity = '1';
                panel.style.transform = 'translateY(0)';
            }, 10);
        } else {
            panel.style.transition = 'all 0.3s ease-out';
            panel.style.opacity = '0';
            panel.style.transform = 'translateY(-10px)';
            setTimeout(() => {
                panel.classList.add('hidden');
            }, 300);
        }
    }

    // Clear Advanced Search
    function clearAdvancedSearch() {
        const form = document.querySelector('#advanced-search form');
        const inputs = form.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], select');
        
        inputs.forEach(input => {
            if (input.tagName === 'SELECT') {
                input.selectedIndex = 0;
            } else {
                input.value = '';
            }
        });
    }

    // Export Data
    function exportData(format) {
        const params = new URLSearchParams(window.location.search);
        params.set('export', format);
        
        // For now, just show alert - implement actual export later
        alert('Export ' + format.toUpperCase() + ' feature will be implemented later');
    }

    // Auto-show advanced search if advanced parameters exist
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('advanced_search')) {
            toggleAdvancedSearch();
        }
    });

    // Date range validation
    document.getElementById('adv_tanggal_masuk_dari').addEventListener('change', function() {
        const sampaiInput = document.getElementById('adv_tanggal_masuk_sampai');
        if (this.value && sampaiInput.value && this.value > sampaiInput.value) {
            alert('Tanggal "Dari" tidak boleh lebih besar dari tanggal "Sampai"');
            this.value = '';
        }
    });

    document.getElementById('adv_tanggal_masuk_sampai').addEventListener('change', function() {
        const dariInput = document.getElementById('adv_tanggal_masuk_dari');
        if (this.value && dariInput.value && this.value < dariInput.value) {
            alert('Tanggal "Sampai" tidak boleh lebih kecil dari tanggal "Dari"');
            this.value = '';
        }
    });

    // Pendapatan range validation
    document.getElementById('adv_pendapatan_min').addEventListener('change', function() {
        const maxInput = document.getElementById('adv_pendapatan_max');
        if (this.value && maxInput.value && parseInt(this.value) > parseInt(maxInput.value)) {
            alert('Pendapatan minimum tidak boleh lebih besar dari maksimum');
            this.value = '';
        }
    });

    document.getElementById('adv_pendapatan_max').addEventListener('change', function() {
        const minInput = document.getElementById('adv_pendapatan_min');
        if (this.value && minInput.value && parseInt(this.value) < parseInt(minInput.value)) {
            alert('Pendapatan maksimum tidak boleh lebih kecil dari minimum');
            this.value = '';
        }
    });
</script>
@endsection