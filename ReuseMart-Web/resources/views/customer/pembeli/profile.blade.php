@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-green-50 via-blue-50 to-purple-50 min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header with Avatar and Welcome -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <!-- Background Header -->
            <div class="bg-gradient-to-r from-green-500 via-green-600 to-blue-600 px-8 py-12 relative">
                <div class="absolute inset-0 bg-black opacity-10"></div>
                <div class="relative z-10">
                    <div class="flex flex-col md:flex-row items-center">
                        <!-- Avatar -->
                        <div class="mb-6 md:mb-0 md:mr-8">
                            <div class="w-24 h-24 bg-white/20 rounded-full flex items-center justify-center backdrop-blur-sm border-4 border-white/30 shadow-2xl">
                                <i class="fas fa-user text-white text-4xl"></i>
                            </div>
                        </div>
                        
                        <!-- User Info -->
                        <div class="text-center md:text-left flex-grow">
                            <h1 class="text-3xl font-bold text-white mb-2">
                                Selamat datang, {{ $pembeli->nama }}! 👋
                            </h1>
                            <p class="text-green-100 text-lg mb-4 flex items-center justify-center md:justify-start">
                                <i class="fas fa-envelope mr-2"></i>
                                {{ $pembeli->email }}
                            </p>
                            
                            <!-- Quick Stats -->
                            <div class="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                                <div class="bg-white/20 backdrop-blur-sm rounded-xl px-6 py-3 border border-white/30">
                                    <div class="flex items-center text-white">
                                        <i class="fas fa-star text-yellow-300 mr-3 text-xl"></i>
                                        <div>
                                            <p class="text-2xl font-bold">{{ number_format($pembeli->poin ?? 0) }}</p>
                                            <p class="text-sm text-green-100">Poin ReUseMart</p>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="bg-white/20 backdrop-blur-sm rounded-xl px-6 py-3 border border-white/30">
                                    <div class="flex items-center text-white">
                                        <i class="fas fa-shopping-bag text-blue-300 mr-3 text-xl"></i>
                                        <div>
                                            <p class="text-2xl font-bold">{{ count($transaksiPenjualan) }}</p>
                                            <p class="text-sm text-green-100">Transaksi Bulan Ini</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Belanja -->
            <a href="{{ route('produk.index') }}" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-green-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-400 to-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-green-500 transition-colors"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Belanja Sekarang</h3>
                <p class="text-sm text-gray-600">Jelajahi produk bekas berkualitas</p>
            </a>

            <!-- Histori Lengkap -->
            <a href="{{ route('pembeli.history') }}" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-blue-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-400 to-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-history text-white text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-blue-500 transition-colors"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Histori Lengkap</h3>
                <p class="text-sm text-gray-600">Lihat semua transaksi Anda</p>
            </a>

            <!-- Rating Produk -->
            <a href="{{ route('pembeli.rating.index') }}" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-yellow-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-orange-500 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-star text-white text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-yellow-500 transition-colors"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Beri Rating</h3>
                <p class="text-sm text-gray-600">Rating produk yang dibeli</p>
            </a>

            <!-- Kelola Alamat -->
            <a href="{{ route('pembeli.alamat.index') }}" class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 border border-gray-100 hover:border-purple-300 transform hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-400 to-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-map-marker-alt text-white text-xl"></i>
                    </div>
                    <i class="fas fa-arrow-right text-gray-400 group-hover:text-purple-500 transition-colors"></i>
                </div>
                <h3 class="font-semibold text-gray-800 mb-2">Kelola Alamat</h3>
                <p class="text-sm text-gray-600">Atur alamat pengiriman</p>
            </a>
        </div>

        <!-- Content Grid -->
        <div class="grid lg:grid-cols-3 gap-8">
            <!-- Left Column - Recent Transactions -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-clock text-blue-500 mr-3"></i>
                                Transaksi Terbaru
                            </h2>
                            <p class="text-sm text-gray-600 mt-1">5 transaksi pembelian terakhir</p>
                        </div>
                        <a href="{{ route('pembeli.history') }}" class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium text-sm bg-blue-50 hover:bg-blue-100 px-4 py-2 rounded-lg transition-colors">
                            Lihat Semua
                            <i class="fas fa-arrow-right ml-2"></i>
                        </a>
                    </div>
                    
                    @if(count($transaksiPenjualan) > 0)
                        <div class="space-y-4">
                            @foreach($transaksiPenjualan as $index => $transaksi)
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl p-4 border border-gray-200 hover:border-green-300 transition-colors group">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <!-- Transaction Number Badge -->
                                        <div class="w-10 h-10 bg-gradient-to-r from-green-400 to-green-600 rounded-full flex items-center justify-center text-white font-bold text-sm">
                                            #{{ $index + 1 }}
                                        </div>
                                        
                                        <div>
                                            <p class="font-semibold text-gray-800 group-hover:text-green-700 transition-colors">
                                                {{ Str::limit($transaksi->deskripsi, 40) }}
                                            </p>
                                            <div class="flex items-center space-x-4 mt-1">
                                                <span class="text-sm text-gray-600">
                                                    <i class="fas fa-calendar mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($transaksi->tanggalLunas)->format('d M Y') }}
                                                </span>
                                                <span class="text-sm font-medium text-green-600">
                                                    Rp {{ number_format($transaksi->hargaJual, 0, ',', '.') }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <a href="{{ route('pembeli.transaksi.detail', $transaksi->idTransaksiPenjualan) }}" 
                                       class="inline-flex items-center text-green-600 hover:text-green-800 bg-green-50 hover:bg-green-100 px-3 py-2 rounded-lg text-sm font-medium transition-colors">
                                        Detail
                                        <i class="fas fa-chevron-right ml-1 text-xs"></i>
                                    </a>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-shopping-bag text-gray-400 text-2xl"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-800 mb-2">Belum Ada Transaksi</h3>
                            <p class="text-gray-600 mb-6">Mulai berbelanja untuk melihat histori transaksi Anda</p>
                            <a href="{{ route('produk.index') }}" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Belanja Sekarang
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column - Info Cards -->
            <div class="space-y-6">
                <!-- Poin Card -->
                <div class="bg-gradient-to-br from-yellow-400 via-yellow-500 to-orange-500 rounded-2xl shadow-lg p-6 text-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold">Poin Reward</h3>
                        <i class="fas fa-star text-2xl"></i>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <p class="text-3xl font-bold">{{ number_format($pembeli->poin ?? 0) }}</p>
                            <p class="text-yellow-100 text-sm">Total Poin Anda</p>
                        </div>
                        <div class="bg-white/20 rounded-lg p-3 backdrop-blur-sm">
                            <p class="text-sm text-yellow-100 mb-1">Nilai Poin:</p>
                            <p class="font-semibold">≈ Rp {{ number_format(($pembeli->poin ?? 0) * 10, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Alamat Quick View -->
                <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="font-bold text-gray-800 flex items-center">
                            <i class="fas fa-map-marker-alt text-purple-500 mr-2"></i>
                            Alamat Saya
                        </h3>
                        <a href="{{ route('pembeli.alamat.index') }}" class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                            Kelola
                        </a>
                    </div>
                    <p class="text-gray-600 text-sm mb-4">Kelola alamat pengiriman untuk memudahkan checkout</p>
                    <a href="{{ route('pembeli.alamat.create') }}" class="inline-flex items-center w-full justify-center bg-purple-50 hover:bg-purple-100 text-purple-700 px-4 py-3 rounded-lg text-sm font-medium transition-colors border border-purple-200">
                        <i class="fas fa-plus mr-2"></i>
                        Tambah Alamat Baru
                    </a>
                </div>

                <!-- Tips Card -->
                <div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-2xl p-6 border border-blue-200">
                    <div class="flex items-center mb-4">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <i class="fas fa-lightbulb text-white"></i>
                        </div>
                        <h3 class="font-bold text-gray-800">Tips ReUseMart</h3>
                    </div>
                    <div class="space-y-3 text-sm text-gray-700">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <p>Belanja minimal Rp 1.5 juta untuk gratis ongkir</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <p>Tukar 100 poin = Rp 10.000 diskon</p>
                        </div>
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-500 mr-2 mt-0.5"></i>
                            <p>Bonus 20% poin untuk belanja > Rp 500K</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Custom animations */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fadeInUp {
    animation: fadeInUp 0.6s ease-out;
}

/* Gradient text */
.gradient-text {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Card hover effects */
.card-hover {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Improved button styles */
.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border: none;
    transition: all 0.3s ease;
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.4);
}
</style>

@endsection