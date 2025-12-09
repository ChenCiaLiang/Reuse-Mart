<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gudang Dashboard') - ReUseMart</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans flex">
    <!-- Sidebar -->
    <aside class="w-64 h-screen bg-green-800 text-white fixed">
        <div class="p-6">
            <div class="flex items-center space-x-2">
                <img src="{{ asset('images/Logo/Logo.jpg') }}" alt="ReUseMart Logo" class="h-8 rounded">
                <span class="text-lg font-bold">ReUseMart</span>
            </div>
            <p class="text-xs text-green-300 mt-1">Panel Pegawai Gudang</p>
        </div>
        
        <nav class="mt-5">
            <!-- Dashboard -->
            <a href="{{ route('gudang.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('gudang.dashboard') ? 'bg-green-700' : '' }}">
                <i class="fa-solid fa-tachometer-alt mr-3"></i>
                <span>Dashboard</span>
            </a>
            
            <!-- Daftar Pengiriman -->
            <a href="{{ route('gudang.pengiriman.index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('gudang.pengiriman.*') ? 'bg-green-700' : '' }}">
                <i class="fa-solid fa-truck mr-3"></i>
                <span>Daftar Pengiriman</span>
            </a>
            
            <!-- Transaksi Penitipan - New -->
            <a href="{{ route('gudang.penitipan.index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('gudang.penitipan.*') ? 'bg-green-700' : '' }}">
                <i class="fa-solid fa-file-invoice mr-3"></i>
                <span>Transaksi Penitipan</span>
            </a>
            
            <!-- Laporan -->
            {{--<a href="{{ route('gudang.laporan.index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('gudang.laporan.*') ? 'bg-green-700' : '' }}">
                <i class="fa-solid fa-chart-bar mr-3"></i>
                <span>Laporan</span>
            </a>--}}
        </nav>
        
        <div class="absolute bottom-0 w-full p-4 border-t border-green-700">
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
                    @if(request()->routeIs('gudang.dashboard'))
                        Dashboard Gudang
                    @elseif(request()->routeIs('gudang.pengiriman.index'))
                        Daftar Pengiriman
                    @elseif(request()->routeIs('gudang.pengiriman.show'))
                        Detail Pengiriman
                    @elseif(request()->routeIs('gudang.penitipan.*'))
                        Manajemen Penitipan
                    @elseif(request()->routeIs('gudang.penitipan.index'))
                        Daftar Transaksi Penitipan
                    @elseif(request()->routeIs('gudang.penitipan.create'))
                        Tambah Transaksi Penitipan
                    @elseif(request()->routeIs('gudang.penitipan.show'))
                        Detail Transaksi Penitipan
                    @elseif(request()->routeIs('gudang.penitipan.edit'))
                        Edit Transaksi Penitipan
                    @else
                        @yield('page-title', 'ReUseMart Pegawai Gudang Panel')
                    @endif
                </h1>
                @yield('breadcrumb')
            </div>
            
            <div class="flex items-center space-x-4">
                <!-- Notifications -->
                <div class="relative">
                    <button class="text-gray-600 hover:text-gray-900 relative">
                        <i class="fa-solid fa-bell text-lg"></i>
                        @if(isset($notificationCount) && $notificationCount > 0)
                            <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full h-5 w-5 flex items-center justify-center">
                                {{ $notificationCount }}
                            </span>
                        @endif
                    </button>
                </div>
                
                <!-- User Profile -->
                <div class="relative">
                    <button class="flex items-center text-gray-700 focus:outline-none">
                        <div class="text-right mr-3">
                            <p class="text-sm font-medium">{{ session('user')['nama'] ?? 'Nama Pegawai' }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst(session('role')) ?? 'Pegawai Gudang' }}</p>
                        </div>
                        <div class="h-8 w-8 bg-green-600 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-user text-white text-sm"></i>
                        </div>
                    </button>
                </div>
            </div>
        </header>
        
        <!-- Page Content -->
        <main class="p-6">
            <!-- Alert Messages -->
            @if(session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
        
        <!-- Footer -->
        <footer class="bg-white p-4 text-center text-gray-500 text-sm border-t">
            <p>&copy; {{ date('Y') }} ReUseMart. All rights reserved.</p>
        </footer>
    </div>
    
    <!-- Flash Message -->
    @if (session('status'))
    <div id="flash-message" class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg z-50">
        {{ session('status') }}
    </div>
    <script>
        setTimeout(function() {
            const flashMessage = document.getElementById('flash-message');
            if (flashMessage) {
                flashMessage.style.display = 'none';
            }
        }, 3000);
    </script>
    @endif

    @yield('scripts')
</body>
</html>