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

                @if(count($cartItems) > 0)
                    <!-- Cart Items -->
                    <div class="space-y-4 mb-6">
                        @php $subtotal = 0; @endphp
                        @foreach($cartItems as $item)
                            @php $subtotal += $item['subtotal']; @endphp
                            <div class="border border-gray-200 rounded-lg p-4 flex items-center space-x-4">
                                <!-- Product Image -->
                                <div class="flex-shrink-0">
                                    @php
                                    $gambarArray = $item['product']->gambar ? explode(',', $item['product']->gambar) : ['default.jpg'];
                                    $thumbnail = $gambarArray[0];
                                    @endphp
                                    <img class="h-20 w-20 rounded-lg object-cover"
                                        src="{{ asset('uploads/produk/' . trim($thumbnail)) }}"
                                        alt="{{ $item['product']->deskripsi }}"
                                        onerror="this.src='{{ asset('images/default.jpg') }}'">
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
                                        <span class="text-lg font-bold text-green-600">
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
                                            class="text-red-600 hover:text-red-800 p-2">
                                        <i class="fas fa-trash text-lg"></i>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Cart Summary -->
                    <div class="border-t border-gray-200 pt-6">
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Ringkasan Belanja</h3>
                            
                            <div class="space-y-2 mb-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Subtotal ({{ count($cartItems) }} item)</span>
                                    <span class="font-semibold">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                <div class="flex justify-between text-sm text-gray-500">
                                    <span>Ongkos kirim akan dihitung di checkout</span>
                                    <span>-</span>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4 mb-6">
                                <div class="flex justify-between text-lg font-bold">
                                    <span>Total</span>
                                    <span class="text-green-600">Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>

                            <!-- Checkout Button -->
                            <button onclick="proceedToCheckout()" 
                                    class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                <i class="fas fa-shopping-cart mr-2"></i>
                                Lanjut ke Checkout
                            </button>
                        </div>
                    </div>
                @else
                    <!-- Empty Cart -->
                    <div class="text-center py-16">
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

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-green-500"></div>
        <span class="text-gray-700">Memproses...</span>
    </div>
</div>

<script>
// Remove from cart function
function removeFromCart(productId) {
    if (!confirm('Apakah Anda yakin ingin menghapus produk ini dari keranjang?')) {
        return;
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
    .then(response => response.json())
    .then(data => {
        hideLoading();
        if (data.success) {
            // Reload page to update cart
            window.location.reload();
        } else {
            alert(data.error || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menghapus produk');
    });
}

// Proceed to checkout
function proceedToCheckout() {
    window.location.href = '{{ route("pembeli.checkout.show") }}';
}

// Loading functions
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Update cart count in navbar if exists
function updateCartCount() {
    fetch('{{ route("pembeli.cart.count") }}', {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        const cartBadge = document.querySelector('.cart-count');
        if (cartBadge) {
            cartBadge.textContent = data.count;
        }
    })
    .catch(error => console.error('Error updating cart count:', error));
}
</script>
@endsection