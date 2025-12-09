<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Panel - ReUseMart</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 font-sans flex">
    <!-- Sidebar -->
    <aside class="w-64 h-screen bg-green-800 text-white fixed overflow-y-auto">
        <div class="p-6">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('images/Logo/Logo.jpg') }}" alt="ReUseMart Logo" class="h-8 rounded">
                <span class="text-lg font-bold">ReUseMart</span>
            </div>
            <p class="text-xs text-green-300 mt-1">Panel Owner</p>
        </div>
        
        <nav class="mt-5">
            
            <a href="{{ route('owner.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.dashboard') ? 'bg-green-700' : '' }}">
                <i class="fas fa-tachometer-alt mr-3"></i>
                <span>Dashboard</span>
            </a>

            <!-- Menu Donasi -->
            <div class="px-6 py-2">
                <p class="text-xs text-green-300 uppercase tracking-wider">Donasi</p>
            </div>

            <a href="{{ route('owner.donasi.request') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.donasi.request') ? 'bg-green-700' : '' }}">
                <i class="fas fa-hands-helping mr-3"></i>
                <span>Request Donasi</span>
            </a>
            
            <a href="{{ route('owner.donasi.history') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.donasi.history') ? 'bg-green-700' : '' }}">
                <i class="fas fa-history mr-3"></i>
                <span>History Donasi</span>
            </a>
            
            <a href="{{ route('owner.donasi.barang') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.donasi.barang') ? 'bg-green-700' : '' }}">
                <i class="fas fa-box mr-3"></i>
                <span>Barang untuk Donasi</span>
            </a>

            <!-- Menu Laporan -->
            <div class="px-6 py-2 mt-4">
                <p class="text-xs text-green-300 uppercase tracking-wider">Laporan</p>
            </div>

            <a href="{{ route('owner.laporan.penjualan-bulanan-index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.penjualan-bulanan-index') ? 'bg-green-700' : '' }}">
                <i class="fas fa-chart-line mr-3"></i>
                <span>Laporan Penjualan</span>
            </a>

            <a href="{{ route('owner.laporan.komisi-bulanan-index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.komisi-bulanan-index') ? 'bg-green-700' : '' }}">
                <i class="fas fa-percentage mr-3"></i>
                <span>Laporan Komisi</span>
            </a>

            <a href="{{ route('owner.laporan.stok-gudang-index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.stok-gudang-index') ? 'bg-green-700' : '' }}">
                <i class="fas fa-warehouse mr-3"></i>
                <span>Laporan Stok Gudang</span>
            </a>

            <a href="{{ route('owner.laporan.laporanKategori') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.laporanKategori') ? 'bg-green-700' : '' }}">
                <i class="fas fa-warehouse mr-3"></i>
                <span>Laporan penjualan per kategori barang</span>
            </a>

            <a href="{{ route('owner.laporan.masaPenitipanHabis') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.masaPenitipanHabis') ? 'bg-green-700' : '' }}">
                <i class="fas fa-warehouse mr-3"></i>
                <span>Laporan Masa Penitipan Habis</span>
            </a>

            <a href="{{ route('owner.laporan.request-donasi') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.request-donasi') ? 'bg-green-700' : '' }}">
                <i class="fas fa-file-invoice mr-3"></i>
                <span>Laporan Request Donasi</span>
            </a>

            <a href="{{ route('owner.laporan.donasi-barang') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.donasi-barang') ? 'bg-green-700' : '' }}">
                <i class="fas fa-box-open mr-3"></i>
                <span>Laporan Donasi Barang</span>
            </a>

            <a href="{{ route('owner.laporan.transaksi-penitip') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.laporan.transaksi-penitip*') ? 'bg-green-700' : '' }}">
                <i class="fas fa-user-check mr-3"></i>
                <span>Laporan Transaksi Penitip</span>
            </a>

        </nav>
        
        <div class="absolute-cinema bottom-0 w-full p-4 border-t border-green-700">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="flex items-center text-green-300 hover:text-white w-full">
                    <i class="fas fa-sign-out-alt mr-3"></i>
                    <span>Logout</span>
                </button>
            </form>
        </div>
    </aside>
    
    <!-- Main Content -->
    <div class="ml-64 w-full">
        <!-- Header -->
        <header class="bg-white shadow-md px-6 py-3 flex justify-between items-center">
            <div>
                <h1 class="text-lg font-semibold text-gray-700">
                    @if(request()->routeIs('owner.dashboard'))
                        Dashboard
                    @elseif(request()->routeIs('owner.donasi.request'))
                        Daftar Request Donasi
                    @elseif(request()->routeIs('owner.donasi.history'))
                        History Donasi
                    @elseif(request()->routeIs('owner.donasi.barang'))
                        Barang untuk Donasi
                    @elseif(request()->routeIs('owner.donasi.edit'))
                        Update Informasi Donasi
                    @elseif(request()->routeIs('owner.laporan.penjualan-bulanan-index'))
                        Laporan Penjualan Bulanan
                    @elseif(request()->routeIs('owner.laporan.komisi-bulanan-index'))
                        Laporan Komisi Bulanan
                    @elseif(request()->routeIs('owner.laporan.stok-gudang-index'))
                        Laporan Stok Gudang
                    @elseif(request()->routeIs('owner.laporan.laporanKategori'))
                        Laporan penjualan per kategori barang
                    @elseif(request()->routeIs('owner.laporan.masaPenitipanHabis'))
                        Laporan Masa Penitipan Habis
                    @elseif(request()->routeIs('owner.laporan.request-donasi'))
                        Laporan Request Donasi
                    @elseif(request()->routeIs('owner.laporan.donasi-barang'))
                        Laporan Donasi Barang
                    @elseif(request()->routeIs('owner.laporan.transaksi-penitip*'))
                        Laporan Transaksi Penitip
                    @else
                        ReUseMart Owner Panel
                    @endif
                </h1>
            </div>
            
            <div class="flex items-center">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-800">{{ session('user')['nama'] ?? 'GILROT' }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst(session('role')) }}</p>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-white p-4 text-center text-gray-500 text-sm border-t">
            <p>&copy; {{ date('Y') }} ReUseMart. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>