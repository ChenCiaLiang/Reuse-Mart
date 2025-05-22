<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Panel - ReUseMart</title>
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
            <p class="text-xs text-green-300 mt-1">Panel Owner</p>
        </div>
        
        <nav class="mt-5">
            
            <a href="{{ route('owner.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.dashboard') ? 'bg-green-700' : '' }}">
                <i class="fas fa-hands-helping mr-3"></i>
                <span>Dashboard</span>
            </a>

            <a href="{{ route('owner.donasi.request') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.request') ? 'bg-green-700' : '' }}">
                <i class="fas fa-hands-helping mr-3"></i>
                <span>Request Donasi</span>
            </a>
            
            <a href="{{ route('owner.donasi.history') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.history') ? 'bg-green-700' : '' }}">
                <i class="fas fa-history mr-3"></i>
                <span>History Donasi</span>
            </a>
            
            <a href="{{ route('owner.donasi.barang') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('owner.barang') ? 'bg-green-700' : '' }}">
                <i class="fas fa-box mr-3"></i>
                <span>Barang untuk Donasi</span>
            </a>
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
                    @if(request()->routeIs('owner.dashboard'))
                        Dashboard
                    @elseif(request()->routeIs('owner.request'))
                        Daftar Request Donasi
                    @elseif(request()->routeIs('owner.history'))
                        History Donasi
                    @elseif(request()->routeIs('owner.barang'))
                        Barang untuk Donasi
                    @elseif(request()->routeIs('owner.edit'))
                        Update Informasi Donasi
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