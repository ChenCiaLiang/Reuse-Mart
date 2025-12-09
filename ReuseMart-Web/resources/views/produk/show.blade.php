@extends(session('user') ? 'layouts.customer' : 'layouts.umum')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- TAMBAHAN BARU: Breadcrumb dan Back Button -->
        <div class="flex items-center justify-between mb-6">
            <nav class="flex" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('produk.index') }}" class="inline-flex items-center text-sm font-medium text-gray-700 hover:text-green-600">
                            <i class="fas fa-home mr-2"></i>
                            Semua Produk
                        </a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-sm font-medium text-gray-500">{{ $produk->kategori->nama }}</span>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
                            <span class="text-sm font-medium text-gray-900 truncate max-w-xs">{{ $produk->deskripsi }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            
            <!-- TAMBAHAN BARU: Back Button -->
            <a href="{{ route('produk.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Produk
            </a>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col md:flex-row">
                <!-- Product Images -->
                <div class="w-full md:w-1/2 mb-6 md:mb-0 md:pr-6 relative">
                    <!-- Status Overlay untuk produk tidak tersedia -->
                    @if($produk->status !== 'Tersedia')
                        <div class="absolute inset-0 bg-black bg-opacity-50 z-10 flex items-center justify-center rounded-lg">
                            <div class="text-center text-white">
                                @if($produk->status === 'Terjual')
                                    <i class="fas fa-check-circle text-8xl mb-4 text-red-400"></i>
                                    <p class="font-bold text-3xl mb-2">PRODUK TERJUAL</p>
                                    <p class="text-lg opacity-90">Produk ini sudah dibeli oleh customer lain</p>
                                @elseif($produk->status === 'Didonasikan')
                                    <i class="fas fa-heart text-8xl mb-4 text-blue-400"></i>
                                    <p class="font-bold text-3xl mb-2">PRODUK DIDONASIKAN</p>
                                    <p class="text-lg opacity-90">Produk ini telah disumbangkan ke organisasi sosial</p>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Main Image -->
                    <div class="bg-gray-100 rounded-lg p-2 mb-4">
                        <img id="mainImage" src="{{ asset('images/produk/' . $gambarArray[0]) }}" 
                            alt="{{ $produk->deskripsi }}" 
                            class="w-full h-64 md:h-80 lg:h-96 object-contain rounded-lg {{ $produk->status !== 'Tersedia' ? 'grayscale' : '' }}"
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
                                class="w-full h-full object-cover {{ $produk->status !== 'Tersedia' ? 'grayscale' : '' }}"
                                onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <!-- Product Info -->
                <div class="w-full md:w-1/2">
                    <h1 class="text-2xl font-bold mb-2 {{ $produk->status !== 'Tersedia' ? 'text-gray-500' : '' }}">
                        {{ $produk->deskripsi }}
                    </h1>
                    
                    <!-- Status Badge - Prominent -->
                    <div class="mb-4">
                        @if($produk->status === 'Tersedia')
                            <span class="bg-green-100 text-green-800 text-lg font-bold py-2 px-4 rounded-full border-2 border-green-500">
                                <i class="fas fa-check-circle mr-2"></i>TERSEDIA UNTUK DIBELI
                            </span>
                        @elseif($produk->status === 'Terjual')
                            <span class="bg-red-100 text-red-800 text-lg font-bold py-2 px-4 rounded-full border-2 border-red-500">
                                <i class="fas fa-times-circle mr-2"></i>SUDAH TERJUAL
                            </span>
                        @elseif($produk->status === 'Didonasikan')
                            <span class="bg-blue-100 text-blue-800 text-lg font-bold py-2 px-4 rounded-full border-2 border-blue-500">
                                <i class="fas fa-heart mr-2"></i>TELAH DIDONASIKAN
                            </span>
                        @endif
                    </div>
                    
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
                    <div class="text-3xl font-bold mb-4 {{ $produk->status !== 'Tersedia' ? 'text-gray-500 line-through' : 'text-green-700' }}">
                        Rp {{ number_format($produk->hargaJual, 0, ',', '.') }}
                        @if($produk->status !== 'Tersedia')
                            <span class="text-sm font-normal text-gray-500 block">
                                (Harga saat masih tersedia)
                            </span>
                        @endif
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
                    
                    <!-- MODIFIKASI BARU: Action Buttons -->
                    @if($produk->status === 'Tersedia')
                        <div class="space-y-3">
                            <!-- Add to Cart Button -->
                            <button id="addToCartBtn" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
                                    data-product-id="{{ $produk->idProduk }}">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                <span>Tambahkan ke Keranjang</span>
                            </button>
                            
                            <!-- Buy Now Button -->
                            <button id="buyNowBtn" 
                                    class="w-full bg-orange-500 hover:bg-orange-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors flex items-center justify-center"
                                    data-product-id="{{ $produk->idProduk }}">
                                <i class="fas fa-bolt mr-2"></i>
                                <span>Beli Sekarang</span>
                            </button>
                            
                            <!-- Divider -->
                            <div class="flex items-center my-4">
                                <div class="flex-grow border-t border-gray-300"></div>
                                <span class="flex-shrink mx-4 text-gray-500 text-sm">atau</span>
                                <div class="flex-grow border-t border-gray-300"></div>
                            </div>
                            
                            <!-- Continue Shopping Button -->
                            <a href="{{ route('produk.index') }}" 
                               class="w-full inline-block text-center border-2 border-gray-400 text-gray-600 hover:bg-gray-50 font-medium py-2 px-4 rounded-lg transition-colors">
                                <i class="fas fa-search mr-2"></i>
                                Lihat Produk Lainnya
                            </a>
                        </div>
                    @else
                        <!-- Not Available Message -->
                        <div class="mb-4 p-4 rounded-lg border-2 border-dashed
                            {{ $produk->status === 'Terjual' ? 'border-red-300 bg-red-50' : 'border-blue-300 bg-blue-50' }}">
                            <div class="text-center">
                                @if($produk->status === 'Terjual')
                                    <i class="fas fa-times-circle text-red-500 text-2xl mb-2"></i>
                                    <p class="text-red-700 font-semibold mb-1">Produk Sudah Terjual</p>
                                    <p class="text-red-600 text-sm">Produk ini telah dibeli oleh customer lain</p>
                                @else
                                    <i class="fas fa-heart text-blue-500 text-2xl mb-2"></i>
                                    <p class="text-blue-700 font-semibold mb-1">Produk Telah Didonasikan</p>
                                    <p class="text-blue-600 text-sm">Produk ini telah disumbangkan untuk kepentingan sosial</p>
                                @endif
                            </div>
                        </div>
                        
                        <!-- Explore Similar Products Button -->
                        <div>
                            <a href="{{ route('produk.index', ['kategori' => $produk->idKategori, 'status' => 'Tersedia']) }}" 
                               class="w-full inline-block text-center border-2 border-green-600 text-green-600 hover:bg-green-50 py-3 rounded-lg transition duration-300">
                                <i class="fas fa-search mr-2"></i> Cari Produk Serupa yang Tersedia
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            
            <!-- Status Information Card -->
            @if($produk->status !== 'Tersedia')
            <div class="mt-8 p-6 rounded-lg {{ $produk->status === 'Terjual' ? 'bg-red-50 border border-red-200' : 'bg-blue-50 border border-blue-200' }}">
                <h3 class="text-lg font-bold mb-3 {{ $produk->status === 'Terjual' ? 'text-red-800' : 'text-blue-800' }}">
                    Informasi Status Produk
                </h3>
                @if($produk->status === 'Terjual')
                    <p class="text-red-700 mb-2">
                        <i class="fas fa-info-circle mr-2"></i>
                        Produk ini telah berhasil terjual kepada customer lain. ReUseMart berkomitmen memberikan kesempatan kedua untuk barang bekas berkualitas.
                    </p>
                    <p class="text-red-600 text-sm">
                        Kami memiliki produk serupa lainnya yang mungkin sesuai dengan kebutuhan Anda.
                    </p>
                @else
                    <p class="text-blue-700 mb-2">
                        <i class="fas fa-heart mr-2"></i>
                        Produk ini telah disumbangkan kepada organisasi sosial sebagai bagian dari program CSR ReUseMart untuk membantu masyarakat yang membutuhkan.
                    </p>
                    <p class="text-blue-600 text-sm">
                        Terima kasih kepada penitip yang telah berkontribusi dalam program sosial ini.
                    </p>
                @endif
            </div>
            @endif
            
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
            
            <!-- Diskusi Produk Section - Hanya tampil untuk produk tersedia -->
            @if($produk->status === 'Tersedia')
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
            @endif
        </div>
        
        <!-- Related Products - Hanya produk tersedia -->
        @if(count($produkTerkait) > 0)
        <div class="mt-12">
            <h2 class="text-2xl font-bold mb-6">Produk Terkait yang Tersedia</h2>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                @foreach($produkTerkait as $p)
                <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                    <a href="{{ route('produk.show', $p->idProduk) }}" class="block">
                        <div class="relative h-48 overflow-hidden">
                            <img src="{{ asset('images/produk/' . $p->thumbnail) }}" 
                                alt="{{ $p->deskripsi }}" class="w-full h-full object-cover"
                                onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                            
                            <!-- Status Badge -->
                            <div class="absolute top-2 left-2">
                                <span class="bg-green-500 text-white text-xs font-bold py-1 px-2 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i>TERSEDIA
                                </span>
                            </div>
                            
                            @if($p->tanggalGaransi && \Carbon\Carbon::parse($p->tanggalGaransi)->isFuture())
                            <div class="absolute top-2 right-2 bg-green-600 text-white text-xs py-1 px-2 rounded">
                                <i class="fas fa-shield-alt mr-1"></i>Garansi
                            </div>
                            @endif
                        </div>
                    </a>
                    <div class="p-4">
                        <h3 class="text-lg font-semibold mb-2 truncate">{{ $p->deskripsi }}</h3>
                        <div class="flex justify-between items-center">
                            <span class="text-green-700 font-bold">Rp {{ number_format($p->hargaJual, 0, ',', '.') }}</span>
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </main>

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

    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
            <span class="text-gray-700">Memproses...</span>
        </div>
    </div>

    <script>
        console.log('Product show page loaded - Modified version');

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

        // MODIFIKASI BARU: Fungsi untuk menampilkan modal
        function showModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.remove('hidden');
                document.body.style.overflow = 'hidden';
            }
        }

        // MODIFIKASI BARU: Fungsi untuk menutup modal
        function closeModal() {
            const modal = document.getElementById('loginModal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.style.overflow = 'auto';
            }
        }

        // MODIFIKASI BARU: Fungsi untuk add to cart
        function addToCart(productId) {
            console.log('Adding to cart:', productId);
            
            // Disable button dan show loading
            const addToCartBtn = document.getElementById('addToCartBtn');
            if (addToCartBtn) {
                addToCartBtn.disabled = true;
                addToCartBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menambahkan...';
            }
            
            showLoading();
            
            fetch('{{ route("pembeli.cart.add") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    idProduk: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showNotification('Produk berhasil ditambahkan ke keranjang!', 'success');
                    updateCartCount();
                    
                    // Reset button
                    if (addToCartBtn) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i><span>Tambahkan ke Keranjang</span>';
                    }
                } else {
                    showNotification(data.error || 'Terjadi kesalahan saat menambahkan ke keranjang', 'error');
                    
                    // Reset button
                    if (addToCartBtn) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i><span>Tambahkan ke Keranjang</span>';
                    }
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
                
                // Reset button
                if (addToCartBtn) {
                    addToCartBtn.disabled = false;
                    addToCartBtn.innerHTML = '<i class="fas fa-shopping-cart mr-2"></i><span>Tambahkan ke Keranjang</span>';
                }
            });
        }

        // MODIFIKASI BARU: Fungsi untuk buy now (direct buy)
        function buyNow(productId) {
            console.log('Buy now:', productId);
            
            // Disable button dan show loading
            const buyNowBtn = document.getElementById('buyNowBtn');
            if (buyNowBtn) {
                buyNowBtn.disabled = true;
                buyNowBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
            }
            
            showLoading();
            
            fetch('{{ route("pembeli.buy.direct") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    idProduk: productId
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                
                if (data.success) {
                    showNotification('Mengarahkan ke checkout...', 'success');
                    
                    // Redirect ke checkout
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    showNotification(data.error || 'Terjadi kesalahan saat memproses pembelian', 'error');
                    
                    // Reset button
                    if (buyNowBtn) {
                        buyNowBtn.disabled = false;
                        buyNowBtn.innerHTML = '<i class="fas fa-bolt mr-2"></i><span>Beli Sekarang</span>';
                    }
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memproses pembelian', 'error');
                
                // Reset button
                if (buyNowBtn) {
                    buyNowBtn.disabled = false;
                    buyNowBtn.innerHTML = '<i class="fas fa-bolt mr-2"></i><span>Beli Sekarang</span>';
                }
            });
        }

        // MODIFIKASI BARU: Fungsi helper untuk notifikasi
        function showNotification(message, type = 'info') {
            // Remove existing notifications
            const existingNotifications = document.querySelectorAll('.notification');
            existingNotifications.forEach(notif => notif.remove());
            
            const notification = document.createElement('div');
            notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
                type === 'success' ? 'bg-green-500 text-white' : 
                type === 'error' ? 'bg-red-500 text-white' : 
                'bg-blue-500 text-white'
            }`;
            notification.innerHTML = `
                <div class="flex items-center">
                    <i class="fas ${
                        type === 'success' ? 'fa-check-circle' : 
                        type === 'error' ? 'fa-times-circle' : 
                        'fa-info-circle'
                    } mr-2"></i>
                    <span>${message}</span>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }

        // MODIFIKASI BARU: Fungsi untuk update cart count
        function updateCartCount() {
            fetch('{{ route("pembeli.cart.count") }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                const cartBadge = document.querySelector('.cart-count');
                if (cartBadge) {
                    cartBadge.textContent = data.count;
                }
            })
            .catch(error => console.error('Error updating cart count:', error));
        }

        // Loading functions
        function showLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.remove('hidden');
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) overlay.classList.add('hidden');
        }

        // MODIFIKASI BARU: Event listener untuk tombol-tombol
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, setting up event listeners...');
            
            // Tombol tambah ke keranjang
            const addToCartBtn = document.getElementById('addToCartBtn');
            if (addToCartBtn) {
                addToCartBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    
                    if (userData.isLoggedIn && userData.role === 'pembeli') {
                        addToCart(productId);
                    } else {
                        showModal();
                    }
                });
            }
            
            // Tombol beli sekarang
            const buyNowBtn = document.getElementById('buyNowBtn');
            if (buyNowBtn) {
                buyNowBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const productId = this.getAttribute('data-product-id');
                    
                    if (userData.isLoggedIn && userData.role === 'pembeli') {
                        buyNow(productId);
                    } else {
                        showModal();
                    }
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

            // Close modal and loading with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                }
            });
        });
    </script>
@endsection