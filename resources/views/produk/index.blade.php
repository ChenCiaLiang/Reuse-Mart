@extends(session('user') ? 'layouts.customer' : 'layouts.umum')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Status Statistics Card -->
        <div class="bg-gradient-to-r from-green-500 to-blue-600 rounded-lg shadow-md p-6 mb-8 text-white">
            <h2 class="text-2xl font-bold mb-4">Status Produk ReUseMart</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-white bg-opacity-20 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">{{ $statusStats['tersedia'] }}</div>
                    <div class="text-sm opacity-90">Produk Tersedia</div>
                    <div class="w-3 h-3 bg-green-500 rounded-full mx-auto mt-2"></div>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">{{ $statusStats['terjual'] }}</div>
                    <div class="text-sm opacity-90">Produk Terjual</div>
                    <div class="w-3 h-3 bg-red-500 rounded-full mx-auto mt-2"></div>
                </div>
                <div class="bg-white bg-opacity-20 rounded-lg p-4 text-center">
                    <div class="text-3xl font-bold">{{ $statusStats['didonasikan'] }}</div>
                    <div class="text-sm opacity-90">Produk Didonasikan</div>
                    <div class="w-3 h-3 bg-blue-500 rounded-full mx-auto mt-2"></div>
                </div>
            </div>
        </div>

        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-8">
            <form action="{{ route('produk.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="md:col-span-2">
                    <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Produk</label>
                    <input type="text" name="search" id="search" value="{{ $search ?? '' }}" 
                        placeholder="Cari berdasarkan deskripsi produk..." 
                        class="w-full border border-gray-300 rounded-md shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600">
                </div>
                
                <div>
                    <label for="kategori" class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="kategori" id="kategori" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $k)
                            <option value="{{ $k->idKategori }}" {{ isset($kategori) && $kategori == $k->idKategori ? 'selected' : '' }}>
                                {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" id="status" 
                            class="w-full border border-gray-300 rounded-md shadow-sm px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" {{ isset($status) && $status == 'Tersedia' ? 'selected' : '' }}>
                            ✅ Tersedia
                        </option>
                        <option value="Terjual" {{ isset($status) && $status == 'Terjual' ? 'selected' : '' }}>
                            ❌ Terjual
                        </option>
                        <option value="Didonasikan" {{ isset($status) && $status == 'Didonasikan' ? 'selected' : '' }}>
                            💝 Didonasikan
                        </option>
                    </select>
                </div>
                
                <div class="flex items-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-300 mr-2">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    @if($search || $kategori || $status)
                        <a href="{{ route('produk.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Status Legend -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-semibold text-blue-800 mb-2">Keterangan Status Produk:</h3>
            <div class="flex flex-wrap gap-4 text-sm">
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                    <span class="text-green-700">Tersedia - Dapat dibeli</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                    <span class="text-red-700">Terjual - Sudah dibeli customer</span>
                </div>
                <div class="flex items-center">
                    <div class="w-3 h-3 bg-blue-500 rounded-full mr-2"></div>
                    <span class="text-blue-700">Didonasikan - Disumbangkan ke organisasi</span>
                </div>
            </div>
        </div>

        <!-- Search Results Info -->
        @if($search || $kategori || $status)
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                <p>
                    Menampilkan hasil pencarian untuk 
                    @if($search)
                        kata kunci "<strong>{{ $search }}</strong>"
                    @endif
                    
                    @if($search && ($kategori || $status))
                        pada 
                    @endif
                    
                    @if($kategori)
                        kategori "<strong>{{ $kategoriList->where('idKategori', $kategori)->first()->nama }}</strong>"
                    @endif

                    @if($kategori && $status)
                        dengan 
                    @endif

                    @if($status)
                        status "<strong>{{ $status }}</strong>"
                    @endif
                    
                    ({{ count($produk) }} produk ditemukan)
                </p>
            </div>
        @endif
        
        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($produk as $p)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300 relative
                {{ $p->status !== 'Tersedia' ? 'opacity-75' : '' }}">
                
                <!-- Status Overlay untuk produk tidak tersedia -->
                @if($p->status !== 'Tersedia')
                    <div class="absolute inset-0 bg-black bg-opacity-50 z-10 flex items-center justify-center rounded-lg">
                        <div class="text-center text-white">
                            @if($p->status === 'Terjual')
                                <i class="fas fa-check-circle text-4xl mb-2 text-red-400"></i>
                                <p class="font-bold text-lg">TERJUAL</p>
                            @elseif($p->status === 'Didonasikan')
                                <i class="fas fa-heart text-4xl mb-2 text-blue-400"></i>
                                <p class="font-bold text-lg">DIDONASIKAN</p>
                            @endif
                        </div>
                    </div>
                @endif

                <!-- Product Link - hanya untuk produk tersedia -->
                @if($p->status === 'Tersedia')
                    <a href="{{ route('produk.show', $p->idProduk) }}">
                @endif
                    <div class="relative h-48 overflow-hidden">
                        @php
                            $gambarArray = $p->gambar ? explode(',', $p->gambar) : ['default.jpg'];
                            $thumbnail = $gambarArray[0];
                        @endphp
                        <img src="{{ asset('images/produk/' . $thumbnail) }}" alt="{{ $p->deskripsi }}" 
                            class="w-full h-full object-cover {{ $p->status !== 'Tersedia' ? 'grayscale' : '' }}" 
                            onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                        
                        <!-- Status Badge -->
                        <div class="absolute top-2 left-2">
                            @if($p->status === 'Tersedia')
                                <span class="bg-green-500 text-white text-xs font-bold py-1 px-2 rounded-full">
                                    <i class="fas fa-check-circle mr-1"></i>TERSEDIA
                                </span>
                            @elseif($p->status === 'Terjual')
                                <span class="bg-red-500 text-white text-xs font-bold py-1 px-2 rounded-full">
                                    <i class="fas fa-times-circle mr-1"></i>TERJUAL
                                </span>
                            @elseif($p->status === 'Didonasikan')
                                <span class="bg-blue-500 text-white text-xs font-bold py-1 px-2 rounded-full">
                                    <i class="fas fa-heart mr-1"></i>DIDONASIKAN
                                </span>
                            @endif
                        </div>

                        <!-- Warranty Badge -->
                        @if($p->tanggalGaransi && \Carbon\Carbon::parse($p->tanggalGaransi)->isFuture())
                        <div class="absolute top-2 right-2 bg-green-600 text-white text-xs py-1 px-2 rounded">
                            <i class="fas fa-shield-alt mr-1"></i>Garansi
                        </div>
                        @endif
                    </div>
                @if($p->status === 'Tersedia')
                    </a>
                @endif
                
                <div class="p-4">
                    <h3 class="text-lg font-semibold mb-2 truncate {{ $p->status !== 'Tersedia' ? 'text-gray-500' : '' }}">
                        {{ $p->deskripsi }}
                    </h3>
                   
                    <div class="flex justify-between items-center mb-3">
                        <span class="text-green-700 font-bold {{ $p->status !== 'Tersedia' ? 'text-gray-500' : '' }}">
                            Rp {{ number_format($p->hargaJual, 0, ',', '.') }}
                        </span>
                        
                        <!-- Status Indicator Dot -->
                        <div class="flex items-center">
                            @if($p->status === 'Tersedia')
                                <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse"></div>
                            @elseif($p->status === 'Terjual')
                                <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            @elseif($p->status === 'Didonasikan')
                                <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            @endif
                        </div>
                    </div>

                    <!-- MODIFIKASI: Dual Button System untuk produk tersedia -->
                    @if($p->status === 'Tersedia')
                        <div class="space-y-2">
                            <!-- Tombol Tambah ke Keranjang -->
                            <button class="add-to-cart-btn w-full bg-green-600 hover:bg-green-700 text-white px-3 py-2 rounded-lg transition duration-300 text-sm" 
                                    data-product-id="{{ $p->idProduk }}" onclick="event.preventDefault(); event.stopPropagation();">
                                <i class="fas fa-shopping-cart mr-1"></i> Tambah ke Keranjang
                            </button>
                            
                            <!-- Tombol Beli Sekarang -->
                            <button class="buy-now-btn w-full border-2 border-green-600 text-green-600 hover:bg-green-50 px-3 py-2 rounded-lg transition duration-300 text-sm" 
                                    data-product-id="{{ $p->idProduk }}" onclick="event.preventDefault(); event.stopPropagation();">
                                <i class="fas fa-bolt mr-1"></i> Beli Sekarang
                            </button>
                        </div>
                    @else
                        <div class="w-full text-center py-2 px-3 rounded-lg border-2 border-dashed
                            {{ $p->status === 'Terjual' ? 'border-red-300 text-red-600' : 'border-blue-300 text-blue-600' }}">
                            @if($p->status === 'Terjual')
                                <i class="fas fa-sold-out mr-1"></i> Sudah Terjual
                            @else
                                <i class="fas fa-heart mr-1"></i> Telah Didonasikan
                            @endif
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-12">
                <img src="{{ asset('images/empty-state.svg') }}" alt="Tidak ada produk" class="w-48 mx-auto mb-4">
                <h3 class="text-xl font-bold text-gray-600 mb-2">Produk Tidak Ditemukan</h3>
                <p class="text-gray-500">Maaf, produk yang Anda cari tidak tersedia saat ini.</p>
                @if($search || $kategori || $status)
                    <a href="{{ route('produk.index') }}" class="inline-block mt-4 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-300">
                        Lihat Semua Produk
                    </a>
                @endif
            </div>
            @endforelse
        </div>
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

    <script>
        // Data user dari server
        const userData = {
            isLoggedIn: {{ session('user') ? 'true' : 'false' }},
            role: '{{ session('role') ?? '' }}'
        };

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

        // MODIFIKASI: Fungsi add to cart (Fungsionalitas 57)
        function addToCart(productId) {
            // Show loading
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
                } else {
                    showNotification(data.error || 'Terjadi kesalahan saat menambahkan ke keranjang', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat menambahkan ke keranjang', 'error');
            });
        }

        // MODIFIKASI: Fungsi buy now (langsung ke checkout)
        function buyNow(productId) {
            // Show loading
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
                    showNotification('Mengarahkan ke halaman checkout...', 'success');
                    // Redirect ke checkout setelah delay singkat
                    setTimeout(() => {
                        window.location.href = data.redirect_url;
                    }, 1000);
                } else {
                    showNotification(data.error || 'Terjadi kesalahan saat memproses pesanan', 'error');
                }
            })
            .catch(error => {
                hideLoading();
                console.error('Error:', error);
                showNotification('Terjadi kesalahan saat memproses pesanan', 'error');
            });
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message, type = 'info') {
            // Create notification element
            const notification = document.createElement('div');
            notification.className = `fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
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
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
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

        // Fungsi untuk menampilkan/menyembunyikan loading
        function showLoading() {
            let overlay = document.getElementById('loadingOverlay');
            if (!overlay) {
                overlay = document.createElement('div');
                overlay.id = 'loadingOverlay';
                overlay.className = 'fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50';
                overlay.innerHTML = `
                    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
                        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
                        <span class="text-gray-700">Memproses...</span>
                    </div>
                `;
                document.body.appendChild(overlay);
            }
            overlay.classList.remove('hidden');
        }

        function hideLoading() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
            }
        }

        // Fungsi untuk update cart count
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

        // MODIFIKASI: Handle button clicks untuk dual button system
        document.addEventListener('DOMContentLoaded', function() {
            // Handle Add to Cart buttons
            const addToCartButtons = document.querySelectorAll('.add-to-cart-btn');
            addToCartButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const productId = this.getAttribute('data-product-id');
                    
                    // Jika user sudah login sebagai pembeli
                    if (userData.isLoggedIn && userData.role === 'pembeli') {
                        addToCart(productId);
                    } else {
                        // Jika belum login atau bukan pembeli, tampilkan modal
                        showModal();
                    }
                });
            });

            // Handle Buy Now buttons
            const buyNowButtons = document.querySelectorAll('.buy-now-btn');
            buyNowButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const productId = this.getAttribute('data-product-id');
                    
                    // Jika user sudah login sebagai pembeli
                    if (userData.isLoggedIn && userData.role === 'pembeli') {
                        buyNow(productId);
                    } else {
                        // Jika belum login atau bukan pembeli, tampilkan modal
                        showModal();
                    }
                });
            });

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