{{-- Solusi 1: Tambahkan Helper Function di bagian atas blade --}}
@php
function getImageUrl($imagePath, $defaultImage = 'default.jpg') {
    if (empty($imagePath)) {
        return asset('images/' . $defaultImage);
    }
    
    $gambarArray = explode(',', $imagePath);
    $thumbnail = trim($gambarArray[0]);
    
    // Check if file exists
    $fullPath = public_path('images/produk/' . $thumbnail);
    if (file_exists($fullPath)) {
        return asset('images/produk/' . $thumbnail);
    }
    
    return asset('images/' . $defaultImage);
}
@endphp

@extends('layouts.customer')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-500 px-6 py-4">
                <h1 class="text-xl font-bold text-white">Pembayaran Transaksi</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Nomor Transaksi: {{ $checkoutData['nomorTransaksi'] ?? $transaksi->idTransaksiPenjualan }}
                </p>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
                @endif
                
                <!-- Status Progress -->
                <div class="mb-8">
                    <div class="flex items-center">
                        <div class="flex items-center text-blue-600">
                            <div class="flex items-center justify-center w-8 h-8 border-2 border-blue-600 rounded-full bg-blue-600 text-white">
                                <i class="fas fa-shopping-cart text-sm"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium">Pesanan Dibuat</span>
                        </div>
                        
                        <div class="flex-1 h-1 mx-4 {{ in_array($transaksi->status, ['menunggu_pembayaran', 'menunggu_verifikasi', 'disiapkan']) ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
                        
                        <div class="flex items-center {{ in_array($transaksi->status, ['menunggu_verifikasi', 'disiapkan']) ? 'text-blue-600' : 'text-gray-400' }}">
                            <div class="flex items-center justify-center w-8 h-8 border-2 rounded-full {{ in_array($transaksi->status, ['menunggu_verifikasi', 'disiapkan']) ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300' }}">
                                <i class="fas fa-credit-card text-sm"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium">Pembayaran</span>
                        </div>
                        
                        <div class="flex-1 h-1 mx-4 {{ $transaksi->status === 'disiapkan' ? 'bg-blue-600' : 'bg-gray-300' }}"></div>
                        
                        <div class="flex items-center {{ $transaksi->status === 'disiapkan' ? 'text-blue-600' : 'text-gray-400' }}">
                            <div class="flex items-center justify-center w-8 h-8 border-2 rounded-full {{ $transaksi->status === 'disiapkan' ? 'border-blue-600 bg-blue-600 text-white' : 'border-gray-300' }}">
                                <i class="fas fa-box text-sm"></i>
                            </div>
                            <span class="ml-2 text-sm font-medium">Disiapkan</span>
                        </div>
                    </div>
                </div>

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Left Column - Payment Info -->
                    <div>
                        <!-- Timer Section (jika masih menunggu pembayaran) -->
                        @if($transaksi->status === 'menunggu_pembayaran')
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <i class="fas fa-clock text-red-600 text-2xl"></i>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-lg font-medium text-red-800">Batas Waktu Pembayaran</h3>
                                    <p class="text-red-600 text-sm">Selesaikan pembayaran sebelum:</p>
                                    <p class="text-red-800 font-semibold">{{ \Carbon\Carbon::parse($transaksi->tanggalBatasLunas)->format('d M Y H:i') }} WIB</p>
                                    <div class="mt-3">
                                        <div class="text-xl font-bold text-red-600" id="countdown">
                                            <i class="fas fa-spinner fa-spin"></i> Menghitung...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <!-- Payment Instructions -->
                        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-4">Instruksi Pembayaran</h3>
                            <div class="space-y-3 text-yellow-700">
                                <div class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-semibold mr-3">1</span>
                                    <p>Transfer ke rekening berikut:</p>
                                </div>
                                <div class="ml-9 bg-white p-4 rounded border">
                                    <p class="font-semibold">Bank BCA</p>
                                    <p class="text-lg font-bold">1234567890</p>
                                    <p>a.n. ReUseMart Indonesia</p>
                                </div>
                                
                                <div class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-semibold mr-3">2</span>
                                    <p>Transfer tepat sebesar <strong>Rp {{ number_format($checkoutData['total_akhir'] ?? 0, 0, ',', '.') }}</strong></p>
                                </div>
                                
                                <div class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-semibold mr-3">3</span>
                                    <p>Upload bukti transfer melalui form di samping</p>
                                </div>
                                
                                <div class="flex items-start">
                                    <span class="flex-shrink-0 w-6 h-6 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-semibold mr-3">4</span>
                                    <p>Tunggu konfirmasi dari tim kami (maksimal 2x24 jam)</p>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Status -->
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-2">Status Transaksi</h4>
                            <div class="flex items-center">
                                @if($transaksi->status === 'menunggu_pembayaran')
                                    <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                                    <span class="text-yellow-600 font-medium">Menunggu Pembayaran</span>
                                @elseif($transaksi->status === 'menunggu_verifikasi')
                                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                                    <span class="text-blue-600 font-medium">Sedang Diverifikasi</span>
                                @elseif($transaksi->status === 'disiapkan')
                                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                                    <span class="text-green-600 font-medium">Sedang Disiapkan</span>
                                @elseif($transaksi->status === 'batal')
                                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                                    <span class="text-red-600 font-medium">Dibatalkan</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Upload Form atau Status -->
                    <div>
                        @if($transaksi->status === 'menunggu_pembayaran')
                        <!-- Upload Form -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload Bukti Pembayaran</h3>
                            
                            <form id="paymentForm" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">
                                        Bukti Transfer <span class="text-red-500">*</span>
                                    </label>
                                    <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                                        <input type="file" name="bukti_pembayaran" id="buktiPembayaran" 
                                               accept="image/*" class="hidden" onchange="previewImage(this)">
                                        <div id="uploadArea" onclick="document.getElementById('buktiPembayaran').click()" class="cursor-pointer">
                                            <i class="fas fa-upload text-3xl text-gray-400 mb-2"></i>
                                            <p class="text-gray-600">Klik untuk memilih file gambar</p>
                                            <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG (Maksimal 2MB)</p>
                                        </div>
                                        <div id="imagePreview" class="hidden mt-4">
                                            <img id="previewImg" src="" alt="Preview" class="max-w-full h-48 object-contain mx-auto rounded">
                                            <button type="button" onclick="removeImage()" class="mt-2 text-red-600 hover:text-red-800 text-sm">
                                                <i class="fas fa-trash mr-1"></i> Hapus
                                            </button>
                                        </div>
                                    </div>
                                    <div id="fileError" class="text-red-500 text-sm mt-1 hidden"></div>
                                </div>
                                
                                <button type="submit" id="uploadBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-upload mr-2"></i>
                                    Upload Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                        @elseif($transaksi->status === 'menunggu_verifikasi')
                        <!-- Verifikasi Status -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                            <i class="fas fa-clock text-4xl text-blue-600 mb-4"></i>
                            <h3 class="text-lg font-semibold text-blue-800 mb-2">Pembayaran Sedang Diverifikasi</h3>
                            <p class="text-blue-600 mb-4">Bukti pembayaran Anda sudah diterima dan sedang diverifikasi oleh tim kami.</p>
                            <p class="text-sm text-blue-500">Proses verifikasi biasanya membutuhkan waktu 2x24 jam.</p>
                            
                            <!-- Show uploaded proof -->
                            @if(session('bukti_pembayaran_' . $transaksi->idTransaksiPenjualan))
                            <div class="mt-4">
                                <p class="text-sm text-blue-600 mb-2">Bukti pembayaran yang diupload:</p>
                                <img src="{{ asset(session('bukti_pembayaran_' . $transaksi->idTransaksiPenjualan)) }}" 
                                     alt="Bukti Pembayaran" 
                                     class="max-w-xs h-auto rounded border mx-auto cursor-pointer"
                                     onload="handleImageLoad(this)"
                                     onerror="handleImageError(this)"
                                     onclick="openImageModal(this.src)">
                            </div>
                            @endif
                        </div>
                        @elseif($transaksi->status === 'disiapkan')
                        <!-- Status Disiapkan -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-6 text-center">
                            <i class="fas fa-check-circle text-4xl text-green-600 mb-4"></i>
                            <h3 class="text-lg font-semibold text-green-800 mb-2">Pembayaran Berhasil!</h3>
                            <p class="text-green-600 mb-4">Pesanan Anda sedang disiapkan untuk pengiriman.</p>
                            <a href="{{ route('pembeli.profile') }}" class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                                Lihat Status Pesanan
                            </a>
                        </div>
                        @endif

                        <!-- Order Summary -->
                        <div class="bg-gray-50 rounded-lg p-6 mt-6">
                            <h4 class="font-semibold text-gray-800 mb-4">Ringkasan Pesanan</h4>
                            
                            @if(isset($checkoutData) && !empty($checkoutData))
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($checkoutData['subtotal'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Ongkos Kirim</span>
                                    <span>{{ ($checkoutData['ongkir'] ?? 0) == 0 ? 'GRATIS' : 'Rp ' . number_format($checkoutData['ongkir'], 0, ',', '.') }}</span>
                                </div>
                                @if(($checkoutData['diskon_poin'] ?? 0) > 0)
                                <div class="flex justify-between text-yellow-600">
                                    <span>Diskon Poin</span>
                                    <span>- Rp {{ number_format($checkoutData['diskon_poin'], 0, ',', '.') }}</span>
                                </div>
                                @endif
                                <hr class="my-2">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span class="text-blue-600">Rp {{ number_format($checkoutData['total_akhir'] ?? 0, 0, ',', '.') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Order Items dengan Solusi Gambar yang Robust -->
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Item Pesanan</h3>
                    <div class="space-y-3">
                        @foreach($transaksi->detailTransaksiPenjualan as $detail)
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
                            {{-- Solusi Gambar yang Robust --}}
                            <div class="h-12 w-12 rounded overflow-hidden bg-gray-200 flex items-center justify-center">
                                <img class="h-full w-full object-cover"
                                    src="{{ getImageUrl($detail->produk->gambar ?? '') }}"
                                    alt="{{ $detail->produk->deskripsi }}"
                                    onload="handleImageLoad(this)"
                                    onerror="handleImageError(this)"
                                    loading="lazy">
                                <div class="hidden text-gray-400 text-xs">
                                    <i class="fas fa-image"></i>
                                </div>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-medium text-gray-900">{{ $detail->produk->deskripsi }}</h4>
                                <p class="text-sm text-gray-500">{{ $detail->produk->kategori->nama ?? 'Kategori' }}</p>
                            </div>
                            <span class="font-semibold">Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="max-w-4xl max-h-full p-4">
        <div class="relative">
            <button onclick="closeImageModal()" 
                    class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
                <i class="fas fa-times text-xl"></i>
            </button>
            <img id="modalImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-full object-contain">
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        <span class="text-gray-700">Mengupload...</span>
    </div>
</div>

<script>
// Fungsi untuk handle loading gambar yang berhasil
function handleImageLoad(img) {
    img.style.display = 'block';
    // Hide placeholder icon if exists
    const placeholder = img.nextElementSibling;
    if (placeholder && placeholder.classList.contains('hidden')) {
        placeholder.style.display = 'none';
    }
}

// Fungsi untuk handle error loading gambar
function handleImageError(img) {
    console.log('Image failed to load:', img.src);
    
    // Jangan coba load default.jpg jika sudah error dengan default.jpg
    if (img.src.includes('default.jpg') || img.src.includes('placeholder')) {
        // Show placeholder icon instead
        img.style.display = 'none';
        const placeholder = img.nextElementSibling;
        if (placeholder) {
            placeholder.classList.remove('hidden');
            placeholder.style.display = 'flex';
        } else {
            // Create placeholder if doesn't exist
            const placeholderDiv = document.createElement('div');
            placeholderDiv.innerHTML = '<i class="fas fa-image text-gray-400"></i>';
            placeholderDiv.className = 'text-gray-400 text-xl flex items-center justify-center';
            img.parentNode.appendChild(placeholderDiv);
        }
        return;
    }
    
    // Try loading default image
    const defaultImageUrl = '{{ asset("images/default.jpg") }}';
    img.src = defaultImageUrl;
}

// Fungsi untuk membuat placeholder gambar
function createImagePlaceholder() {
    return `
        <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center">
            <i class="fas fa-image text-gray-400"></i>
        </div>
    `;
}

// Countdown timer untuk batas pembayaran
@if($transaksi->status === 'menunggu_pembayaran')
function updateCountdown() {
    const targetTime = new Date('{{ $transaksi->tanggalBatasLunas }}').getTime();
    const now = new Date().getTime();
    const distance = targetTime - now;
    
    if (distance < 0) {
        document.getElementById('countdown').innerHTML = '<span class="text-red-600">WAKTU HABIS</span>';
        // Refresh page to show expired status
        setTimeout(() => location.reload(), 3000);
        return;
    }
    
    const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
    const seconds = Math.floor((distance % (1000 * 60)) / 1000);
    
    document.getElementById('countdown').innerHTML = 
        `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
}

// Update countdown setiap detik
setInterval(updateCountdown, 1000);
updateCountdown();
@endif

// Image preview functionality
function previewImage(input) {
    const file = input.files[0];
    const fileError = document.getElementById('fileError');
    const uploadArea = document.getElementById('uploadArea');
    const imagePreview = document.getElementById('imagePreview');
    
    // Reset error
    fileError.classList.add('hidden');
    
    if (file) {
        // Validate file type
        if (!file.type.startsWith('image/')) {
            fileError.textContent = 'File harus berupa gambar';
            fileError.classList.remove('hidden');
            input.value = '';
            return;
        }
        
        // Validate file size (2MB = 2 * 1024 * 1024 bytes)
        if (file.size > 2 * 1024 * 1024) {
            fileError.textContent = 'Ukuran file maksimal 2MB';
            fileError.classList.remove('hidden');
            input.value = '';
            return;
        }
        
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('previewImg').src = e.target.result;
            uploadArea.classList.add('hidden');
            imagePreview.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }
}

function removeImage() {
    document.getElementById('buktiPembayaran').value = '';
    document.getElementById('uploadArea').classList.remove('hidden');
    document.getElementById('imagePreview').classList.add('hidden');
    document.getElementById('fileError').classList.add('hidden');
}

// Form submission
document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    
    const buktiInput = document.getElementById('buktiPembayaran');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (!buktiInput.files[0]) {
        showNotification('Silakan pilih file bukti pembayaran', 'error');
        return;
    }
    
    // Disable button and show loading
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupload...';
    
    document.getElementById('loadingOverlay').classList.remove('hidden');
    
    const formData = new FormData();
    formData.append('bukti_pembayaran', buktiInput.files[0]);
    formData.append('_token', '{{ csrf_token() }}');
    
    fetch('{{ route("pembeli.payment.upload", $transaksi->idTransaksiPenjualan) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        
        if (data.success) {
            showNotification('Bukti pembayaran berhasil diupload!', 'success');
            
            // Reload page after short delay
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            // Reset button
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran';
            
            showNotification(data.error || 'Terjadi kesalahan saat mengupload bukti pembayaran', 'error');
        }
    })
    .catch(error => {
        document.getElementById('loadingOverlay').classList.add('hidden');
        console.error('Error:', error);
        
        // Reset button
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran';
        
        showNotification('Terjadi kesalahan saat mengupload bukti pembayaran', 'error');
    });
});

// Notification function
function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-times-circle' : 
                'fa-info-circle'
            } mr-2"></i>
            <span>${message}</span>
            <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

// Image modal functions
function openImageModal(src) {
    // Don't open modal for placeholder images
    if (src.includes('default.jpg') || src.includes('placeholder')) {
        return;
    }
    
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside image
document.getElementById('imageModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});

// Ensure all images have proper error handling when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (!img.onload) {
            img.onload = function() { handleImageLoad(this); };
        }
        if (!img.onerror) {
            img.onerror = function() { handleImageError(this); };
        }
    });
});
</script>

{{-- Pastikan file default.jpg ada di public/images/ --}}
<script>
// Check if default image exists, if not create a base64 placeholder
function checkDefaultImage() {
    const testImg = new Image();
    testImg.onload = function() {
        console.log('Default image exists');
    };
    testImg.onerror = function() {
        console.warn('Default image not found, using icon placeholder');
        // You could create a base64 placeholder here if needed
    };
    testImg.src = '{{ asset("images/default.jpg") }}';
}

checkDefaultImage();
</script>
@endsection