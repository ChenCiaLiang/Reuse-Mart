@extends('layouts.customer')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-green-500 px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-white">Keranjang Belanja</h1>
                <a href="{{ route('produk.index') }}" class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Lanjut Belanja
                </a>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if (session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
                @endif

                @if (session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
                @endif

                <!-- Cart Content Container -->
                <div id="cartContentContainer">
                    @if(count($cartItems) > 0)
                        <!-- Cart Items -->
                        <div class="cart-items-container space-y-4 mb-6">
                            @php $subtotal = 0; @endphp
                            @foreach($cartItems as $item)
                                @php $subtotal += $item['subtotal']; @endphp
                                <div class="cart-item border border-gray-200 rounded-lg p-4 flex items-center space-x-4" 
                                     data-product-id="{{ $item['product']->idProduk }}">
                                    <!-- Product Image - FIXED -->
                                    <div class="flex-shrink-0">
                                        @php
                                        $gambarArray = $item['product']->gambar ? explode(',', $item['product']->gambar) : ['default.jpg'];
                                        $thumbnail = $gambarArray[0];
                                        @endphp
                                        <img class="h-20 w-20 rounded-lg object-cover bg-gray-200"
                                            src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                            alt="{{ $item['product']->deskripsi }}"
                                            onerror="handleImageError(this)"
                                            data-attempted-default="false">
                                    </div>

                                    <!-- Product Info -->
                                    <div class="flex-grow">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            {{ $item['product']->deskripsi }}
                                        </h3>
                                        <p class="text-sm text-gray-600 mb-2">
                                            Kategori: {{ $item['product']->kategori->nama }}
                                        </p>
                                        <div class="flex items-center space-x-4">
                                            <span class="item-price text-lg font-bold text-green-600" 
                                                  data-price="{{ $item['product']->hargaJual }}">
                                                Rp {{ number_format($item['product']->hargaJual, 0, ',', '.') }}
                                            </span>
                                            @if($item['product']->tanggalGaransi && \Carbon\Carbon::parse($item['product']->tanggalGaransi)->isFuture())
                                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                                Garansi
                                            </span>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Quantity (Always 1 for used items) -->
                                    <div class="text-center">
                                        <p class="text-sm text-gray-500">Jumlah</p>
                                        <span class="text-lg font-semibold">1</span>
                                    </div>

                                    <!-- Remove Button -->
                                    <div class="flex-shrink-0">
                                        <button onclick="removeFromCart({{ $item['product']->idProduk }})" 
                                                class="remove-btn text-red-600 hover:text-red-800 p-2 transition-colors"
                                                data-product-id="{{ $item['product']->idProduk }}"
                                                type="button">
                                            <i class="fas fa-trash text-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Cart Summary -->
                        <div class="cart-summary border-t border-gray-200 pt-6">
                            <div class="bg-gray-50 rounded-lg p-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Belanja</h3>
                                
                                <div class="space-y-2 mb-4">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Subtotal (<span id="item-count">{{ count($cartItems) }}</span> item)</span>
                                        <span id="subtotal-amount" class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between text-sm text-gray-500">
                                        <span>Ongkos kirim akan dihitung di checkout</span>
                                        <span>-</span>
                                    </div>
                                </div>
                                
                                <div class="border-t border-gray-200 pt-4 mb-6">
                                    <div class="flex justify-between text-lg font-bold">
                                        <span>Total</span>
                                        <span id="total-amount" class="text-green-600">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                    </div>
                                </div>

                                <!-- Checkout Button -->
                                <button onclick="proceedToCheckout()" 
                                        class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
                                        type="button">
                                    <i class="fas fa-shopping-cart mr-2"></i>
                                    Lanjut ke Checkout
                                </button>
                            </div>
                        </div>
                    @else
                        <!-- Empty Cart -->
                        <div class="empty-cart-container text-center py-16">
                            <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-4">
                                <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Keranjang Belanja Kosong</h3>
                            <p class="text-gray-500 mb-6">Anda belum menambahkan produk ke dalam keranjang</p>
                            
                            <a href="{{ route('produk.index') }}" 
                               class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                                <i class="fas fa-search mr-2"></i>
                                Mulai Belanja
                            </a>
                        </div>
                    @endif
                </div>
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
        placeholder.className = 'h-20 w-20 rounded-lg bg-gray-200 flex items-center justify-center';
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
    placeholder.className = 'h-20 w-20 rounded-lg bg-gray-200 flex items-center justify-center';
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
                cartItem.style.transition = 'opacity 0.3s ease';
                cartItem.style.opacity = '0';
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
                removeBtn.innerHTML = '<i class="fas fa-trash text-lg"></i>';
            }
            showNotification(data.error || 'Terjadi kesalahan', 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        
        if (removeBtn) {
            removeBtn.disabled = false;
            removeBtn.innerHTML = '<i class="fas fa-trash text-lg"></i>';
        }
        
        showNotification('Terjadi kesalahan saat menghapus produk', 'error');
    });
    
    return false;
}

function showEmptyCartMessage() {
    const cartContainer = document.getElementById('cartContentContainer');
    if (cartContainer) {
        cartContainer.innerHTML = `
            <div class="empty-cart-container text-center py-16">
                <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-4">
                    <i class="fas fa-shopping-cart text-2xl text-gray-400"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">Keranjang Belanja Kosong</h3>
                <p class="text-gray-500 mb-6">Anda belum menambahkan produk ke dalam keranjang</p>
                
                <a href="{{ route('produk.index') }}" 
                   class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    <i class="fas fa-search mr-2"></i>
                    Mulai Belanja
                </a>
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
    if (itemCountElement) itemCountElement.textContent = itemCount;
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
    notification.className = `notification fixed top-4 right-4 z-50 p-4 rounded-lg shadow-lg transition-all duration-300 ${
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