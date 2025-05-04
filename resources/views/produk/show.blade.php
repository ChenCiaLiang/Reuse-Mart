<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $produk->deskripsi }} - ReUseMart</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        .thumbnail.active {
            border: 2px solid #16a34a;
        }
    </style>
</head>
<body class="bg-gray-100 font-sans">
    <!-- Header/Navbar (sama seperti halaman index) -->
    <header class="bg-green-700 text-white shadow-md">
        <!-- Isi header (sama seperti halaman index) -->
    </header>

    <!-- Page Title -->
    <div class="bg-green-600 text-white py-4">
        <div class="container mx-auto px-4">
            <div class="flex items-center text-sm">
                <a href="{{ url('/') }}" class="hover:underline">Beranda</a>
                <span class="mx-2">></span>
                <a href="{{ route('produk.index') }}" class="hover:underline">Produk</a>
                <span class="mx-2">></span>
                <span>{{ $produk->deskripsi }}</span>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col md:flex-row">
                <!-- Product Images -->
                <div class="w-full md:w-1/2 mb-6 md:mb-0 md:pr-6">
                    <!-- Main Image -->
                    <div class="bg-gray-100 rounded-lg p-2 mb-4">
                        <img id="mainImage" src="{{ asset('images/produk/' . $gambarArray[0]) }}" 
                             alt="{{ $produk->deskripsi }}" class="w-full rounded-lg"
                             onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                    </div>
                    
                    <!-- Thumbnail Images -->
                    @if(count($gambarArray) > 1)
                    <div class="flex flex-wrap gap-2">
                        @foreach($gambarArray as $index => $gambar)
                        <div class="thumbnail cursor-pointer w-20 h-20 rounded-md overflow-hidden border-2 
                                  {{ $index === 0 ? 'border-green-600' : 'border-transparent' }}"
                             onclick="changeImage('{{ asset('images/produk/' . $gambar) }}', this)">
                            <img src="{{ asset('images/produk/' . $gambar) }}" 
                                 alt="{{ $produk->deskripsi }} - Gambar {{ $index + 1 }}"
                                 class="w-full h-full object-cover"
                                 onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <!-- Product Info -->
                <div class="w-full md:w-1/2">
                    <h1 class="text-2xl font-bold mb-2">{{ $produk->deskripsi }}</h1>
                    
                    <!-- Rating -->
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $produk->ratingProduk)
                                    <i class="fas fa-star"></i>
                                @elseif($i <= $produk->ratingProduk + 0.5)
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-gray-600 text-sm ml-1">({{ $produk->ratingProduk }})</span>
                    </div>
                    
                    <!-- Price -->
                    <div class="text-3xl font-bold text-green-700 mb-4">
                        Rp {{ number_format($produk->hargaJual, 0, ',', '.') }}
                    </div>
                    
                    <!-- Status -->
                    <div class="mb-4">
                        <span class="text-gray-700">Status: </span>
                        <span class="font-medium text-green-600">{{ $produk->status }}</span>
                    </div>
                    
                    <!-- Warranty Badge -->
                    @if($produk->tanggalGaransi && \Carbon\Carbon::parse($produk->tanggalGaransi)->isFuture())
                    <div class="mb-4">
                        <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                            <i class="fas fa-shield-alt mr-1"></i> Garansi sampai {{ \Carbon\Carbon::parse($produk->tanggalGaransi)->format('d M Y') }}
                        </span>
                    </div>
                    @endif
                    
                    <!-- Weight -->
                    <div class="mb-4">
                        <span class="text-gray-700">Berat: </span>
                        <span class="font-medium">{{ $produk->berat }} kg</span>
                    </div>
                    
                    <!-- Category -->
                    <div class="mb-6">
                        <span class="text-gray-700">Kategori: </span>
                        <span class="font-medium">{{ $produk->kategori->nama }}</span>
                    </div>
                    
                    <!-- Add to Cart Button -->
                    <div class="mb-4">
                        <button class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 rounded-lg transition duration-300">
                            <i class="fas fa-shopping-cart mr-2"></i> Tambahkan ke Keranjang
                        </button>
                    </div>
                    
                    <!-- Buy Now Button -->
                    <div>
                        <button class="w-full border-2 border-green-600 text-green-600 hover:bg-green-50 text-center py-3 rounded-lg transition duration-300">
                            Beli Sekarang
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Product Description -->
            <div class="mt-12 border-t pt-8">
                <h2 class="text-xl font-bold mb-4">Deskripsi Produk</h2>
                <div class="text-gray-700 space-y-4">
                    <p>{{ $produk->deskripsi }}</p>
                    <p>Kondisi barang: Bekas berkualitas, telah melalui proses QC oleh tim ReUseMart.</p>
                    
                    @if($produk->tanggalGaransi && \Carbon\Carbon::parse($produk->tanggalGaransi)->isFuture())
                    <p>Produk ini masih dalam masa garansi resmi sampai {{ \Carbon\Carbon::parse($produk->tanggalGaransi)->format('d F Y') }}.</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Related Products -->
        @if(count($produkTerkait) > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Produk Terkait</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($produkTerkait as $p)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                    <div class="relative h-48 overflow-hidden">
                        @php
                            $gambarP = explode(',', $p->gambar);
                            $gambarUtama = count($gambarP) > 0 ? $gambarP[0] : '';
                        @endphp
                        <img src="{{ asset('images/produk/' . $gambarUtama) }}" 
                             alt="{{ $p->deskripsi }}" class="w-full h-full object-cover"
                             onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                        @if($p->tanggalGaransi && \Carbon\Carbon::parse($p->tanggalGaransi)->isFuture())
                        <div class="absolute top-0 right-0 bg-green-600 text-white text-xs py-1 px-2 m-2 rounded">
                            Garansi
                        </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2 truncate">{{ $p->deskripsi }}</h3>
                        <div class="flex justify-between items-center">
                            <span class="text-green-700 font-bold">Rp {{ number_format($p->hargaJual, 0, ',', '.') }}</span>
                            <a href="{{ route('produk.show', $p->idProduk) }}" class="text-green-600 hover:text-green-800">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

    <!-- Footer (sama seperti halaman index) -->
    <footer class="bg-gray-800 text-white py-12">
        <!-- Isi footer (sama seperti halaman index) -->
    </footer>

    <script>
        function changeImage(src, element) {
            // Ubah gambar utama
            document.getElementById('mainImage').src = src;
            
            // Atur kelas aktif pada thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => {
                thumb.classList.remove('border-green-600');
                thumb.classList.add('border-transparent');
            });
            
            element.classList.remove('border-transparent');
            element.classList.add('border-green-600');
        }
    </script>
</body>
</html>