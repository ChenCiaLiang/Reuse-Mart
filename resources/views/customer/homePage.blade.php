<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ReUseMart - Tentang Kami</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        html {
            scroll-behavior: smooth;
        }
        .hero-section {
            background-image: url('{{ asset("images/bg/tree.jpg") }}');
            background-size: cover;
            background-position: center;
        }
        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .hero-content {
            position: relative;
            z-index: 2; /* Memastikan konten berada di atas overlay */
        }
    </style>
</head>
<body class="bg-gray-50 font-sans">
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
                        <li><a href="#home" class="hover:text-green-200">Beranda</a></li>
                        <li><a href="#about" class="hover:text-green-200">Tentang Kami</a></li>
                        <li><a href="#how-it-works" class="hover:text-green-200">Cara Kerja</a></li>
                        <li><a href="#categories" class="hover:text-green-200">Kategori</a></li>
                        <li><a href="#benefits" class="hover:text-green-200">Keuntungan</a></li>
                        <li><a href="#location" class="hover:text-green-200">Lokasi</a></li>
                    </ul>
                </nav>
                <div class="flex items-center space-x-4">
                    <a href="#" class="hover:text-green-200"><i class="fas fa-search"></i></a>
                    <a href="#" class="hover:text-green-200"><i class="fas fa-shopping-cart"></i></a>
                    <a class="nav-link" style="color: white;" href="{{ route('customer.profile') }}"><i
                                    class="fa-solid fa-user" style="border-bottom:1px;"></i></a>
                    <button class="md:hidden hover:text-green-200"><i class="fas fa-bars"></i></button>
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <section id="home" class="hero-section text-white py-20">
        <div class="container mx-auto px-4 text-center">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">Selamat Datang di ReUseMart</h1>
            <p class="text-xl mb-8">Platform untuk menjual dan membeli barang bekas berkualitas</p>
            <div class="flex justify-center space-x-4">
                <a href="{{ url('/produk/index') }}" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg transition duration-300">Jelajahi Produk</a>
                <a href="#" class="bg-white hover:bg-gray-100 text-green-700 font-bold py-3 px-6 rounded-lg transition duration-300">Titipkan Barang</a>
            </div>
        </div>
    </section>

    <!-- About Us Section -->
    <section id="about" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Tentang ReUseMart</h2>
                <div class="w-20 h-1 bg-green-600 mx-auto mb-8"></div>
                <p class="text-gray-600 max-w-3xl mx-auto">ReUseMart adalah perusahaan yang bergerak di bidang penjualan barang bekas berkualitas yang berbasis di Yogyakarta. Didirikan oleh Pak Raka Pratama, seorang pengusaha muda yang memiliki kepedulian tinggi terhadap isu lingkungan, pengelolaan limbah, dan konsep ekonomi sirkular.</p>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Visi & Misi Kami</h3>
                    <p class="text-gray-600 mb-4">Dengan visi untuk mengurangi penumpukan sampah dan memberikan kesempatan kedua bagi barang-barang bekas yang masih layak pakai, ReUseMart hadir sebagai solusi inovatif yang memadukan nilai sosial dan bisnis.</p>
                    <p class="text-gray-600 mb-4">ReuseMart memfasilitasi masyarakat untuk menjual dan membeli barang bekas berkualitas, dengan aneka kategori, baik elektronik maupun non-elektronik, mulai dari kulkas, TV, oven, meja makan, rak buku, pakaian, buku, sepatu, dll.</p>
                    <p class="text-gray-600">Tidak hanya itu, platform ini juga menjadi jembatan bagi mereka yang ingin mendapatkan barang berkualitas dengan harga terjangkau, sekaligus berkontribusi dalam upaya pengurangan limbah.</p>
                </div>
                <div>
                    <img src="{{ asset('images/bg/office.jpeg') }}" alt="ReUseMart Office" class="rounded-lg shadow-lg">
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Bagaimana Kami Bekerja</h2>
                <div class="w-20 h-1 bg-green-600 mx-auto mb-8"></div>
                <p class="text-gray-600 max-w-3xl mx-auto">Berbeda dengan platform marketplace pada umumnya, ReUseMart menawarkan layanan utama yang dirancang untuk memudahkan pengguna dalam proses jual beli, yaitu penjualan dengan sistem penitipan.</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-box-open text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Penitipan Barang</h3>
                    <p class="text-gray-600">Titipkan barang bekas berkualitas Anda di gudang kami. Tim kami akan melakukan pemeriksaan kualitas dan memasarkannya untuk Anda.</p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-store text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Pemasaran</h3>
                    <p class="text-gray-600">Kami akan memasarkan barang Anda melalui platform kami. Anda tidak perlu repot memasukkan data, memfoto, atau melayani pertanyaan pembeli.</p>
                </div>

                <div class="bg-white p-8 rounded-lg shadow-md text-center">
                    <div class="w-16 h-16 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-hand-holding-usd text-2xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Penjualan & Pembayaran</h3>
                    <p class="text-gray-600">Ketika barang terjual, kami menangani proses pengiriman dan pembayaran. Anda menerima dana setelah dipotong komisi 20% untuk layanan kami.</p>
                </div>
            </div>

            <div class="mt-12 bg-white p-8 rounded-lg shadow-md">
                <h3 class="text-xl font-bold text-gray-800 mb-4">Kebijakan Penitipan</h3>
                <ul class="text-gray-600 space-y-2">
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                        <span>Masa penitipan barang adalah 30 hari. Jika tidak laku selama periode tersebut, penitip harus mengambil kembali barangnya.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                        <span>Masa penitipan dapat diperpanjang 1 kali dengan durasi 30 hari, dengan komisi naik menjadi 30% dari harga jual.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                        <span>Jika barang tidak diambil dalam waktu 7 hari setelah masa penitipan berakhir, barang akan didonasikan ke organisasi sosial.</span>
                    </li>
                    <li class="flex items-start">
                        <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                        <span>Penitip mendapat bonus 10% dari komisi jika barang laku dalam waktu kurang dari 7 hari.</span>
                    </li>
                </ul>
            </div>
        </div>
    </section>

    <!-- Categories Section -->
    <section id="categories" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Kategori Barang</h2>
                <div class="w-20 h-1 bg-green-600 mx-auto mb-8"></div>
                <p class="text-gray-600 max-w-3xl mx-auto">ReUseMart menerima berbagai jenis barang bekas berkualitas dalam kategori berikut:</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-laptop text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Elektronik & Gadget</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-tshirt text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Pakaian & Aksesori</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-couch text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Perabotan Rumah Tangga</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-book text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Buku & Alat Tulis</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-gamepad text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Hobi & Mainan</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-pink-100 text-pink-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-baby text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Perlengkapan Bayi & Anak</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-car text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Otomotif & Aksesori</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-green-100 text-green-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-leaf text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Perlengkapan Taman & Outdoor</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-briefcase text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Peralatan Kantor & Industri</h3>
                </div>

                <div class="category-card bg-white p-4 rounded-lg shadow-md text-center transition duration-300">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-spray-can text-xl"></i>
                    </div>
                    <h3 class="font-semibold text-gray-800">Kosmetik & Perawatan Diri</h3>
                </div>
            </div>
        </div>
    </section>

    <!-- Benefits Section -->
    <section id="benefits" class="py-16 bg-gray-100">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Keuntungan Menggunakan ReUseMart</h2>
                <div class="w-20 h-1 bg-green-600 mx-auto mb-8"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Bagi Penitip</h3>
                    <ul class="text-gray-600 space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Tidak perlu repot mengurus penjualan (foto, deskripsi, pertanyaan pembeli)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Tidak perlu mengurus pengiriman barang ke pembeli</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Bonus 10% dari komisi jika barang laku cepat</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Poin reward untuk donasi barang yang tidak laku</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Status Top Seller dan bonus untuk penitip dengan penjualan tertinggi</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-md">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Bagi Pembeli</h3>
                    <ul class="text-gray-600 space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Barang bekas berkualitas dengan harga terjangkau</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Semua barang sudah melalui proses Quality Control yang ketat</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Poin loyalitas untuk setiap pembelian (1 poin per Rp10.000)</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Bonus poin 20% untuk pembelian di atas Rp500.000</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Ongkir gratis untuk pembelian min. Rp1.500.000 di area Yogyakarta</span>
                        </li>
                        <li class="flex items-start">
                            <i class="fas fa-check-circle text-green-600 mt-1 mr-2"></i>
                            <span>Tukar poin dengan diskon atau merchandise menarik</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-16 bg-green-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold mb-6">Bergabunglah dengan Kami</h2>
            <p class="text-xl mb-8 max-w-3xl mx-auto">Jadilah bagian dari upaya pengurangan limbah dan ekonomi sirkular. Mulai jual atau beli barang bekas berkualitas sekarang!</p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="#" class="bg-white hover:bg-gray-100 text-green-700 font-bold py-3 px-6 rounded-lg transition duration-300">Daftar Sekarang</a>
                <a href="#" class="bg-transparent hover:bg-green-800 border-2 border-white text-white font-bold py-3 px-6 rounded-lg transition duration-300">Pelajari Lebih Lanjut</a>
            </div>
        </div>
    </section>

    <!-- Location Section -->
    <section id="location" class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800 mb-4">Lokasi Kami</h2>
                <div class="w-20 h-1 bg-green-600 mx-auto mb-8"></div>
            </div>

            <div class="grid md:grid-cols-2 gap-12 items-center">
                <div>
                    <div class="bg-gray-200 rounded-lg h-80 flex items-center justify-center" style="background-image: url('{{ asset('images/bg/map.jpeg') }}'); background-size: cover; background-position: center;">
                    </div>
                </div>
                <div>
                    <h3 class="text-2xl font-bold text-gray-800 mb-4">Kunjungi ReUseMart</h3>
                    <div class="space-y-4 text-gray-600">
                        <p class="flex items-start">
                            <i class="fas fa-map-marker-alt text-green-600 mt-1 mr-3 w-5"></i>
                            <span>Jl. Green Eco Park No. 456, Yogyakarta</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-phone-alt text-green-600 mt-1 mr-3 w-5"></i>
                            <span>+62 274 123456</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-envelope text-green-600 mt-1 mr-3 w-5"></i>
                            <span>info@reusemart.com</span>
                        </p>
                        <p class="flex items-start">
                            <i class="fas fa-clock text-green-600 mt-1 mr-3 w-5"></i>
                            <span>Jam Operasional: 08:00 - 20:00 WIB (Setiap Hari)</span>
                        </p>
                    </div>
                    <div class="mt-6 flex space-x-4">
                        <a href="#" class="text-green-600 hover:text-green-800">
                            <i class="fab fa-facebook-square text-2xl"></i>
                        </a>
                        <a href="#" class="text-green-600 hover:text-green-800">
                            <i class="fab fa-instagram text-2xl"></i>
                        </a>
                        <a href="#" class="text-green-600 hover:text-green-800">
                            <i class="fab fa-twitter-square text-2xl"></i>
                        </a>
                        <a href="#" class="text-green-600 hover:text-green-800">
                            <i class="fab fa-youtube text-2xl"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

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
                        <li><a href="#" class="hover:text-white">Beranda</a></li>
                        <li><a href="#" class="hover:text-white">Kategori</a></li>
                        <li><a href="#" class="hover:text-white">Tentang Kami</a></li>
                        <li><a href="#" class="hover:text-white">Cara Kerja</a></li>
                        <li><a href="#" class="hover:text-white">Kontak</a></li>
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
    <script>
        // Highlight active nav item based on scroll position
        window.addEventListener('scroll', function() {
            const sections = document.querySelectorAll('section[id]');
            const navItems = document.querySelectorAll('nav ul li a');
            
            let current = '';
            
            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.clientHeight;
                
                if (scrollY >= (sectionTop - 100)) {
                    current = section.getAttribute('id');
                }
            });
            
            navItems.forEach(item => {
                item.classList.remove('text-green-200');
                item.classList.add('hover:text-green-200');
                
                if (item.getAttribute('href').substring(1) === current) {
                    item.classList.add('text-green-200');
                    item.classList.remove('hover:text-green-200');
                }
            });
        });
    </script>
</body>
</html>