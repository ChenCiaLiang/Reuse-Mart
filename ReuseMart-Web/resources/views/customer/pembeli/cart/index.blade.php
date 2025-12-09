@extends('layouts.customer')

@section('content')
<div class="bg-gradient-to-br from-gray-50 to-gray-100 min-h-screen py-4">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 flex items-center">
                        <i class="fas fa-shopping-cart text-green-600 mr-3"></i>
                        Keranjang Belanja
                    </h1>
                    <p class="text-gray-600 text-sm mt-1">Kelola produk yang akan Anda beli</p>
                </div>
                <a href="{{ route('produk.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors text-sm font-medium text-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i> 
                    Lanjut Belanja
                </a>
            </div>
        </div>

        @if (session('success'))
        <div class="bg-green-50 border-l-4 border-green-400 text-green-700 p-4 mb-6 rounded-r-lg" role="alert">
            <div class="flex">
                <i class="fas fa-check-circle mt-0.5 mr-3"></i>
                <p>{{ session('success') }}</p>
            </div>
        </div>
        @endif

        @if (session('error'))
        <div class="bg-red-50 border-l-4 border-red-400 text-red-700 p-4 mb-6 rounded-r-lg" role="alert">
            <div class="flex">
                <i class="fas fa-exclamation-circle mt-0.5 mr-3"></i>
                <p>{{ session('error') }}</p>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <div id="cartContentContainer">
            @if(count($cartItems) > 0)
                <div class="grid lg:grid-cols-3 gap-6">
                    <!-- Cart Items - 2/3 width -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-200">
                                <h2 class="text-lg font-semibold text-gray-900">Item Dipilih ({{ count($cartItems) }})</h2>
                            </div>
                            
                            <div class="cart-items-container p-6 space-y-4">
                                @php $subtotal = 0; @endphp
                                @foreach($cartItems as $item)
                                    @php $subtotal += $item['subtotal']; @endphp
                                    <div class="cart-item group hover:bg-gray-50 p-4 rounded-lg border border-gray-100 transition-all duration-200" 
                                         data-product-id="{{ $item['product']->idProduk }}">
                                        <div class="flex items-center space-x-4">
                                            <!-- Product Image -->
                                            <div class="flex-shrink-0">
                                                @php
                                                $gambarArray = $item['product']->gambar ? explode(',', $item['product']->gambar) : ['default.jpg'];
                                                $thumbnail = $gambarArray[0];
                                                @endphp
                                                <div class="relative">
                                                    <img class="h-20 w-20 rounded-lg object-cover bg-gray-200 border border-gray-200"
                                                        src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                                        alt="{{ $item['product']->deskripsi }}"
                                                        onerror="handleImageError(this)"
                                                        data-attempted-default="false">
                                                    
                                                    @if($item['product']->tanggalGaransi && \Carbon\Carbon::parse($item['product']->tanggalGaransi)->isFuture())
                                                    <span class="absolute -top-2 -right-2 bg-green-500 text-white text-xs px-2 py-0.5 rounded-full">
                                                        <i class="fas fa-shield-alt"></i>
                                                    </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <!-- Product Info -->
                                            <div class="flex-grow min-w-0">
                                                <h3 class="text-base font-semibold text-gray-900 truncate mb-1">
                                                    {{ $item['product']->deskripsi }}
                                                </h3>
                                                <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
                                                    <span class="bg-gray-100 px-2 py-1 rounded-full">{{ $item['product']->kategori->nama }}</span>
                                                    @if($item['product']->tanggalGaransi && \Carbon\Carbon::parse($item['product']->tanggalGaransi)->isFuture())
                                                    <span class="bg-green-100 text-green-700 px-2 py-1 rounded-full text-xs">
                                                        Bergaransi
                                                    </span>
                                                    @endif
                                                </div>
                                                <div class="flex items-center justify-between">
                                                    <span class="item-price text-lg font-bold text-green-600" 
                                                          data-price="{{ $item['product']->hargaJual }}">
                                                        Rp {{ number_format($item['product']->hargaJual, 0, ',', '.') }}
                                                    </span>
                                                    <div class="flex items-center space-x-3">
                                                        <div class="text-center">
                                                            <span class="text-xs text-gray-500 block">Jumlah</span>
                                                            <span class="text-sm font-semibold">1 pcs</span>
                                                        </div>
                                                        <button onclick="removeFromCart({{ $item['product']->idProduk }})" 
                                                                class="remove-btn opacity-0 group-hover:opacity-100 text-red-500 hover:text-red-700 hover:bg-red-50 p-2 rounded-full transition-all duration-200"
                                                                data-product-id="{{ $item['product']->idProduk }}"
                                                                type="button"
                                                                title="Hapus dari keranjang">
                                                            <i class="fas fa-trash text-sm"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Cart Summary - 1/3 width -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-200 sticky top-6">
                            <div class="bg-gradient-to-r from-green-500 to-green-600 px-6 py-4 rounded-t-xl">
                                <h3 class="text-lg font-semibold text-white flex items-center">
                                    <i class="fas fa-calculator mr-2"></i>
                                    Ringkasan Belanja
                                </h3>
                            </div>
                            
                            <div class="p-6 space-y-4">
                                <div class="space-y-3">
                                    <div class="flex justify-between items-center">
                                        <span class="text-gray-600">Subtotal</span>
                                        <span id="subtotal-amount" class="font-semibold text-gray-900">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm">
                                        <span class="text-gray-500">Total item</span>
                                        <span id="item-count" class="text-gray-700">{{ count($cartItems) }} produk</span>
                                    </div>
                                    <div class="flex justify-between items-center text-sm text-blue-600">
                                        <span>Ongkos kirim</span>
                                        <span>Dihitung saat checkout</span>
                                    </div>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4">
                                    <div class="flex justify-between items-center text-lg font-bold">
                                        <span class="text-gray-900">Total Sementara</span>
                                        <span id="total-amount" class="text-green-600">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
                                    <div class="flex items-start">
                                        <i class="fas fa-info-circle text-blue-500 mt-0.5 mr-2"></i>
                                        <div class="text-sm">
                                            <p class="text-blue-700 font-medium">Gratis ongkir untuk pembelian ≥ Rp 1.500.000</p>
                                            <p class="text-blue-600 text-xs mt-1">Berlaku khusus area Yogyakarta</p>
                                        </div>
                                    </div>
                                </div>

                                <button onclick="proceedToCheckout()" 
                                        class="w-full bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg"
                                        type="button">
                                    <i class="fas fa-credit-card mr-2"></i>
                                    Lanjut ke Checkout
                                </button>
                                
                                <p class="text-xs text-gray-500 text-center">
                                    <i class="fas fa-shield-alt mr-1"></i>
                                    Transaksi aman dan terpercaya
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty Cart -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                    <div class="max-w-md mx-auto">
                        <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">Keranjang Masih Kosong</h3>
                        <p class="text-gray-500 mb-8">Ayo mulai berbelanja dan temukan produk berkualitas dengan harga terjangkau!</p>
                        
                        <div class="space-y-3">
                            <a href="{{ route('produk.index') }}" 
                               class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                                <i class="fas fa-search mr-2"></i>
                                Mulai Belanja
                            </a>
                        </div>
                        
                        <div class="mt-8 grid grid-cols-3 gap-4 text-center">
                            <div class="bg-green-50 p-4 rounded-lg">
                                <i class="fas fa-recycle text-green-600 text-xl mb-2"></i>
                                <p class="text-sm text-green-700 font-medium">Produk Berkualitas</p>
                            </div>
                            <div class="bg-blue-50 p-4 rounded-lg">
                                <i class="fas fa-shield-alt text-blue-600 text-xl mb-2"></i>
                                <p class="text-sm text-blue-700 font-medium">Transaksi Aman</p>
                            </div>
                            <div class="bg-yellow-50 p-4 rounded-lg">
                                <i class="fas fa-coins text-yellow-600 text-xl mb-2"></i>
                                <p class="text-sm text-yellow-700 font-medium">Harga Terjangkau</p>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-xl p-6 flex items-center space-x-3 shadow-2xl">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
        <span class="text-gray-700 font-medium">Memproses...</span>
    </div>
</div>

<script>
console.log('Cart page loaded');

// =============================================
// FIX INFINITE LOOP GAMBAR DEFAULT
// =============================================
function handleImageError(img) {
    console.log('Image failed to load:', img.src);
    
    // Cek apakah sudah pernah mencoba default image
    if (img.dataset.attemptedDefault === 'true') {
        console.log('Default image also failed, using placeholder');
        // Jika default image juga gagal, ganti dengan placeholder
        img.style.display = 'none';
        
        // Buat placeholder div
        const placeholder = document.createElement('div');
        placeholder.className = 'h-20 w-20 rounded-lg bg-gray-200 flex items-center justify-center border border-gray-200';
        placeholder.innerHTML = '<i class="fas fa-image text-gray-400 text-2xl"></i>';
        
        // Replace image dengan placeholder
        img.parentNode.replaceChild(placeholder, img);
        return;
    }
    
    // Tandai bahwa kita sudah mencoba default
    img.dataset.attemptedDefault = 'true';
    
    // Coba beberapa path default
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
    placeholder.className = 'h-20 w-20 rounded-lg bg-gray-200 flex items-center justify-center border border-gray-200';
    placeholder.innerHTML = '<i class="fas fa-image text-gray-400 text-2xl"></i>';
    img.parentNode.replaceChild(placeholder, img);
}

// =============================================
// CART FUNCTIONS (SIMPLIFIED - NO DEBUG)
// =============================================
function removeFromCart(productId) {
    console.log('Removing product:', productId);
    
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        return false;
    }
    
    const removeBtn = document.querySelector(`[data-product-id="${productId}"].remove-btn`);
    if (removeBtn) {
        removeBtn.disabled = true;
        removeBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    }
    
    showLoading();
    
    fetch('{{ route("pembeli.cart.remove") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            idProduk: productId
        })
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
        hideLoading();
        
        if (data.success) {
            // Remove item dari UI
            const cartItem = document.querySelector(`.cart-item[data-product-id="${productId}"]`);
            if (cartItem) {
                cartItem.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                cartItem.style.opacity = '0';
                cartItem.style.transform = 'translateX(-100%)';
                setTimeout(() => cartItem.remove(), 300);
            }
            
            // Update cart count
            updateCartCountInHeader(data.cartCount || 0);
            showNotification('Produk berhasil dihapus dari keranjang', 'success');
            
            // Check if cart empty
            setTimeout(() => {
                const remainingItems = document.querySelectorAll('.cart-item');
                if (remainingItems.length === 0) {
                    showEmptyCartMessage();
                } else {
                    updateCartSummary();
                }
            }, 400);
            
        } else {
            if (removeBtn) {
                removeBtn.disabled = false;
                removeBtn.innerHTML = '<i class="fas fa-trash text-sm"></i>';
            }
            showNotification(data.error || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        
        if (removeBtn) {
            removeBtn.disabled = false;
            removeBtn.innerHTML = '<i class="fas fa-trash text-sm"></i>';
        }
        
        showNotification('Terjadi kesalahan saat menghapus produk', 'error');
    });
    
    return false;
}

function showEmptyCartMessage() {
    const cartContainer = document.getElementById('cartContentContainer');
    if (cartContainer) {
        cartContainer.innerHTML = `
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
                <div class="max-w-md mx-auto">
                    <div class="bg-gray-100 w-24 h-24 rounded-full flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-shopping-cart text-3xl text-gray-400"></i>
                    </div>
                    <h3 class="text-xl font-semibold text-gray-900 mb-3">Keranjang Masih Kosong</h3>
                    <p class="text-gray-500 mb-8">Ayo mulai berbelanja dan temukan produk berkualitas dengan harga terjangkau!</p>
                    
                    <a href="{{ route('produk.index') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-green-600 to-green-700 hover:from-green-700 hover:to-green-800 text-white font-medium rounded-lg transition-all duration-200 transform hover:scale-105 shadow-lg">
                        <i class="fas fa-search mr-2"></i>
                        Mulai Belanja
                    </a>
                </div>
            </div>
        `;
    }
}

function updateCartSummary() {
    let subtotal = 0;
    let itemCount = 0;
    const cartItems = document.querySelectorAll('.cart-item');
    
    cartItems.forEach(item => {
        const priceElement = item.querySelector('.item-price');
        if (priceElement) {
            const price = parseInt(priceElement.dataset.price) || 0;
            subtotal += price;
            itemCount++;
        }
    });
    
    const subtotalElement = document.getElementById('subtotal-amount');
    const totalElement = document.getElementById('total-amount');
    const itemCountElement = document.getElementById('item-count');
    
    if (subtotalElement) subtotalElement.textContent = 'Rp ' + formatNumber(subtotal);
    if (totalElement) totalElement.textContent = 'Rp ' + formatNumber(subtotal);
    if (itemCountElement) itemCountElement.textContent = itemCount + ' produk';
}

function formatNumber(number) {
    return new Intl.NumberFormat('id-ID').format(number);
}

function updateCartCountInHeader(count) {
    const cartBadge = document.querySelector('.cart-count');
    if (cartBadge) {
        cartBadge.textContent = count;
    }
}

function showNotification(message, type = 'info') {
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
    
    setTimeout(() => {
        if (notification.parentElement) {
            notification.remove();
        }
    }, 5000);
}

function proceedToCheckout() {
    window.location.href = '{{ route("pembeli.checkout.show") }}';
}

function showLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.remove('hidden');
}

function hideLoading() {
    const overlay = document.getElementById('loadingOverlay');
    if (overlay) overlay.classList.add('hidden');
}

function updateCartCount() {
    fetch('{{ route("pembeli.cart.count") }}', {
        method: 'GET',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => updateCartCountInHeader(data.count))
    .catch(error => console.error('Error updating cart count:', error));
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    updateCartCount();
    
    // Prevent form submissions
    document.addEventListener('submit', function(e) {
        e.preventDefault();
        return false;
    });
    
    // Fix all button types
    document.querySelectorAll('button').forEach(button => {
        if (!button.type) button.type = 'button';
    });
});

// Prevent page reload during operations
window.addEventListener('beforeunload', function(e) {
    const loadingOverlay = document.getElementById('loadingOverlay');
    if (loadingOverlay && !loadingOverlay.classList.contains('hidden')) {
        e.preventDefault();
        e.returnValue = '';
        return '';
    }
});
</script>
@endsection