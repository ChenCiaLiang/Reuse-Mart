{{-- IMPROVED UI untuk resources/views/customer/pembeli/payment/index.blade.php --}}

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

// Perhitungan remaining time yang benar
$remainingSeconds = 0;
$isExpired = false;
$totalDuration = 60; // 1 menit = 60 detik

if ($transaksi->status === 'menunggu_pembayaran') {
    $now = \Carbon\Carbon::now();
    $batasLunas = \Carbon\Carbon::parse($transaksi->tanggalBatasLunas);
    
    if ($now->lt($batasLunas)) {
        $remainingSeconds = $now->diffInSeconds($batasLunas);
        $isExpired = false;
        
        if ($remainingSeconds > $totalDuration) {
            $remainingSeconds = $totalDuration;
        }
    } else {
        $remainingSeconds = 0;
        $isExpired = true;
    }
    
    // Debug log
    \Log::info('Payment Timer with Auto Cancel', [
        'transaction_id' => $transaksi->idTransaksiPenjualan,
        'now_formatted' => $now->format('Y-m-d H:i:s'),
        'batas_formatted' => $batasLunas->format('Y-m-d H:i:s'),
        'calculated_remaining' => $remainingSeconds,
        'is_expired' => $isExpired
    ]);
}
@endphp

@extends('layouts.customer')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-indigo-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pembayaran Transaksi</h1>
            <p class="text-gray-600">
                Nomor Transaksi: <span class="font-semibold text-blue-600">#{{ $checkoutData['nomorTransaksi'] ?? $transaksi->idTransaksiPenjualan }}</span>
            </p>
        </div>

        @if (session('success'))
        <div class="mb-6 mx-auto max-w-4xl">
            <div class="bg-green-50 border-l-4 border-green-400 p-4 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-check-circle text-green-400 mr-3 mt-0.5"></i>
                    <p class="text-green-700">{{ session('success') }}</p>
                </div>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="mb-6 mx-auto max-w-4xl">
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-r-lg">
                <div class="flex">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-3 mt-0.5"></i>
                    <p class="text-red-700">{{ session('error') }}</p>
                </div>
            </div>
        </div>
        @endif
        
        <!-- Progress Steps -->
        <div class="mb-10">
            <div class="max-w-4xl mx-auto">
                <div class="flex items-center justify-between">
                    <!-- Step 1: Pesanan Dibuat -->
                    <div class="flex flex-col items-center text-center flex-1">
                        <div class="w-12 h-12 rounded-full bg-blue-600 text-white flex items-center justify-center mb-2 shadow-lg">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="text-sm font-medium text-blue-600">Pesanan Dibuat</div>
                        <div class="text-xs text-gray-500 mt-1">✓ Selesai</div>
                    </div>
                    
                    <!-- Connector -->
                    <div class="flex-1 h-1 mx-4 {{ in_array($transaksi->status, ['menunggu_pembayaran', 'menunggu_verifikasi', 'disiapkan']) ? 'bg-blue-600' : 'bg-gray-300' }} rounded-full"></div>
                    
                    <!-- Step 2: Pembayaran -->
                    <div class="flex flex-col items-center text-center flex-1">
                        <div class="w-12 h-12 rounded-full {{ in_array($transaksi->status, ['menunggu_verifikasi', 'disiapkan']) ? 'bg-blue-600 text-white' : 'bg-yellow-500 text-white' }} flex items-center justify-center mb-2 shadow-lg">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="text-sm font-medium {{ in_array($transaksi->status, ['menunggu_verifikasi', 'disiapkan']) ? 'text-blue-600' : 'text-yellow-600' }}">Pembayaran</div>
                        <div class="text-xs text-gray-500 mt-1">
                            @if($transaksi->status === 'menunggu_pembayaran')
                                Menunggu...
                            @elseif(in_array($transaksi->status, ['menunggu_verifikasi', 'disiapkan']))
                                ✓ Selesai
                            @endif
                        </div>
                    </div>
                    
                    <!-- Connector -->
                    <div class="flex-1 h-1 mx-4 {{ $transaksi->status === 'disiapkan' ? 'bg-blue-600' : 'bg-gray-300' }} rounded-full"></div>
                    
                    <!-- Step 3: Disiapkan -->
                    <div class="flex flex-col items-center text-center flex-1">
                        <div class="w-12 h-12 rounded-full {{ $transaksi->status === 'disiapkan' ? 'bg-blue-600 text-white' : 'bg-gray-300 text-gray-500' }} flex items-center justify-center mb-2 shadow-lg">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="text-sm font-medium {{ $transaksi->status === 'disiapkan' ? 'text-blue-600' : 'text-gray-500' }}">Disiapkan</div>
                        <div class="text-xs text-gray-500 mt-1">
                            {{ $transaksi->status === 'disiapkan' ? '✓ Selesai' : 'Menunggu...' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto">
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column - Payment Info (2/3 width) -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Timer Section dengan auto cancel -->
                    @if($transaksi->status === 'menunggu_pembayaran' && !$isExpired && $remainingSeconds > 0)
                    <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center justify-between mb-6">
                            <div class="flex-1">
                                <div class="flex items-center mb-2">
                                    <i class="fas fa-clock text-red-600 text-xl mr-3"></i>
                                    <h3 class="text-xl font-bold text-red-800">Batas Waktu Pembayaran</h3>
                                </div>
                                <p class="text-red-600 text-sm mb-2">Selesaikan pembayaran sebelum:</p>
                                <p class="text-red-800 font-bold text-lg">{{ \Carbon\Carbon::parse($transaksi->tanggalBatasLunas)->format('d M Y H:i') }} WIB</p>
                                <div class="flex items-center mt-3 p-3 bg-red-100 rounded-lg">
                                    <i class="fas fa-exclamation-triangle text-red-600 mr-2"></i>
                                    <p class="text-red-700 text-sm font-medium">
                                        Transaksi akan dibatalkan otomatis jika waktu habis
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Enhanced Visual Countdown Timer -->
                            <div class="text-center ml-6">
                                <div class="relative inline-block">
                                    <!-- Circular Progress with glow effect -->
                                    <div class="w-24 h-24 relative">
                                        <svg class="w-24 h-24 transform -rotate-90 filter drop-shadow-lg" viewBox="0 0 100 100">
                                            <!-- Background Circle -->
                                            <circle
                                                cx="50"
                                                cy="50"
                                                r="42"
                                                stroke="#fee2e2"
                                                stroke-width="8"
                                                fill="none"
                                            />
                                            <!-- Progress Circle -->
                                            <circle
                                                id="timerCircle"
                                                cx="50"
                                                cy="50"
                                                r="42"
                                                stroke="#16a34a"
                                                stroke-width="8"
                                                fill="none"
                                                stroke-linecap="round"
                                                stroke-dasharray="264"
                                                stroke-dashoffset="0"
                                                class="transition-all duration-1000 ease-linear filter drop-shadow-sm"
                                            />
                                        </svg>
                                    </div>
                                    <!-- Timer Text -->
                                    <div class="absolute inset-0 flex items-center justify-center">
                                        <div class="text-center">
                                            <div id="countdown" class="text-xl font-black text-red-600">
                                                01:00
                                            </div>
                                            <div class="text-xs text-red-500 font-medium">menit</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Enhanced status indicator -->
                        <div id="timerStatus" class="p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
                            <div class="flex items-center">
                                <div class="w-2 h-2 bg-green-500 rounded-full mr-3 animate-pulse"></div>
                                <span class="font-medium">Waktu pembayaran sedang berjalan...</span>
                            </div>
                        </div>
                    </div>
                    @elseif($transaksi->status === 'menunggu_pembayaran' && ($isExpired || $remainingSeconds <= 0))
                    <!-- Timer sudah habis -->
                    <div class="bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-2xl p-8 text-center shadow-lg">
                        <div class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-times-circle text-4xl text-red-600"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-red-800 mb-3">Waktu Pembayaran Habis</h3>
                        <p class="text-red-600 mb-6 text-lg">Transaksi akan dibatalkan otomatis dan produk dikembalikan ke status tersedia.</p>
                        <div id="autoCancelStatus" class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-yellow-800">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin mr-3 text-lg"></i>
                                <span class="font-medium">Sedang membatalkan transaksi...</span>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Payment Instructions -->
                    <div class="bg-gradient-to-r from-yellow-50 to-amber-50 border border-yellow-200 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-info-circle text-yellow-600 text-xl"></i>
                            </div>
                            <h3 class="text-xl font-bold text-yellow-800">Instruksi Pembayaran</h3>
                        </div>
                        
                        <div class="space-y-6">
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-bold mr-4">1</div>
                                <div class="flex-1">
                                    <p class="font-medium text-yellow-700 mb-3">Transfer ke rekening berikut:</p>
                                    <div class="bg-white border border-yellow-200 rounded-xl p-4 shadow-sm">
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-center">
                                            <div>
                                                <p class="text-sm text-gray-600 mb-1">Bank</p>
                                                <p class="font-bold text-gray-900">BCA</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600 mb-1">No. Rekening</p>
                                                <p class="font-bold text-gray-900 text-lg">1234567890</p>
                                            </div>
                                            <div>
                                                <p class="text-sm text-gray-600 mb-1">Atas Nama</p>
                                                <p class="font-bold text-gray-900">ReUseMart Indonesia</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-bold mr-4">2</div>
                                <div class="flex-1">
                                    <p class="font-medium text-yellow-700">
                                        Transfer tepat sebesar 
                                        <span class="bg-yellow-200 text-yellow-800 px-3 py-1 rounded-full font-bold text-lg">
                                            Rp {{ number_format($checkoutData['total_akhir'] ?? 0, 0, ',', '.') }}
                                        </span>
                                    </p>
                                </div>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-bold mr-4">3</div>
                                <p class="flex-1 font-medium text-yellow-700">Upload bukti transfer melalui form di samping</p>
                            </div>
                            
                            <div class="flex items-start">
                                <div class="flex-shrink-0 w-8 h-8 bg-yellow-200 text-yellow-800 rounded-full flex items-center justify-center text-sm font-bold mr-4">4</div>
                                <p class="flex-1 font-medium text-yellow-700">Tunggu konfirmasi dari tim kami (maksimal 2x24 jam)</p>
                            </div>
                        </div>
                    </div>

                    <!-- Alamat Pengiriman Section -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mr-4">
                                <i class="fas fa-shipping-fast text-blue-600 text-xl"></i>
                            </div>
                            <h4 class="text-xl font-bold text-gray-800">Detail Pengiriman</h4>
                        </div>
                        
                        @php
                        $alamatData = null;
                        if($transaksi->alamatPengiriman) {
                            $alamatData = json_decode($transaksi->alamatPengiriman, true);
                        }
                        @endphp
                        
                        <div class="space-y-4">
                            <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                                <i class="fas fa-truck text-gray-500 mt-1 mr-4 text-lg"></i>
                                <div>
                                    <span class="text-sm font-medium text-gray-700 block mb-2">Metode Pengiriman:</span>
                                    @if($transaksi->metodePengiriman === 'kurir')
                                        <span class="bg-blue-100 text-blue-800 px-3 py-2 rounded-full text-sm font-medium">
                                            <i class="fas fa-shipping-fast mr-2"></i>Kurir ReUseMart
                                        </span>
                                    @else
                                        <span class="bg-green-100 text-green-800 px-3 py-2 rounded-full text-sm font-medium">
                                            <i class="fas fa-store mr-2"></i>Ambil Sendiri
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="flex items-start p-4 bg-gray-50 rounded-xl">
                                <i class="fas fa-map-marker-alt text-gray-500 mt-1 mr-4 text-lg"></i>
                                <div class="flex-grow">
                                    <span class="text-sm font-medium text-gray-700 block mb-2">Alamat:</span>
                                    @if($alamatData)
                                        <div class="bg-white border border-gray-200 rounded-lg p-4">
                                            <div class="flex items-center mb-2">
                                                <span class="font-semibold text-gray-900">{{ $alamatData['jenis'] ?? 'Alamat' }}</span>
                                            </div>
                                            <p class="text-gray-600">{{ $alamatData['alamatLengkap'] ?? 'Alamat tidak tersedia' }}</p>
                                        </div>
                                    @else
                                        <p class="text-gray-500 italic">Alamat tidak tersedia</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Upload Form dan Summary (1/3 width) -->
                <div class="lg:col-span-1 space-y-6">
                    @if($transaksi->status === 'menunggu_pembayaran' && !$isExpired && $remainingSeconds > 0)
                    <!-- Upload Form -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-upload text-blue-600"></i>
                            </div>
                            <h3 class="text-lg font-bold text-gray-800">Upload Bukti Pembayaran</h3>
                        </div>
                        
                        <form id="paymentForm" enctype="multipart/form-data" onsubmit="return false;">
                            @csrf
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Bukti Transfer <span class="text-red-500">*</span>
                                </label>
                                <div class="border-2 border-dashed border-gray-300 rounded-xl p-6 text-center hover:border-blue-400 transition-colors">
                                    <input type="file" name="bukti_pembayaran" id="buktiPembayaran" 
                                           accept="image/*" class="hidden" onchange="previewImage(this)">
                                    <div id="uploadArea" onclick="document.getElementById('buktiPembayaran').click()" class="cursor-pointer">
                                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                            <i class="fas fa-upload text-2xl text-gray-400"></i>
                                        </div>
                                        <p class="text-gray-600 font-medium mb-1">Klik untuk memilih file gambar</p>
                                        <p class="text-xs text-gray-500">Format: JPG, PNG (Maksimal 2MB)</p>
                                    </div>
                                    <div id="imagePreview" class="hidden mt-4">
                                        <img id="previewImg" src="" alt="Preview" class="max-w-full h-48 object-contain mx-auto rounded-lg shadow-md">
                                        <button type="button" onclick="removeImage()" class="mt-3 text-red-600 hover:text-red-800 text-sm font-medium">
                                            <i class="fas fa-trash mr-1"></i> Hapus
                                        </button>
                                    </div>
                                </div>
                                <div id="fileError" class="text-red-500 text-sm mt-2 hidden"></div>
                            </div>
                            
                            <button type="button" onclick="handleUpload()" id="uploadBtn" 
                                    class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl">
                                <i class="fas fa-upload mr-2"></i>
                                Upload Bukti Pembayaran
                            </button>
                        </form>
                    </div>
                    @elseif($transaksi->status === 'menunggu_pembayaran' && ($isExpired || $remainingSeconds <= 0))
                    <!-- Waktu habis - akan auto cancel -->
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center shadow-lg">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-clock text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-red-800 mb-3">Waktu Pembayaran Habis</h3>
                        <p class="text-red-600 mb-4">Transaksi sedang dibatalkan otomatis...</p>
                        <div id="autoCancelStatus" class="bg-yellow-50 border border-yellow-200 rounded-lg p-3 text-yellow-800 text-sm">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-spinner fa-spin mr-2"></i>
                                <span>Membatalkan transaksi dan mengembalikan status produk...</span>
                            </div>
                        </div>
                    </div>
                    @elseif($transaksi->status === 'menunggu_verifikasi')
                    <!-- Verifikasi Status -->
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-6 text-center shadow-lg">
                        <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-search text-3xl text-blue-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-blue-800 mb-3">Pembayaran Sedang Diverifikasi</h3>
                        <p class="text-blue-600 mb-4">Bukti pembayaran Anda sudah diterima dan sedang diverifikasi oleh tim kami.</p>
                        <div class="bg-blue-100 rounded-lg p-3">
                            <p class="text-sm text-blue-700 font-medium">Proses verifikasi biasanya membutuhkan waktu 2x24 jam.</p>
                        </div>
                    </div>
                    @elseif($transaksi->status === 'disiapkan')
                    <!-- Status Disiapkan -->
                    <div class="bg-green-50 border border-green-200 rounded-2xl p-6 text-center shadow-lg">
                        <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-check-circle text-3xl text-green-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-green-800 mb-3">Pembayaran Berhasil!</h3>
                        <p class="text-green-600 mb-6">Pesanan Anda sedang disiapkan untuk pengiriman.</p>
                        <a href="{{ route('pembeli.profile') }}" class="inline-block bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-bold px-6 py-3 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            Lihat Status Pesanan
                        </a>
                    </div>
                    @elseif($transaksi->status === 'batal')
                    <!-- Status Batal -->
                    <div class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center shadow-lg">
                        <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-times-circle text-3xl text-red-600"></i>
                        </div>
                        <h3 class="text-lg font-bold text-red-800 mb-3">Transaksi Dibatalkan</h3>
                        <p class="text-red-600 mb-6">Transaksi telah dibatalkan dan produk dikembalikan ke status tersedia.</p>
                        <a href="{{ route('produk.index') }}" class="inline-block bg-gradient-to-r from-gray-600 to-gray-700 hover:from-gray-700 hover:to-gray-800 text-white font-bold px-6 py-3 rounded-lg transition-all duration-200 shadow-lg hover:shadow-xl">
                            Belanja Lagi
                        </a>
                    </div>
                    @endif

                    <!-- Enhanced Order Summary -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-receipt text-green-600"></i>
                            </div>
                            <h4 class="text-lg font-bold text-gray-800">Ringkasan Pesanan</h4>
                        </div>
                        
                        @php
                        $subtotalFromItems = $transaksi->detailTransaksiPenjualan->sum(function($detail) {
                            return $detail->produk->hargaJual ?? 0;
                        });
                        
                        $ongkirCalculated = 0;
                        if($transaksi->metodePengiriman === 'kurir') {
                            $ongkirCalculated = $subtotalFromItems >= 1500000 ? 0 : 100000;
                        }
                        
                        $subtotal = $checkoutData['subtotal'] ?? $subtotalFromItems;
                        $ongkir = $checkoutData['ongkir'] ?? $ongkirCalculated;
                        $diskonPoin = $transaksi->poinDigunakan * 10;
                        $totalAkhir = $checkoutData['total_akhir'] ?? ($subtotal + $ongkir - $diskonPoin);
                        @endphp
                        
                        <div class="space-y-3">
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Ongkos Kirim</span>
                                <span class="font-semibold">{{ $ongkir == 0 ? 'GRATIS' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($transaksi->poinDigunakan > 0)
                            <div class="flex justify-between py-2 text-yellow-600">
                                <span>Diskon Poin ({{ number_format($transaksi->poinDigunakan) }} poin)</span>
                                <span class="font-semibold">- Rp {{ number_format($diskonPoin, 0, ',', '.') }}</span>
                            </div>
                            @endif
                            
                            <hr class="my-3">
                            <div class="flex justify-between py-2">
                                <span class="text-lg font-bold">Total</span>
                                <span class="text-xl font-bold text-blue-600">Rp {{ number_format($totalAkhir, 0, ',', '.') }}</span>
                            </div>
                            
                            @if($transaksi->poinDidapat > 0)
                            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center">
                                        <i class="fas fa-star text-green-600 mr-2"></i>
                                        <span class="text-green-700 font-medium">Poin yang akan didapat</span>
                                    </div>
                                    <span class="font-bold text-green-600">{{ number_format($transaksi->poinDidapat) }} poin</span>
                                </div>
                                <p class="text-xs text-green-600 mt-2">
                                    *Poin akan diberikan setelah pembayaran diverifikasi
                                </p>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Transaction Status -->
                    <div class="bg-white border border-gray-200 rounded-2xl p-6 shadow-lg">
                        <h4 class="font-bold text-gray-800 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                            Status Transaksi
                        </h4>
                        <div class="flex items-center p-3 rounded-lg {{ 
                            $transaksi->status === 'menunggu_pembayaran' ? 'bg-yellow-50' :
                            ($transaksi->status === 'menunggu_verifikasi' ? 'bg-blue-50' :
                            ($transaksi->status === 'disiapkan' ? 'bg-green-50' : 'bg-red-50'))
                        }}">
                            @if($transaksi->status === 'menunggu_pembayaran')
                                <div class="w-3 h-3 bg-yellow-500 rounded-full mr-3 animate-pulse"></div>
                                <span class="text-yellow-700 font-medium">Menunggu Pembayaran</span>
                            @elseif($transaksi->status === 'menunggu_verifikasi')
                                <div class="w-3 h-3 bg-blue-500 rounded-full mr-3 animate-pulse"></div>
                                <span class="text-blue-700 font-medium">Sedang Diverifikasi</span>
                            @elseif($transaksi->status === 'disiapkan')
                                <div class="w-3 h-3 bg-green-500 rounded-full mr-3"></div>
                                <span class="text-green-700 font-medium">Sedang Disiapkan</span>
                            @elseif($transaksi->status === 'batal')
                                <div class="w-3 h-3 bg-red-500 rounded-full mr-3"></div>
                                <span class="text-red-700 font-medium">Dibatalkan</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Order Items Section -->
            <div class="mt-10 bg-white border border-gray-200 rounded-2xl p-6 shadow-lg">
                <div class="flex items-center mb-6">
                    <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center mr-4">
                        <i class="fas fa-shopping-bag text-purple-600 text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800">Item Pesanan</h3>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($transaksi->detailTransaksiPenjualan as $detail)
                    <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl border border-gray-100 hover:shadow-md transition-shadow">
                        <div class="h-16 w-16 rounded-lg overflow-hidden bg-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm">
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
                        <div class="flex-grow min-w-0">
                            <h4 class="font-semibold text-gray-900 truncate">{{ $detail->produk->deskripsi }}</h4>
                            <p class="text-sm text-gray-500">{{ $detail->produk->kategori->nama ?? 'Kategori' }}</p>
                            <span class="text-lg font-bold text-blue-600">Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-2xl p-8 flex items-center space-x-4 shadow-2xl max-w-sm mx-4">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <span class="text-gray-700 font-medium">Memproses...</span>
    </div>
</div>

<script>
console.log('Payment page loaded - WITH AUTO CANCEL SYSTEM');

// SEMUA JAVASCRIPT TETAP SAMA SEPERTI SEBELUMNYA
// Hanya UI yang diimprove, fungsi tidak berubah

// ================================================
// TAMBAHAN BARU: Auto Cancel System Variables
// ================================================
let countdownTimer = null;
let isTimerExpired = false;
let isUploadInProgress = false;
let isCancelInProgress = false; // BARU: flag untuk prevent multiple cancel calls

// Data dari backend
const serverRemainingSeconds = {{ $remainingSeconds ?? 0 }};
const serverIsExpired = {{ $isExpired ? 'true' : 'false' }};
const totalDuration = {{ $totalDuration ?? 60 }};
const transactionStatus = '{{ $transaksi->status }}';
const transactionId = {{ $transaksi->idTransaksiPenjualan }};

// Validasi data
let remainingSeconds = Math.max(0, Math.floor(serverRemainingSeconds));
let isInitiallyExpired = serverIsExpired || remainingSeconds <= 0;

console.log('AUTO CANCEL Timer initialization:', {
    serverRemainingSeconds: serverRemainingSeconds,
    calculatedRemaining: remainingSeconds,
    serverIsExpired: serverIsExpired,
    isInitiallyExpired: isInitiallyExpired,
    totalDuration: totalDuration,
    status: transactionStatus,
    transactionId: transactionId
});

// ================================================
// PERBAIKAN: Countdown Timer dengan Auto Cancel
// ================================================
@if($transaksi->status === 'menunggu_pembayaran')
function initializeCountdown() {
    console.log('AUTO CANCEL: Initializing countdown...');
    
    // Jika sudah expired dari awal, langsung cancel
    if (isInitiallyExpired || remainingSeconds <= 0 || transactionStatus !== 'menunggu_pembayaran') {
        console.log('AUTO CANCEL: Transaction expired from start, triggering auto cancel');
        handleTimerExpiredWithAutoCancel();
        return;
    }
    
    console.log('AUTO CANCEL: Starting countdown with', remainingSeconds, 'seconds');
    
    let currentSeconds = remainingSeconds;
    updateCountdownDisplay(currentSeconds);
    
    // Start countdown interval
    countdownTimer = setInterval(() => {
        currentSeconds--;
        console.log('AUTO CANCEL: Countdown tick:', currentSeconds);
        
        if (currentSeconds <= 0) {
            console.log('AUTO CANCEL: Timer reached zero, triggering auto cancel');
            handleTimerExpiredWithAutoCancel();
            return;
        }
        
        updateCountdownDisplay(currentSeconds);
    }, 1000);
    
    console.log('AUTO CANCEL: Countdown timer started successfully');
}

function updateCountdownDisplay(seconds) {
    if (isTimerExpired) {
        console.log('AUTO CANCEL: Timer already expired, skipping update');
        return;
    }
    
    seconds = Math.max(0, Math.floor(seconds));
    
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = seconds % 60;
    
    // Update text countdown
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        countdownElement.innerHTML = `${minutes.toString().padStart(2, '0')}:${remainingSeconds.toString().padStart(2, '0')}`;
    }
    
    // Update circular progress
    const circle = document.getElementById('timerCircle');
    if (circle) {
        const progress = Math.max(0, Math.min(1, seconds / totalDuration));
        const circumference = 2 * Math.PI * 42;
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
            statusElement.className = 'p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm';
            statusElement.innerHTML = '<div class="flex items-center"><div class="w-2 h-2 bg-red-500 rounded-full mr-3 animate-pulse"></div><span class="font-medium">Waktu pembayaran hampir habis! Transaksi akan dibatalkan otomatis jika waktu habis.</span></div>';
        } else if (seconds <= 40) {
            statusElement.className = 'p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-800 text-sm';
            statusElement.innerHTML = '<div class="flex items-center"><div class="w-2 h-2 bg-yellow-500 rounded-full mr-3 animate-pulse"></div><span class="font-medium">Waktu pembayaran sedang berjalan...</span></div>';
        } else {
            statusElement.className = 'p-4 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm';
            statusElement.innerHTML = '<div class="flex items-center"><div class="w-2 h-2 bg-green-500 rounded-full mr-3 animate-pulse"></div><span class="font-medium">Waktu pembayaran sedang berjalan...</span></div>';
        }
    }
}

// ================================================
// TAMBAHAN BARU: Auto Cancel Function
// ================================================
function handleTimerExpiredWithAutoCancel() {
    console.log('AUTO CANCEL: Timer expired, starting auto cancel process');
    isTimerExpired = true;
    
    // Prevent multiple cancel calls
    if (isCancelInProgress) {
        console.log('AUTO CANCEL: Cancel already in progress, skipping');
        return;
    }
    
    isCancelInProgress = true;
    
    // Stop timer
    if (countdownTimer) {
        clearInterval(countdownTimer);
        countdownTimer = null;
        console.log('AUTO CANCEL: Timer interval cleared');
    }
    
    // Update UI to show expiration
    updateUIForExpiredTransaction();
    
    // Show auto cancel notification
    showAutoCancelNotification();
    
    // Call API to cancel transaction
    autoCancelTransaction();
}

function updateUIForExpiredTransaction() {
    console.log('AUTO CANCEL: Updating UI for expired transaction');
    
    // Update countdown display
    const countdownElement = document.getElementById('countdown');
    if (countdownElement) {
        countdownElement.innerHTML = '<span class="text-red-600">HABIS</span>';
    }
    
    // Update circle
    const circle = document.getElementById('timerCircle');
    if (circle) {
        circle.style.strokeDashoffset = '264'; // Fully empty
        circle.style.stroke = '#dc2626';
    }
    
    // Update status
    const statusElement = document.getElementById('timerStatus');
    if (statusElement) {
        statusElement.className = 'p-4 bg-red-50 border border-red-200 rounded-xl text-red-800 text-sm';
        statusElement.innerHTML = '<div class="flex items-center"><i class="fas fa-times-circle mr-3 text-red-600"></i><span class="font-medium">Waktu pembayaran telah habis. Transaksi sedang dibatalkan otomatis...</span></div>';
    }
    
    // Disable upload button
    const uploadBtn = document.getElementById('uploadBtn');
    if (uploadBtn) {
        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<i class="fas fa-times mr-2"></i>Waktu Habis';
        uploadBtn.className = 'w-full bg-gray-400 text-white font-bold py-4 px-6 rounded-xl cursor-not-allowed';
    }
}

function autoCancelTransaction() {
    console.log('AUTO CANCEL: Calling cancel API for transaction', transactionId);
    
    // Update auto cancel status
    const autoCancelStatus = document.getElementById('autoCancelStatus');
    if (autoCancelStatus) {
        autoCancelStatus.innerHTML = '<div class="flex items-center justify-center"><i class="fas fa-spinner fa-spin mr-3 text-lg"></i><span class="font-medium">Membatalkan transaksi dan mengembalikan status produk...</span></div>';
    }
    
    // PERBAIKAN: Gunakan URL manual yang lebih aman
    const cancelUrl = '/customer/pembeli/payment/' + transactionId + '/cancel-expired';
    
    // Call cancel API
    fetch(cancelUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('AUTO CANCEL: API response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('AUTO CANCEL: API response data:', data);
        
        if (data.success) {
            // Update status to show success
            if (autoCancelStatus) {
                autoCancelStatus.className = 'bg-green-50 border border-green-200 rounded-xl p-4 text-green-800 text-sm';
                autoCancelStatus.innerHTML = `
                    <div class="flex items-center justify-center">
                        <i class="fas fa-check-circle mr-3 text-lg"></i>
                        <span class="font-medium">
                            Transaksi berhasil dibatalkan. 
                            ${data.data.restored_products} produk dikembalikan, 
                            ${data.data.points_refunded} poin dikembalikan.
                        </span>
                    </div>
                `;
            }
            
            showNotification('Transaksi dibatalkan otomatis karena waktu pembayaran habis', 'info');
            
            // Redirect after 5 seconds
            setTimeout(() => {
                console.log('AUTO CANCEL: Redirecting to profile');
                window.location.href = data.redirect_url || '{{ route("pembeli.profile") }}';
            }, 5000);
            
        } else {
            console.error('AUTO CANCEL: API returned error:', data.error);
            
            if (autoCancelStatus) {
                autoCancelStatus.className = 'bg-red-50 border border-red-200 rounded-xl p-4 text-red-800 text-sm';
                autoCancelStatus.innerHTML = '<div class="flex items-center justify-center"><i class="fas fa-exclamation-triangle mr-3 text-lg"></i><span class="font-medium">Gagal membatalkan transaksi otomatis. Silakan refresh halaman.</span></div>';
            }
            
            showNotification('Gagal membatalkan transaksi otomatis', 'error');
        }
    })
    .catch(error => {
        console.error('AUTO CANCEL: API call failed:', error);
        
        if (autoCancelStatus) {
            autoCancelStatus.className = 'bg-red-50 border border-red-200 rounded-xl p-4 text-red-800 text-sm';
            autoCancelStatus.innerHTML = '<div class="flex items-center justify-center"><i class="fas fa-exclamation-triangle mr-3 text-lg"></i><span class="font-medium">Koneksi bermasalah. Silakan refresh halaman.</span></div>';
        }
        
        showNotification('Koneksi bermasalah saat membatalkan transaksi', 'error');
    })
    .finally(() => {
        isCancelInProgress = false;
    });
}

function showAutoCancelNotification() {
    const notification = document.createElement('div');
    notification.className = 'fixed top-4 right-4 z-50 p-6 bg-red-500 text-white rounded-2xl shadow-2xl max-w-sm animate-pulse';
    notification.innerHTML = `
        <div class="flex items-start">
            <i class="fas fa-exclamation-triangle text-2xl mr-3 mt-1"></i>
            <div class="flex-grow">
                <h4 class="font-bold mb-2">Waktu Pembayaran Habis</h4>
                <p class="text-sm mb-3">Transaksi sedang dibatalkan otomatis. Produk akan dikembalikan ke status tersedia dan poin dikembalikan ke akun Anda.</p>
                <p class="text-xs">Anda akan diarahkan kembali ke profil setelah proses selesai.</p>
            </div>
            <button onclick="this.parentElement.parentElement.remove()" class="text-white hover:text-gray-200 ml-2">
                <i class="fas fa-times"></i>
            </button>
        </div>
    `;
    
    document.body.appendChild(notification);
}

// Initialize countdown saat DOM ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('AUTO CANCEL: DOM loaded, checking timer conditions...');
    
    if (transactionStatus === 'menunggu_pembayaran') {
        // Jika sudah expired dari backend, langsung trigger auto cancel
        if (isInitiallyExpired || remainingSeconds <= 0) {
            console.log('AUTO CANCEL: Transaction already expired, triggering immediate cancel');
            setTimeout(() => {
                handleTimerExpiredWithAutoCancel();
            }, 1000); // Delay 1 detik untuk UI loading
        } else {
            console.log('AUTO CANCEL: Starting normal countdown');
            initializeCountdown();
        }
    } else {
        console.log('AUTO CANCEL: Timer not initialized - status:', transactionStatus);
    }
});

@else
console.log('AUTO CANCEL: Timer not initialized - status:', transactionStatus);

// Jika status bukan menunggu_pembayaran dan batal, auto redirect
@if($transaksi->status === 'batal')
setTimeout(() => {
    window.location.href = '{{ route("pembeli.profile") }}';
}, 5000);
@endif
@endif

// ================================================
// Upload Function - tetap sama seperti sebelumnya
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
    
    isUploadInProgress = true;
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
            
            // Stop countdown timer karena pembayaran sudah diupload
            if (countdownTimer) {
                clearInterval(countdownTimer);
                countdownTimer = null;
                console.log('AUTO CANCEL: Timer stopped due to successful upload');
            }
            
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
        uploadBtn.className = 'w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 shadow-lg hover:shadow-xl';
    }
}

// ================================================
// Image Functions - tetap sama
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
// Notification Function - improved
// ================================================
function showNotification(message, type = 'info') {
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-2xl shadow-2xl transition-all duration-300 max-w-sm ${
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
            } mr-3 text-xl"></i>
            <span class="flex-grow font-medium">${message}</span>
            <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200 transition-colors">
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
        console.log('AUTO CANCEL: Timer cleaned up on page unload');
    }
});
</script>

@endsection