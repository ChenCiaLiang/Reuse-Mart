@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 min-h-screen py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-green-500 via-blue-600 to-indigo-600 px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-plus-circle mr-3"></i>
                            Tambah Alamat Baru
                        </h1>
                        <p class="text-blue-100">Lengkapi informasi alamat untuk memudahkan pengiriman</p>
                    </div>
                    <div class="mt-4 md:mt-0">
                        <a href="{{ route('pembeli.alamat.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all backdrop-blur-sm border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Progress Indicator -->
            <div class="bg-gradient-to-r from-gray-50 to-blue-50 px-8 py-4 border-b border-gray-200">
                <div class="flex items-center justify-center">
                    <div class="flex items-center space-x-4">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white text-sm font-bold">
                            1
                        </div>
                        <span class="text-sm font-medium text-gray-700">Informasi Alamat</span>
                        <div class="w-16 h-1 bg-gray-300 rounded-full"></div>
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-gray-500 text-sm font-bold">
                            2
                        </div>
                        <span class="text-sm text-gray-500">Konfirmasi</span>
                    </div>
                </div>
            </div>

            <div class="p-8">
                <form action="{{ route('pembeli.alamat.store') }}" method="POST" id="alamatForm">
                    @csrf
                    
                    @if ($errors->any())
                    <div class="bg-gradient-to-r from-red-50 to-pink-50 border-l-4 border-red-400 p-6 mb-8 rounded-r-xl shadow-sm">
                        <div class="flex items-start">
                            <div class="w-6 h-6 bg-red-400 rounded-full flex items-center justify-center mr-3 flex-shrink-0">
                                <i class="fas fa-exclamation text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 class="text-red-800 font-semibold mb-2">Terdapat kesalahan pada form:</h3>
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li class="text-red-700">{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="grid lg:grid-cols-2 gap-8">
                        <!-- Left Column - Form Fields -->
                        <div class="space-y-6">
                            <!-- Jenis Alamat -->
                            <div class="form-group">
                                <label for="jenis" class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-tag text-blue-500 mr-2"></i>
                                    Jenis Alamat
                                </label>
                                <div class="relative">
                                    <input type="text" id="jenis" name="jenis" required
                                           value="{{ old('jenis') }}" 
                                           placeholder="Contoh: Rumah, Kantor, Apartemen"
                                           class="w-full px-4 py-4 pl-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-700 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-home text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Berikan nama yang mudah diingat untuk alamat ini</p>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div class="form-group">
                                <label for="alamatLengkap" class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-map-marker-alt text-blue-500 mr-2"></i>
                                    Alamat Lengkap
                                </label>
                                <div class="relative">
                                    <textarea id="alamatLengkap" name="alamatLengkap" required
                                              class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all text-gray-700 shadow-sm resize-none"
                                              rows="4" placeholder="Masukkan alamat lengkap termasuk:&#10;- Nama jalan dan nomor rumah&#10;- RT/RW, Kelurahan, Kecamatan&#10;- Kota/Kabupaten dan Kode Pos">{{ old('alamatLengkap') }}</textarea>
                                    <div class="absolute top-4 right-4">
                                        <i class="fas fa-edit text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Pastikan alamat lengkap dan akurat untuk pengiriman yang tepat</p>
                            </div>

                            <!-- Status Default -->
                            <div class="form-group">
                                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
                                    <label class="flex items-start cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" name="statusDefault" value="1" {{ old('statusDefault') ? 'checked' : '' }}
                                                   class="w-5 h-5 text-blue-600 border-2 border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-all">
                                            <div class="absolute inset-0 w-5 h-5 border-2 border-blue-500 rounded opacity-0 group-hover:opacity-20 transition-opacity"></div>
                                        </div>
                                        <div class="ml-4">
                                            <span class="text-sm font-semibold text-gray-800 flex items-center">
                                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                                Jadikan sebagai alamat utama
                                            </span>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Alamat utama akan dipilih secara otomatis saat checkout
                                            </p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Preview & Tips -->
                        <div class="space-y-6">
                            <!-- Live Preview -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-eye text-blue-500 mr-2"></i>
                                    Preview Alamat
                                </h3>
                                <div class="bg-white rounded-lg p-4 shadow-sm border border-blue-100">
                                    <div class="flex items-center mb-3">
                                        <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                            <i class="fas fa-home text-white text-sm"></i>
                                        </div>
                                        <div>
                                            <h4 class="font-semibold text-gray-800" id="previewJenis">
                                                Jenis Alamat
                                            </h4>
                                            <p class="text-xs text-gray-500">Alamat Pengiriman</p>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 rounded-lg p-3">
                                        <p class="text-sm text-gray-700" id="previewAlamat">
                                            Alamat lengkap akan muncul di sini...
                                        </p>
                                    </div>
                                    <div class="mt-3" id="previewDefault" style="display: none;">
                                        <span class="inline-flex items-center px-2 py-1 bg-yellow-100 text-yellow-800 text-xs rounded-full font-medium">
                                            <i class="fas fa-star mr-1"></i>
                                            Alamat Utama
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tips Card -->
                            <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-lightbulb text-green-500 mr-2"></i>
                                    Tips Alamat
                                </h3>
                                <ul class="space-y-3 text-sm text-gray-700">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                                        <span>Gunakan nama alamat yang jelas seperti "Rumah Utama" atau "Kantor"</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                                        <span>Sertakan nomor rumah, nama jalan, dan patokan yang mudah ditemukan</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                                        <span>Pastikan kode pos benar untuk mempercepat pengiriman</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-green-500 mr-3 mt-0.5"></i>
                                        <span>Tetapkan satu alamat sebagai default untuk kemudahan checkout</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Quick Templates -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-magic text-purple-500 mr-2"></i>
                                    Template Cepat
                                </h3>
                                <div class="space-y-2">
                                    <button type="button" onclick="fillTemplate('rumah')" 
                                            class="w-full text-left px-3 py-2 text-sm bg-white hover:bg-blue-50 border border-gray-200 rounded-lg transition-colors">
                                        <i class="fas fa-home text-blue-500 mr-2"></i>
                                        Rumah Tinggal
                                    </button>
                                    <button type="button" onclick="fillTemplate('kantor')" 
                                            class="w-full text-left px-3 py-2 text-sm bg-white hover:bg-blue-50 border border-gray-200 rounded-lg transition-colors">
                                        <i class="fas fa-building text-blue-500 mr-2"></i>
                                        Kantor / Tempat Kerja
                                    </button>
                                    <button type="button" onclick="fillTemplate('kos')" 
                                            class="w-full text-left px-3 py-2 text-sm bg-white hover:bg-blue-50 border border-gray-200 rounded-lg transition-colors">
                                        <i class="fas fa-bed text-blue-500 mr-2"></i>
                                        Kos / Boarding House
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-4">
                            <a href="{{ route('pembeli.alamat.index') }}" 
                               class="inline-flex items-center justify-center px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-all">
                                <i class="fas fa-times mr-2"></i>
                                Batal
                            </a>
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Alamat
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Live preview functionality
document.addEventListener('DOMContentLoaded', function() {
    const jenisInput = document.getElementById('jenis');
    const alamatInput = document.getElementById('alamatLengkap');
    const defaultInput = document.querySelector('input[name="statusDefault"]');
    
    const previewJenis = document.getElementById('previewJenis');
    const previewAlamat = document.getElementById('previewAlamat');
    const previewDefault = document.getElementById('previewDefault');

    function updatePreview() {
        previewJenis.textContent = jenisInput.value || 'Jenis Alamat';
        previewAlamat.textContent = alamatInput.value || 'Alamat lengkap akan muncul di sini...';
        previewDefault.style.display = defaultInput.checked ? 'block' : 'none';
    }

    jenisInput.addEventListener('input', updatePreview);
    alamatInput.addEventListener('input', updatePreview);
    defaultInput.addEventListener('change', updatePreview);
});

// Template functions
function fillTemplate(type) {
    const jenisInput = document.getElementById('jenis');
    
    switch(type) {
        case 'rumah':
            jenisInput.value = 'Rumah Utama';
            break;
        case 'kantor':
            jenisInput.value = 'Kantor';
            break;
        case 'kos':
            jenisInput.value = 'Kos/Boarding House';
            break;
    }
    
    // Trigger preview update
    jenisInput.dispatchEvent(new Event('input'));
    jenisInput.focus();
}

// Form validation enhancement
document.getElementById('alamatForm').addEventListener('submit', function(e) {
    const jenis = document.getElementById('jenis').value.trim();
    const alamat = document.getElementById('alamatLengkap').value.trim();
    
    if (jenis.length < 3) {
        e.preventDefault();
        alert('Jenis alamat minimal 3 karakter');
        return;
    }
    
    if (alamat.length < 10) {
        e.preventDefault();
        alert('Alamat lengkap minimal 10 karakter');
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    submitBtn.disabled = true;
});

// Character counter for textarea
document.getElementById('alamatLengkap').addEventListener('input', function() {
    const maxLength = 255;
    const currentLength = this.value.length;
    const remaining = maxLength - currentLength;
    
    // You can add a character counter here if needed
});
</script>

<style>
/* Enhanced form styling */
.form-group input:focus,
.form-group textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

/* Smooth animations */
.form-group {
    transition: all 0.3s ease;
}

.form-group:hover {
    transform: translateY(-1px);
}

/* Loading animation */
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

/* Custom checkbox styling */
input[type="checkbox"]:checked {
    background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='m13.854 3.646-1.708-1.708-5.293 5.293-2.646-2.647-1.708 1.708 4.354 4.354 7-7z'/%3e%3c/svg%3e");
}

/* Improved button hover effects */
button:hover, .btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

/* Template button effects */
.template-btn {
    transition: all 0.2s ease;
}

.template-btn:hover {
    transform: scale(1.02);
    border-color: #3B82F6;
    background-color: #EBF8FF;
}
</style>

@endsection