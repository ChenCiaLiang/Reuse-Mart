{{-- FILE: resources/views/pegawai/cs/verification/show.blade.php --}}
@extends('layouts.cs')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-500 px-6 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-white">Verifikasi Pembayaran</h1>
                    <p class="text-blue-100 text-sm mt-1">
                        Transaksi #{{ $transaksi->idTransaksiPenjualan }}
                    </p>
                </div>
                <a href="{{ route('cs.verification.index') }}" class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </a>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <div class="flex">
                        <i class="fas fa-check-circle mr-3 mt-0.5"></i>
                        <p>{{ session('success') }}</p>
                    </div>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <div class="flex">
                        <i class="fas fa-exclamation-triangle mr-3 mt-0.5"></i>
                        <p>{{ session('error') }}</p>
                    </div>
                </div>
                @endif

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Left Column - Transaction Info -->
                    <div>
                        <!-- Customer Info -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-user text-blue-600 mr-2"></i>
                                Informasi Pembeli
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Nama:</span>
                                    <span class="font-medium text-gray-900">{{ $transaksi->pembeli->nama }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Email:</span>
                                    <span class="font-medium text-gray-900">{{ $transaksi->pembeli->email }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Poin Saat Ini:</span>
                                    <span class="font-medium text-yellow-600">{{ number_format($transaksi->pembeli->poin) }} poin</span>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Details -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-receipt text-green-600 mr-2"></i>
                                Detail Transaksi
                            </h3>
                            <div class="space-y-3">
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Tanggal Pesanan:</span>
                                    <span class="font-medium text-gray-900">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }} WIB
                                    </span>
                                </div>
                                @if($transaksi->tanggalUploadBukti)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Upload Bukti:</span>
                                    <span class="font-medium text-blue-600">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalUploadBukti)->format('d M Y H:i') }} WIB
                                    </span>
                                </div>
                                @endif
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <span class="bg-yellow-100 text-yellow-800 text-sm px-3 py-1 rounded-full">
                                        <i class="fas fa-clock mr-1"></i>
                                        Menunggu Verifikasi
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Metode Pengiriman:</span>
                                    <span class="font-medium text-gray-900">
                                        @if($transaksi->metodePengiriman === 'kurir')
                                            <i class="fas fa-truck mr-1 text-blue-600"></i>Kurir ReUseMart
                                        @else
                                            <i class="fas fa-store mr-1 text-green-600"></i>Ambil Sendiri
                                        @endif
                                    </span>
                                </div>
                                @if($transaksi->poinDigunakan > 0)
                                <div class="flex justify-between">
                                    <span class="text-sm text-gray-600">Poin Digunakan:</span>
                                    <span class="font-medium text-yellow-600">{{ number_format($transaksi->poinDigunakan) }} poin</span>
                                </div>
                                @endif
                                <div class="flex justify-between border-t pt-3">
                                    <span class="text-sm font-semibold text-gray-700">Total Pembayaran:</span>
                                    <span class="font-bold text-lg text-blue-600">
                                        Rp {{ number_format($transaksi->detailTransaksiPenjualan->sum(function($detail) { 
                                            return $detail->produk->hargaJual; 
                                        }), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-shopping-bag text-purple-600 mr-2"></i>
                                Item Pesanan ({{ $transaksi->detailTransaksiPenjualan->count() }})
                            </h3>
                            <div class="space-y-3 max-h-64 overflow-y-auto">
                                @foreach($transaksi->detailTransaksiPenjualan as $detail)
                                <div class="flex items-center space-x-3 p-3 bg-white rounded border hover:shadow-sm transition-shadow">
                                    @php
                                        $gambarArray = $detail->produk->gambar ? explode(',', $detail->produk->gambar) : ['default.jpg'];
                                        $thumbnail = trim($gambarArray[0]);
                                        $imagePath = $thumbnail !== 'default.jpg' ? 'images/produk/' . $thumbnail : 'images/default.jpg';
                                    @endphp
                                    
                                    <div class="h-12 w-12 rounded bg-gray-200 flex items-center justify-center overflow-hidden flex-shrink-0">
                                        <img class="h-full w-full object-cover"
                                             src="{{ asset($imagePath) }}"
                                             alt="{{ $detail->produk->deskripsi }}"
                                             onerror="handleProductImageError(this)"
                                             onload="handleProductImageLoad(this)">
                                             
                                        <!-- Fallback icon -->
                                        <div class="h-full w-full flex items-center justify-center bg-gray-300 hidden">
                                            <i class="fas fa-image text-gray-500 text-lg"></i>
                                        </div>
                                    </div>
                                    
                                    <div class="flex-grow min-w-0">
                                        <h4 class="font-medium text-gray-900 truncate">{{ $detail->produk->deskripsi }}</h4>
                                        <p class="text-sm text-gray-500">{{ $detail->produk->kategori->nama ?? 'Kategori' }}</p>
                                        @if($detail->produk->tanggalGaransi && \Carbon\Carbon::parse($detail->produk->tanggalGaransi)->isFuture())
                                        <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full mt-1">
                                            <i class="fas fa-shield-alt mr-1"></i>Garansi
                                        </span>
                                        @endif
                                    </div>
                                    <span class="font-semibold text-blue-600 flex-shrink-0">Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Payment Proof & Verification -->
                    <div>
                        <!-- Payment Proof -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-image text-indigo-600 mr-2"></i>
                                Bukti Pembayaran
                            </h3>
                            
                            @php
                                // Debug path - dapat dihapus setelah masalah teratasi
                                $buktiPath = $transaksi->buktiPembayaran;
                                $fullPath = $buktiPath ? public_path($buktiPath) : null;
                                $fileExists = $buktiPath && file_exists($fullPath);
                                
                                // Log untuk debugging
                                \Log::info('Bukti Pembayaran Debug', [
                                    'transaksi_id' => $transaksi->idTransaksiPenjualan,
                                    'bukti_path' => $buktiPath,
                                    'full_path' => $fullPath,
                                    'file_exists' => $fileExists,
                                    'asset_url' => $buktiPath ? asset($buktiPath) : null
                                ]);
                            @endphp
                            
                            @if($transaksi->buktiPembayaran && !empty(trim($transaksi->buktiPembayaran)))
                            <div class="text-center">
                                <div class="bg-white rounded-lg p-4 border-2 border-dashed border-gray-300 hover:border-blue-400 transition-colors">
                                    <!-- Loading state -->
                                    <div id="imageLoading" class="py-8">
                                        <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-3"></i>
                                        <p class="text-gray-500">Memuat gambar...</p>
                                    </div>
                                    
                                    <!-- Main image -->
                                    <img id="buktiPembayaran"
                                         src="{{ asset($transaksi->buktiPembayaran) }}" 
                                         alt="Bukti Pembayaran" 
                                         class="max-w-full h-auto rounded-lg border cursor-pointer hover:shadow-lg transition-shadow hidden"
                                         onclick="openImageModal(this.src)"
                                         onerror="handleImageError(this)"
                                         onload="handleImageLoad(this)">
                                    
                                    <!-- Error fallback -->
                                    <div id="imageError" class="hidden py-8">
                                        <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-3"></i>
                                        <p class="text-red-500 font-medium">Gagal memuat bukti pembayaran</p>
                                        <p class="text-gray-400 text-sm mt-1">Path: {{ $transaksi->buktiPembayaran }}</p>
                                        <div class="mt-3 space-x-2">
                                            <button onclick="retryLoadImage()" class="bg-blue-500 text-white px-4 py-2 rounded text-sm hover:bg-blue-600 transition-colors">
                                                <i class="fas fa-refresh mr-1"></i>Coba Lagi
                                            </button>
                                            <button onclick="showDebugInfo()" class="bg-gray-500 text-white px-4 py-2 rounded text-sm hover:bg-gray-600 transition-colors">
                                                <i class="fas fa-info-circle mr-1"></i>Debug Info
                                            </button>
                                        </div>
                                        <div id="debugInfo" class="hidden mt-3 p-3 bg-gray-100 rounded text-left text-xs">
                                            <p><strong>Original Path:</strong> {{ $transaksi->buktiPembayaran }}</p>
                                            <p><strong>Full URL:</strong> {{ asset($transaksi->buktiPembayaran) }}</p>
                                            <p><strong>File Exists:</strong> {{ $fileExists ? 'Yes' : 'No' }}</p>
                                            <p><strong>Public Path:</strong> {{ public_path() }}</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <p class="text-sm text-gray-500 mt-3 flex items-center justify-center">
                                    <i class="fas fa-click mr-2"></i>
                                    Klik gambar untuk memperbesar
                                </p>
                                <div class="mt-2 text-xs text-gray-400">
                                    @if($transaksi->tanggalUploadBukti)
                                        Upload: {{ \Carbon\Carbon::parse($transaksi->tanggalUploadBukti)->format('d M Y H:i') }} WIB
                                    @endif
                                </div>
                            </div>
                            @else
                            <div class="text-center py-8 bg-white rounded-lg border-2 border-dashed border-gray-300">
                                <i class="fas fa-image text-4xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500 font-medium">Bukti pembayaran belum diupload</p>
                                <p class="text-gray-400 text-sm mt-1">Menunggu customer mengupload bukti transfer</p>
                            </div>
                            @endif
                        </div>

                        <!-- Verification Form -->
                        <div class="bg-white border-2 border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
                                <i class="fas fa-check-circle text-green-600 mr-2"></i>
                                Verifikasi Pembayaran
                            </h3>
                            
                            <form action="{{ route('cs.verification.verify', $transaksi->idTransaksiPenjualan) }}" method="POST">
                                @csrf
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Status Verifikasi <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <div class="space-y-3">
                                        <label class="flex items-start p-4 border-2 border-green-200 rounded-lg cursor-pointer hover:bg-green-50 transition-colors group">
                                            <input type="radio" name="status_verifikasi" value="valid" 
                                                   class="h-4 w-4 text-green-600 focus:ring-green-500 mt-1" required>
                                            <div class="ml-3">
                                                <div class="font-medium text-green-800 group-hover:text-green-900">
                                                    <i class="fas fa-check-circle mr-2"></i>
                                                    Pembayaran Valid
                                                </div>
                                                <div class="text-sm text-green-600 mt-1">
                                                    Bukti transfer sesuai dan pembayaran berhasil. Transaksi akan diproses ke tahap selanjutnya.
                                                </div>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-start p-4 border-2 border-red-200 rounded-lg cursor-pointer hover:bg-red-50 transition-colors group">
                                            <input type="radio" name="status_verifikasi" value="tidak_valid" 
                                                   class="h-4 w-4 text-red-600 focus:ring-red-500 mt-1" required>
                                            <div class="ml-3">
                                                <div class="font-medium text-red-800 group-hover:text-red-900">
                                                    <i class="fas fa-times-circle mr-2"></i>
                                                    Pembayaran Tidak Valid
                                                </div>
                                                <div class="text-sm text-red-600 mt-1">
                                                    Bukti transfer tidak sesuai atau bermasalah. Transaksi akan dibatalkan dan poin dikembalikan.
                                                </div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Catatan Verifikasi <span class="text-gray-400">(Opsional)</span>
                                    </label>
                                    <textarea name="catatan" id="catatan" rows="4" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                              placeholder="Tambahkan catatan verifikasi...">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors shadow-lg hover:shadow-xl">
                                        <i class="fas fa-check mr-2"></i>
                                        Simpan Verifikasi
                                    </button>
                                    
                                    <a href="{{ route('cs.verification.index') }}" 
                                       class="flex-1 text-center bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-times mr-2"></i>
                                        Batal
                                    </a>
                                </div>
                            </form>
                        </div>
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
                    class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-3 hover:bg-opacity-75 transition-colors z-10">
                <i class="fas fa-times text-xl"></i>
            </button>
            <img id="modalImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-full object-contain rounded-lg shadow-2xl">
        </div>
    </div>
</div>

<script>
// Prevent page reload on any image errors
window.addEventListener('error', function(e) {
    if (e.target.tagName === 'IMG') {
        e.preventDefault();
        console.log('Image failed to load:', e.target.src);
        return false;
    }
}, true);

// Global variables
let imageRetryCount = 0;
const maxRetries = 3;

// Handle bukti pembayaran image loading
function handleImageError(img) {
    console.log('Bukti pembayaran image error:', img.src);
    img.classList.add('hidden');
    document.getElementById('imageError').classList.remove('hidden');
    document.getElementById('imageLoading').classList.add('hidden');
    
    // Log error for debugging
    fetch('/log-image-error', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        },
        body: JSON.stringify({
            url: img.src,
            error: 'Image failed to load',
            transaksi_id: {{ $transaksi->idTransaksiPenjualan }}
        })
    }).catch(e => console.log('Log error failed:', e));
}

function handleImageLoad(img) {
    console.log('Bukti pembayaran image loaded successfully:', img.src);
    img.classList.remove('hidden');
    document.getElementById('imageError').classList.add('hidden');
    document.getElementById('imageLoading').classList.add('hidden');
    imageRetryCount = 0; // Reset retry count on successful load
}

// Handle product image loading
function handleProductImageError(img) {
    console.log('Product image error:', img.src);
    img.style.display = 'none';
    const fallback = img.nextElementSibling;
    if (fallback) {
        fallback.classList.remove('hidden');
    }
}

function handleProductImageLoad(img) {
    console.log('Product image loaded successfully:', img.src);
    img.style.display = 'block';
    const fallback = img.nextElementSibling;
    if (fallback) {
        fallback.classList.add('hidden');
    }
}

function retryLoadImage() {
    if (imageRetryCount >= maxRetries) {
        alert('Gagal memuat gambar setelah ' + maxRetries + ' kali percobaan. Silakan refresh halaman atau hubungi administrator.');
        return;
    }
    
    const img = document.getElementById('buktiPembayaran');
    const originalSrc = '{{ asset($transaksi->buktiPembayaran) }}';
    
    imageRetryCount++;
    
    // Show loading
    document.getElementById('imageLoading').classList.remove('hidden');
    document.getElementById('imageError').classList.add('hidden');
    img.classList.add('hidden');
    
    // Clear current src and retry with cache busting
    img.src = '';
    setTimeout(() => {
        img.src = originalSrc + '?retry=' + imageRetryCount + '&t=' + new Date().getTime();
    }, 100);
    
    console.log('Retrying image load, attempt:', imageRetryCount);
}

function showDebugInfo() {
    const debugDiv = document.getElementById('debugInfo');
    debugDiv.classList.toggle('hidden');
}

function openImageModal(src) {
    // Check if image exists before opening modal
    const testImg = new Image();
    testImg.onload = function() {
        document.getElementById('modalImage').src = src;
        document.getElementById('imageModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    };
    testImg.onerror = function() {
        alert('Gambar tidak dapat ditampilkan dalam modal. Silakan coba refresh halaman.');
    };
    testImg.src = src;
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside image
document.getElementById('imageModal').addEventListener('click', function(e) {
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

// Monitor network performance for debugging
if ('PerformanceObserver' in window) {
    const observer = new PerformanceObserver((list) => {
        for (const entry of list.getEntries()) {
            if (entry.name.includes('.jpg') || entry.name.includes('.jpeg') || entry.name.includes('.png')) {
                console.log('Image request:', {
                    url: entry.name,
                    status: entry.responseStatus || 'unknown',
                    duration: entry.duration,
                    transferSize: entry.transferSize
                });
            }
        }
    });
    observer.observe({entryTypes: ['resource']});
}

// Add loading state for initial page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('Page loaded, checking images...');
    
    // Check if bukti pembayaran image exists
    const buktiImg = document.getElementById('buktiPembayaran');
    if (buktiImg && buktiImg.src) {
        // Start with loading state
        document.getElementById('imageLoading').classList.remove('hidden');
    }
});

// Log page unload to detect unwanted reloads
window.addEventListener('beforeunload', function(e) {
    console.log('Page is about to unload/reload');
});
</script>
@endsection