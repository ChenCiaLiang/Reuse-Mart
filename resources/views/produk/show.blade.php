@extends(session('user') ? 'layouts.customer' : 'layouts.umum')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col md:flex-row">
                <!-- Product Images -->
                <div class="w-full md:w-1/2 mb-6 md:mb-0 md:pr-6">
                    <!-- Main Image -->
                    <div class="bg-gray-100 rounded-lg p-2 mb-4">
                        <img id="mainImage" src="{{ asset('images/produk/' . $gambarArray[0]) }}" 
                            alt="{{ $produk->deskripsi }}" class="w-full h-64 md:h-80 lg:h-96 object-contain rounded-lg"
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
                    
                    <!-- Rating Penitip -->
                    <div class="flex items-center mb-4">
                        <div class="flex text-yellow-400">
                            @for($i = 1; $i <= 5; $i++)
                                @if($i <= $ratingPenitip)
                                    <i class="fas fa-star"></i>
                                @elseif($i <= $ratingPenitip + 0.5)
                                    <i class="fas fa-star-half-alt"></i>
                                @else
                                    <i class="far fa-star"></i>
                                @endif
                            @endfor
                        </div>
                        <span class="text-gray-600 text-sm ml-2">
                            ({{ number_format($ratingPenitip, 1) }})
                            @if($penitip)
                                - Rating Penitip: {{ $penitip->nama }}
                            @endif
                        </span>
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
                    
                    <!-- Seller Info -->
                    @if($penitip)
                    <div class="mb-4 p-3 bg-gray-50 rounded-lg border">
                        <div class="flex items-center">
                            <i class="fas fa-user-circle text-gray-400 text-2xl mr-3"></i>
                            <div>
                                <div class="font-medium text-gray-800">{{ $penitip->nama }}</div>
                                <div class="text-sm text-gray-600">Penitip</div>
                                <div class="flex items-center mt-1">
                                    <div class="flex text-yellow-400 text-sm">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $penitip->rating)
                                                <i class="fas fa-star"></i>
                                            @elseif($i <= $penitip->rating + 0.5)
                                                <i class="fas fa-star-half-alt"></i>
                                            @else
                                                <i class="far fa-star"></i>
                                            @endif
                                        @endfor
                                    </div>
                                    <span class="text-xs text-gray-500 ml-1">({{ number_format($penitip->rating, 1) }})</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
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
                        <button id="addToCartBtn" class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 rounded-lg transition duration-300">
                            <i class="fas fa-shopping-cart mr-2"></i> Tambahkan ke Keranjang
                        </button>
                    </div>
                    
                    <!-- Buy Now Button -->
                    <div>
                        <button id="buyNowBtn" class="w-full border-2 border-green-600 text-green-600 hover:bg-green-50 text-center py-3 rounded-lg transition duration-300">
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
            
            <!-- Diskusi Produk Section -->
            <div class="mt-12 border-t pt-8">
                <h2 class="text-xl font-bold mb-6">Diskusi Produk</h2>
                
                <!-- Flash Message untuk notifikasi sukses -->
                @if(session('success'))
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p>{{ session('success') }}</p>
                    </div>
                @endif
                
                <!-- Daftar Diskusi -->
                <div class="bg-gray-50 rounded-lg p-6 mb-6">
                    @if(isset($diskusi) && count($diskusi) > 0)
                        <div class="space-y-4">
                            @foreach($diskusi as $item)
                                <div class="p-4 rounded-lg {{ $item->idPembeli ? 'bg-gray-100' : 'bg-green-50' }}">
                                    <div class="flex justify-between mb-2">
                                        <div class="font-medium">
                                            @if($item->idPembeli)
                                                {{ $item->pembeli->nama ?? 'Pembeli' }}
                                            @else
                                                {{ $item->pegawai->nama ?? 'Customer Service' }}
                                                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full ml-2">CS</span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-500">{{ $item->tanggalDiskusi->format('d M Y H:i') }}</div>
                                    </div>
                                    <p class="text-gray-700">{{ $item->pesan }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="py-4 text-center text-gray-500">
                            <p>Belum ada diskusi untuk produk ini.</p>
                        </div>
                    @endif
                </div>

                <!-- Form Tambah Diskusi -->
                @if(session('user') && in_array(session('role'), ['pembeli', 'cs']))
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-800 mb-4">Kirim Pertanyaan</h3>
                        
                        <form action="{{ route('produk.diskusi.store', $produk->idProduk) }}" method="POST">
                            @csrf
                            
                            <div class="mb-4">
                                <textarea name="pesan" rows="3" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                        placeholder="Tulis pertanyaan atau komentar tentang produk ini...">{{ old('pesan') }}</textarea>
                                
                                @error('pesan')
                                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div class="flex justify-end">
                                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                                    Kirim Pesan
                                </button>
                            </div>
                        </form>
                    </div>
                @else
                    <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4">
                        <p>Silakan <a href="{{ route('loginPage') }}" class="font-medium underline">login</a> sebagai pembeli atau customer service untuk bertanya tentang produk ini.</p>
                    </div>
                @endif
            </div>
        </div>
        
        <!-- Related Products -->
        @if(count($produkTerkait) > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Produk Terkait</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($produkTerkait as $p)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                    <a href="{{ route('produk.show', $p->idProduk) }}" class="block">
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ asset('images/produk/' . $p->thumbnail) }}" 
                                alt="{{ $p->deskripsi }}" class="w-full h-full object-cover"
                                onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                            @if($p->tanggalGaransi && \Carbon\Carbon::parse($p->tanggalGaransi)->isFuture())
                            <div class="absolute top-0 right-0 bg-green-600 text-white text-xs py-1 px-2 m-2 rounded">
                                Garansi
                            </div>
                            @endif
                        </div>
                    </a>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2 truncate">{{ $p->deskripsi }}</h3>
                        <div class="flex justify-between items-center">
                            <span class="text-green-700 font-bold">Rp {{ number_format($p->hargaJual, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
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

    <!-- Modal Login (hanya tampil jika belum login atau bukan pembeli) -->
    @if(!session('user') || session('role') !== 'pembeli')
    <div id="loginModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg shadow-xl p-6 max-w-md w-full">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gray-900">Login Diperlukan</h3>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-800">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="mb-6">
                <p class="text-gray-700 mb-4">Anda perlu login terlebih dahulu untuk melakukan pembelian di ReUseMart.</p>
                <div class="flex items-center text-sm text-gray-600 mb-4">
                    <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                    <span>Dengan login, Anda dapat menikmati fitur keranjang belanja dan melakukan pembelian produk.</span>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-2">
                <a href="{{ url('/login') }}" class="bg-green-600 hover:bg-green-700 text-white text-center py-2 px-4 rounded-lg transition duration-300 flex-1">
                    Login
                </a>
            </div>
        </div>
    </div>
    @endif

    <script>
        // Data user dari server
        const userData = {
            isLoggedIn: {{ session('user') ? 'true' : 'false' }},
            role: '{{ session('role') ?? '' }}'
        };

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

        // Fungsi untuk menampilkan modal
        function showModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        // Fungsi untuk menutup modal
        function closeModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // Fungsi untuk handle aksi beli
        function handleBuyAction(actionType) {
            if (userData.isLoggedIn && userData.role === 'pembeli') {
                // User sudah login sebagai pembeli
                if (actionType === 'cart') {
                    alert('Produk berhasil ditambahkan ke keranjang!');
                    // Nanti bisa diganti dengan fungsi add to cart yang sesungguhnya
                } else if (actionType === 'buy') {
                    alert('Mengarahkan ke halaman checkout...');
                    // Nanti bisa diganti dengan redirect ke checkout
                    // window.location.href = `/checkout/{{ $produk->idProduk }}`;
                }
            } else {
                // Belum login atau bukan pembeli - tampilkan modal
                showModal();
            }
        }

        // Event listener untuk tombol-tombol
        document.addEventListener('DOMContentLoaded', function() {
            // Tombol tambah ke keranjang
            const addToCartBtn = document.getElementById('addToCartBtn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function() {
                    handleBuyAction('cart');
                });
            }
            
            // Tombol beli sekarang
            const buyNowBtn = document.getElementById('buyNowBtn');
            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', function() {
                    handleBuyAction('buy');
                });
            }

            // Close modal when clicking outside
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.addEventListener('click', function(e) {
                    if (e.target === modal) {
                        closeModal();
                    }
                });
            }
        });
    </script>
@endsection