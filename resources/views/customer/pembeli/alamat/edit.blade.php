@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-yellow-50 via-orange-50 to-red-50 min-h-screen py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-yellow-500 via-orange-600 to-red-600 px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-edit mr-3"></i>
                            Edit Alamat: {{ $alamat->jenis }}
                        </h1>
                        <p class="text-orange-100">Perbarui informasi alamat untuk pengiriman yang lebih akurat</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <a href="{{ route('pembeli.alamat.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all backdrop-blur-sm border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                        </a>
                        <a href="{{ route('pembeli.alamat.show', $alamat->idAlamat) }}" class="bg-white text-orange-600 hover:bg-orange-50 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-eye mr-2"></i> Lihat Detail
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Status Indicator -->
            <div class="bg-gradient-to-r from-gray-50 to-orange-50 px-8 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-sm font-bold mr-3">
                            <i class="fas fa-edit"></i>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-700">Mode Edit Alamat</span>
                            <p class="text-xs text-gray-500">ID: {{ $alamat->idAlamat }}</p>
                        </div>
                    </div>
                    
                    @if($alamat->statusDefault)
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs rounded-full font-medium">
                            <i class="fas fa-star mr-1"></i>
                            Alamat Utama
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="p-8">
                <form action="{{ route('pembeli.alamat.update', $alamat->idAlamat) }}" method="POST" id="alamatEditForm">
                    @csrf
                    @method('PUT')
                    
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
                                    <i class="fas fa-tag text-orange-500 mr-2"></i>
                                    Jenis Alamat
                                </label>
                                <div class="relative">
                                    <input type="text" id="jenis" name="jenis" required
                                           value="{{ old('jenis', $alamat->jenis) }}" 
                                           placeholder="Contoh: Rumah, Kantor, Apartemen"
                                           class="w-full px-4 py-4 pl-12 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all text-gray-700 shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                        <i class="fas fa-home text-gray-400"></i>
                                    </div>
                                </div>
                                <p class="mt-2 text-sm text-gray-500">Berikan nama yang mudah diingat untuk alamat ini</p>
                            </div>

                            <!-- Alamat Lengkap -->
                            <div class="form-group">
                                <label for="alamatLengkap" class="block text-sm font-semibold text-gray-700 mb-3">
                                    <i class="fas fa-map-marker-alt text-orange-500 mr-2"></i>
                                    Alamat Lengkap
                                </label>
                                <div class="relative">
                                    <textarea id="alamatLengkap" name="alamatLengkap" required
                                              class="w-full px-4 py-4 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all text-gray-700 shadow-sm resize-none"
                                              rows="5" placeholder="Masukkan alamat lengkap termasuk:&#10;- Nama jalan dan nomor rumah&#10;- RT/RW, Kelurahan, Kecamatan&#10;- Kota/Kabupaten dan Kode Pos">{{ old('alamatLengkap', $alamat->alamatLengkap) }}</textarea>
                                    <div class="absolute top-4 right-4">
                                        <i class="fas fa-edit text-gray-400"></i>
                                    </div>
                                </div>
                                <div class="mt-2 flex justify-between items-center">
                                    <p class="text-sm text-gray-500">Pastikan alamat lengkap dan akurat untuk pengiriman yang tepat</p>
                                    <span class="text-xs text-gray-400" id="charCount">0/255</span>
                                </div>
                            </div>

                            <!-- Status Default -->
                            <div class="form-group">
                                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 border border-yellow-200">
                                    <label class="flex items-start cursor-pointer group">
                                        <div class="relative flex items-center">
                                            <input type="checkbox" name="statusDefault" value="1" {{ old('statusDefault', $alamat->statusDefault) ? 'checked' : '' }}
                                                   class="w-5 h-5 text-orange-600 border-2 border-gray-300 rounded focus:ring-orange-500 focus:ring-2 transition-all">
                                            <div class="absolute inset-0 w-5 h-5 border-2 border-orange-500 rounded opacity-0 group-hover:opacity-20 transition-opacity"></div>
                                        </div>
                                        <div class="ml-4">
                                            <span class="text-sm font-semibold text-gray-800 flex items-center">
                                                <i class="fas fa-star text-yellow-500 mr-2"></i>
                                                Jadikan sebagai alamat utama
                                            </span>
                                            <p class="text-sm text-gray-600 mt-1">
                                                Alamat utama akan dipilih secara otomatis saat checkout
                                            </p>
                                            @if($alamat->statusDefault)
                                            <p class="text-xs text-green-600 mt-1 font-medium">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Saat ini sudah menjadi alamat utama
                                            </p>
                                            @endif
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <!-- Change History (if you track changes) -->
                            <div class="bg-gray-50 rounded-xl p-4">
                                <h4 class="text-sm font-semibold text-gray-700 mb-2 flex items-center">
                                    <i class="fas fa-history text-gray-500 mr-2"></i>
                                    Informasi Alamat
                                </h4>
                                <div class="space-y-2 text-sm text-gray-600">
                                    <div class="flex justify-between">
                                        <span>Dibuat:</span>
                                        <span>{{ $alamat->created_at ? $alamat->created_at->format('d M Y H:i') : '-' }}</span>
                                    </div>
                                    @if($alamat->updated_at && $alamat->updated_at != $alamat->created_at)
                                    <div class="flex justify-between">
                                        <span>Terakhir diubah:</span>
                                        <span>{{ $alamat->updated_at->format('d M Y H:i') }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Right Column - Preview & Comparison -->
                        <div class="space-y-6">
                            <!-- Before & After Comparison -->
                            <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-exchange-alt text-blue-500 mr-2"></i>
                                    Perbandingan Perubahan
                                </h3>
                                
                                <!-- Current/Before -->
                                <div class="mb-4">
                                    <h4 class="text-sm font-medium text-gray-600 mb-2">Alamat Saat Ini:</h4>
                                    <div class="bg-white rounded-lg p-3 shadow-sm border border-gray-200">
                                        <div class="flex items-center mb-2">
                                            <div class="w-6 h-6 bg-gray-400 rounded-lg flex items-center justify-center mr-2">
                                                <i class="fas fa-home text-white text-xs"></i>
                                            </div>
                                            <span class="font-medium text-gray-800">{{ $alamat->jenis }}</span>
                                        </div>
                                        <p class="text-sm text-gray-600">{{ $alamat->alamatLengkap }}</p>
                                    </div>
                                </div>

                                <!-- Live Preview/After -->
                                <div>
                                    <h4 class="text-sm font-medium text-gray-600 mb-2">Preview Perubahan:</h4>
                                    <div class="bg-white rounded-lg p-3 shadow-sm border border-blue-200">
                                        <div class="flex items-center mb-2">
                                            <div class="w-6 h-6 bg-orange-500 rounded-lg flex items-center justify-center mr-2">
                                                <i class="fas fa-home text-white text-xs"></i>
                                            </div>
                                            <span class="font-medium text-gray-800" id="previewJenis">{{ $alamat->jenis }}</span>
                                            <span class="ml-2" id="previewDefault" style="display: {{ $alamat->statusDefault ? 'inline' : 'none' }};">
                                                <i class="fas fa-star text-yellow-500 text-xs"></i>
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600" id="previewAlamat">{{ $alamat->alamatLengkap }}</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Tips -->
                            <div class="bg-gradient-to-br from-orange-50 to-red-50 rounded-xl p-6 border border-orange-200">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-lightbulb text-orange-500 mr-2"></i>
                                    Tips Edit Alamat
                                </h3>
                                <ul class="space-y-3 text-sm text-gray-700">
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-orange-500 mr-3 mt-0.5"></i>
                                        <span>Periksa kembali perubahan sebelum menyimpan</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-orange-500 mr-3 mt-0.5"></i>
                                        <span>Pastikan nomor rumah dan kode pos sudah benar</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-orange-500 mr-3 mt-0.5"></i>
                                        <span>Jika mengubah alamat utama, alamat lain otomatis menjadi tidak utama</span>
                                    </li>
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle text-orange-500 mr-3 mt-0.5"></i>
                                        <span>Perubahan akan berpengaruh pada pesanan mendatang</span>
                                    </li>
                                </ul>
                            </div>

                            <!-- Quick Actions -->
                            <div class="bg-gray-50 rounded-xl p-6">
                                <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                                    <i class="fas fa-tools text-purple-500 mr-2"></i>
                                    Aksi Cepat
                                </h3>
                                <div class="space-y-3">
                                    <button type="button" onclick="resetForm()" 
                                            class="w-full text-left px-4 py-3 text-sm bg-white hover:bg-yellow-50 border border-gray-200 rounded-lg transition-colors flex items-center">
                                        <i class="fas fa-undo text-yellow-500 mr-3"></i>
                                        Reset ke Data Asli
                                    </button>
                                    <button type="button" onclick="validateForm()" 
                                            class="w-full text-left px-4 py-3 text-sm bg-white hover:bg-blue-50 border border-gray-200 rounded-lg transition-colors flex items-center">
                                        <i class="fas fa-check-double text-blue-500 mr-3"></i>
                                        Validasi Form
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row justify-between">
                            <!-- Danger Zone -->
                            <div class="mb-4 sm:mb-0">
                                <button type="button" onclick="confirmDelete()" 
                                        class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white font-medium rounded-xl transition-all">
                                    <i class="fas fa-trash mr-2"></i>
                                    Hapus Alamat
                                </button>
                            </div>

                            <!-- Save Actions -->
                            <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                                <a href="{{ route('pembeli.alamat.index') }}" 
                                   class="inline-flex items-center justify-center px-8 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-xl transition-all">
                                    <i class="fas fa-times mr-2"></i>
                                    Batal
                                </a>
                                <button type="submit" 
                                        class="inline-flex items-center justify-center px-8 py-3 bg-gradient-to-r from-orange-500 to-red-600 hover:from-orange-600 hover:to-red-700 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan Perubahan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- Hidden delete form -->
                <form id="deleteForm" action="{{ route('pembeli.alamat.destroy', $alamat->idAlamat) }}" method="POST" style="display: none;">
                    @csrf
                    @method('DELETE')
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
    const charCount = document.getElementById('charCount');

    function updatePreview() {
        previewJenis.textContent = jenisInput.value || '{{ $alamat->jenis }}';
        previewAlamat.textContent = alamatInput.value || '{{ $alamat->alamatLengkap }}';
        previewDefault.style.display = defaultInput.checked ? 'inline' : 'none';
        
        // Update character count
        const currentLength = alamatInput.value.length;
        charCount.textContent = `${currentLength}/255`;
        
        if (currentLength > 200) {
            charCount.classList.add('text-red-500');
        } else {
            charCount.classList.remove('text-red-500');
        }
    }

    jenisInput.addEventListener('input', updatePreview);
    alamatInput.addEventListener('input', updatePreview);
    defaultInput.addEventListener('change', updatePreview);
    
    // Initial update
    updatePreview();
});

// Reset form to original values
function resetForm() {
    if (confirm('Apakah Anda yakin ingin mereset form ke data asli?')) {
        document.getElementById('jenis').value = '{{ $alamat->jenis }}';
        document.getElementById('alamatLengkap').value = '{{ $alamat->alamatLengkap }}';
        document.querySelector('input[name="statusDefault"]').checked = {{ $alamat->statusDefault ? 'true' : 'false' }};
        
        // Trigger preview update
        document.getElementById('jenis').dispatchEvent(new Event('input'));
    }
}

// Validate form
function validateForm() {
    const jenis = document.getElementById('jenis').value.trim();
    const alamat = document.getElementById('alamatLengkap').value.trim();
    
    let errors = [];
    
    if (jenis.length < 3) {
        errors.push('Jenis alamat minimal 3 karakter');
    }
    
    if (alamat.length < 10) {
        errors.push('Alamat lengkap minimal 10 karakter');
    }
    
    if (alamat.length > 255) {
        errors.push('Alamat lengkap maksimal 255 karakter');
    }
    
    if (errors.length > 0) {
        alert('Validasi Form:\n\n' + errors.join('\n'));
    } else {
        alert('✅ Form valid! Siap untuk disimpan.');
    }
}

// Confirm delete
function confirmDelete() {
    if (confirm('⚠️ PERINGATAN!\n\nApakah Anda yakin ingin menghapus alamat "{{ $alamat->jenis }}"?\n\nTindakan ini tidak dapat dibatalkan dan akan mempengaruhi pesanan yang menggunakan alamat ini.')) {
        document.getElementById('deleteForm').submit();
    }
}

// Form submission enhancement
document.getElementById('alamatEditForm').addEventListener('submit', function(e) {
    const jenis = document.getElementById('jenis').value.trim();
    const alamat = document.getElementById('alamatLengkap').value.trim();
    
    if (jenis.length < 3 || alamat.length < 10) {
        e.preventDefault();
        validateForm();
        return;
    }
    
    // Show loading state
    const submitBtn = e.target.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan Perubahan...';
    submitBtn.disabled = true;
});

// Highlight changes
function highlightChanges() {
    const originalJenis = '{{ $alamat->jenis }}';
    const originalAlamat = '{{ $alamat->alamatLengkap }}';
    const originalDefault = {{ $alamat->statusDefault ? 'true' : 'false' }};
    
    const currentJenis = document.getElementById('jenis').value;
    const currentAlamat = document.getElementById('alamatLengkap').value;
    const currentDefault = document.querySelector('input[name="statusDefault"]').checked;
    
    // Add visual indicators for changes
    const jenisInput = document.getElementById('jenis');
    const alamatInput = document.getElementById('alamatLengkap');
    
    if (currentJenis !== originalJenis) {
        jenisInput.classList.add('border-orange-400', 'bg-orange-50');
    } else {
        jenisInput.classList.remove('border-orange-400', 'bg-orange-50');
    }
    
    if (currentAlamat !== originalAlamat) {
        alamatInput.classList.add('border-orange-400', 'bg-orange-50');
    } else {
        alamatInput.classList.remove('border-orange-400', 'bg-orange-50');
    }
}

// Monitor changes
document.getElementById('jenis').addEventListener('input', highlightChanges);
document.getElementById('alamatLengkap').addEventListener('input', highlightChanges);
document.querySelector('input[name="statusDefault"]').addEventListener('change', highlightChanges);
</script>

<style>
/* Enhanced form styling for edit mode */
.form-group input:focus,
.form-group textarea:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(251, 146, 60, 0.15);
}

/* Change highlighting */
.border-orange-400 {
    border-color: #fb923c !important;
}

.bg-orange-50 {
    background-color: #fff7ed !important;
}

/* Smooth animations */
.form-group {
    transition: all 0.3s ease;
}

.form-group:hover {
    transform: translateY(-1px);
}

/* Enhanced comparison cards */
.comparison-card {
    transition: all 0.3s ease;
    position: relative;
}

.comparison-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(251, 146, 60, 0.1), rgba(239, 68, 68, 0.1));
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: 0.5rem;
}

.comparison-card:hover::before {
    opacity: 1;
}

/* Loading states */
.loading {
    pointer-events: none;
    opacity: 0.6;
}

/* Character counter styling */
#charCount {
    transition: color 0.3s ease;
}

/* Improved button effects */
button:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.btn-danger:hover {
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}
</style>

@endsection