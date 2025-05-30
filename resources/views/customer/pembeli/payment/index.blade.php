{{-- PERBAIKAN untuk masalah countdown yang langsung habis --}}
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

// PERBAIKAN: Hitung remaining time dengan lebih akurat
$remainingSeconds = 0;
$isExpired = false;

if ($transaksi->status === 'menunggu_pembayaran') {
    $now = \Carbon\Carbon::now();
    $batasLunas = \Carbon\Carbon::parse($transaksi->tanggalBatasLunas);
    
    // Gunakan diffInSeconds dengan parameter false untuk mendapatkan nilai negatif jika sudah lewat
    $diffSeconds = $batasLunas->diffInSeconds($now, false);
    
    if ($now->lt($batasLunas)) {
        // Masih dalam batas waktu
        $remainingSeconds = $diffSeconds;
    } else {
        // Sudah expired
        $remainingSeconds = 0;
        $isExpired = true;
    }
    
    // Debug log (bisa dihapus setelah testing)
    \Log::info('Timer calculation', [
        'now' => $now->format('Y-m-d H:i:s'),
        'batas' => $batasLunas->format('Y-m-d H:i:s'),
        'diff_seconds' => $diffSeconds,
        'remaining' => $remainingSeconds,
        'is_expired' => $isExpired
    ]);
}

// Total duration tetap 60 detik (1 menit)
$totalDuration = 60;
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

                <!-- PERBAIKAN: Debug info (hapus setelah testing) -->
                @if($transaksi->status === 'menunggu_pembayaran')
                <div class="bg-gray-100 border border-gray-300 rounded-lg p-4 mb-6 text-sm">
                    <h4 class="font-semibold mb-2">Debug Info (akan dihapus):</h4>
                    <p><strong>Server Time:</strong> {{ \Carbon\Carbon::now()->format('Y-m-d H:i:s') }}</p>
                    <p><strong>Batas Lunas:</strong> {{ $transaksi->tanggalBatasLunas }}</p>
                    <p><strong>Remaining Seconds:</strong> {{ $remainingSeconds }}</p>
                    <p><strong>Status:</strong> {{ $transaksi->status }}</p>
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
                        <!-- PERBAIKAN: Visual Timer Section -->
                        @if($transaksi->status === 'menunggu_pembayaran' && $remainingSeconds > 0)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-medium text-red-800">Batas Waktu Pembayaran</h3>
                                    <p class="text-red-600 text-sm">Selesaikan pembayaran sebelum:</p>
                                    <p class="text-red-800 font-semibold">{{ \Carbon\Carbon::parse($transaksi->tanggalBatasLunas)->format('d M Y H:i') }} WIB</p>
                                </div>
                                
                                <!-- PERBAIKAN: Visual Countdown Timer -->
                                <div class="text-center">
                                    <div class="relative inline-block">
                                        <!-- Circular Progress -->
                                        <div class="w-20 h-20">
                                            <svg class="w-20 h-20 transform -rotate-90" viewBox="0 0 100 100">
                                                <!-- Background Circle -->
                                                <circle
                                                    cx="50"
                                                    cy="50"
                                                    r="45"
                                                    stroke="#fee2e2"
                                                    stroke-width="8"
                                                    fill="none"
                                                />
                                                <!-- Progress Circle -->
                                                <circle
                                                    id="timerCircle"
                                                    cx="50"
                                                    cy="50"
                                                    r="45"
                                                    stroke="#16a34a"
                                                    stroke-width="8"
                                                    fill="none"
                                                    stroke-linecap="round"
                                                    stroke-dasharray="283"
                                                    stroke-dashoffset="0"
                                                    class="transition-all duration-1000 ease-linear"
                                                />
                                            </svg>
                                        </div>
                                        <!-- Timer Text -->
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="text-center">
                                                <div id="countdown" class="text-lg font-bold text-red-600">
                                                    01:00
                                                </div>
                                                <div class="text-xs text-red-500">menit</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- PERBAIKAN: Status indicator -->
                            <div id="timerStatus" class="mt-4 p-3 bg-green-100 border border-green-300 rounded text-green-800 text-sm">
                                <i class="fas fa-clock mr-2"></i>
                                Waktu pembayaran sedang berjalan...
                            </div>
                        </div>
                        @elseif($transaksi->status === 'menunggu_pembayaran' && $remainingSeconds <= 0)
                        <!-- Timer sudah habis -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 mb-6 text-center">
                            <i class="fas fa-times-circle text-4xl text-red-600 mb-4"></i>
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Waktu Pembayaran Habis</h3>
                            <p class="text-red-600 mb-4">Batas waktu pembayaran telah berakhir. Transaksi akan segera dibatalkan.</p>
                            <button onclick="location.reload()" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                                <i class="fas fa-refresh mr-2"></i>Refresh Halaman
                            </button>
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

                        <!-- Alamat Pengiriman Section - TAMBAHAN BARU -->
                        <div class="bg-gray-50 rounded-lg p-4 mt-4">
                            <h4 class="font-semibold text-gray-800 mb-3">Detail Pengiriman</h4>
                            
                            @php
                            $alamatData = null;
                            if($transaksi->alamatPengiriman) {
                                $alamatData = json_decode($transaksi->alamatPengiriman, true);
                            }
                            @endphp
                            
                            <div class="space-y-2">
                                <div class="flex items-start">
                                    <i class="fas fa-truck text-gray-500 mt-1 mr-3"></i>
                                    <div>
                                        <span class="text-sm font-medium text-gray-700">Metode Pengiriman:</span>
                                        <p class="text-gray-900">
                                            @if($transaksi->metodePengiriman === 'kurir')
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs mr-2">
                                                    <i class="fas fa-shipping-fast mr-1"></i>Kurir ReUseMart
                                                </span>
                                            @else
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs mr-2">
                                                    <i class="fas fa-store mr-1"></i>Ambil Sendiri
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-500 mt-1 mr-3"></i>
                                    <div class="flex-grow">
                                        <span class="text-sm font-medium text-gray-700">Alamat:</span>
                                        @if($alamatData)
                                            <div class="bg-white border border-gray-200 rounded p-3 mt-1">
                                                <div class="flex items-center mb-1">
                                                    <span class="font-medium text-gray-900">{{ $alamatData['jenis'] ?? 'Alamat' }}</span>
                                                    @if($transaksi->metodePengiriman === 'kurir')
                                                        <span class="ml-2 bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">Pengiriman</span>
                                                    @else
                                                        <span class="ml-2 bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Pickup</span>
                                                    @endif
                                                </div>
                                                <p class="text-gray-600 text-sm">{{ $alamatData['alamatLengkap'] ?? 'Alamat tidak tersedia' }}</p>
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-sm italic">Alamat tidak tersedia</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Upload Form atau Status -->
                    <div>
                        @if($transaksi->status === 'menunggu_pembayaran' && $remainingSeconds > 0)
                        <!-- PERBAIKAN: Upload Form -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Upload Bukti Pembayaran</h3>
                            
                            <!-- PERBAIKAN: Form dengan proper handling -->
                            <form id="paymentForm" enctype="multipart/form-data" onsubmit="return false;">
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
                                
                                <button type="button" onclick="handleUpload()" id="uploadBtn" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                    <i class="fas fa-upload mr-2"></i>
                                    Upload Bukti Pembayaran
                                </button>
                            </form>
                        </div>
                        @elseif($transaksi->status === 'menunggu_pembayaran' && $remainingSeconds <= 0)
                        <!-- Waktu habis -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                            <i class="fas fa-clock text-4xl text-red-600 mb-4"></i>
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Waktu Pembayaran Habis</h3>
                            <p class="text-red-600 mb-4">Upload bukti pembayaran tidak dapat dilakukan karena waktu telah habis.</p>
                        </div>
                        @elseif($transaksi->status === 'menunggu_verifikasi')
                        <!-- Verifikasi Status -->
                        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 text-center">
                            <i class="fas fa-clock text-4xl text-blue-600 mb-4"></i>
                            <h3 class="text-lg font-semibold text-blue-800 mb-2">Pembayaran Sedang Diverifikasi</h3>
                            <p class="text-blue-600 mb-4">Bukti pembayaran Anda sudah diterima dan sedang diverifikasi oleh tim kami.</p>
                            <p class="text-sm text-blue-500">Proses verifikasi biasanya membutuhkan waktu 2x24 jam.</p>
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
                        @elseif($transaksi->status === 'batal')
                        <!-- Status Batal -->
                        <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
                            <i class="fas fa-times-circle text-4xl text-red-600 mb-4"></i>
                            <h3 class="text-lg font-semibold text-red-800 mb-2">Transaksi Dibatalkan</h3>
                            <p class="text-red-600 mb-4">Waktu pembayaran telah habis atau bukti pembayaran tidak valid.</p>
                            <a href="{{ route('produk.index') }}" class="inline-block bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                                Belanja Lagi
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

                <!-- Order Items -->
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Item Pesanan</h3>
                    <div class="space-y-3">
                        @foreach($transaksi->detailTransaksiPenjualan as $detail)
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded">
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

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-500"></div>
        <span class="text-gray-700">Mengupload...</span>
    </div>
</div>

<script>
console.log('Payment page loaded - FIXED timer version');

// ================================================
// PERBAIKAN: Global Variables dengan data backend yang akurat
// ================================================
let countdownTimer = null;
let isTimerExpired = false;
let isUploadInProgress = false;

// PERBAIKAN: Data dari backend dengan perhitungan yang tepat
const remainingSeconds = {{ $remainingSeconds ?? 0 }};
const isInitiallyExpired = {{ $isExpired ? 'true' : 'false' }};
const totalDuration = {{ $totalDuration ?? 60 }};
const transactionStatus = '{{ $transaksi->status }}';

console.log('Timer initialization:', {
    remainingSeconds: remainingSeconds,
    isInitiallyExpired: isInitiallyExpired,
    totalDuration: totalDuration,
    status: transactionStatus
});

// ================================================
// PERBAIKAN: Countdown Timer dengan validasi lebih ketat
// ================================================
@if($transaksi->status === 'menunggu_pembayaran')
function initializeCountdown() {
    console.log('Initializing countdown...');
    
    // Jika sudah expired dari backend, langsung handle
    if (isInitiallyExpired || remainingSeconds <= 0) {
        console.log('Transaction already expired from backend');
        handleTimerExpired();
        return;
    }
    
    // Set initial values
    let currentSeconds = Math.max(0, Math.floor(remainingSeconds));
    console.log('Starting countdown with', currentSeconds, 'seconds');
    
    // Jika currentSeconds masih 0 atau negatif, langsung expired
    if (currentSeconds <= 0) {
        console.log('No time remaining, expiring immediately');
        handleTimerExpired();
        return;
    }
    
    // Initial update
    updateCountdownDisplay(currentSeconds);
    
    // Update setiap detik
    countdownTimer = setInterval(() => {
        currentSeconds--;
        console.log('Countdown tick:', currentSeconds);
        
        if (currentSeconds <= 0) {
            console.log('Timer reached zero, expiring...');
            handleTimerExpired();
            return;
        }
        
        updateCountdownDisplay(currentSeconds);
    }, 1000);
}

function updateCountdownDisplay(seconds) {
    if (isTimerExpired) return;
    
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    
    // Update text countdown
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        countdownElement.innerHTML = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
    
    // Update circular progress - PERBAIKAN: Gunakan totalDuration yang benar
    const circle = document.getElementById('timerCircle');
    if (circle) {
        const progress = Math.max(0, Math.min(1, seconds / totalDuration));
        const circumference = 2 * Math.PI * 45; // radius = 45
        const offset = circumference * (1 - progress);
        circle.style.strokeDashoffset = offset;
        
        // Change color based on remaining time
        if (seconds <= 20) {
            circle.style.stroke = '#dc2626'; // red-600
        } else if (seconds <= 40) {
            circle.style.stroke = '#ea580c'; // orange-600
        } else {
            circle.style.stroke = '#16a34a'; // green-600
        }
    }
    
    // Update status message
    const statusElement = document.getElementById('timerStatus');
    if (statusElement) {
        if (seconds <= 20) {
            statusElement.className = 'mt-4 p-3 bg-red-100 border border-red-300 rounded text-red-800 text-sm';
            statusElement.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Waktu pembayaran hampir habis! Segera upload bukti pembayaran.';
        } else if (seconds <= 40) {
            statusElement.className = 'mt-4 p-3 bg-yellow-100 border border-yellow-300 rounded text-yellow-800 text-sm';
            statusElement.innerHTML = '<i class="fas fa-clock mr-2"></i>Waktu pembayaran sedang berjalan...';
        } else {
            statusElement.className = 'mt-4 p-3 bg-green-100 border border-green-300 rounded text-green-800 text-sm';
            statusElement.innerHTML = '<i class="fas fa-clock mr-2"></i>Waktu pembayaran sedang berjalan...';
        }
    }
}

function handleTimerExpired() {
    console.log('Timer expired!');
    isTimerExpired = true;
    
    // Stop timer
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
    }
    
    // Update UI
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        countdownElement.innerHTML = '<span class="text-red-600">HABIS</span>';
    }
    
    const circle = document.getElementById('timerCircle');
    if (circle) {
        circle.style.strokeDashoffset = '283'; // Fully empty
        circle.style.stroke = '#dc2626';
    }
    
    const statusElement = document.getElementById('timerStatus');
    if (statusElement) {
        statusElement.className = 'mt-4 p-3 bg-red-100 border border-red-300 rounded text-red-800 text-sm';
        statusElement.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Waktu pembayaran telah habis. Transaksi akan dibatalkan.';
    }
    
    // Disable upload form
    const uploadBtn = document.getElementById('uploadBtn');
    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Waktu Habis';
        uploadBtn.className = 'w-full bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg cursor-not-allowed';
    }
    
    // Show notification dan auto redirect setelah 5 detik
    showTimerExpiredNotification();
    
    // Auto redirect ke profile setelah 10 detik
    setTimeout(() => {
        window.location.href = '{{ route("pembeli.profile") }}';
    }, 10000);
}

function showTimerExpiredNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 p-6 bg-red-500 text-white rounded-lg shadow-lg max-w-sm';
    notification.innerHTML = `
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-2xl mr-3 mt-1"></i>
            <div class="flex-grow">
                <h4 class="font-bold mb-2">Waktu Pembayaran Habis</h4>
                <p class="text-sm mb-3">Transaksi akan dibatalkan otomatis. Anda akan diarahkan kembali ke profil dalam 10 detik.</p>
                <button onclick="window.location.href='{{ route("pembeli.profile") }}'" class="bg-white text-red-600 px-3 py-1 rounded text-sm font-medium hover:bg-gray-100">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali Sekarang
                </button>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 ml-2">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
}

// Initialize countdown when DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, status:', transactionStatus);
    if (transactionStatus === 'menunggu_pembayaran') {
        initializeCountdown();
    }
});

@else
console.log('Timer not initialized - status:', transactionStatus, 'remaining:', remainingSeconds);

// Jika status bukan menunggu_pembayaran tapi ada expired transaction, auto redirect
@if($transaksi->status === 'batal')
setTimeout(() => {
    window.location.href = '{{ route("pembeli.profile") }}';
}, 5000);
@endif
@endif

// ================================================
// Upload Function dengan Error Handling yang lebih baik
// ================================================
function handleUpload() {
    if (isUploadInProgress) {
        showNotification('Upload sedang dalam proses, harap tunggu', 'warning');
        return;
    }
    
    if (isTimerExpired) {
        showNotification('Waktu pembayaran telah habis', 'error');
        return;
    }
    
    const buktiInput = document.getElementById('buktiPembayaran');
    const uploadBtn = document.getElementById('uploadBtn');
    
    if (!buktiInput.files[0]) {
        showNotification('Silakan pilih file bukti pembayaran', 'error');
        return;
    }
    
    // Prevent multiple uploads
    isUploadInProgress = true;
    
    // Disable button and show loading
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupload...';
    
    document.getElementById('loadingOverlay').classList.remove('hidden');
    
    const formData = new FormData();
    formData.append('bukti_pembayaran', buktiInput.files[0]);
    formData.append('_token', '{{ csrf_token() }}');
    
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);
    
    fetch('{{ route("pembeli.payment.upload", $transaksi->idTransaksiPenjualan) }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: controller.signal
    })
    .then(response => {
        clearTimeout(timeoutId);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return response.json();
    })
    .then(data => {
        console.log('Upload response:', data);
        
        document.getElementById('loadingOverlay').classList.add('hidden');
        isUploadInProgress = false;
        
        if (data.success) {
            showNotification('Bukti pembayaran berhasil diupload!', 'success');
            
            // Stop countdown timer
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
            }
            
            // Show success dan reload setelah delay
            setTimeout(() => {
                location.reload();
            }, 2000);
            
        } else {
            resetUploadButton();
            showNotification(data.error || 'Terjadi kesalahan saat mengupload bukti pembayaran', 'error');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        document.getElementById('loadingOverlay').classList.add('hidden');
        isUploadInProgress = false;
        
        console.error('Upload error:', error);
        resetUploadButton();
        
        if (error.name === 'AbortError') {
            showNotification('Upload timeout. Silakan coba lagi.', 'error');
        } else if (error.message.includes('Failed to fetch')) {
            showNotification('Koneksi bermasalah. Silakan periksa koneksi internet Anda.', 'error');
        } else {
            showNotification('Terjadi kesalahan saat mengupload bukti pembayaran', 'error');
        }
    });
}

function resetUploadButton() {
    const uploadBtn = document.getElementById('uploadBtn');
    if (uploadBtn && !isTimerExpired) {
        uploadBtn.disabled = false;
        uploadBtn.innerHTML = '<i class="fas fa-upload mr-2"></i>Upload Bukti Pembayaran';
    }
}

// ================================================
// Image Functions
// ================================================
function handleImageLoad(img) {
    img.style.display = 'block';
    const placeholder = img.nextElementSibling;
    if (placeholder && placeholder.classList.contains('hidden')) {
        placeholder.style.display = 'none';
    }
}

function handleImageError(img) {
    if (img.src.includes('default.jpg') || img.src.includes('placeholder')) {
        img.style.display = 'none';
        const placeholder = img.nextElementSibling;
        if (placeholder) {
            placeholder.classList.remove('hidden');
            placeholder.style.display = 'flex';
        }
        return;
    }
    
    const defaultImageUrl = '{{ asset("images/default.jpg") }}';
    img.src = defaultImageUrl;
}

function previewImage(input) {
    const file = input.files[0];
    const fileError = document.getElementById('fileError');
    const uploadArea = document.getElementById('uploadArea');
    const imagePreview = document.getElementById('imagePreview');
    
    fileError.classList.add('hidden');
    
    if (file) {
        if (!file.type.startsWith('image/')) {
            fileError.textContent = 'File harus berupa gambar';
            fileError.classList.remove('hidden');
            input.value = '';
            return;
        }
        
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

// ================================================
// Notification Function
// ================================================
function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 max-w-sm ${
        type === 'success' ? 'bg-green-500 text-white' : 
        type === 'error' ? 'bg-red-500 text-white' : 
        type === 'warning' ? 'bg-yellow-500 text-white' :
        'bg-blue-500 text-white'
    }`;
    notification.innerHTML = `
        <div class="flex items-center">
            <i class="fas ${
                type === 'success' ? 'fa-check-circle' : 
                type === 'error' ? 'fa-times-circle' : 
                type === 'warning' ? 'fa-exclamation-triangle' :
                'fa-info-circle'
            } mr-2"></i>
            <span class="flex-grow">${message}</span>
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

// ================================================
// Cleanup
// ================================================
window.addEventListener('beforeunload', function() {
    if (countdownTimer) {
        clearInterval(countdownTimer);
    }
});
</script>

@endsection