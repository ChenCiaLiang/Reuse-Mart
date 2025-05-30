@extends('layouts.gudang')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <!-- Transaction Header Info -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Detail Transaksi Penjualan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <span class="text-gray-600">Status:</span>
                    <span class="font-semibold ml-2 px-3 py-1 rounded-full text-sm
                        {{ $pengiriman->status === 'kirim' || $pengiriman->status === 'diambil' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                        {{ ucfirst($pengiriman->status) }}
                    </span>
                </div>
                <div>
                    <span class="text-gray-600">Tanggal Laku:</span>
                    <span class="font-semibold ml-2">{{ $pengiriman->tanggalLaku ?? 'Belum Tersedia' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Pembeli:</span>
                    <span class="font-semibold ml-2">{{ $pengiriman->namaPembeli ?? 'N/A' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Tanggal Kirim:</span>
                    <span class="font-semibold ml-2">{{ $pengiriman->tanggalKirim ?? '-' }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Tanggal Ambil:</span>
                    <span class="font-semibold ml-2">{{ $pengiriman->tanggalAmbil ?? '-' }}</span>
                </div>
                @if($pengiriman->idPegawai)
                    <div>
                        <span class="text-gray-600">Kurir: </span>
                        <span class="font-semibold ml-2 px-3 py-1 rounded-full text-sm
                                {{ $pengiriman->namaPegawai ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $pengiriman->namaPegawai ?? 'Diambil Sendiri' }}
                        </span>
                    </div>
                @endIf
            </div>
        </div>

        <!-- Products List - Each Product in Separate Row -->
        <div class="space-y-6">
            @foreach($pengiriman->produkList as $index => $produk)
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Product Card Header -->
                    <div class="bg-gray-50 px-6 py-3 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">
                            Produk {{ $index + 1 }} - {{ $produk['nama'] }}
                        </h3>
                    </div>
                    
                    <!-- Product Content -->
                    <div class="p-6">
                        <div class="flex flex-col lg:flex-row gap-6">
                            <!-- Product Images Section -->
                            <div class="lg:w-1/2">
                                <!-- Main Image -->
                                <div class="bg-gray-100 rounded-lg p-4 mb-4">
                                    <img id="mainImage{{ $index }}" 
                                         src="{{ asset('images/produk/' . $produk['gambar'][0]) }}" 
                                         alt="Foto Produk {{ $produk['nama'] }}" 
                                         class="w-full h-64 md:h-80 object-contain rounded-lg"
                                         onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                                </div>
                                
                                <!-- Thumbnail Images -->
                                @if(count($produk['gambar']) > 1)
                                <div class="flex flex-wrap gap-2">
                                    @foreach($produk['gambar'] as $imgIndex => $gambar)
                                    <div class="thumbnail cursor-pointer w-16 h-16 rounded-md overflow-hidden border-2 
                                            {{ $imgIndex === 0 ? 'border-blue-500' : 'border-gray-300' }} hover:border-blue-400"
                                         onclick="changeImage{{ $index }}('{{ asset('images/produk/' . $gambar) }}', this)">
                                        <img src="{{ asset('images/produk/' . $gambar) }}" 
                                             alt="Thumbnail {{ $imgIndex + 1 }}"
                                             class="w-full h-full object-cover"
                                             onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                                    </div>
                                    @endforeach
                                </div>
                                @endif
                            </div>
                            
                            <!-- Product Details Section -->
                            <div class="lg:w-1/2">
                                <div class="space-y-4">
                                    <!-- Product Name -->
                                    <div class="border-b pb-3">
                                        <h4 class="text-xl font-bold text-gray-800">{{ $produk['nama'] }}</h4>
                                    </div>
                                    
                                    <!-- Product Price -->
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700 font-medium">Harga Jual:</span>
                                        <span class="text-2xl font-bold text-green-600">
                                            Rp {{ number_format($produk['harga'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                    
                                    <!-- Additional Product Info (if available) -->
                                    @if(isset($produk['berat']))
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">Berat:</span>
                                        <span class="text-gray-900 font-medium">{{ $produk['berat'] }} kg</span>
                                    </div>
                                    @endif
                                    
                                    @if(isset($produk['garansi']) && $produk['garansi'])
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-700">Garansi:</span>
                                        <span class="text-gray-900 font-medium">
                                            {{ date('d/m/Y', strtotime($produk['garansi'])) }}
                                        </span>
                                    </div>
                                    @endif
                                    
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- JavaScript for this specific product -->
                <script>
                    function changeImage{{ $index }}(src, element) {
                        // Change main image for this specific product
                        document.getElementById('mainImage{{ $index }}').src = src;
                        
                        // Remove active class from all thumbnails in this product
                        const thumbnails = element.parentElement.parentElement.querySelectorAll('.thumbnail');
                        thumbnails.forEach(thumb => {
                            thumb.classList.remove('border-blue-500');
                            thumb.classList.add('border-gray-300');
                        });
                        
                        // Add active class to clicked thumbnail
                        element.classList.remove('border-gray-300');
                        element.classList.add('border-blue-500');
                    }
                </script>
            @endforeach
        </div>

        <!-- Action Buttons Section -->
        <div class="bg-white rounded-lg shadow-md p-6 mt-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Aksi Transaksi</h3>
            <div class="flex flex-wrap gap-3">
                {{-- Show different buttons based on delivery method --}}
                @if($pengiriman->status == 'terjual')
                    {{-- Delivery by courier --}}
                    <a href="{{ route('gudang.pengiriman.penjadwalanKirimPage', $pengiriman->idTransaksiPenjualan) }}" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
                        <i class="fa-solid fa-truck mr-2"></i>
                        Penjadwalan Kirim
                    </a>
                    {{-- Self pickup --}}
                    <a href="{{ route('gudang.pengiriman.penjadwalanAmbilPage', $pengiriman->idTransaksiPenjualan) }}" 
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition-colors duration-200">
                        <i class="fa-solid fa-warehouse mr-2"></i>
                        Penjadwalan Ambil
                    </a>
                @else
                    @if($pengiriman->tanggalBatasAmbil)
                        <a href="{{ route('gudang.pengiriman.konfirmasiAmbil', $pengiriman->idTransaksiPenjualan) }}" 
                            class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition-colors duration-200">
                            <i class="fa-solid fa-circle-check mr-2"></i>
                            Konfirmasi Selesai
                        </a>
                    @endIf
                    
                    <a href="{{ route('gudang.penjualan.print-nota', $pengiriman->idTransaksiPenjualan) }}" 
                        class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md transition-colors duration-200">
                        <i class="fa-solid fa-file-pdf mr-2"></i>
                        Print Nota PDF
                    </a>
                @endif
                
            </div>
        </div>
    </main>

    <!-- Global JavaScript -->
    <script>
        // Add loading state for all images
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('img');
            images.forEach(img => {
                img.addEventListener('load', function() {
                    this.style.opacity = '1';
                    this.classList.add('loaded');
                });
                
                img.addEventListener('error', function() {
                    this.src = '{{ asset("images/produk/default.jpg") }}';
                    this.alt = 'Gambar tidak tersedia';
                });
            });
        });

        // Add smooth scroll to product sections
        function scrollToProduct(index) {
            const element = document.querySelector(`#product-${index}`);
            if (element) {
                element.scrollIntoView({ behavior: 'smooth' });
            }
        }
    </script>

    <style>
        /* Custom styles for better UX */
        .thumbnail {
            transition: all 0.2s ease-in-out;
        }
        
        .thumbnail:hover {
            transform: scale(1.05);
        }
        
        img {
            transition: opacity 0.3s ease-in-out;
        }
        
        .loaded {
            opacity: 1 !important;
        }
        
        /* Responsive improvements */
        @media (max-width: 768px) {
            .grid-cols-3 {
                grid-template-columns: repeat(1, minmax(0, 1fr));
            }
        }
    </style>
@endsection