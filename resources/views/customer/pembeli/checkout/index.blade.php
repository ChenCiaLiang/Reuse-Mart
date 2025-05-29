@extends('layouts.customer')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid lg:grid-cols-2 gap-8">
            <!-- Left Column - Checkout Form -->
            <div class="bg-white rounded-lg shadow p-6">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Checkout</h1>
                    <a href="{{ route('pembeli.cart.show') }}" class="text-green-600 hover:text-green-700 text-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Kembali ke Keranjang
                    </a>
                </div>

                @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
                @endif

                <!-- Shipping Method Section (Fungsionalitas 58) -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Metode Pengiriman</h2>
                    <div class="space-y-3">
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="metode_pengiriman" value="kurir" checked 
                                   class="h-4 w-4 text-green-600 focus:ring-green-500" onchange="updateShippingMethod()">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">Pengiriman dengan Kurir</div>
                                <div class="text-sm text-gray-500">Khusus area Yogyakarta - Gratis ongkir untuk pembelian ≥ Rp 1.500.000</div>
                            </div>
                        </label>
                        
                        <label class="flex items-center p-4 border rounded-lg cursor-pointer hover:bg-gray-50">
                            <input type="radio" name="metode_pengiriman" value="ambil_sendiri" 
                                   class="h-4 w-4 text-green-600 focus:ring-green-500" onchange="updateShippingMethod()">
                            <div class="ml-3">
                                <div class="font-medium text-gray-900">Ambil Sendiri ke Gudang</div>
                                <div class="text-sm text-gray-500">Jam operasional: 08:00 - 20:00 WIB</div>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Shipping Address Section (Fungsionalitas 59) -->
                <div id="addressSection" class="mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">Alamat Pengiriman</h2>
                        <a href="{{ route('pembeli.alamat.create') }}" class="text-green-600 hover:text-green-700 text-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Alamat
                        </a>
                    </div>
                    
                    @if(count($alamatList) > 0)
                        <div class="space-y-3">
                            @foreach($alamatList as $alamat)
                            <label class="flex items-start p-4 border rounded-lg cursor-pointer hover:bg-gray-50 {{ $alamat->statusDefault ? 'border-green-500 bg-green-50' : '' }}">
                                <input type="radio" name="idAlamat" value="{{ $alamat->idAlamat }}" 
                                       {{ $alamat->statusDefault ? 'checked' : '' }}
                                       class="h-4 w-4 text-green-600 focus:ring-green-500 mt-1" onchange="updateShippingAddress()">
                                <div class="ml-3 flex-grow">
                                    <div class="flex items-center">
                                        <span class="font-medium text-gray-900">{{ $alamat->jenis }}</span>
                                        @if($alamat->statusDefault)
                                        <span class="ml-2 bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Default</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-gray-600 mt-1">{{ $alamat->alamatLengkap }}</p>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 bg-gray-50 rounded-lg">
                            <i class="fas fa-map-marker-alt text-2xl text-gray-400 mb-2"></i>
                            <p class="text-gray-500 mb-4">Belum ada alamat tersimpan</p>
                            <a href="{{ route('pembeli.alamat.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">
                                Tambah Alamat Baru
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Points Usage Section (Fungsionalitas 61) -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Gunakan Poin ReUseMart</h2>
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-700">Poin Anda:</span>
                            <span class="font-semibold text-yellow-600">{{ number_format($pembeli->poin) }} poin</span>
                        </div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm text-gray-700">Nilai tukar:</span>
                            <span class="text-sm text-gray-600">100 poin = Rp 1.000</span>
                        </div>
                        
                        <div class="flex items-center space-x-3">
                            <label class="flex items-center">
                                <input type="checkbox" id="usePoints" class="h-4 w-4 text-green-600 focus:ring-green-500 rounded" 
                                       onchange="togglePointUsage()">
                                <span class="ml-2 text-sm text-gray-700">Gunakan poin</span>
                            </label>
                        </div>
                        
                        <div id="pointInputSection" class="mt-3 hidden">
                            <div class="flex space-x-2">
                                <input type="number" id="poinDigunakan" min="0" max="{{ $pembeli->poin }}" value="0"
                                       class="flex-grow px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                       placeholder="Masukkan jumlah poin" onchange="updatePointUsage()">
                                <button type="button" onclick="useAllPoints()" 
                                        class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md text-sm">
                                    Gunakan Semua
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">
                                Diskon: <span id="diskonPoin">Rp 0</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Order Summary -->
            <div class="bg-white rounded-lg shadow p-6 h-fit">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Pesanan</h2>
                
                <!-- Cart Items -->
                <div class="space-y-3 mb-6">
                    @foreach($cartItems as $item)
                    <div class="flex items-center space-x-3">
                        @php
                        $gambarArray = $item['product']->gambar ? explode(',', $item['product']->gambar) : ['default.jpg'];
                        $thumbnail = $gambarArray[0];
                        @endphp
                        <img class="h-12 w-12 rounded object-cover"
                            src="{{ asset('uploads/produk/' . trim($thumbnail)) }}"
                            alt="{{ $item['product']->deskripsi }}"
                            onerror="this.src='{{ asset('images/default.jpg') }}'">
                        <div class="flex-grow">
                            <h4 class="text-sm font-medium text-gray-900 truncate">{{ $item['product']->deskripsi }}</h4>
                            <p class="text-xs text-gray-500">Qty: {{ $item['quantity'] }}</p>
                        </div>
                        <span class="text-sm font-semibold">Rp {{ number_format($item['product']->hargaJual, 0, ',', '.') }}</span>
                    </div>
                    @endforeach
                </div>

                <!-- Price Breakdown (Fungsionalitas 60, 62) -->
                <div class="border-t border-gray-200 pt-4 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Subtotal</span>
                        <span id="subtotalAmount">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                    </div>
                    
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-600">Ongkos Kirim</span>
                        <span id="ongkirAmount">-</span>
                    </div>
                    
                    <div class="flex justify-between text-sm text-yellow-600" id="poinDiscountRow" style="display: none;">
                        <span>Diskon Poin</span>
                        <span id="poinDiscountAmount">- Rp 0</span>
                    </div>
                    
                    <div class="border-t border-gray-200 pt-2">
                        <div class="flex justify-between text-lg font-bold">
                            <span>Total</span>
                            <span class="text-green-600" id="totalAmount">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    
                    <!-- Points Earned (Fungsionalitas 62) -->
                    <div class="bg-green-50 border border-green-200 rounded p-3 mt-4">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-green-700">Poin yang akan didapat</span>
                            <span class="font-semibold text-green-600" id="poinDapat">0 poin</span>
                        </div>
                        <p class="text-xs text-green-600 mt-1">
                            *Bonus 20% poin untuk pembelian > Rp 500.000
                        </p>
                    </div>
                </div>

                <!-- Checkout Button -->
                <button onclick="proceedCheckout()" id="checkoutBtn"
                        class="w-full mt-6 bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                    <i class="fas fa-credit-card mr-2"></i>
                    Lanjut ke Pembayaran
                </button>
                
                <p class="text-xs text-gray-500 text-center mt-3">
                    Dengan melanjutkan, Anda menyetujui syarat dan ketentuan ReUseMart
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
        <span class="text-gray-700">Memproses...</span>
    </div>
</div>

<script>
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
    currentCalculation.poin_didapat = Math.floor(currentCalculation.total_akhir / 10000);
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

// Proceed to checkout (will be implemented in next functionality)
function proceedCheckout() {
    // Validate shipping method and address
    const metodePengiriman = document.querySelector('input[name="metode_pengiriman"]:checked');
    if (!metodePengiriman) {
        alert('Silakan pilih metode pengiriman');
        return;
    }
    
    if (metodePengiriman.value === 'kurir') {
        const alamat = document.querySelector('input[name="idAlamat"]:checked');
        if (!alamat) {
            alert('Silakan pilih alamat pengiriman');
            return;
        }
    }
    
    // For now, show confirmation (will be replaced with actual checkout process)
    const confirmation = confirm(
        `Konfirmasi Pesanan:\n` +
        `Total: Rp ${new Intl.NumberFormat('id-ID').format(currentCalculation.total_akhir)}\n` +
        `Poin digunakan: ${currentCalculation.poin_digunakan}\n` +
        `Poin didapat: ${currentCalculation.poin_didapat}\n\n` +
        `Lanjutkan ke pembayaran?`
    );
    
    if (confirmation) {
        alert('Fitur checkout akan segera tersedia. Terima kasih!');
        // TODO: Implement actual checkout process (Fungsionalitas 63-70)
    }
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