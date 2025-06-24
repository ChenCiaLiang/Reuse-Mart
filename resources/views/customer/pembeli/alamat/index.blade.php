@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 min-h-screen py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Header Section -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 via-indigo-600 to-purple-600 px-8 py-6">
                <div class="flex flex-col md:flex-row items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-white mb-2 flex items-center">
                            <i class="fas fa-map-marker-alt mr-3"></i>
                            Kelola Alamat Pengiriman
                        </h1>
                        <p class="text-blue-100">Atur dan kelola alamat untuk pengiriman yang lebih mudah</p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <a href="{{ route('pembeli.profile') }}" class="bg-white/20 hover:bg-white/30 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all backdrop-blur-sm border border-white/30">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Profil
                        </a>
                        <a href="{{ route('pembeli.alamat.create') }}" class="bg-white text-blue-600 hover:bg-blue-50 px-4 py-2 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-plus mr-2"></i> Tambah Alamat
                        </a>
                    </div>
                </div>
            </div>
        </div>

        @if (session('success'))
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 border-l-4 border-green-400 p-4 mb-6 rounded-r-lg shadow-sm animate-fadeIn">
            <div class="flex items-center">
                <div class="w-6 h-6 bg-green-400 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-check text-white text-sm"></i>
                </div>
                <p class="text-green-700 font-medium">{{ session('success') }}</p>
            </div>
        </div>
        @endif

        <!-- Search and Add Section -->
        <div class="bg-white rounded-2xl shadow-lg p-6 mb-8 border border-gray-100">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center text-gray-600">
                    <i class="fas fa-info-circle text-blue-500 mr-3 text-lg"></i>
                    <span class="text-sm">
                        Anda memiliki <span class="font-semibold text-blue-600">{{ $alamat->count() }}</span> alamat tersimpan
                    </span>
                </div>
                
                <form action="{{ route('pembeli.alamat.search') }}" method="GET" class="flex w-full md:w-auto">
                    <div class="relative flex-grow md:w-80">
                        <input type="text" name="search" placeholder="Cari berdasarkan jenis atau alamat..." 
                               value="{{ $search ?? '' }}" 
                               class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-l-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors">
                        <div class="absolute inset-y-0 right-0 flex items-center pr-3">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                    </div>
                    <button type="submit" class="bg-gradient-to-r from-blue-500 to-indigo-600 hover:from-blue-600 hover:to-indigo-700 text-white px-6 py-3 rounded-r-xl font-medium transition-all transform hover:scale-105">
                        Cari
                    </button>
                </form>
            </div>
        </div>

        <!-- Alamat Grid -->
        @if($alamat->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
            @foreach ($alamat as $a)
            <div class="group bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 overflow-hidden border {{ $a->statusDefault ? 'border-green-400 ring-2 ring-green-100' : 'border-gray-200 hover:border-blue-300' }} transform hover:-translate-y-1">
                
                <!-- Card Header -->
                <div class="relative px-6 pt-6 pb-4">
                    <!-- Default Badge -->
                    @if ($a->statusDefault)
                    <div class="absolute top-4 right-4">
                        <div class="bg-gradient-to-r from-green-400 to-emerald-500 text-white text-xs px-3 py-1 rounded-full font-bold flex items-center shadow-lg">
                            <i class="fas fa-star mr-1"></i>
                            UTAMA
                        </div>
                    </div>
                    @endif
                    
                    <!-- Address Type -->
                    <div class="flex items-center mb-3">
                        <div class="w-10 h-10 bg-gradient-to-r {{ $a->statusDefault ? 'from-green-400 to-emerald-500' : 'from-blue-400 to-indigo-500' }} rounded-xl flex items-center justify-center mr-3 group-hover:scale-110 transition-transform">
                            <i class="fas {{ $a->jenis === 'Rumah' ? 'fa-home' : ($a->jenis === 'Kantor' ? 'fa-building' : 'fa-map-marker-alt') }} text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-lg text-gray-800 group-hover:text-blue-700 transition-colors">
                                {{ $a->jenis }}
                            </h3>
                            <p class="text-sm text-gray-500">Alamat Pengiriman</p>
                        </div>
                    </div>
                </div>

                <!-- Address Content -->
                <div class="px-6 pb-4">
                    <div class="bg-gray-50 rounded-xl p-4 mb-4 group-hover:bg-blue-50 transition-colors">
                        <p class="text-gray-700 leading-relaxed">{{ $a->alamatLengkap }}</p>
                    </div>
                </div>

                <!-- Card Actions -->
                <div class="px-6 pb-6">
                    <div class="flex space-x-3">
                        <a href="{{ route('pembeli.alamat.show', $a->idAlamat) }}" 
                           class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-eye mr-2"></i>
                            Detail
                        </a>
                        <a href="{{ route('pembeli.alamat.edit', $a->idAlamat) }}" 
                           class="flex-1 inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-yellow-400 to-orange-500 hover:from-yellow-500 hover:to-orange-600 text-white rounded-lg text-sm font-medium transition-all">
                            <i class="fas fa-edit mr-2"></i>
                            Edit
                        </a>
                        <form action="{{ route('pembeli.alamat.destroy', $a->idAlamat) }}" method="POST" class="inline-block"
                              onsubmit="return confirmDelete('{{ $a->jenis }}')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-red-400 to-red-600 hover:from-red-500 hover:to-red-700 text-white rounded-lg text-sm font-medium transition-all">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Hover Overlay Effect -->
                <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 to-purple-500/5 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none"></div>
            </div>
            @endforeach
        </div>
        @else
        <!-- Enhanced Empty State -->
        <div class="bg-white rounded-2xl shadow-lg p-12 text-center border border-gray-100">
            <div class="relative mb-8">
                <!-- Animated Background -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div class="w-32 h-32 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full animate-pulse"></div>
                </div>
                <!-- Main Icon -->
                <div class="relative w-32 h-32 bg-gradient-to-r from-blue-400 to-purple-500 rounded-full flex items-center justify-center mx-auto">
                    <i class="fas fa-map-marker-alt text-white text-4xl"></i>
                </div>
                <!-- Floating Elements -->
                <div class="absolute top-0 right-8 w-8 h-8 bg-yellow-300 rounded-full animate-bounce"></div>
                <div class="absolute bottom-4 left-8 w-6 h-6 bg-green-300 rounded-full animate-bounce" style="animation-delay: 0.5s;"></div>
            </div>
            
            <h3 class="text-2xl font-bold text-gray-800 mb-3">Belum Ada Alamat Tersimpan</h3>
            <p class="text-gray-600 mb-8 max-w-md mx-auto">
                Tambahkan alamat pengiriman untuk memudahkan proses checkout saat berbelanja
            </p>
            
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="{{ route('pembeli.alamat.create') }}" 
                   class="inline-flex items-center px-8 py-4 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all transform hover:scale-105 shadow-lg">
                    <i class="fas fa-plus mr-3"></i>
                    Tambah Alamat Pertama
                </a>
                <a href="{{ route('produk.index') }}" 
                   class="inline-flex items-center px-8 py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold rounded-xl transition-all">
                    <i class="fas fa-shopping-bag mr-3"></i>
                    Lihat Produk
                </a>
            </div>
        </div>
        @endif

        <!-- Tips Section -->
        @if($alamat->count() > 0)
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-2xl p-6 border border-blue-200">
            <div class="flex items-start">
                <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-4 flex-shrink-0">
                    <i class="fas fa-lightbulb text-white text-sm"></i>
                </div>
                <div>
                    <h4 class="font-semibold text-gray-800 mb-2">Tips Mengelola Alamat</h4>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Pastikan alamat lengkap dengan kode pos</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Tetapkan satu alamat sebagai alamat utama</li>
                        <li class="flex items-center"><i class="fas fa-check text-green-500 mr-2"></i>Gunakan nama yang mudah diingat untuk jenis alamat</li>
                    </ul>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function confirmDelete(jenisAlamat) {
    return confirm(`Apakah Anda yakin ingin menghapus alamat "${jenisAlamat}"?\n\nTindakan ini tidak dapat dibatalkan.`);
}

// Animation on page load
document.addEventListener('DOMContentLoaded', function() {
    const cards = document.querySelectorAll('.group');
    cards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        setTimeout(() => {
            card.style.transition = 'all 0.5s ease-out';
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, index * 100);
    });
});
</script>

<style>
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.animate-fadeIn {
    animation: fadeIn 0.5s ease-out;
}

/* Smooth transitions for all interactive elements */
* {
    transition-property: transform, background-color, border-color, color, box-shadow;
    transition-duration: 200ms;
    transition-timing-function: ease-in-out;
}

/* Enhanced hover effects */
.hover-lift:hover {
    transform: translateY(-2px);
}

/* Custom scrollbar */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: linear-gradient(135deg, #5a6fd8 0%, #6b4190 100%);
}
</style>

@endsection