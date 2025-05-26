@extends('layouts.gudang')

@section('title', 'Tambah Transaksi Penitipan')

@section('breadcrumb')
<nav class="text-sm text-gray-500 mt-1">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('gudang.dashboard') }}" class="hover:text-green-600">Dashboard</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="flex items-center">
            <a href="{{ route('gudang.transaksi.index') }}" class="hover:text-green-600">Transaksi Penitipan</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="text-gray-600">Tambah Transaksi</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="max-w-6xl mx-auto">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-800">Tambah Transaksi Penitipan</h2>
                <p class="text-gray-600 mt-1">Buat transaksi penitipan barang baru</p>
            </div>
            <a href="{{ route('gudang.transaksi.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                <i class="fa-solid fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <form action="{{ route('gudang.transaksi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
            @csrf
            
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
                                <option value="{{ $p->idPenitip }}" {{ old('idPenitip') == $p->idPenitip ? 'selected' : '' }}
                                        data-email="{{ $p->email }}" data-alamat="{{ $p->alamat }}">
                                    {{ $p->nama }} - {{ $p->email }}
                                </option>
                            @endforeach
                        </select>
                        <!-- Info Penitip yang dipilih -->
                        <div id="penitip-info" class="mt-2 p-2 bg-gray-50 rounded text-sm text-gray-600 hidden">
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
                                <option value="{{ $pg->idPegawai }}" {{ old('idPegawai') == $pg->idPegawai ? 'selected' : '' }}>
                                    {{ $pg->nama }} ({{ $pg->jabatan }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Date Information with Auto Calculation -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-calendar mr-2 text-green-600"></i>
                    Informasi Tanggal (Otomatis: Masa Penitipan = Tanggal Masuk + 30 Hari)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Tanggal Masuk -->
                    <div>
                        <label for="tanggalMasukPenitipan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-plus mr-1"></i>
                            Tanggal Masuk Gudang *
                        </label>
                        <input type="datetime-local" id="tanggalMasukPenitipan" name="tanggalMasukPenitipan" 
                               value="{{ old('tanggalMasukPenitipan', now()->format('Y-m-d\TH:i')) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <p class="text-xs text-gray-500 mt-1">Tanggal titip = tanggal masuk gudang</p>
                    </div>

                    <!-- Tanggal Akhir (Auto calculated) -->
                    <div>
                        <label for="tanggalAkhirPenitipan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-check mr-1"></i>
                            Tanggal Akhir Penitipan *
                        </label>
                        <input type="datetime-local" id="tanggalAkhirPenitipan" name="tanggalAkhirPenitipan" 
                               value="{{ old('tanggalAkhirPenitipan') }}" required readonly
                               class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 cursor-not-allowed">
                        <p class="text-xs text-green-600 mt-1">Otomatis: +30 hari dari tanggal masuk</p>
                    </div>

                    <!-- Batas Ambil (Auto calculated) -->
                    <div>
                        <label for="batasAmbil" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fa-solid fa-calendar-times mr-1"></i>
                            Batas Ambil *
                        </label>
                        <input type="datetime-local" id="batasAmbil" name="batasAmbil" 
                               value="{{ old('batasAmbil') }}" required readonly
                               class="w-full border-gray-300 rounded-md shadow-sm bg-gray-50 cursor-not-allowed">
                        <p class="text-xs text-orange-600 mt-1">Otomatis: +7 hari dari tanggal akhir</p>
                    </div>
                </div>
                
                <!-- Duration Display -->
                <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                    <div class="flex items-center">
                        <i class="fa-solid fa-clock text-blue-600 mr-2"></i>
                        <span class="font-medium text-blue-800">Durasi Penitipan: </span>
                        <span id="durasi-display" class="text-blue-900 ml-1 font-semibold">30 hari</span>
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
                               value="{{ old('pendapatan') }}" required
                               class="w-full pl-12 border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                               placeholder="0">
                    </div>
                </div>
            </div>

            <!-- Product Input Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-box mr-2 text-green-600"></i>
                    Daftar Produk yang Akan Dititipkan
                </h3>
                <div class="border border-gray-200 rounded-lg p-4">
                    <div id="produk-container">
                        <!-- Produk pertama (default) -->
                        <div class="produk-item border border-gray-100 p-4 rounded-lg mb-4">
                            <div class="flex justify-between items-center mb-3">
                                <h4 class="text-md font-medium text-gray-800">Produk #1</h4>
                                <button type="button" class="text-red-600 hover:text-red-800 hidden remove-produk-btn">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi Produk *</label>
                                    <input type="text" name="produk_baru[0][deskripsi]" required
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="Contoh: Laptop Asus ROG">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp) *</label>
                                    <input type="number" name="produk_baru[0][harga]" min="0" step="0.01" required
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga Jual (Rp) *</label>
                                    <input type="number" name="produk_baru[0][hargaJual]" min="0" step="0.01" required
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Berat (kg) *</label>
                                    <input type="number" name="produk_baru[0][berat]" min="0" step="0.01" required
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                           placeholder="0">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Kategori *</label>
                                    <select name="produk_baru[0][idKategori]" required
                                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                        <option value="">Pilih Kategori</option>
                                        @foreach($kategori as $k)
                                            <option value="{{ $k->idKategori }}">{{ $k->nama }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Garansi (Opsional)</label>
                                    <input type="date" name="produk_baru[0][tanggalGaransi]"
                                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-between items-center">
                        <button type="button" id="tambah-produk-btn" 
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Tambah Produk</span>
                        </button>
                        <div class="text-sm text-gray-500">
                            <i class="fa-solid fa-info-circle mr-1"></i>
                            Minimal 1 produk harus diinput
                        </div>
                    </div>
                </div>
            </div>

            <!-- Photo Upload Section -->
            <div>
                <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fa-solid fa-camera mr-2 text-green-600"></i>
                    Foto Barang Titipan (Minimal 2 Foto) *
                </h3>
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6">
                    <div class="text-center">
                        <i class="fa-solid fa-cloud-upload-alt text-4xl text-gray-400 mb-4"></i>
                        <p class="text-sm text-gray-600 mb-4">Upload foto barang yang akan dititipkan (JPG, PNG, JPEG)</p>
                        
                        <!-- File Input -->
                        <input type="file" id="foto-barang" name="foto_barang[]" multiple accept="image/*" 
                               class="hidden" onchange="previewPhotos(this)">
                        <label for="foto-barang" 
                               class="cursor-pointer bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg inline-flex items-center space-x-2">
                            <i class="fa-solid fa-plus"></i>
                            <span>Pilih Foto</span>
                        </label>
                        
                        <p class="text-xs text-gray-500 mt-2">Minimal 2 foto, maksimal 3 foto per transaksi</p>
                    </div>
                    
                    <!-- Photo Preview -->
                    <div id="photo-preview" class="mt-6 grid grid-cols-2 md:grid-cols-3 gap-4 hidden">
                        <!-- Preview akan ditampilkan di sini via JavaScript -->
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <a href="{{ route('gudang.transaksi.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-times"></i>
                    <span>Batal</span>
                </a>
                <button type="submit" 
                        class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-save"></i>
                    <span>Simpan Transaksi</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let produkCounter = 1;

    // Auto calculate dates (30 days storage period)
    document.getElementById('tanggalMasukPenitipan').addEventListener('change', function() {
        const tanggalMasuk = new Date(this.value);
        
        if (!isNaN(tanggalMasuk.getTime())) {
            // Set tanggal akhir (default 30 hari dari tanggal masuk)
            const tanggalAkhir = new Date(tanggalMasuk);
            tanggalAkhir.setDate(tanggalAkhir.getDate() + 30);
            document.getElementById('tanggalAkhirPenitipan').value = tanggalAkhir.toISOString().slice(0, 16);
            
            // Set batas ambil (default 7 hari setelah tanggal akhir)
            const batasAmbil = new Date(tanggalAkhir);
            batasAmbil.setDate(batasAmbil.getDate() + 7);
            document.getElementById('batasAmbil').value = batasAmbil.toISOString().slice(0, 16);
            
            // Update duration display
            updateDurationDisplay(tanggalMasuk, tanggalAkhir);
        }
    });

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

    function updateDurationDisplay(startDate, endDate) {
        const diffTime = Math.abs(endDate - startDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        document.getElementById('durasi-display').textContent = diffDays + ' hari';
    }

    // Product management
    document.getElementById('tambah-produk-btn').addEventListener('click', function() {
        const container = document.getElementById('produk-container');
        const newProduk = document.createElement('div');
        newProduk.className = 'produk-item border border-gray-100 p-4 rounded-lg mb-4';
        newProduk.innerHTML = `
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-md font-medium text-gray-800">Produk #${produkCounter + 1}</h4>
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
        
        // Show remove buttons when more than 1 product
        updateRemoveButtons();
    });

    // Remove product handler
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-produk-btn')) {
            e.target.closest('.produk-item').remove();
            updateRemoveButtons();
            renumberProducts();
        }
    });

    function updateRemoveButtons() {
        const produkItems = document.querySelectorAll('.produk-item');
        const removeButtons = document.querySelectorAll('.remove-produk-btn');
        
        removeButtons.forEach(btn => {
            if (produkItems.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }

    function renumberProducts() {
        const produkItems = document.querySelectorAll('.produk-item');
        produkItems.forEach((item, index) => {
            const title = item.querySelector('h4');
            title.textContent = `Produk #${index + 1}`;
            
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
        const input = document.getElementById('foto-barang');
        const dt = new DataTransfer();
        
        Array.from(input.files).forEach((file, i) => {
            if (i !== index) {
                dt.items.add(file);
            }
        });
        
        input.files = dt.files;
        previewPhotos(input);
    }

    // Validate form before submit
    document.querySelector('form').addEventListener('submit', function(e) {
        const produkItems = document.querySelectorAll('.produk-item');
        const photoInput = document.getElementById('foto-barang');
        
        if (produkItems.length === 0) {
            e.preventDefault();
            alert('Silakan input minimal satu produk!');
            return false;
        }
        
        // Validate each product has required fields
        let valid = true;
        produkItems.forEach((item, index) => {
            const deskripsi = item.querySelector('input[name*="[deskripsi]"]').value.trim();
            const harga = item.querySelector('input[name*="[harga]"]').value;
            const hargaJual = item.querySelector('input[name*="[hargaJual]"]').value;
            const berat = item.querySelector('input[name*="[berat]"]').value;
            const kategori = item.querySelector('select[name*="[idKategori]"]').value;
            
            if (!deskripsi || !harga || harga <= 0 || !hargaJual || hargaJual <= 0 || !berat || berat <= 0 || !kategori) {
                valid = false;
                alert(`Produk #${index + 1}: Semua field wajib harus diisi dengan benar!`);
                return;
            }
        });
        
        if (!valid) {
            e.preventDefault();
            return false;
        }
        
        if (!photoInput.files || photoInput.files.length < 2) {
            e.preventDefault();
            alert('Silakan upload minimal 2 foto barang!');
            return false;
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

    // Initialize date if not set
    window.addEventListener('load', function() {
        const tanggalMasukInput = document.getElementById('tanggalMasukPenitipan');
        if (tanggalMasukInput.value) {
            tanggalMasukInput.dispatchEvent(new Event('change'));
        }
    });
</script>
@endsection