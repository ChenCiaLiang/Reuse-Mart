@extends('layouts.customer')

@section('content')
<!-- CSRF Token untuk JavaScript -->
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="bg-gray-100 min-h-screen py-12">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-yellow-500 px-6 py-4 flex justify-between items-center">
                <h1 class="text-xl font-bold text-white">Berikan Rating Produk</h1>
                <a href="{{ route('pembeli.profile') }}" class="text-white bg-yellow-600 hover:bg-yellow-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali ke Profil
                </a>
            </div>

            <!-- Content -->
            <div class="p-6">
                @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                    <p>{{ session('success') }}</p>
                </div>
                @endif

                @if(session('error'))
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6" role="alert">
                    <p>{{ session('error') }}</p>
                </div>
                @endif

                @if(count($produkDibeli) > 0)
                    <div class="mb-6">
                        <p class="text-gray-600">Berikan rating untuk produk yang sudah Anda beli. Rating Anda akan membantu pembeli lain dan penitip.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($produkDibeli as $produk)
                        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden hover:shadow-md transition-shadow">
                            <!-- Product Image -->
                            <div class="aspect-w-16 aspect-h-9">
                                @php
                                $gambarArray = $produk->gambar ? explode(',', $produk->gambar) : ['default.jpg'];
                                $thumbnail = $gambarArray[0];
                                @endphp
                                <img class="w-full h-48 object-cover"
                                    src="{{ asset('uploads/produk/' . trim($thumbnail)) }}"
                                    alt="{{ $produk->deskripsi }}"
                                    onerror="this.src='{{ asset('images/default.jpg') }}'">
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-4">
                                <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ $produk->deskripsi }}</h3>
                                <p class="text-sm text-gray-500 mb-2">{{ $produk->kategori }}</p>
                                <p class="text-lg font-bold text-green-600 mb-3">Rp {{ number_format($produk->hargaJual, 0, ',', '.') }}</p>
                                
                                <!-- Current Rating Display -->
                                <div class="flex items-center mb-3">
                                    <span class="text-sm text-gray-500 mr-2">Rating Saat Ini:</span>
                                    <div class="flex items-center">
                                        @for($i = 1; $i <= 5; $i++)
                                            @if($i <= $produk->ratingProduk)
                                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4 text-gray-300 fill-current" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endif
                                        @endfor
                                        <span class="ml-2 text-sm text-gray-600">({{ number_format($produk->ratingProduk, 1) }})</span>
                                    </div>
                                </div>

                                <!-- Rating Button -->
                                 @if($produk->ratingProduk == 0)
                                    <button onclick="openRatingModal({{ $produk->idProduk }}, '{{ addslashes($produk->deskripsi) }}')" 
                                        class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded-md transition-colors">
                                        <svg class="w-4 h-4 inline mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Beri Rating
                                    </button>
                                @endIf

                                <p class="text-xs text-gray-400 mt-2 text-center">
                                    Dibeli: {{ \Carbon\Carbon::parse($produk->tanggalLunas)->format('d M Y') }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                        </svg>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Belum Ada Produk untuk Diberi Rating</h3>
                        <p class="text-gray-500 mb-4">Anda belum memiliki produk yang dapat diberi rating. Belanja dulu untuk dapat memberikan rating!</p>
                        <a href="{{ route('produk.index') }}" class="inline-flex items-center px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white font-medium rounded-md">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                            </svg>
                            Belanja Sekarang
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Rating Modal -->
<div id="ratingModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <!-- Modal Header -->
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Berikan Rating</h3>
                <button onclick="closeRatingModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <!-- Product Name -->
            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-2">Produk:</p>
                <p id="modalProductName" class="font-semibold text-gray-900"></p>
            </div>
            
            <!-- Rating Stars -->
            <div class="mb-6">
                <p class="text-sm text-gray-600 mb-3">Pilih Rating (1-5 bintang):</p>
                <div class="flex justify-center space-x-1">
                    @for($i = 1; $i <= 5; $i++)
                    <button type="button" onclick="selectRating({{ $i }})" class="rating-star" data-rating="{{ $i }}">
                        <svg class="w-8 h-8 text-gray-300 hover:text-yellow-400 fill-current transition-colors" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                    @endfor
                </div>
                <p id="ratingText" class="text-center text-sm text-gray-500 mt-2">Pilih rating Anda</p>
            </div>
            
            <!-- Modal Actions -->
            <div class="flex justify-end space-x-3">
                <button onclick="closeRatingModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md">
                    Batal
                </button>
                <button onclick="submitRating()" id="submitRatingBtn" class="px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-md" disabled>
                    Simpan Rating
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-60">
    <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
        <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-yellow-500"></div>
        <span class="text-gray-700">Menyimpan rating...</span>
    </div>
</div>

<script>
let selectedRating = 0;
let currentProductId = null;

function openRatingModal(productId, productName) {
    // Pastikan productId adalah integer
    currentProductId = parseInt(productId);
    
    console.log('Opening modal for product:', {
        productId: currentProductId,
        productName: productName,
        type: typeof currentProductId
    });
    
    document.getElementById('modalProductName').textContent = productName;
    document.getElementById('ratingModal').classList.remove('hidden');
    resetRating();
}

function closeRatingModal() {
    document.getElementById('ratingModal').classList.add('hidden');
    resetRating();
}

function resetRating() {
    selectedRating = 0;
    document.getElementById('ratingText').textContent = 'Pilih rating Anda';
    document.getElementById('submitRatingBtn').disabled = true;
    
    // Reset star colors
    document.querySelectorAll('.rating-star svg').forEach(star => {
        star.classList.remove('text-yellow-400');
        star.classList.add('text-gray-300');
    });
}

function selectRating(rating) {
    // Pastikan rating adalah number dengan 2 decimal places untuk double(8,2)
    selectedRating = parseFloat(rating);
    
    console.log('Selected rating:', {
        rating: selectedRating,
        type: typeof selectedRating,
        formatted: selectedRating.toFixed(2)
    });
    
    document.getElementById('submitRatingBtn').disabled = false;
    
    const ratingTexts = {
        1: 'Sangat Buruk',
        2: 'Buruk', 
        3: 'Cukup',
        4: 'Baik',
        5: 'Sangat Baik'
    };
    
    document.getElementById('ratingText').textContent = `${rating} Bintang - ${ratingTexts[rating]}`;
    
    // Update star colors
    document.querySelectorAll('.rating-star').forEach((button, index) => {
        const star = button.querySelector('svg');
        if (index < rating) {
            star.classList.remove('text-gray-300');
            star.classList.add('text-yellow-400');
        } else {
            star.classList.remove('text-yellow-400');
            star.classList.add('text-gray-300');
        }
    });
}

function submitRating() {
    // Validasi data dengan tipe yang tepat
    if (selectedRating === 0 || !currentProductId) {
        alert('Pilih rating terlebih dahulu!');
        return;
    }
    
    // Konversi ke tipe data yang sesuai dengan database
    const productIdInt = parseInt(currentProductId);     // int untuk idProduk
    const ratingDouble = parseFloat(selectedRating);     // double(8,2) untuk rating
    
    // Validasi tipe data
    if (isNaN(productIdInt) || productIdInt <= 0) {
        alert('ID Produk tidak valid!');
        console.error('Invalid product ID:', currentProductId);
        return;
    }
    
    if (isNaN(ratingDouble) || ratingDouble < 1 || ratingDouble > 5) {
        alert('Rating tidak valid!');
        console.error('Invalid rating:', selectedRating);
        return;
    }
    
    console.log('Submitting rating with correct types:', {
        idProduk: productIdInt,
        rating: ratingDouble,
        types: {
            idProduk: typeof productIdInt,
            rating: typeof ratingDouble
        },
        formatted: {
            idProduk: productIdInt,
            rating: ratingDouble.toFixed(2) // Format untuk double(8,2)
        }
    });
    
    // Show loading
    document.getElementById('loadingOverlay').classList.remove('hidden');
    
    // Get CSRF token
    const token = document.querySelector('meta[name="csrf-token"]');
    if (!token) {
        alert('CSRF token tidak ditemukan!');
        document.getElementById('loadingOverlay').classList.add('hidden');
        return;
    }
    
    // Prepare data dengan tipe yang benar
    const formData = new FormData();
    formData.append('idProduk', productIdInt.toString());           // Kirim sebagai string
    formData.append('rating', ratingDouble.toFixed(2));            // Format double(8,2)
    formData.append('_token', token.getAttribute('content'));
    
    // Log data yang akan dikirim
    console.log('FormData contents:', {
        idProduk: formData.get('idProduk'),
        rating: formData.get('rating'),
        token: formData.get('_token') ? 'present' : 'missing'
    });
    
    // Submit rating
    fetch('{{ route("pembeli.rating.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', {
            contentType: response.headers.get('content-type'),
            status: response.status,
            ok: response.ok
        });
        
        // Handle response
        return response.text().then(text => {
            console.log('Response text (first 200 chars):', text.substring(0, 200));
            
            // Check if HTML error page
            if (text.trim().startsWith('<')) {
                console.error('Received HTML response (likely error page)');
                throw new Error('Server error - check Laravel logs');
            }
            
            // Parse JSON
            try {
                const data = JSON.parse(text);
                console.log('Parsed response:', data);
                return data;
            } catch (e) {
                console.error('JSON parse error:', e);
                throw new Error('Invalid JSON response: ' + text.substring(0, 100));
            }
        });
    })
    .then(data => {
        // Hide loading
        document.getElementById('loadingOverlay').classList.add('hidden');
        
        if (data.success) {
            alert(data.message || 'Rating berhasil disimpan!');
            location.reload();
        } else {
            // Handle validation errors
            let errorMessage = data.message || 'Terjadi kesalahan saat menyimpan rating';
            
            if (data.errors) {
                const errorList = Object.values(data.errors).flat();
                errorMessage += '\n\nDetail error:\n' + errorList.join('\n');
            }
            
            alert(errorMessage);
        }
        
        closeRatingModal();
    })
    .catch(error => {
        console.error('Fetch error:', error);
        
        // Hide loading
        document.getElementById('loadingOverlay').classList.add('hidden');
        
        alert('Terjadi kesalahan: ' + error.message);
        closeRatingModal();
    });
}

// Close modal when clicking outside
document.getElementById('ratingModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeRatingModal();
    }
});
</script>
@endsection