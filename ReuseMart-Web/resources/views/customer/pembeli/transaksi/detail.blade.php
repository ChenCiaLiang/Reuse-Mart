{{-- Improved UI untuk resources/views/customer/pembeli/transaksi/detail.blade.php --}}
@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Compact Header -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden mb-6">
            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-bold text-white">Detail Transaksi</h1>
                        <p class="text-green-100 text-sm">ID: #{{ $transaksi->idTransaksiPenjualan }}</p>
                    </div>
                    <a href="{{ route('pembeli.profile') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm transition-all">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Visual Progress Stepper -->
            <div class="px-6 py-4 bg-gray-50">
                @php
                $steps = [
                    ['key' => 'pesanan', 'icon' => 'fa-shopping-cart', 'label' => 'Pesanan', 'always_active' => true],
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
                
                <div class="flex items-center justify-between">
                    @foreach($steps as $index => $step)
                    @php
                    $isActive = $step['always_active'] ?? false || $index < $currentStep || ($transaksi->status === 'batal' && $index === 0);
                    $isCurrent = $index === ($currentStep - 1);
                    @endphp
                    <div class="flex items-center {{ $index < count($steps) - 1 ? 'flex-1' : '' }}">
                        <!-- Step Circle -->
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center transition-all duration-300 {{ 
                                $isActive ? 'bg-green-500 text-white shadow-lg' : 
                                ($isCurrent ? 'bg-blue-500 text-white' : 'bg-gray-300 text-gray-500') 
                            }}">
                                <i class="fas {{ $step['icon'] }} text-sm"></i>
                            </div>
                            @if($isCurrent)
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-blue-500 rounded-full animate-pulse"></div>
                            @endif
                        </div>
                        
                        <!-- Step Label -->
                        <span class="ml-2 text-sm font-medium {{ $isActive ? 'text-green-600' : 'text-gray-500' }}">
                            {{ $step['label'] }}
                        </span>
                        
                        <!-- Connector Line -->
                        @if($index < count($steps) - 1)
                        <div class="flex-1 h-0.5 mx-4 {{ $index < $currentStep - 1 ? 'bg-green-500' : 'bg-gray-300' }}"></div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Main Content Grid - 3 Columns for better space utilization -->
        <div class="grid lg:grid-cols-12 gap-6">
            <!-- Left Column - Status & Info (4 columns) -->
            <div class="lg:col-span-4 space-y-4">
                <!-- Compact Status Card -->
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-800">Status</h3>
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
                        
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $currentStatus['color'] }}-100 text-{{ $currentStatus['color'] }}-700">
                            <i class="fas {{ $currentStatus['icon'] }} mr-2"></i>{{ $currentStatus['text'] }}
                        </span>
                    </div>
                    
                    <div class="grid grid-cols-1 gap-3 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal Pesan:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }}</span>
                        </div>
                        @if($transaksi->tanggalLunas)
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal Lunas:</span>
                            <span class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d M Y H:i') }}</span>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- Compact Shipping Info -->
                <div class="bg-white rounded-xl shadow-sm p-4">
                    <h3 class="font-semibold text-gray-800 mb-3">Pengiriman</h3>
                    
                    <div class="space-y-3">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                                <i class="fas fa-truck text-blue-600 text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium">
                                    @if($transaksi->metodePengiriman === 'kurir')
                                        Kurir ReUseMart
                                    @else
                                        Ambil Sendiri
                                    @endif
                                </p>
                            </div>
                        </div>
                        
                        <div class="flex items-start">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center mr-3 mt-0.5">
                                <i class="fas fa-map-marker-alt text-green-600 text-sm"></i>
                            </div>
                            <div class="flex-grow">
                                @if($alamatPengiriman)
                                    <p class="text-sm font-medium">{{ $alamatPengiriman['jenis'] ?? 'Alamat' }}</p>
                                    <p class="text-xs text-gray-600 mt-1">{{ $alamatPengiriman['alamatLengkap'] ?? 'Alamat tidak tersedia' }}</p>
                                @else
                                    <p class="text-sm text-gray-500 italic">Data alamat tidak tersedia</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Compact Point Info -->
                @if($transaksi->poinDidapat > 0 || $transaksi->poinDigunakan > 0)
                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl shadow-sm p-4 border border-yellow-200">
                    <div class="flex items-center mb-3">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center mr-3">
                            <i class="fas fa-star text-yellow-600 text-sm"></i>
                        </div>
                        <h3 class="font-semibold text-gray-800">Poin ReUseMart</h3>
                    </div>
                    
                    <div class="space-y-2">
                        @if($transaksi->poinDigunakan > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Poin Digunakan</span>
                            <span class="font-medium text-yellow-600">{{ number_format($transaksi->poinDigunakan) }}</span>
                        </div>
                        @endif
                        
                        @if($transaksi->poinDidapat > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Poin Didapat</span>
                            <span class="font-medium text-green-600">{{ number_format($transaksi->poinDidapat) }}</span>
                        </div>
                        @endif
                        
                        <div class="pt-2 border-t border-yellow-200">
                            @if(in_array($transaksi->status, ['disiapkan', 'kirim', 'diambil', 'terjual']))
                                <span class="inline-flex items-center text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">
                                    <i class="fas fa-check mr-1"></i>Poin diberikan
                                </span>
                            @elseif($transaksi->status === 'batal')
                                <span class="inline-flex items-center text-xs bg-red-100 text-red-800 px-2 py-1 rounded-full">
                                    <i class="fas fa-times mr-1"></i>Poin tidak diberikan
                                </span>
                            @else
                                <span class="inline-flex items-center text-xs bg-yellow-100 text-yellow-800 px-2 py-1 rounded-full">
                                    <i class="fas fa-clock mr-1"></i>Menunggu selesai
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Middle Column - Items (5 columns) -->
            <div class="lg:col-span-5">
                <div class="bg-white rounded-xl shadow-sm p-4 h-fit">
                    <h3 class="font-semibold text-gray-800 mb-4">Item Pesanan ({{ $detailTransaksi->count() }})</h3>
                    
                    <div class="space-y-3 max-h-96 overflow-y-auto">
                        @foreach($detailTransaksi as $detail)
                        <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                            @php
                            $gambarArray = $detail->produk->gambar ? explode(',', $detail->produk->gambar) : ['default.jpg'];
                            $thumbnail = $gambarArray[0];
                            @endphp
                            
                            <div class="w-12 h-12 rounded-lg overflow-hidden bg-gray-200 flex-shrink-0">
                                <img class="w-full h-full object-cover"
                                    src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                    alt="{{ $detail->produk->deskripsi }}"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="hidden w-full h-full bg-gray-200 items-center justify-center">
                                    <i class="fas fa-image text-gray-400 text-xs"></i>
                                </div>
                            </div>
                            
                            <div class="flex-grow min-w-0">
                                <h4 class="font-medium text-gray-900 text-sm truncate">{{ $detail->produk->deskripsi }}</h4>
                                <p class="text-xs text-gray-500">{{ $detail->produk->kategori->nama ?? 'Kategori' }}</p>
                                <div class="flex items-center mt-1">
                                    @if($detail->produk->tanggalGaransi && \Carbon\Carbon::parse($detail->produk->tanggalGaransi)->isFuture())
                                    <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-0.5 rounded-full">
                                        Garansi
                                    </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="text-right">
                                <span class="font-semibold text-gray-900 text-sm">
                                    Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}
                                </span>
                                <p class="text-xs text-gray-500">Qty: 1</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Right Column - Payment Summary (3 columns) -->
            <div class="lg:col-span-3">
                <div class="bg-gradient-to-br from-white to-gray-50 rounded-xl shadow-sm p-4 border">
                    <h3 class="font-semibold text-gray-800 mb-4">Ringkasan</h3>
                    
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
                    
                    <div class="space-y-3">
                        <!-- Subtotal -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Subtotal</span>
                            <span class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                        
                        <!-- Ongkir -->
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Ongkir</span>
                            <span class="font-medium">
                                @if($ongkir == 0)
                                    <span class="text-green-600">GRATIS</span>
                                @else
                                    Rp {{ number_format($ongkir, 0, ',', '.') }}
                                @endif
                            </span>
                        </div>
                        
                        <!-- Diskon Poin -->
                        @if($transaksi->poinDigunakan > 0)
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Diskon Poin</span>
                            <span class="font-medium text-yellow-600">- Rp {{ number_format($diskonPoin, 0, ',', '.') }}</span>
                        </div>
                        @endif
                        
                        <!-- Divider -->
                        <hr class="border-gray-200">
                        
                        <!-- Total -->
                        <div class="flex justify-between">
                            <span class="font-semibold text-gray-800">Total</span>
                            <span class="font-bold text-lg text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        
                        <!-- Payment Method Info -->
                        <div class="mt-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                            <div class="flex items-center">
                                <i class="fas fa-info-circle text-blue-600 mr-2"></i>
                                <span class="text-sm text-blue-800">
                                    @if($transaksi->metodePengiriman === 'kurir')
                                        Pembayaran dengan kurir ReUseMart
                                    @else
                                        Pembayaran saat pengambilan
                                    @endif
                                </span>
                            </div>
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
    width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Hover effects */
.hover\:bg-gray-100:hover {
    background-color: #f3f4f6;
}

/* Animation for step indicator */
@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>

@endsection