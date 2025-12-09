@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-credit-card text-blue-600 mr-3"></i>
                        Checkout
                    </h1>
                    <p class="text-gray-600 text-sm mt-1">Lengkapi detail pemesanan Anda</p>
                </div>
                <a href="{{ route('pembeli.cart.show') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> 
                    Kembali ke Keranjang
                </a>
            </div>
        </div>

        @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
            <div class="flex">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
                <p>{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <div class="grid lg:grid-cols-5 gap-6">
            <!-- Left Column - Checkout Form (3/5 width) -->
            <div class="lg:col-span-3 space-y-6">
                <!-- Shipping Method Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-truck mr-2"></i>
                            Metode Pengiriman
                        </h2>
                    </div>
                    
                    <div class="p-6 space-y-4">
                        <label class="group flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="metode_pengiriman" value="kurir" checked 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" onchange="updateShippingMethod()">
                            <div class="ml-4 flex-grow">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-shipping-fast text-blue-600 mr-2"></i>
                                        Pengiriman dengan Kurir
                                    </div>
                                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Gratis Ongkir > 1.5jt</span>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">Khusus area Yogyakarta - Estimasi 1-2 hari kerja</div>
                            </div>
                        </label>
                        
                        <label class="group flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-300 hover:bg-blue-50 transition-all duration-200 has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                            <input type="radio" name="metode_pengiriman" value="ambil_sendiri" 
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300" onchange="updateShippingMethod()">
                            <div class="ml-4 flex-grow">
                                <div class="flex items-center justify-between">
                                    <div class="font-medium text-gray-900 flex items-center">
                                        <i class="fas fa-store text-green-600 mr-2"></i>
                                        Ambil Sendiri ke Gudang
                                    </div>
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">GRATIS</span>
                                </div>
                                <div class="text-sm text-gray-600 mt-1">
                                    <i class="fas fa-clock mr-1"></i>
                                    Jam operasional: 08:00 - 20:00 WIB
                                </div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Shipping Address Section -->
                <div id="addressSection" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white flex items-center">
                                <i class="fas fa-map-marker-alt mr-2"></i>
                                Alamat Pengiriman
                            </h2>
                            <a href="{{ route('pembeli.alamat.create') }}" class="text-white hover:text-green-100 text-sm bg-green-600 hover:bg-green-700 px-3 py-1 rounded-lg transition-colors">
                                <i class="fas fa-plus mr-1"></i> Tambah Alamat
                            </a>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        @if(count($alamatList) > 0)
                            <div class="space-y-3">
                                @foreach($alamatList as $alamat)
                                <label class="group flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-green-300 hover:bg-green-50 transition-all duration-200 {{ $alamat->statusDefault ? 'border-green-500 bg-green-50' : '' }} has-[:checked]:border-green-500 has-[:checked]:bg-green-50">
                                    <input type="radio" name="idAlamat" value="{{ $alamat->idAlamat }}" 
                                           {{ $alamat->statusDefault ? 'checked' : '' }}
                                           class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 mt-1" onchange="updateShippingAddress()">
                                    <div class="ml-4 flex-grow">
                                        <div class="flex items-center justify-between mb-2">
                                            <span class="font-medium text-gray-900 flex items-center">
                                                <i class="fas fa-home text-gray-500 mr-2"></i>
                                                {{ $alamat->jenis }}
                                            </span>
                                            @if($alamat->statusDefault)
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                <i class="fas fa-star mr-1"></i>Default
                                            </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600">{{ $alamat->alamatLengkap }}</p>
                                    </div>
                                </label>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8 bg-gray-50 rounded-lg">
                                <i class="fas fa-map-marker-alt text-3xl text-gray-400 mb-3"></i>
                                <p class="text-gray-500 mb-4">Belum ada alamat tersimpan</p>
                                <a href="{{ route('pembeli.alamat.create') }}" class="inline-flex items-center bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                    <i class="fas fa-plus mr-2"></i>
                                    Tambah Alamat Baru
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Points Usage Section -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 px-6 py-4">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-coins mr-2"></i>
                            Gunakan Poin ReUseMart
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <div class="bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-4">
                                <div class="flex items-center">
                                    <div class="bg-yellow-500 p-2 rounded-lg mr-3">
                                        <i class="fas fa-wallet text-white"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm text-gray-700 block">Poin Tersedia:</span>
                                        <span class="font-bold text-yellow-600 text-lg">{{ number_format($pembeli->poin) }} poin</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm text-gray-600 block">Nilai tukar:</span>
                                    <span class="text-sm font-medium text-gray-800">100 poin = Rp 1.000</span>
                                </div>
                            </div>
                            
                            <div class="flex items-center space-x-4 mb-4">
                                <label class="flex items-center cursor-pointer">
                                    <input type="checkbox" id="usePoints" class="h-4 w-4 text-yellow-600 focus:ring-yellow-500 rounded border-gray-300" 
                                           onchange="togglePointUsage()">
                                    <span class="ml-2 text-sm font-medium text-gray-700">Gunakan poin untuk diskon</span>
                                </label>
                            </div>
                            
                            <div id="pointInputSection" class="hidden">
                                <div class="flex space-x-2">
                                    <input type="number" id="poinDigunakan" min="0" max="{{ $pembeli->poin }}" value="0"
                                           class="flex-grow px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:border-yellow-500"
                                           placeholder="Masukkan jumlah poin" onchange="updatePointUsage()">
                                    <button type="button" onclick="useAllPoints()" 
                                            class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg text-sm font-medium transition-colors">
                                        Gunakan Semua
                                    </button>
                                </div>
                                <p class="text-xs text-gray-600 mt-2 flex items-center">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    Diskon: <span id="diskonPoin" class="font-medium text-yellow-600 ml-1">Rp 0</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary (2/5 width) -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-6">
                    <div class="bg-gradient-to-r from-purple-500 to-purple-600 px-6 py-4 rounded-t-xl">
                        <h2 class="text-lg font-semibold text-white flex items-center">
                            <i class="fas fa-receipt mr-2"></i>
                            Ringkasan Pesanan
                        </h2>
                    </div>
                    
                    <div class="p-6">
                        <!-- Cart Items -->
                        <div class="space-y-3 mb-6 max-h-64 overflow-y-auto">
                            @foreach($cartItems as $item)
                            <div class="flex items-center space-x-3 p-3 bg-gray-50 rounded-lg">
                                @php
                                $gambarArray = $item['product']->gambar ? explode(',', $item['product']->gambar) : ['default.jpg'];
                                $thumbnail = $gambarArray[0];
                                @endphp
                                <div class="relative">
                                    <img class="h-12 w-12 rounded-lg object-cover bg-gray-200 border border-gray-200"
                                        src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                        alt="{{ $item['product']->deskripsi }}"
                                        onerror="handleCheckoutImageError(this)"
                                        data-attempted-default="false">
                                    <span class="absolute -top-1 -right-1 bg-purple-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">1</span>
                                </div>
                                <div class="flex-grow min-w-0">
                                    <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['product']->deskripsi }}</h4>
                                    <p class="text-xs text-gray-500">{{ $item['product']->kategori->nama }}</p>
                                </div>
                                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($item['product']->hargaJual, 0, ',', '.') }}</span>
                            </div>
                            @endforeach
                        </div>

                        <!-- Price Breakdown -->
                        <div class="border-t border-gray-200 pt-4 space-y-3">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Subtotal ({{ count($cartItems) }} item)</span>
                                <span id="subtotalAmount" class="font-medium">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 flex items-center">
                                    <i class="fas fa-truck mr-1"></i>
                                    Ongkos Kirim
                                </span>
                                <span id="ongkirAmount" class="font-medium">-</span>
                            </div>
                            
                            <div class="flex justify-between text-sm text-yellow-600" id="poinDiscountRow" style="display: none;">
                                <span class="flex items-center">
                                    <i class="fas fa-coins mr-1"></i>
                                    Diskon Poin
                                </span>
                                <span id="poinDiscountAmount" class="font-medium">- Rp 0</span>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-3">
                                <div class="flex justify-between text-lg font-bold">
                                    <span class="text-gray-900">Total Pembayaran</span>
                                    <span class="text-purple-600" id="totalAmount">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Points Earned -->
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mt-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <div class="bg-green-500 p-2 rounded-lg mr-3">
                                        <i class="fas fa-gift text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <span class="text-sm text-green-700 font-medium">Poin Reward</span>
                                        <p class="text-xs text-green-600">*Bonus 20% untuk pembelian > Rp 500.000</p>
                                    </div>
                                </div>
                                <span class="font-bold text-green-600" id="poinDapat">0 poin</span>
                            </div>
                        </div>

                        <!-- Checkout Button -->
                        <button onclick="proceedCheckout()" id="checkoutBtn"
                                class="w-full mt-6 bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-semibold py-4 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                            <i class="fas fa-lock mr-2"></i>
                            Bayar Sekarang
                        </button>
                        
                        <div class="mt-4 text-center">
                            <div class="flex items-center justify-center space-x-4 text-xs text-gray-500">
                                <div class="flex items-center">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    <span>Transaksi Aman</span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-lock mr-1"></i>
                                    <span>Data Terlindungi</span>
                                </div>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">
                                Dengan melanjutkan, Anda menyetujui syarat dan ketentuan ReUseMart
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-6 flex items-center space-x-3 shadow-2xl">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-purple-500"></div>
        <span class="text-gray-700 font-medium">Memproses pesanan...</span>
    </div>
</div>

<script>
console.log('Checkout page loaded');

// =============================================
// PERBAIKAN: Fungsi untuk handle image error di checkout
// =============================================
function handleCheckoutImageError(img) {
    console.log('Checkout image failed to load:', img.src);
    
    // Cek apakah sudah pernah mencoba default image
    if (img.dataset.attemptedDefault === 'true') {
        console.log('Default image also failed, using placeholder');
        // Jika default image juga gagal, ganti dengan placeholder
        img.style.display = 'none';
        
        // Buat placeholder div dengan ukuran yang sesuai untuk checkout
        const placeholder = document.createElement('div');
        placeholder.className = 'h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0 border border-gray-200';
        placeholder.innerHTML = '<i class="fas fa-image text-gray-400"></i>';
        
        // Replace image dengan placeholder
        img.parentNode.replaceChild(placeholder, img);
        return;
    }
    
    // Tandai bahwa kita sudah mencoba default
    img.dataset.attemptedDefault = 'true';
    
    // Coba beberapa path default yang sama dengan cart
    const defaultPaths = [
        '/images/default.jpg',
        '/images/produk/default.jpg', 
        '/images/no-image.png',
        '/images/placeholder.jpg'
    ];
    
    // Coba path default pertama yang berbeda
    const currentSrc = img.src;
    for (let path of defaultPaths) {
        if (!currentSrc.includes(path)) {
            console.log('Trying default image:', path);
            img.src = path;
            return;
        }
    }
    
    // Jika semua default gagal, buat placeholder
    console.log('All default images failed, creating placeholder');
    img.style.display = 'none';
    const placeholder = document.createElement('div');
    placeholder.className = 'h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center flex-shrink-0 border border-gray-200';
    placeholder.innerHTML = '<i class="fas fa-image text-gray-400"></i>';
    img.parentNode.replaceChild(placeholder, img);
}

// Global variables
let currentCalculation = {
    subtotal: {{ $subtotal }},
    ongkir: 0,
    poin_digunakan: 0,
    diskon_poin: 0,
    total_akhir: {{ $subtotal }},
    poin_didapat: 0,
    metode_pengiriman: 'kurir'
};

// Initialize page
document.addEventListener('DOMContentLoaded', function() {
    updateCalculation();
});

// Update shipping method (Fungsionalitas 58)
function updateShippingMethod() {
    const method = document.querySelector('input[name="metode_pengiriman"]:checked').value;
    currentCalculation.metode_pengiriman = method;
    
    // Show/hide address section
    const addressSection = document.getElementById('addressSection');
    if (method === 'kurir') {
        addressSection.style.display = 'block';
    } else {
        addressSection.style.display = 'none';
    }
    
    updateCalculation();
}

// Update shipping address (Fungsionalitas 59)
function updateShippingAddress() {
    const selectedAddress = document.querySelector('input[name="idAlamat"]:checked');
    if (selectedAddress) {
        // Address updated, recalculate if needed
        updateCalculation();
    }
}

// Toggle point usage (Fungsionalitas 61)
function togglePointUsage() {
    const usePoints = document.getElementById('usePoints').checked;
    const pointInputSection = document.getElementById('pointInputSection');
    
    if (usePoints) {
        pointInputSection.classList.remove('hidden');
    } else {
        pointInputSection.classList.add('hidden');
        document.getElementById('poinDigunakan').value = 0;
        currentCalculation.poin_digunakan = 0;
        currentCalculation.diskon_poin = 0;
        updateCalculation();
    }
}

// Use all points
function useAllPoints() {
    const maxPoints = {{ $pembeli->poin }};
    document.getElementById('poinDigunakan').value = maxPoints;
    updatePointUsage();
}

// Update point usage (Fungsionalitas 61)
function updatePointUsage() {
    const poinInput = document.getElementById('poinDigunakan');
    const poinDigunakan = Math.min(parseInt(poinInput.value) || 0, {{ $pembeli->poin }});
    
    // Update input value if it exceeds maximum
    poinInput.value = poinDigunakan;
    
    currentCalculation.poin_digunakan = poinDigunakan;
    currentCalculation.diskon_poin = poinDigunakan * 10; // 1 poin = Rp 10
    
    // Update diskon display
    document.getElementById('diskonPoin').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(currentCalculation.diskon_poin);
    
    updateCalculation();
}

// Update calculation (Fungsionalitas 60, 62)
function updateCalculation() {
    // Calculate shipping cost
    if (currentCalculation.metode_pengiriman === 'kurir') {
        currentCalculation.ongkir = currentCalculation.subtotal >= 1500000 ? 0 : 100000;
    } else {
        currentCalculation.ongkir = 0;
    }
    
    // Calculate total
    const totalBeforeDiscount = currentCalculation.subtotal + currentCalculation.ongkir;
    const actualDiscount = Math.min(currentCalculation.diskon_poin, totalBeforeDiscount);
    currentCalculation.total_akhir = totalBeforeDiscount - actualDiscount;
    
    // Calculate points earned (Fungsionalitas 62)
    currentCalculation.poin_didapat = Math.floor(currentCalculation.subtotal / 10000);
    if (currentCalculation.total_akhir > 500000) {
        const bonus = Math.floor(currentCalculation.poin_didapat * 0.2);
        currentCalculation.poin_didapat += bonus;
    }
    
    // Update UI
    updatePriceDisplay();
}

// Update price display
function updatePriceDisplay() {
    document.getElementById('ongkirAmount').textContent = 
        currentCalculation.ongkir === 0 ? 'GRATIS' : 'Rp ' + new Intl.NumberFormat('id-ID').format(currentCalculation.ongkir);
    
    document.getElementById('totalAmount').textContent = 
        'Rp ' + new Intl.NumberFormat('id-ID').format(currentCalculation.total_akhir);
    
    document.getElementById('poinDapat').textContent = 
        new Intl.NumberFormat('id-ID').format(currentCalculation.poin_didapat) + ' poin';
    
    // Show/hide point discount row
    const poinDiscountRow = document.getElementById('poinDiscountRow');
    if (currentCalculation.diskon_poin > 0) {
        poinDiscountRow.style.display = 'flex';
        document.getElementById('poinDiscountAmount').textContent = 
            '- Rp ' + new Intl.NumberFormat('id-ID').format(currentCalculation.diskon_poin);
    } else {
        poinDiscountRow.style.display = 'none';
    }
}

// Proceed to checkout (Fungsionalitas 63)
function proceedCheckout() {
    // Validate shipping method and address
    const metodePengiriman = document.querySelector('input[name="metode_pengiriman"]:checked');
    if (!metodePengiriman) {
        showNotification('Silakan pilih metode pengiriman', 'error');
        return;
    }
    
    if (metodePengiriman.value === 'kurir') {
        const alamat = document.querySelector('input[name="idAlamat"]:checked');
        if (!alamat) {
            showNotification('Silakan pilih alamat pengiriman', 'error');
            return;
        }
    }
    
    // Disable button and show loading
    const checkoutBtn = document.getElementById('checkoutBtn');
    checkoutBtn.disabled = true;
    checkoutBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses pesanan...';
    
    showLoading();
    
    // Prepare data
    const checkoutData = {
        metode_pengiriman: metodePengiriman.value,
        idAlamat: metodePengiriman.value === 'kurir' ? document.querySelector('input[name="idAlamat"]:checked')?.value : null,
        poin_digunakan: currentCalculation.poin_digunakan
    };
    
    // Send checkout request
    fetch('{{ route("pembeli.checkout.proceed") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify(checkoutData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            showNotification('Pesanan berhasil dibuat! Mengarahkan ke halaman pembayaran...', 'success');
            
            // Redirect ke halaman pembayaran
            setTimeout(() => {
                window.location.href = data.redirect_url;
            }, 1500);
        } else {
            // Reset button
            checkoutBtn.disabled = false;
            checkoutBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Bayar Sekarang';
            
            showNotification(data.error || 'Terjadi kesalahan saat memproses pesanan', 'error');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        
        // Reset button
        checkoutBtn.disabled = false;
        checkoutBtn.innerHTML = '<i class="fas fa-lock mr-2"></i>Bayar Sekarang';
        
        showNotification('Terjadi kesalahan saat memproses pesanan', 'error');
    });
}

// Fungsi helper untuk menampilkan notifikasi
function showNotification(message, type = 'info') {
    // Remove existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notif => notif.remove());
    
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-xl transition-all duration-300 max-w-sm ${
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
            <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
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

// Loading functions
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}
</script>
@endsection