@extends(session('user') ? 'layouts.customer' : 'layouts.umum')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Search and Filter Section -->
        <div class="bg-white rounded-lg shadow-md p-4 mb-8">
            <form action="{{ route('produk.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
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
                
                <div class="flex items-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                        <i class="fas fa-search mr-2"></i>Cari
                    </button>
                    @if($search || $kategori)
                        <a href="{{ route('produk.index') }}" class="ml-2 bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-md transition duration-300">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Search Results Info -->
        @if($search || $kategori)
            <div class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-4 mb-6">
                <p>
                    Menampilkan hasil pencarian untuk 
                    @if($search)
                        kata kunci "<strong>{{ $search }}</strong>"
                    @endif
                    
                    @if($search && $kategori)
                        pada 
                    @endif
                    
                    @if($kategori)
                        kategori "<strong>{{ $kategoriList->where('idKategori', $kategori)->first()->nama }}</strong>"
                    @endif
                    
                    ({{ count($produk) }} produk ditemukan)
                </p>
            </div>
        @endif
        
        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($produk as $p)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <a href="{{ route('produk.show', $p->idProduk) }}">
                    <div class="relative h-48 overflow-hidden">
                        @php
                            $gambarArray = $p->gambar ? explode(',', $p->gambar) : ['default.jpg'];
                            $thumbnail = $gambarArray[0];
                        @endphp
                        <img src="{{ asset('images/produk/' . $thumbnail) }}" alt="{{ $p->deskripsi }}" 
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
                   {{-- <div class="flex items-center mb-2">
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
                    </div>--}}
                    <div class="flex justify-between items-center">
                        <span class="text-green-700 font-bold">Rp {{ number_format($p->hargaJual, 0, ',', '.') }}</span>
                        <button class="buy-button bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded-full" 
                                data-product-id="{{ $p->idProduk }}" onclick="event.preventDefault(); event.stopPropagation();">
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

        // Fungsi add to cart (Fungsionalitas 57)
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

        // Handle buy button clicks
        document.addEventListener('DOMContentLoaded', function() {
            const buyButtons = document.querySelectorAll('.buy-button');
            
            buyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    const productId = this.getAttribute('data-product-id');
                    
                    // Jika user sudah login sebagai pembeli
                    if (userData.isLoggedIn && userData.role === 'pembeli') {
                        // Add to cart
                        addToCart(productId);
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