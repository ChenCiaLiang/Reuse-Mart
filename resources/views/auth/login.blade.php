<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    @vite('resources/css/app.css')
    <title>Login</title>
    <style>
        .login-bg {
            background-image: url('/images/bg/Login-BG.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="login-bg min-h-screen flex flex-col">
    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4">
        <div class="bg-white bg-opacity-90 rounded-3xl shadow-lg p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Selamat Datang!</h2>

            <!-- Logo -->
            <div class="flex justify-center mb-6">
                <div class="rounded-lg p-2 w-32 h-16 flex flex-col items-center justify-center">
                    <img src="{{ asset('/images/Logo/Logo.jpg') }}" alt="LOGO">
                </div>
            </div>

            <!-- Login Form -->
            <form method="POST" action="{{ route('login') }}">
                @csrf
                @if ($errors->any())
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                
                <!-- Username Field -->
                <div class="mb-4">
                    <label for="email" class="block text-gray-800 font-medium mb-2">Email</label>
                    <input type="text" id="email" name="email" placeholder="Email" value="{{ old('email') }}" required
                        class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>

                <!-- Password Field -->
                <div class="mb-2">
                    <label for="password" class="block text-gray-800 font-medium mb-2">Password</label>
                    <div class="relative">
                        <input type="password" id="password" placeholder="Password" name="password" required
                            class="w-full px-4 py-2 bg-gray-100 border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
                        <button type="button"
                            class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Forgot Password Link -->
                <div class="text-center mb-6">
                    <a href="#" class="text-sm text-gray-700 hover:text-green-600">Forgot Password</a>
                </div>

                <!-- Login Button -->
                <div class="flex justify-center">
                    <button type="submit"
                        class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-8 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
                        Login
                    </button>
                </div>
            </form>
            <div class="mt-3 text-center">
                <p class="mb-0">Belum memiliki akun?</p>
                <div class="btn-group" role="group" aria-label="Basic example">
                    <div class="flex justify-center gap-1 mt-2">
                        <a href="{{ route('register.pembeli') }}"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-8 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
                            Daftar sebagai Pembeli
                        </a>
                        <a href="{{ route('register.organisasi') }}"
                            class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-8 rounded-full focus:outline-none focus:ring-2 focus:ring-green-500">
                            Daftar sebagai Organisasi
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Link Kembali ke Dashboard -->
            <div class="mt-6 text-center">
                <a href="{{ url('/') }}" class="text-green-600 hover:text-green-800 font-medium">
                    Kembali ke Dashboard
                </a>
            </div>
        </div>
    </main>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.querySelector('button[type="button"]');
            const password = document.getElementById('password');

            togglePassword.addEventListener('click', function() {
                const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
                password.setAttribute('type', type);
            });
        });
    </script>
</body>

</html>