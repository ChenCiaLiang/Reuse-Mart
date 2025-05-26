@extends('layouts.gudang')

@section('title', 'Edit Transaksi Penitipan')

@section('breadcrumb')
<nav class="text-sm text-gray-500 mt-1">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('gudang.dashboard') }}" class="hover:text-green-600">Dashboard</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="flex items-center">
            <a href="{{ route('gudang.penitipan.index') }}" class="hover:text-green-600">Transaksi Penitipan</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="text-gray-600">Edit #{{ $transaksi->idTransaksiPenitipan }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fa-solid fa-edit mr-2 text-blue-600"></i>
                    Edit Transaksi #{{ $transaksi->idTransaksiPenitipan }}
                </h2>
                <p class="text-gray-600 mt-1">Ubah informasi transaksi penitipan</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('gudang.penitipan.show', $transaksi->idTransaksiPenitipan) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-eye"></i>
                    <span>Lihat Detail</span>
                </a>
                <a href="{{ route('gudang.penitipan.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('gudang.penitipan.update', $transaksi->idTransaksiPenitipan) }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            @method('PUT')
            
            <!-- Basic Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-info-circle mr-2 text-green-600"></i>
                    Informasi Dasar
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Penitip - FIXED: Menampilkan email sebagai kontak -->
                    <div>
                        <label for="idPenitip" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-user mr-1"></i>
                            Penitip *
                        </label>
                        <select id="idPenitip" name="idPenitip" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Penitip</option>
                            @foreach($penitip as $p)
                                <option value="{{ $p->idPenitip }}" 
                                    {{ old('idPenitip', $transaksi->idPenitip) == $p->idPenitip ? 'selected' : '' }}
                                    data-email="{{ $p->email }}" data-alamat="{{ $p->alamat }}">
                                    {{ $p->nama }} - {{ $p->email }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Info Penitip yang dipilih -->
                        <div id="penitip-info" class="mt-2 p-2 bg-gray-50 rounded text-sm text-gray-600 {{ old('idPenitip', $transaksi->idPenitip) ? '' : 'hidden' }}">
                            <div><strong>Email:</strong> <span id="penitip-email">-</span></div>
                            <div><strong>Alamat:</strong> <span id="penitip-alamat">-</span></div>
                        </div>
                    </div>

                    <!-- Pegawai -->
                    <div>
                        <label for="idPegawai" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-user-tie mr-1"></i>
                            Pegawai QC *
                        </label>
                        <select id="idPegawai" name="idPegawai" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="">Pilih Pegawai</option>
                            @foreach($pegawai as $pg)
                                <option value="{{ $pg->idPegawai }}" 
                                    {{ old('idPegawai', $transaksi->idPegawai) == $pg->idPegawai ? 'selected' : '' }}>
                                    {{ $pg->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Date Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-calendar mr-2 text-green-600"></i>
                    Informasi Tanggal
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Tanggal Masuk -->
                    <div>
                        <label for="tanggalMasukPenitipan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-plus mr-1"></i>
                            Tanggal Masuk *
                        </label>
                        <input type="datetime-local" id="tanggalMasukPenitipan" name="tanggalMasukPenitipan" 
                               value="{{ old('tanggalMasukPenitipan', \Carbon\Carbon::parse($transaksi->tanggalMasukPenitipan)->format('Y-m-d\TH:i')) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Tanggal Akhir -->
                    <div>
                        <label for="tanggalAkhirPenitipan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-check mr-1"></i>
                            Tanggal Akhir *
                        </label>
                        <input type="datetime-local" id="tanggalAkhirPenitipan" name="tanggalAkhirPenitipan" 
                               value="{{ old('tanggalAkhirPenitipan', \Carbon\Carbon::parse($transaksi->tanggalAkhirPenitipan)->format('Y-m-d\TH:i')) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>

                    <!-- Batas Ambil -->
                    <div>
                        <label for="batasAmbil" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-times mr-1"></i>
                            Batas Ambil *
                        </label>
                        <input type="datetime-local" id="batasAmbil" name="batasAmbil" 
                               value="{{ old('batasAmbil', \Carbon\Carbon::parse($transaksi->batasAmbil)->format('Y-m-d\TH:i')) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-tasks mr-2 text-green-600"></i>
                    Informasi Status
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Status Penitipan -->
                    <div>
                        <label for="statusPenitipan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-circle mr-1"></i>
                            Status Penitipan *
                        </label>
                        <select id="statusPenitipan" name="statusPenitipan" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="Aktif" {{ old('statusPenitipan', $transaksi->statusPenitipan) == 'Aktif' ? 'selected' : '' }}>
                                Aktif
                            </option>
                            <option value="Selesai" {{ old('statusPenitipan', $transaksi->statusPenitipan) == 'Selesai' ? 'selected' : '' }}>
                                Selesai
                            </option>
                            <option value="Expired" {{ old('statusPenitipan', $transaksi->statusPenitipan) == 'Expired' ? 'selected' : '' }}>
                                Expired
                            </option>
                        </select>
                    </div>

                    <!-- Status Perpanjangan -->
                    <div>
                        <label for="statusPerpanjangan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-clock mr-1"></i>
                            Status Perpanjangan *
                        </label>
                        <select id="statusPerpanjangan" name="statusPerpanjangan" required 
                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                            <option value="0" {{ old('statusPerpanjangan', $transaksi->statusPerpanjangan) == '0' ? 'selected' : '' }}>
                                Tidak Diperpanjang
                            </option>
                            <option value="1" {{ old('statusPerpanjangan', $transaksi->statusPerpanjangan) == '1' ? 'selected' : '' }}>
                                Diperpanjang
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-money-bill mr-2 text-green-600"></i>
                    Informasi Keuangan
                </h3>
                <div class="max-w-md">
                    <label for="pendapatan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fa-solid fa-rupiah-sign mr-1"></i>
                        Pendapatan (Rp) *
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <span class="text-gray-500 sm:text-sm">Rp</span>
                        </div>
                        <input type="number" id="pendapatan" name="pendapatan" step="0.01" min="0"
                               value="{{ old('pendapatan', $transaksi->pendapatan) }}" required
                               class="w-full pl-12 border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                               placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Current Products -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-box mr-2 text-green-600"></i>
                    Produk yang Sudah Ada di Transaksi Ini
                </h3>
                <div class="border border-gray-200 rounded-lg p-4 bg-blue-50">
                    @if($produkTransaksi->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($produkTransaksi as $produk)
                                <div class="bg-white p-4 rounded-lg border border-blue-200">
                                    <div class="flex items-start space-x-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $produk->deskripsi }}</h4>
                                            <p class="text-xs text-gray-600 mt-1">Status: {{ $produk->status }}</p>
                                            <div class="mt-2 flex justify-between text-xs">
                                                <span class="text-green-600 font-medium">Harga: Rp {{ number_format($produk->harga, 0, ',', '.') }}</span>
                                                <span class="text-blue-600 font-medium">Jual: Rp {{ number_format($produk->hargaJual, 0, ',', '.') }}</span>
                                            </div>
                                            @if($produk->gambar)
                                                <div class="mt-2">
                                                    @php
                                                        $gambarArray = explode(',', $produk->gambar);
                                                        $firstImage = trim($gambarArray[0]);
                                                    @endphp
                                                    <img src="{{ asset('uploads/produk/' . $firstImage) }}" 
                                                         alt="Foto Produk" 
                                                         class="w-full h-20 object-cover rounded border">
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-500 text-center py-4">Belum ada produk di transaksi ini</p>
                    @endif
                </div>
            </div>

            <!-- Update Existing Products (Simplified) -->
            @if($produkTransaksi->count() > 0)
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                        <i class="fa-solid fa-edit mr-2 text-blue-600"></i>
                        Update Informasi Produk Existing
                    </h3>
                    <div class="space-y-4">
                        @foreach($produkTransaksi as $index => $produk)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h4 class="text-md font-medium text-gray-800 mb-3">Produk: {{ $produk->deskripsi }}</h4>
                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Produk *</label>
                                        <input type="text" name="produk_existing[{{ $produk->idProduk }}][deskripsi]" 
                                               value="{{ old('produk_existing.' . $produk->idProduk . '.deskripsi', $produk->deskripsi) }}" required
                                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                                        <input type="number" name="produk_existing[{{ $produk->idProduk }}][harga]" 
                                               value="{{ old('produk_existing.' . $produk->idProduk . '.harga', $produk->harga) }}" 
                                               min="0" step="0.01" required
                                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Harga Jual (Rp) *</label>
                                        <input type="number" name="produk_existing[{{ $produk->idProduk }}][hargaJual]" 
                                               value="{{ old('produk_existing.' . $produk->idProduk . '.hargaJual', $produk->hargaJual) }}" 
                                               min="0" step="0.01" required
                                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Berat (kg) *</label>
                                        <input type="number" name="produk_existing[{{ $produk->idProduk }}][berat]" 
                                               value="{{ old('produk_existing.' . $produk->idProduk . '.berat', $produk->berat) }}" 
                                               min="0" step="0.01" required
                                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                                        <select name="produk_existing[{{ $produk->idProduk }}][idKategori]" required
                                                class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                            <option value="">Pilih Kategori</option>
                                            @foreach($kategori as $k)
                                                <option value="{{ $k->idKategori }}" 
                                                    {{ old('produk_existing.' . $produk->idProduk . '.idKategori', $produk->idKategori) == $k->idKategori ? 'selected' : '' }}>
                                                    {{ $k->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Garansi (Opsional)</label>
                                        <input type="date" name="produk_existing[{{ $produk->idProduk }}][tanggalGaransi]" 
                                               value="{{ old('produk_existing.' . $produk->idProduk . '.tanggalGaransi', $produk->tanggalGaransi ? \Carbon\Carbon::parse($produk->tanggalGaransi)->format('Y-m-d') : '') }}"
                                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Add New Products Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-plus mr-2 text-green-600"></i>
                    Tambah Produk Baru (Opsional)
                </h3>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div id="produk-container">
                        <!-- Container untuk produk baru akan ditambahkan dengan JavaScript -->
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button type="button" id="tambah-produk-btn" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Produk Baru</span>
                        </button>
                        <div class="text-sm text-gray-500">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Opsional: Tambah produk baru ke transaksi ini
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Foto Baru (jika ada produk baru) -->
            <div id="foto-section" class="hidden">
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-camera mr-2 text-green-600"></i>
                    Foto untuk Produk Baru (Minimal 2 Foto)
                </h3>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <div class="text-center">
                        <i class="fa-solid fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                        <p class="text-sm text-gray-600 mb-4">Upload foto untuk produk baru (JPG, PNG, JPEG)</p>
                        
                        <!-- File Input -->
                        <input type="file" id="foto-barang-baru" name="foto_barang_baru[]" multiple accept="image/*" 
                               class="hidden" onchange="previewPhotos(this)">
                        <label for="foto-barang-baru" 
                               class="cursor-pointer bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center space-x-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Pilih Foto</span>
                        </label>
                        
                        <p class="text-xs text-gray-500 mt-2">Minimal 2 foto, maksimal 3 foto untuk produk baru</p>
                    </div>
                    
                    <!-- Photo Preview -->
                    <div id="photo-preview" class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-4 hidden">
                        <!-- Preview akan ditampilkan di sini via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('gudang.penitipan.show', $transaksi->idTransaksiPenitipan) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-times"></i>
                    <span>Batal</span>
                </a>
                <button type="submit" 
                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-save"></i>
                    <span>Update Transaksi</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let produkCounter = 0;

    // Show penitip info when selected  
    document.getElementById('idPenitip').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const infoDiv = document.getElementById('penitip-info');
        
        if (this.value) {
            const email = selectedOption.getAttribute('data-email');
            const alamat = selectedOption.getAttribute('data-alamat');
            
            document.getElementById('penitip-email').textContent = email || '-';
            document.getElementById('penitip-alamat').textContent = alamat || '-';
            infoDiv.classList.remove('hidden');
        } else {
            infoDiv.classList.add('hidden');
        }
    });

    // Initialize penitip info on page load
    window.addEventListener('load', function() {
        const penitipSelect = document.getElementById('idPenitip');
        if (penitipSelect.value) {
            penitipSelect.dispatchEvent(new Event('change'));
        }
    });

    // Product management for new products
    document.getElementById('tambah-produk-btn').addEventListener('click', function() {
        const container = document.getElementById('produk-container');
        const fotoSection = document.getElementById('foto-section');
        
        const newProduk = document.createElement('div');
        newProduk.className = 'produk-item border border-gray-100 p-4 rounded-lg mb-4';
        newProduk.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-medium text-gray-800">Produk Baru #${produkCounter + 1}</h4>
                <button type="button" class="text-red-600 hover:text-red-800 remove-produk-btn">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Produk *</label>
                    <input type="text" name="produk_baru[${produkCounter}][deskripsi]" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Contoh: Laptop Asus ROG">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                    <input type="number" name="produk_baru[${produkCounter}][harga]" min="0" step="0.01" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga Jual (Rp) *</label>
                    <input type="number" name="produk_baru[${produkCounter}][hargaJual]" min="0" step="0.01" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Berat (kg) *</label>
                    <input type="number" name="produk_baru[${produkCounter}][berat]" min="0" step="0.01" required
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                    <select name="produk_baru[${produkCounter}][idKategori]" required
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="">Pilih Kategori</option>
                        @foreach($kategori as $k)
                            <option value="{{ $k->idKategori }}">{{ $k->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Garansi (Opsional)</label>
                    <input type="date" name="produk_baru[${produkCounter}][tanggalGaransi]"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
        `;
        
        container.appendChild(newProduk);
        produkCounter++;
        
        // Show foto section when adding products
        fotoSection.classList.remove('hidden');
    });

    // Remove product handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-produk-btn')) {
            e.target.closest('.produk-item').remove();
            
            // Hide foto section if no products
            const produkItems = document.querySelectorAll('.produk-item');
            const fotoSection = document.getElementById('foto-section');
            if (produkItems.length === 0) {
                fotoSection.classList.add('hidden');
            }
            
            renumberProducts();
        }
    });

    function renumberProducts() {
        const produkItems = document.querySelectorAll('.produk-item');
        produkItems.forEach((item, index) => {
            const title = item.querySelector('h4');
            title.textContent = `Produk Baru #${index + 1}`;
            
            // Update input names
            const inputs = item.querySelectorAll('input, select');
            inputs.forEach(input => {
                const name = input.getAttribute('name');
                if (name && name.includes('produk_baru[')) {
                    const newName = name.replace(/produk_baru\[\d+\]/, `produk_baru[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
    }

    // Photo preview functionality
    function previewPhotos(input) {
        const previewContainer = document.getElementById('photo-preview');
        previewContainer.innerHTML = '';
        
        if (input.files && input.files.length > 0) {
            if (input.files.length < 2) {
                alert('Minimal 2 foto harus diupload!');
                input.value = '';
                previewContainer.classList.add('hidden');
                return;
            }
            
            if (input.files.length > 3) {
                alert('Maksimal 3 foto yang dapat diupload!');
                input.value = '';
                previewContainer.classList.add('hidden');
                return;
            }
            
            previewContainer.classList.remove('hidden');
            
            Array.from(input.files).forEach((file, index) => {
                if (file.type.startsWith('image/')) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const photoDiv = document.createElement('div');
                        photoDiv.className = 'relative group';
                        photoDiv.innerHTML = `
                            <img src="${e.target.result}" alt="Preview ${index + 1}" 
                                 class="w-full h-32 object-cover rounded-lg border border-gray-200">
                            <div class="absolute inset-0 bg-black bg-opacity-50 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-200 flex items-center justify-center">
                                <span class="text-white text-sm">Foto ${index + 1}</span>
                            </div>
                            <button type="button" onclick="removePhoto(${index})" 
                                    class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs hover:bg-red-600">
                                <i class="fa-solid fa-times"></i>
                            </button>
                        `;
                        previewContainer.appendChild(photoDiv);
                    };
                    reader.readAsDataURL(file);
                }
            });
        } else {
            previewContainer.classList.add('hidden');
        }
    }

    function removePhoto(index) {
        const input = document.getElementById('foto-barang-baru');
        const dt = new DataTransfer();
        
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        previewPhotos(input);
    }

    // Format number input for pendapatan
    document.getElementById('pendapatan').addEventListener('input', function() {
        let value = this.value;
        // Remove non-numeric characters except decimal point
        value = value.replace(/[^0-9.]/g, '');
        this.value = value;
    });

    // Validate form before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        // Check if adding new products
        const produkItems = document.querySelectorAll('.produk-item');
        const photoInput = document.getElementById('foto-barang-baru');
        
        if (produkItems.length > 0) {
            // Validate new products
            let valid = true;
            produkItems.forEach((item, index) => {
                const deskripsi = item.querySelector('input[name*="[deskripsi]"]').value.trim();
                const harga = item.querySelector('input[name*="[harga]"]').value;
                const hargaJual = item.querySelector('input[name*="[hargaJual]"]').value;
                const berat = item.querySelector('input[name*="[berat]"]').value;
                const kategori = item.querySelector('select[name*="[idKategori]"]').value;
                
                if (!deskripsi || !harga || harga <= 0 || !hargaJual || hargaJual <= 0 || !berat || berat <= 0 || !kategori) {
                    valid = false;
                    alert(`Produk Baru #${index + 1}: Semua field wajib harus diisi dengan benar!`);
                    return;
                }
            });
            
            if (!valid) {
                e.preventDefault();
                return false;
            }
            
            // Check photos for new products
            if (!photoInput.files || photoInput.files.length < 2) {
                e.preventDefault();
                alert('Silakan upload minimal 2 foto untuk produk baru!');
                return false;
            }
        }

        // Validate dates
        const tanggalMasuk = new Date(document.getElementById('tanggalMasukPenitipan').value);
        const tanggalAkhir = new Date(document.getElementById('tanggalAkhirPenitipan').value);
        const batasAmbil = new Date(document.getElementById('batasAmbil').value);

        if (tanggalAkhir <= tanggalMasuk) {
            e.preventDefault();
            alert('Tanggal akhir harus setelah tanggal masuk!');
            return false;
        }

        if (batasAmbil <= tanggalAkhir) {
            e.preventDefault();
            alert('Batas ambil harus setelah tanggal akhir!');
            return false;
        }
    });
</script>
@endsection