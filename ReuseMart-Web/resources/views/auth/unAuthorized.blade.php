<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UnAuthorized - ReUseMart</title>
    @vite('resources/css/app.css')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shake {
            animation: shake 0.8s cubic-bezier(.36,.07,.19,.97) both;
        }
        .fadeIn {
            animation: fadeIn 1s ease-in;
        }
        @keyframes fadeIn {
            0% { opacity: 0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<body class="bg-gradient-to-br from-gray-100 to-gray-200 font-sans min-h-screen flex items-center justify-center p-4">
    <div class="max-w-lg w-full bg-white rounded-2xl shadow-xl overflow-hidden fadeIn">
        <div class="bg-red-600 text-white p-6 flex items-center justify-between">
            <h1 class="text-xl font-bold flex items-center">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                403 - Akses Dilarang
            </h1>
            <div class="shake" id="lock-icon">
                <i class="fas fa-lock text-2xl"></i>
            </div>
        </div>
        <div class="p-8">
            <div class="border-b border-gray-200 pb-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-4">Oops! Sepertinya Anda tersesat.</h2>
                <p class="text-gray-600">
                    Anda tidak memiliki izin untuk mengakses halaman ini. Ini mungkin karena:
                </p>
                <ul class="text-gray-600 mt-4 space-y-2 list-disc pl-5">
                    <li>Anda belum login</li>
                    <li>Akun Anda tidak memiliki hak akses yang cukup</li>
                    <li>Anda perlu mengaktifkan fitur tertentu terlebih dahulu</li>
                </ul>
            </div>
            <div class="flex flex-col sm:flex-row justify-center space-y-3 sm:space-y-0 sm:space-x-4">
                <button onclick="window.history.back()" class="bg-gray-800 hover:bg-gray-900 text-white font-medium py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
                </button>
                <form action="{{ route('logout') }}" method="POST">
                @csrf
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-medium py-3 px-6 rounded-lg transition duration-300 flex items-center justify-center">
                        <i class="fas fa-home mr-2"></i> Login
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Menambahkan animasi shake saat icon diklik
        document.getElementById('lock-icon').addEventListener('click', function() {
            this.classList.remove('shake');
            void this.offsetWidth; // Trigger reflow
            this.classList.add('shake');
        });
        
        // Juga lakukan animasi setelah page load
        window.addEventListener('load', function() {
            setTimeout(function() {
                document.getElementById('lock-icon').classList.add('shake');
            }, 1000);
        });
    </script>
</body>
</html>