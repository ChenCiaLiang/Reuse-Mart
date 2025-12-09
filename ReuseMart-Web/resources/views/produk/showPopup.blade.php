@extends(session('user') ? 'layouts.customer' : 'layouts.umum')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Product Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            @forelse($produk as $p)
            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition duration-300">
                <div class="cursor-pointer" onclick="showProductModal('{{ $p->idProduk }}')">
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
                </div>
                <div class="p-4">
                    <!-- Judul juga bisa diklik untuk memunculkan modal -->
                    <h3 class="text-lg font-semibold mb-2 truncate cursor-pointer" onclick="showProductModal('{{ $p->idProduk }}')">{{ $p->deskripsi }}</h3>
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

    <!-- Modal Login -->
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
                <a href="{{ url('/register/pembeli') }}" class="border-2 border-green-600 text-green-600 hover:bg-green-50 text-center py-2 px-4 rounded-lg transition duration-300 flex-1">
                    Daftar
                </a>
            </div>
        </div>
    </div>

    <!-- Modal Detail Produk -->
    <div id="productDetailModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden overflow-y-auto">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-4xl max-h-screen overflow-y-auto">
            <!-- Header Modal -->
            <div class="flex justify-between items-center border-b border-gray-200 px-6 py-3">
                <h3 class="text-xl font-bold text-gray-900" id="modalTitle">Detail Produk</h3>
                <button onclick="closeProductModal()" class="text-gray-500 hover:text-gray-800 focus:outline-none">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Konten Modal -->
            <div class="p-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Gambar Produk -->
                    <div class="w-full md:w-1/2">
                        <div class="bg-gray-100 rounded-lg p-2 mb-4">
                            <img id="modalMainImage" src="" alt="Gambar Produk" class="w-full h-64 md:h-80 lg:h-96 object-contain rounded-lg">
                        </div>
                        
                        <!-- Thumbnail Images -->
                        <div id="modalThumbnails" class="flex flex-wrap gap-2"></div>
                    </div>
                    
                    <!-- Informasi Produk -->
                    <div class="w-full md:w-1/2">
                        <h2 id="modalProductName" class="text-2xl font-bold mb-2"></h2>
                        
                        <!-- Rating -->
                        <div class="flex items-center mb-4">
                            <div id="modalRating" class="flex text-yellow-400"></div>
                            <span id="modalRatingValue" class="text-gray-600 text-sm ml-1"></span>
                        </div>
                        
                        <!-- Harga -->
                        <div id="modalPrice" class="text-3xl font-bold text-green-700 mb-4"></div>
                        
                        <!-- Status -->
                        <div class="mb-4">
                            <span class="text-gray-700">Status: </span>
                            <span id="modalStatus" class="font-medium text-green-600"></span>
                        </div>
                        
                        <!-- Badge Garansi -->
                        <div id="modalWarrantyBadge" class="mb-4 hidden">
                            <span class="bg-green-100 text-green-800 text-sm font-medium px-3 py-1 rounded-full">
                                <i class="fas fa-shield-alt mr-1"></i> <span id="modalWarrantyText"></span>
                            </span>
                        </div>
                        
                        <!-- Berat -->
                        <div class="mb-4">
                            <span class="text-gray-700">Berat: </span>
                            <span id="modalWeight" class="font-medium"></span>
                        </div>
                        
                        <!-- Kategori -->
                        <div class="mb-6">
                            <span class="text-gray-700">Kategori: </span>
                            <span id="modalCategory" class="font-medium"></span>
                        </div>
                        
                        <!-- Tombol -->
                        <div class="space-y-3">
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white text-center py-3 rounded-lg transition duration-300" onclick="showLoginModal()">
                                <i class="fas fa-shopping-cart mr-2"></i> Tambahkan ke Keranjang
                            </button>
                            <button class="w-full border-2 border-green-600 text-green-600 hover:bg-green-50 text-center py-3 rounded-lg transition duration-300" onclick="showLoginModal()">
                                Beli Sekarang
                            </button>
                            <a href="" id="modalDetailLink" class="inline-block w-full text-center text-green-600 hover:text-green-800 mt-4">
                                Lihat Detail Lengkap
                            </a>
                        </div>
                    </div>
                </div>
                
                <!-- Deskripsi Produk -->
                <div class="mt-8 border-t pt-6">
                    <h3 class="text-lg font-bold mb-3">Deskripsi Produk</h3>
                    <p id="modalDescription" class="text-gray-700"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const productsData = @json($produk);
        
        function showProductModal(productId) {
        const product = productsData.find(p => p.idProduk === productId);
        if (!product) {
            console.error('Produk tidak ditemukan:', productId);
            return;
        }
        
        document.getElementById('modalTitle').textContent = 'Detail Produk';
        document.getElementById('modalProductName').textContent = product.deskripsi || 'Tanpa Deskripsi';
        document.getElementById('modalPrice').textContent = 'Rp ' + (product.hargaJual ? formatNumber(product.hargaJual) : '0');
        document.getElementById('modalStatus').textContent = product.status || 'Tidak Diketahui';
        document.getElementById('modalWeight').textContent = (product.berat || '0') + ' kg';
        
        document.getElementById('productDetailModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
        
        function closeProductModal() {
            document.getElementById('productDetailModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        function changeModalImage(src, element) {
            document.getElementById('modalMainImage').src = src;
            
            const thumbnails = document.querySelectorAll('#modalThumbnails .thumbnail');
            thumbnails.forEach(thumb => {
                thumb.classList.remove('border-green-600');
                thumb.classList.add('border-transparent');
            });
            
            element.classList.remove('border-transparent');
            element.classList.add('border-green-600');
        }
        
        function formatNumber(number) {
            return new Intl.NumberFormat('id-ID').format(number);
        }
        
        function formatDate(dateString) {
            const date = new Date(dateString);
            return new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'short', year: 'numeric' }).format(date);
        }
        
        function showLoginModal() {
            closeProductModal();
            document.getElementById('loginModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        
        function closeModal() {
            document.getElementById('loginModal').classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            const buyButtons = document.querySelectorAll('.bg-green-600.hover\\:bg-green-700.text-white.px-3.py-1.rounded-full');
            buyButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    showLoginModal();
                });
            });
            
            document.getElementById('productDetailModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeProductModal();
                }
            });
            
            document.getElementById('loginModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeModal();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeProductModal();
                    closeModal();
                }
            });
        });
    </script>
@endsection