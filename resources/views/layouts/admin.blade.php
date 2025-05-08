<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - ReUseMart</title>
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
            <p class="text-xs text-green-300 mt-1">Panel Admin</p>
        </div>
        
        <nav class="mt-5">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('admin.dashboard') ? 'bg-green-700' : '' }}">
                <i class="fas fa-home mr-3"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="{{ route('admin.pegawai.index') }}" class="flex items-center px-6 py-3 hover:bg-green-700 {{ request()->routeIs('admin.pegawai.*') ? 'bg-green-700' : '' }}">
                <i class="fas fa-users mr-3"></i>
                <span>Manajemen Pegawai</span>
            </a>
            
            <!-- Tambahkan menu lain sesuai kebutuhan -->
            <a href="#" class="flex items-center px-6 py-3 hover:bg-green-700">
                <i class="fas fa-user-tie mr-3"></i>
                <span>Manajemen Jabatan</span>
            </a>
            
            <a href="#" class="flex items-center px-6 py-3 hover:bg-green-700">
                <i class="fas fa-building mr-3"></i>
                <span>Manajemen Organisasi</span>
            </a>
            
            <a href="#" class="flex items-center px-6 py-3 hover:bg-green-700">
                <i class="fas fa-gift mr-3"></i>
                <span>Manajemen Merchandise</span>
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
                    @if(request()->routeIs('admin.dashboard'))
                        Dashboard
                    @elseif(request()->routeIs('admin.pegawai.index'))
                        Manajemen Pegawai
                    @elseif(request()->routeIs('admin.pegawai.create'))
                        Tambah Pegawai Baru
                    @elseif(request()->routeIs('admin.pegawai.edit'))
                        Edit Pegawai
                    @elseif(request()->routeIs('admin.pegawai.show'))
                        Detail Pegawai
                    @else
                        ReUseMart Admin Panel
                    @endif
                </h1>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <button class="flex items-center text-gray-700 focus:outline-none">
                        <div class="text-right">
                            {{--<p class="text-sm font-medium">{{ Auth::user()->nama }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()->jabatan->nama }}</p>
                            --}}

                            <!-- kode test tanpa uth -->
                            <p class="text-sm font-medium">{{ Auth::user()?->nama ?? 'Admin Testing' }}</p>
                            <p class="text-xs text-gray-500">{{ Auth::user()?->jabatan?->nama ?? 'Development Mode' }}</p>
                        </div>
                        <img src="{{ asset('images/users/admin.jpg') }}" alt="Profile" class="h-8 w-8 rounded-full ml-2" 
                            onerror="this.src='{{ asset('images/users/default.jpg') }}'">
                    </button>
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
    
    <!-- Flash Message -->
    @if (session('status'))
    <div id="flash-message" class="fixed bottom-4 right-4 bg-green-600 text-white px-4 py-2 rounded shadow-lg">
        {{ session('status') }}
    </div>
    <script>
        setTimeout(function() {
            document.getElementById('flash-message').style.display = 'none';
        }, 3000);
    </script>
    @endif
</body>
</html>