@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-purple-50 via-indigo-50 to-blue-50 min-h-screen py-6">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-500 via-indigo-600 to-blue-600 px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-3"></i>
                            Detail Alamat: {{ $alamat->jenis }}
                        </h1>
                        <p class="text-purple-100">Informasi lengkap alamat pengiriman Anda</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <a href="{{ route('pembeli.alamat.index') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all backdrop-blur-sm border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Daftar
                        </a>
                        <a href="{{ route('pembeli.alamat.edit', $alamat->idAlamat) }}" class="bg-white text-purple-600 hover:bg-purple-50 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-edit mr-2"></i> Edit Alamat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column - Address Details -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Main Address Card -->
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-100">
                    <!-- Address Header -->
                    <div class="bg-gradient-to-r from-gray-50 to-purple-50 px-8 py-6 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center">
                                <div class="w-16 h-16 bg-gradient-to-r {{ $alamat->statusDefault ? 'from-green-400 to-emerald-500' : 'from-purple-400 to-indigo-500' }} rounded-2xl flex items-center justify-center mr-4">
                                    <i class="fas {{ $alamat->jenis === 'Rumah' ? 'fa-home' : ($alamat->jenis === 'Kantor' ? 'fa-building' : 'fa-map-marker-alt') }} text-white text-2xl"></i>
                                </div>
                                <div>
                                    <h2 class="text-2xl font-bold text-gray-800">{{ $alamat->jenis }}</h2>
                                    <p class="text-sm text-gray-500">ID Alamat: #{{ $alamat->idAlamat }}</p>
                                </div>
                            </div>
                            
                            @if ($alamat->statusDefault)
                            <div class="text-right">
                                <div class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-400 to-emerald-500 text-white rounded-full font-semibold shadow-lg">
                                    <i class="fas fa-star mr-2"></i>
                                    Alamat Utama
                                </div>
                                <p class="text-xs text-gray-500 mt-1">Dipilih otomatis saat checkout</p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Address Content -->
                    <div class="p-8">
                        <div class="mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3 flex items-center">
                                <i class="fas fa-location-arrow text-purple-500 mr-2"></i>
                                Alamat Lengkap
                            </h3>
                            <div class="bg-gradient-to-r from-gray-50 to-purple-50 rounded-xl p-6 border border-purple-100">
                                <p class="text-gray-700 leading-relaxed text-lg">{{ $alamat->alamatLengkap }}</p>
                            </div>
                        </div>

                        <!-- Address Analytics -->
                        <div class="grid md:grid-cols-2 gap-6 mb-6">
                            <div class="bg-blue-50 rounded-xl p-4 border border-blue-200">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-blue-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-calendar text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Dibuat</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ $alamat->created_at ? $alamat->created_at->format('d M Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-purple-50 rounded-xl p-4 border border-purple-200">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-purple-500 rounded-lg flex items-center justify-center mr-3">
                                        <i class="fas fa-edit text-white"></i>
                                    </div>
                                    <div>
                                        <p class="text-sm text-gray-600">Terakhir Diubah</p>
                                        <p class="font-semibold text-gray-800">
                                            {{ $alamat->updated_at ? $alamat->updated_at->format('d M Y') : '-' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- QR Code for Address (Optional Feature) -->
                        <div class="bg-gradient-to-r from-indigo-50 to-blue-50 rounded-xl p-6 border border-indigo-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-800 mb-2 flex items-center">
                                        <i class="fas fa-qrcode text-indigo-500 mr-2"></i>
                                        Quick Share
                                    </h4>
                                    <p class="text-sm text-gray-600">Bagikan alamat ini dengan mudah</p>
                                </div>
                                <div class="flex space-x-3">
                                    <button onclick="copyAddress()" class="inline-flex items-center px-4 py-2 bg-indigo-500 hover:bg-indigo-600 text-white rounded-lg text-sm font-medium transition-all">
                                        <i class="fas fa-copy mr-2"></i>
                                        Salin
                                    </button>
                                    <button onclick="shareAddress()" class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition-all">
                                        <i class="fas fa-share mr-2"></i>
                                        Bagikan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Related Transactions (if any) -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-history text-green-500 mr-2"></i>
                        Riwayat Penggunaan Alamat
                    </h3>
                    
                    <!-- You can add transaction history here if you track which address was used for which order -->
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-shipping-fast text-gray-400 text-xl"></i>
                        </div>
                        <p class="text-gray-500 mb-4">Riwayat pengiriman ke alamat ini akan muncul di sini</p>
                        <a href="{{ route('pembeli.history') }}" class="inline-flex items-center text-purple-600 hover:text-purple-800 font-medium text-sm">
                            <i class="fas fa-external-link-alt mr-2"></i>
                            Lihat Semua Transaksi
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column - Actions & Info -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-tools text-purple-500 mr-2"></i>
                        Aksi Cepat
                    </h3>
                    
                    <div class="space-y-3">
                        <a href="{{ route('pembeli.alamat.edit', $alamat->idAlamat) }}" 
                           class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-medium rounded-lg transition-all transform hover:scale-105">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Alamat
                        </a>
                        
                        @if(!$alamat->statusDefault)
                        <form action="{{ route('pembeli.alamat.update', $alamat->idAlamat) }}" method="POST" class="w-full">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="jenis" value="{{ $alamat->jenis }}">
                            <input type="hidden" name="alamatLengkap" value="{{ $alamat->alamatLengkap }}">
                            <input type="hidden" name="statusDefault" value="1">
                            <button type="submit" 
                                    class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-400 to-emerald-500 hover:from-green-500 hover:to-emerald-600 text-white font-medium rounded-lg transition-all">
                                <i class="fas fa-star mr-2"></i>
                                Jadikan Utama
                            </button>
                        </form>
                        @endif
                        
                        <button onclick="confirmDelete()" 
                                class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white font-medium rounded-lg transition-all">
                            <i class="fas fa-trash mr-2"></i>
                            Hapus Alamat
                        </button>
                    </div>
                </div>

                <!-- Address Status Info -->
                <div class="bg-gradient-to-br {{ $alamat->statusDefault ? 'from-green-50 to-emerald-50 border-green-200' : 'from-blue-50 to-indigo-50 border-blue-200' }} rounded-2xl p-6 border">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-info-circle {{ $alamat->statusDefault ? 'text-green-500' : 'text-blue-500' }} mr-2"></i>
                        Status Alamat
                    </h3>
                    
                    <div class="space-y-3">
                        @if($alamat->statusDefault)
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-green-800">Alamat Utama</p>
                                <p class="text-sm text-green-600">Dipilih otomatis saat checkout</p>
                            </div>
                        </div>
                        @else
                        <div class="flex items-start">
                            <i class="fas fa-circle text-blue-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-blue-800">Alamat Sekunder</p>
                                <p class="text-sm text-blue-600">Dapat dipilih manual saat checkout</p>
                            </div>
                        </div>
                        @endif
                        
                        <div class="flex items-start">
                            <i class="fas fa-shield-alt text-gray-500 mr-3 mt-1"></i>
                            <div>
                                <p class="font-medium text-gray-700">Data Aman</p>
                                <p class="text-sm text-gray-600">Informasi tersimpan dengan enkripsi</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Usage Tips -->
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl p-6 border border-yellow-200">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                        Tips Penggunaan
                    </h3>
                    
                    <ul class="space-y-3 text-sm text-gray-700">
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Pastikan alamat selalu up-to-date untuk pengiriman tepat waktu</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Gunakan alamat utama untuk checkout yang lebih cepat</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Simpan multiple alamat untuk fleksibilitas pengiriman</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check text-yellow-500 mr-3 mt-0.5"></i>
                            <span>Edit alamat jika ada perubahan detail lokasi</span>
                        </li>
                    </ul>
                </div>

                <!-- Map Preview (Optional - requires Google Maps API) -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <h3 class="font-semibold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-map text-red-500 mr-2"></i>
                        Lokasi di Peta
                    </h3>
                    
                    <div class="bg-gray-100 rounded-lg h-48 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-500 text-sm">Preview peta akan muncul di sini</p>
                            <p class="text-xs text-gray-400 mt-1">Fitur akan segera hadir</p>
                        </div>
                    </div>
                    
                    <button class="w-full mt-4 inline-flex items-center justify-center px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 font-medium rounded-lg transition-all border border-red-200">
                        <i class="fas fa-external-link-alt mr-2"></i>
                        Buka di Google Maps
                    </button>
                </div>
            </div>
        </div>

        <!-- Hidden delete form -->
        <form id="deleteForm" action="{{ route('pembeli.alamat.destroy', $alamat->idAlamat) }}" method="POST" style="display: none;">
            @csrf
            @method('DELETE')
        </form>
    </div>
</div>

<script>
// Copy address function
function copyAddress() {
    const addressText = `${document.querySelector('h2').textContent}\n${document.querySelector('.text-lg').textContent}`;
    
    navigator.clipboard.writeText(addressText).then(function() {
        // Show success notification
        showNotification('Alamat berhasil disalin!', 'success');
    }).catch(function(err) {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = addressText;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
        showNotification('Alamat berhasil disalin!', 'success');
    });
}

// Share address function
function shareAddress() {
    const addressData = {
        title: 'Alamat: {{ $alamat->jenis }}',
        text: '{{ $alamat->alamatLengkap }}',
        url: window.location.href
    };
    
    if (navigator.share) {
        navigator.share(addressData).then(() => {
            showNotification('Berhasil dibagikan!', 'success');
        }).catch(err => {
            console.log('Error sharing:', err);
            copyAddress(); // Fallback to copy
        });
    } else {
        copyAddress(); // Fallback to copy
    }
}

// Confirm delete function
function confirmDelete() {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-2xl p-8 max-w-md w-full shadow-2xl transform transition-all">
            <div class="text-center">
                <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-exclamation-triangle text-red-500 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Konfirmasi Penghapusan</h3>
                <p class="text-gray-600 mb-6">
                    Apakah Anda yakin ingin menghapus alamat "<strong>{{ $alamat->jenis }}</strong>"?
                    <br><br>
                    Tindakan ini tidak dapat dibatalkan.
                </p>
                <div class="flex space-x-4">
                    <button onclick="closeModal()" 
                            class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 font-medium rounded-lg transition-all">
                        Batal
                    </button>
                    <button onclick="deleteAddress()" 
                            class="flex-1 px-4 py-3 bg-red-500 hover:bg-red-600 text-white font-medium rounded-lg transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close modal function
    window.closeModal = function() {
        document.body.removeChild(modal);
    };
    
    // Delete function
    window.deleteAddress = function() {
        document.getElementById('deleteForm').submit();
    };
    
    // Close on outside click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closeModal();
        }
    });
}

// Show notification function
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 z-50 transform transition-all duration-300 translate-x-full`;
    
    const bgColor = type === 'success' ? 'bg-green-500' : type === 'error' ? 'bg-red-500' : 'bg-blue-500';
    
    notification.innerHTML = `
        <div class="${bgColor} text-white px-6 py-4 rounded-lg shadow-lg flex items-center">
            <i class="fas fa-${type === 'success' ? 'check' : type === 'error' ? 'times' : 'info'} mr-3"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                document.body.removeChild(notification);
            }
        }, 300);
    }, 3000);
}

// Page load animations
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.bg-white, .bg-gradient-to-br');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.6s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<style>
/* Enhanced animations */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-slideInUp {
    animation: slideInUp 0.6s ease-out;
}

/* Hover effects */
.hover-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Button effects */
.btn-hover {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.btn-hover::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
    transition: left 0.5s;
}

.btn-hover:hover::before {
    left: 100%;
}

/* Copy feedback animation */
@keyframes copyFeedback {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.1); }
}

.copy-feedback {
    animation: copyFeedback 0.3s ease;
}

/* Modal animations */
.modal-enter {
    animation: modalEnter 0.3s ease-out;
}

@keyframes modalEnter {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

/* Smooth transitions for all interactive elements */
* {
    transition-property: transform, background-color, border-color, color, box-shadow;
    transition-duration: 200ms;
    transition-timing-function: ease-in-out;
}

/* Enhanced gradient effects */
.gradient-hover {
    background-size: 200% 200%;
    animation: gradientShift 3s ease infinite;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
</style>

@endsection