@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-blue-50 to-purple-50 min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header dengan informasi utama -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 via-purple-600 to-indigo-600 px-8 py-6">
                <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between">
                    <div class="mb-4 lg:mb-0">
                        <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-receipt mr-3"></i>
                            Detail Transaksi #{{ $transaksi->idTransaksiPenjualan }}
                        </h1>
                        <div class="flex flex-wrap items-center space-x-6 text-blue-100">
                            <span class="flex items-center">
                                <i class="fas fa-calendar mr-2"></i>
                                {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }}
                            </span>
                            <span class="flex items-center">
                                @php
                                $statusConfig = [
                                    'menunggu_pembayaran' => ['color' => 'yellow', 'icon' => 'fa-clock', 'text' => 'Menunggu Pembayaran'],
                                    'menunggu_verifikasi' => ['color' => 'blue', 'icon' => 'fa-search', 'text' => 'Diverifikasi'],
                                    'disiapkan' => ['color' => 'green', 'icon' => 'fa-box', 'text' => 'Disiapkan'],
                                    'kirim' => ['color' => 'blue', 'icon' => 'fa-truck', 'text' => 'Dikirim'],
                                    'diambil' => ['color' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Diambil'],
                                    'terjual' => ['color' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Selesai'],
                                    'batal' => ['color' => 'red', 'icon' => 'fa-times-circle', 'text' => 'Dibatalkan'],
                                ];
                                $currentStatus = $statusConfig[$transaksi->status] ?? ['color' => 'gray', 'icon' => 'fa-question', 'text' => 'Unknown'];
                                @endphp
                                <i class="fas {{ $currentStatus['icon'] }} mr-2"></i>
                                {{ $currentStatus['text'] }}
                            </span>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('pembeli.history') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all backdrop-blur-sm border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Histori
                        </a>
                        <a href="{{ route('pembeli.profile') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-user mr-2"></i> Profil
                        </a>
                    </div>
                </div>
            </div>

            <!-- Visual Progress Tracker -->
            <div class="px-8 py-6 bg-gradient-to-r from-gray-50 to-blue-50">
                @php
                $steps = [
                    ['key' => 'pesanan', 'icon' => 'fa-shopping-cart', 'label' => 'Pesanan Dibuat', 'always_active' => true],
                    ['key' => 'pembayaran', 'icon' => 'fa-credit-card', 'label' => 'Pembayaran'],
                    ['key' => 'proses', 'icon' => 'fa-cogs', 'label' => 'Diproses'],
                    ['key' => 'kirim', 'icon' => 'fa-truck', 'label' => 'Pengiriman'],
                    ['key' => 'selesai', 'icon' => 'fa-check-circle', 'label' => 'Selesai']
                ];
                
                $currentStep = match($transaksi->status) {
                    'menunggu_pembayaran' => 1,
                    'menunggu_verifikasi' => 2,
                    'disiapkan' => 3,
                    'kirim', 'pengambilan' => 4,
                    'diambil', 'terjual' => 5,
                    'batal' => 0,
                    default => 1
                };
                @endphp
                
                <div class="relative">
                    <!-- Progress Line -->
                    <div class="absolute top-6 left-0 right-0 h-1 bg-gray-200 rounded-full"></div>
                    <div class="absolute top-6 left-0 h-1 bg-gradient-to-r from-green-400 to-blue-500 rounded-full transition-all duration-1000" 
                         style="width: {{ $currentStep > 0 ? (($currentStep - 1) / (count($steps) - 1)) * 100 : 0 }}%"></div>
                    
                    <!-- Steps -->
                    <div class="relative flex justify-between">
                        @foreach($steps as $index => $step)
                        @php
                        $isActive = $step['always_active'] ?? false || $index < $currentStep || ($transaksi->status === 'batal' && $index === 0);
                        $isCurrent = $index === ($currentStep - 1);
                        @endphp
                        <div class="flex flex-col items-center">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center transition-all duration-300 mb-3 {{ 
                                $isActive ? 'bg-gradient-to-r from-green-400 to-blue-500 text-white shadow-lg scale-110' : 
                                ($isCurrent ? 'bg-blue-500 text-white shadow-lg' : 'bg-gray-300 text-gray-500') 
                            }}">
                                <i class="fas {{ $step['icon'] }} text-lg"></i>
                                @if($isCurrent)
                                <div class="absolute w-12 h-12 rounded-full bg-blue-400 animate-ping opacity-20"></div>
                                @endif
                            </div>
                            <span class="text-sm font-medium text-center {{ $isActive ? 'text-blue-600' : 'text-gray-500' }}">
                                {{ $step['label'] }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid lg:grid-cols-12 gap-8">
            <!-- Left Column - Status & Timeline (4 columns) -->
            <div class="lg:col-span-4 space-y-6">
                <!-- Status Detail Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-blue-400 to-purple-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas {{ $currentStatus['icon'] }} text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">Status Transaksi</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600">Status Saat Ini:</span>
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $currentStatus['color'] }}-100 text-{{ $currentStatus['color'] }}-700">
                                <i class="fas {{ $currentStatus['icon'] }} mr-2"></i>{{ $currentStatus['text'] }}
                            </span>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tanggal Pesan:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }}</span>
                            </div>
                            @if($transaksi->tanggalLunas)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tanggal Lunas:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d M Y H:i') }}</span>
                            </div>
                            @endif
                            @if($transaksi->tanggalKirim)
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Tanggal Kirim:</span>
                                <span class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalKirim)->format('d M Y H:i') }}</span>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Shipping Info Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-blue-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-truck text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">Informasi Pengiriman</h3>
                    </div>
                    
                    <div class="space-y-4">
                        <div class="flex items-center p-3 bg-blue-50 rounded-lg">
                            <i class="fas {{ $transaksi->metodePengiriman === 'kurir' ? 'fa-truck' : 'fa-store' }} text-blue-600 mr-3"></i>
                            <div>
                                <p class="font-medium text-gray-800">
                                    @if($transaksi->metodePengiriman === 'kurir')
                                        Kurir ReUseMart
                                    @else
                                        Ambil Sendiri
                                    @endif
                                </p>
                                <p class="text-sm text-gray-600">
                                    @if($transaksi->metodePengiriman === 'kurir')
                                        Dikirim ke alamat tujuan
                                    @else
                                        Diambil di toko (08:00 - 20:00)
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="bg-gray-50 rounded-lg p-4">
                            <div class="flex items-start">
                                <i class="fas fa-map-marker-alt text-green-600 mr-3 mt-1"></i>
                                <div class="flex-grow">
                                    @if($alamatPengiriman)
                                        <p class="font-medium text-gray-800">{{ $alamatPengiriman['jenis'] ?? 'Alamat' }}</p>
                                        <p class="text-sm text-gray-600 mt-1">{{ $alamatPengiriman['alamatLengkap'] ?? 'Alamat tidak tersedia' }}</p>
                                    @else
                                        <p class="text-sm text-gray-500 italic">Data alamat tidak tersedia</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Point Info Card -->
                @if($transaksi->poinDidapat > 0 || $transaksi->poinDigunakan > 0)
                <div class="bg-gradient-to-br from-yellow-50 to-orange-50 rounded-2xl shadow-lg p-6 border border-yellow-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-star text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">Poin ReUseMart</h3>
                    </div>
                    
                    <div class="space-y-3">
                        @if($transaksi->poinDigunakan > 0)
                        <div class="flex justify-between items-center p-3 bg-white/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-minus-circle text-red-500 mr-2"></i>
                                <span class="text-sm text-gray-700">Poin Digunakan</span>
                            </div>
                            <span class="font-bold text-red-600">-{{ number_format($transaksi->poinDigunakan) }}</span>
                        </div>
                        @endif
                        
                        @if($transaksi->poinDidapat > 0)
                        <div class="flex justify-between items-center p-3 bg-white/50 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-plus-circle text-green-500 mr-2"></i>
                                <span class="text-sm text-gray-700">Poin Didapat</span>
                            </div>
                            <span class="font-bold text-green-600">+{{ number_format($transaksi->poinDidapat) }}</span>
                        </div>
                        @endif
                        
                        <div class="pt-3 border-t border-yellow-200">
                            @if(in_array($transaksi->status, ['disiapkan', 'kirim', 'diambil', 'terjual']))
                                <div class="flex items-center text-sm text-green-700 bg-green-100 px-3 py-2 rounded-lg">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    Poin telah ditambahkan ke akun Anda
                                </div>
                            @elseif($transaksi->status === 'batal')
                                <div class="flex items-center text-sm text-red-700 bg-red-100 px-3 py-2 rounded-lg">
                                    <i class="fas fa-times-circle mr-2"></i>
                                    Poin tidak diberikan (transaksi dibatalkan)
                                </div>
                            @else
                                <div class="flex items-center text-sm text-yellow-700 bg-yellow-100 px-3 py-2 rounded-lg">
                                    <i class="fas fa-clock mr-2"></i>
                                    Menunggu transaksi selesai
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Middle Column - Items (5 columns) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-shopping-bag text-blue-500 mr-3"></i>
                            Item Pesanan
                        </h3>
                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm font-medium">
                            {{ $detailTransaksi->count() }} item
                        </span>
                    </div>
                    
                    <div class="space-y-4 max-h-96 overflow-y-auto">
                        @foreach($detailTransaksi as $detail)
                        <div class="group bg-gradient-to-r from-gray-50 to-blue-50 rounded-xl p-4 hover:from-blue-50 hover:to-purple-50 transition-all duration-300 border border-gray-200 hover:border-blue-300">
                            <div class="flex items-center space-x-4">
                                @php
                                $gambarArray = $detail->produk->gambar ? explode(',', $detail->produk->gambar) : ['default.jpg'];
                                $thumbnail = $gambarArray[0];
                                @endphp
                                
                                <!-- Product Image -->
                                <div class="relative w-16 h-16 rounded-xl overflow-hidden bg-gray-200 flex-shrink-0">
                                    <img class="w-full h-full object-cover transition-transform group-hover:scale-110"
                                        src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                        alt="{{ $detail->produk->deskripsi }}"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="hidden w-full h-full bg-gray-200 items-center justify-center">
                                        <i class="fas fa-image text-gray-400"></i>
                                    </div>
                                    
                                    <!-- Quality Badge -->
                                    @if($detail->produk->tanggalGaransi && \Carbon\Carbon::parse($detail->produk->tanggalGaransi)->isFuture())
                                    <div class="absolute -top-1 -right-1 bg-green-500 text-white text-xs px-2 py-1 rounded-full font-bold">
                                        GAR
                                    </div>
                                    @endif
                                </div>
                                
                                <!-- Product Info -->
                                <div class="flex-grow min-w-0">
                                    <h4 class="font-semibold text-gray-900 group-hover:text-blue-700 transition-colors">
                                        {{ $detail->produk->deskripsi }}
                                    </h4>
                                    <div class="flex items-center space-x-4 mt-2">
                                        <span class="text-sm text-gray-600 bg-white px-2 py-1 rounded-md">
                                            {{ $detail->produk->kategori->nama ?? 'Kategori' }}
                                        </span>
                                        @if($detail->produk->tanggalGaransi && \Carbon\Carbon::parse($detail->produk->tanggalGaransi)->isFuture())
                                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-medium">
                                            <i class="fas fa-shield-alt mr-1"></i>Bergaransi
                                        </span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Price -->
                                <div class="text-right">
                                    <span class="text-lg font-bold text-gray-900">
                                        Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}
                                    </span>
                                    <p class="text-sm text-gray-600">Qty: 1</p>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column - Payment Summary (3 columns) -->
            <div class="lg:col-span-3">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-2xl shadow-lg p-6 border border-gray-100 sticky top-6">
                    <div class="flex items-center mb-6">
                        <div class="w-10 h-10 bg-gradient-to-r from-purple-400 to-pink-500 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-calculator text-white"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-800">Ringkasan Pembayaran</h3>
                    </div>
                    
                    @php
                    $subtotal = $detailTransaksi->sum(function($detail) {
                        return $detail->produk->hargaJual ?? 0;
                    });
                    
                    $ongkir = 0;
                    if($transaksi->metodePengiriman === 'kurir') {
                        $ongkir = $subtotal >= 1500000 ? 0 : 100000;
                    }
                    
                    $diskonPoin = $transaksi->poinDigunakan * 10;
                    $total = $subtotal + $ongkir - $diskonPoin;
                    @endphp
                    
                    <div class="space-y-4">
                        <!-- Subtotal -->
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Subtotal ({{ $detailTransaksi->count() }} item)</span>
                            <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <!-- Ongkir -->
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Ongkos Kirim</span>
                            <span class="font-semibold">
                                @if($ongkir == 0)
                                    <span class="text-green-600 bg-green-100 px-2 py-1 rounded-md text-sm">GRATIS</span>
                                @else
                                    Rp {{ number_format($ongkir, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        
                        <!-- Diskon Poin -->
                        @if($transaksi->poinDigunakan > 0)
                        <div class="flex justify-between items-center py-2">
                            <span class="text-gray-600">Diskon Poin ({{ number_format($transaksi->poinDigunakan) }})</span>
                            <span class="font-semibold text-yellow-600">- Rp {{ number_format($diskonPoin, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        
                        <!-- Divider -->
                        <hr class="border-gray-300 my-4">
                        
                        <!-- Total -->
                        <div class="flex justify-between items-center py-3 bg-gradient-to-r from-blue-50 to-purple-50 rounded-lg px-4">
                            <span class="text-lg font-bold text-gray-800">Total Pembayaran</span>
                            <span class="text-xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">
                                Rp {{ number_format($total, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <!-- Payment Method Info -->
                        <div class="mt-6 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
                            <div class="flex items-center mb-2">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <span class="font-medium text-blue-800">Metode Pembayaran</span>
                            </div>
                            <p class="text-sm text-blue-700">
                                Transfer Bank ke rekening ReUseMart
                            </p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="mt-6 space-y-3">
                            @if($transaksi->status === 'terjual')
                            <a href="{{ route('pembeli.rating.index') }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white font-medium rounded-lg transition-all">
                                <i class="fas fa-star mr-2"></i>
                                Beri Rating Produk
                            </a>
                            @endif
                            
                            <a href="{{ route('produk.index') }}" class="w-full inline-flex items-center justify-center px-4 py-3 bg-gradient-to-r from-green-500 to-blue-600 hover:from-green-600 hover:to-blue-700 text-white font-medium rounded-lg transition-all">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Belanja Lagi
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for items list */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%);
}

/* Animation for progress steps */
@keyframes stepPulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

.animate-step-pulse {
    animation: stepPulse 2s infinite;
}

/* Gradient text effect */
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Enhanced hover effects */
.hover-lift {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
}

/* Card gradients */
.card-gradient {
    background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
}

/* Button effects */
.btn-gradient {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: all 0.3s ease;
}

.btn-gradient:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}
</style>

@endsection