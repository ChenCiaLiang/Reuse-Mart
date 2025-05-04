<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReUseMart - Produk</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <!-- Header/Navbar -->
    <header class="bg-green-700 text-white shadow-md">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center">
                    <img src="{{ asset('images/Logo/Logo.jpg') }}" alt="ReUseMart Logo" class="h-10 rounded">
                    <span class="text-xl font-bold ml-2">ReUseMart</span>
                </div>
                <nav class="hidden md:block">
                    <ul class="flex space-x-6">
                        <li><a href="{{ url('/') }}" class="hover:text-green-200">Beranda</a></li>
                        <li><a href="{{ url('/#about') }}" class="hover:text-green-200">Tentang Kami</a></li>
                        <li><a href="{{ url('/#how-it-works') }}" class="hover:text-green-200">Cara Kerja</a></li>
                        <li><a href="{{ url('/#categories') }}" class="hover:text-green-200">Kategori</a></li>
                        <li><a href="{{ url('/#benefits') }}" class="hover:text-green-200">Keuntungan</a></li>
                        <li><a href="{{ url('/#location') }}" class="hover:text-green-200">Lokasi</a></li>
                    </ul>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-green-200"><i class="fas fa-search"></i></a>
                    <a href="#" class="hover:text-green-200"><i class="fas fa-shopping-cart"></i></a>
                    <a href="{{ url('/login') }}" class="bg-white text-green-700 hover:bg-gray-100 px-3 py-1 rounded text-sm font-medium transition duration-300">Masuk</a>
                    <a href="{{ url('/register/pembeli') }}" class="bg-white text-green-700 hover:bg-gray-100 px-3 py-1 rounded text-sm font-medium transition duration-300">Daftar</a>
                    <button class="md:hidden hover:text-green-200"><i class="fas fa-bars"></i></button>
                </div>
            </div>
        </div>
    </header>

    <!-- Page Title -->
    <div class="bg-green-600 text-white py-4">
        <div class="container mx-auto px-4">
            <h1 class="text-2xl font-bold">Produk Kami</h1>
            <div class="flex items-center text-sm">
                <a href="{{ url('/') }}" class="hover:underline">Beranda</a>
                <span class="mx-2">></span>
                <span>Produk</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($produk as $p)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <a href="{{ route('produk.show', $p->idProduk) }}">
                    <div class="relative h-48 overflow-hidden">
                        <img src="{{ asset('images/produk/' . $p->gambar . '.jpg') }}" alt="{{ $p->deskripsi }}" 
                            class="w-full h-full object-cover" onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                        @if($p->tanggalGaransi && \Carbon\Carbon::parse($p->tanggalGaransi)->isFuture())
                        <div class="absolute top-0 right-0 bg-green-600 text-white text-xs py-1 px-2 m-2 rounded">
                            Garansi
                        </div>
                        @endif
                    </div>
                </a>
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2 truncate">{{ $p->deskripsi }}</h3>
                    <div class="flex items-center mb-2">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $p->ratingProduk)
                                    <i class="fas fa-star"></i>
                                @elseif($i <= $p->ratingProduk + 0.5)
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-gray-600 text-sm ml-1">({{ $p->ratingProduk }})</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-green-700 font-bold">Rp {{ number_format($p->hargaJual, 0, ',', '.') }}</span>
                        <button class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-full">
                            <i class="fas fa-shopping-cart mr-1"></i> Beli
                        </button>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <img src="{{ asset('images/empty-state.svg') }}" alt="Tidak ada produk" class="w-48 mx-auto mb-4">
                <h3 class="text-xl font-bold text-gray-600 mb-2">Produk Tidak Ditemukan</h3>
                <p class="text-gray-500">Maaf, produk yang Anda cari tidak tersedia saat ini.</p>
            </div>
            @endforelse
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-800 text-white py-12">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold mb-4">ReUseMart</h3>
                    <p class="text-gray-400 mb-4">Platform untuk menjual dan membeli barang bekas berkualitas dengan sistem penitipan.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-facebook-square text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-twitter-square text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white">
                            <i class="fab fa-youtube text-xl"></i>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4">Navigasi</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="{{ url('/') }}" class="hover:text-white">Beranda</a></li>
                        <li><a href="{{ url('/#about') }}" class="hover:text-white">Tentang Kami</a></li>
                        <li><a href="{{ url('/#how-it-works') }}" class="hover:text-white">Cara Kerja</a></li>
                        <li><a href="{{ url('/#categories') }}" class="hover:text-white">Kategori</a></li>
                        <li><a href="{{ url('/#benefits') }}" class="hover:text-white">Keuntungan</a></li>
                        <li><a href="{{ url('/#location') }}" class="hover:text-white">Lokasi</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4">Layanan</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li><a href="#" class="hover:text-white">Penitipan Barang</a></li>
                        <li><a href="#" class="hover:text-white">Pembelian</a></li>
                        <li><a href="#" class="hover:text-white">Pengiriman</a></li>
                        <li><a href="#" class="hover:text-white">Program Reward</a></li>
                        <li><a href="#" class="hover:text-white">Donasi</a></li>
                    </ul>
                </div>

                <div>
                    <h3 class="text-lg font-bold mb-4">Hubungi Kami</h3>
                    <ul class="space-y-2 text-gray-400">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-2"></i>
                            <span>Jl. Green Eco Park No. 456, Yogyakarta</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-phone-alt mt-1 mr-2"></i>
                            <span>+62 274 123456</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-envelope mt-1 mr-2"></i>
                            <span>info@reusemart.com</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-clock mt-1 mr-2"></i>
                            <span>08:00 - 20:00 WIB (Setiap Hari)</span>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="border-t border-gray-700 mt-10 pt-6 text-center text-gray-400">
                <p>&copy; 2025 ReUseMart. Hak Cipta Dilindungi. Dikembangkan oleh GreenTech Solutions.</p>
            </div>
        </div>
    </footer>
</body>
</html>