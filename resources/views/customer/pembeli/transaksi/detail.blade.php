{{-- Template untuk resources/views/customer/pembeli/transaksi/detail.blade.php --}}
@extends('layouts.customer')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-green-500 px-6 py-4">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-xl font-bold text-white">Detail Transaksi</h1>
                        <p class="text-green-100 text-sm">ID Transaksi: #{{ $transaksi->idTransaksiPenjualan }}</p>
                    </div>
                    <a href="{{ route('pembeli.profile') }}" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6">
                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Left Column - Transaksi Info -->
                    <div>
                        <!-- Status Transaksi -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Status Transaksi</h3>
                            <div class="flex items-center mb-4">
                                @php
                                $statusConfig = [
                                    'menunggu_pembayaran' => ['color' => 'yellow', 'icon' => 'fa-clock', 'text' => 'Menunggu Pembayaran'],
                                    'menunggu_verifikasi' => ['color' => 'blue', 'icon' => 'fa-search', 'text' => 'Sedang Diverifikasi'],
                                    'disiapkan' => ['color' => 'green', 'icon' => 'fa-box', 'text' => 'Sedang Disiapkan'],
                                    'kirim' => ['color' => 'blue', 'icon' => 'fa-truck', 'text' => 'Sedang Dikirim'],
                                    'diambil' => ['color' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Sudah Diambil'],
                                    'terjual' => ['color' => 'green', 'icon' => 'fa-check-circle', 'text' => 'Selesai'],
                                    'batal' => ['color' => 'red', 'icon' => 'fa-times-circle', 'text' => 'Dibatalkan'],
                                ];
                                
                                $currentStatus = $statusConfig[$transaksi->status] ?? ['color' => 'gray', 'icon' => 'fa-question', 'text' => 'Status Tidak Dikenal'];
                                @endphp
                                
                                <div class="w-3 h-3 bg-{{ $currentStatus['color'] }}-500 rounded-full mr-3"></div>
                                <span class="text-{{ $currentStatus['color'] }}-600 font-medium">
                                    <i class="fas {{ $currentStatus['icon'] }} mr-2"></i>{{ $currentStatus['text'] }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-600">Tanggal Pesan:</span>
                                    <p class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }}</p>
                                </div>
                                @if($transaksi->tanggalLunas)
                                <div>
                                    <span class="text-gray-600">Tanggal Lunas:</span>
                                    <p class="font-medium">{{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d M Y H:i') }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        <!-- ALAMAT PENGIRIMAN - TAMBAHAN BARU -->
                        <div class="bg-gray-50 rounded-lg p-4 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-3">Detail Pengiriman</h3>
                            
                            <div class="space-y-3">
                                <div class="flex items-start">
                                    <i class="fas fa-truck text-gray-500 mt-1 mr-3 w-4"></i>
                                    <div>
                                        <span class="text-sm text-gray-600">Metode Pengiriman:</span>
                                        <p class="font-medium">
                                            @if($transaksi->metodePengiriman === 'kurir')
                                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                                                    <i class="fas fa-shipping-fast mr-1"></i>Kurir ReUseMart
                                                </span>
                                            @else
                                                <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                                                    <i class="fas fa-store mr-1"></i>Ambil Sendiri
                                                </span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex items-start">
                                    <i class="fas fa-map-marker-alt text-gray-500 mt-1 mr-3 w-4"></i>
                                    <div class="flex-grow">
                                        <span class="text-sm text-gray-600">Alamat:</span>
                                        @if($alamatPengiriman)
                                            <div class="bg-white border border-gray-200 rounded p-3 mt-1">
                                                <div class="flex items-center mb-1">
                                                    <span class="font-medium text-gray-900">{{ $alamatPengiriman['jenis'] ?? 'Alamat' }}</span>
                                                </div>
                                                <p class="text-gray-600 text-sm">{{ $alamatPengiriman['alamatLengkap'] ?? 'Alamat tidak tersedia' }}</p>
                                            </div>
                                        @else
                                            <p class="text-gray-500 text-sm italic">Data alamat tidak tersedia</p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Ringkasan Pembayaran -->
                    <div>
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Pembayaran</h3>
                            
                            @php
                            $subtotal = $detailTransaksi->sum(function($detail) {
                                return $detail->produk->hargaJual ?? 0;
                            });
                            
                            // Hitung ongkir berdasarkan metode dan subtotal
                            $ongkir = 0;
                            if($transaksi->metodePengiriman === 'kurir') {
                                $ongkir = $subtotal >= 1500000 ? 0 : 100000;
                            }
                            
                            $total = $subtotal + $ongkir;
                            @endphp
                            
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal ({{ $detailTransaksi->count() }} item)</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Ongkos Kirim</span>
                                    <span>{{ $ongkir == 0 ? 'GRATIS' : 'Rp ' . number_format($ongkir, 0, ',', '.') }}</span>
                                </div>
                                
                                <hr class="my-2">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span class="text-green-600">Rp {{ number_format($total, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Item Pesanan -->
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-semibold mb-4">Item Pesanan</h3>
                    <div class="space-y-3">
                        @foreach($detailTransaksi as $detail)
                        <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-lg">
                            @php
                            $gambarArray = $detail->produk->gambar ? explode(',', $detail->produk->gambar) : ['default.jpg'];
                            $thumbnail = $gambarArray[0];
                            @endphp
                            
                            <div class="h-16 w-16 rounded overflow-hidden bg-gray-200 flex items-center justify-center flex-shrink-0">
                                <img class="h-full w-full object-cover"
                                    src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                    alt="{{ $detail->produk->deskripsi }}"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="hidden h-full w-full bg-gray-200 items-center justify-center">
                                    <i class="fas fa-image text-gray-400"></i>
                                </div>
                            </div>
                            
                            <div class="flex-grow">
                                <h4 class="font-medium text-gray-900">{{ $detail->produk->deskripsi }}</h4>
                                <p class="text-sm text-gray-500">{{ $detail->produk->kategori->nama ?? 'Kategori' }}</p>
                                @if($detail->produk->tanggalGaransi && \Carbon\Carbon::parse($detail->produk->tanggalGaransi)->isFuture())
                                <span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full mt-1">
                                    Bergaransi
                                </span>
                                @endif
                            </div>
                            
                            <div class="text-right">
                                <span class="text-lg font-semibold text-gray-900">
                                    Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}
                                </span>
                                <p class="text-sm text-gray-500">Qty: 1</p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection