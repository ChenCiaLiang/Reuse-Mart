{{-- FILE: resources/views/pegawai/cs/verification/index.blade.php --}}
@extends('layouts.pegawai')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-500 px-6 py-4">
                <h1 class="text-xl font-bold text-white">Verifikasi Pembayaran</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Daftar transaksi yang menunggu verifikasi pembayaran
                </p>
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

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-clock text-2xl text-yellow-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-yellow-600">Menunggu Verifikasi</p>
                                <p class="text-2xl font-bold text-yellow-900">{{ $transaksiList->total() }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-credit-card text-2xl text-blue-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-blue-600">Total Nilai</p>
                                <p class="text-2xl font-bold text-blue-900">
                                    Rp {{ number_format($transaksiList->sum(function($t) { 
                                        return $t->detailTransaksiPenjualan->sum(function($d) { 
                                            return $d->produk->hargaJual; 
                                        }); 
                                    }), 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <i class="fas fa-users text-2xl text-green-600"></i>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-green-600">Pembeli Unik</p>
                                <p class="text-2xl font-bold text-green-900">{{ $transaksiList->unique('idPembeli')->count() }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Transaction List -->
                @if(count($transaksiList) > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Transaksi
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Pembeli
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Total
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Tanggal
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Aksi
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($transaksiList as $transaksi)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            #{{ $transaksi->idTransaksiPenjualan }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $transaksi->detailTransaksiPenjualan->count() }} item
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div>
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $transaksi->pembeli->nama }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ $transaksi->pembeli->email }}
                                        </div>
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        Rp {{ number_format($transaksi->detailTransaksiPenjualan->sum(function($detail) { 
                                            return $detail->produk->hargaJual; 
                                        }), 0, ',', '.') }}
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('H:i') }} WIB
                                    </div>
                                </td>
                                
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <a href="{{ route('cs.verification.show', $transaksi->idTransaksiPenjualan) }}" 
                                       class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition-colors">
                                        <i class="fas fa-eye mr-1"></i>
                                        Verifikasi
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $transaksiList->links() }}
                </div>
                @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mx-auto flex items-center justify-center h-24 w-24 rounded-full bg-gray-100 mb-4">
                        <i class="fas fa-check-circle text-2xl text-gray-400"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Verifikasi Pending</h3>
                    <p class="text-gray-500">Semua pembayaran sudah diverifikasi.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

{{-- FILE: resources/views/pegawai/cs/verification/show.blade.php --}}
@extends('layouts.pegawai')

@section('content')
<div class="bg-gray-100 min-h-screen py-8">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <!-- Header -->
            <div class="bg-blue-500 px-6 py-4 flex justify-between items-center">
                <div>
                    <h1 class="text-xl font-bold text-white">Verifikasi Pembayaran</h1>
                    <p class="text-blue-100 text-sm mt-1">
                        Transaksi #{{ $transaksi->idTransaksiPenjualan }}
                    </p>
                </div>
                <a href="{{ route('cs.verification.index') }}" class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-lg text-sm">
                    <i class="fas fa-arrow-left mr-2"></i> Kembali
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

                <div class="grid lg:grid-cols-2 gap-8">
                    <!-- Left Column - Transaction Info -->
                    <div>
                        <!-- Customer Info -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pembeli</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Nama:</span>
                                    <span class="font-medium text-gray-900 ml-2">{{ $transaksi->pembeli->nama }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Email:</span>
                                    <span class="font-medium text-gray-900 ml-2">{{ $transaksi->pembeli->email }}</span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Poin:</span>
                                    <span class="font-medium text-gray-900 ml-2">{{ number_format($transaksi->pembeli->poin) }} poin</span>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Details -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Detail Transaksi</h3>
                            <div class="space-y-3">
                                <div>
                                    <span class="text-sm text-gray-600">Tanggal Pesanan:</span>
                                    <span class="font-medium text-gray-900 ml-2">
                                        {{ \Carbon\Carbon::parse($transaksi->tanggalPesan)->format('d M Y H:i') }} WIB
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Status:</span>
                                    <span class="ml-2 bg-yellow-100 text-yellow-800 text-sm px-2 py-1 rounded-full">
                                        Menunggu Verifikasi
                                    </span>
                                </div>
                                <div>
                                    <span class="text-sm text-gray-600">Total Pembayaran:</span>
                                    <span class="font-bold text-lg text-blue-600 ml-2">
                                        Rp {{ number_format($transaksi->detailTransaksiPenjualan->sum(function($detail) { 
                                            return $detail->produk->hargaJual; 
                                        }), 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="bg-gray-50 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Item Pesanan</h3>
                            <div class="space-y-3">
                                @foreach($transaksi->detailTransaksiPenjualan as $detail)
                                <div class="flex items-center space-x-3 p-3 bg-white rounded border">
                                    @php
                                    $gambarArray = $detail->produk->gambar ? explode(',', $detail->produk->gambar) : ['default.jpg'];
                                    $thumbnail = $gambarArray[0];
                                    @endphp
                                    <img class="h-12 w-12 rounded object-cover"
                                        src="{{ asset('images/produk/' . trim($thumbnail)) }}"
                                        alt="{{ $detail->produk->deskripsi }}"
                                        onerror="this.src='{{ asset('images/default.jpg') }}'">
                                    <div class="flex-grow">
                                        <h4 class="font-medium text-gray-900">{{ $detail->produk->deskripsi }}</h4>
                                        <p class="text-sm text-gray-500">{{ $detail->produk->kategori->nama }}</p>
                                    </div>
                                    <span class="font-semibold">Rp {{ number_format($detail->produk->hargaJual, 0, ',', '.') }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Payment Proof & Verification -->
                    <div>
                        <!-- Payment Proof -->
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Bukti Pembayaran</h3>
                            
                            @if(session('bukti_pembayaran_' . $transaksi->idTransaksiPenjualan))
                            <div class="text-center">
                                <img src="{{ asset(session('bukti_pembayaran_' . $transaksi->idTransaksiPenjualan)) }}" 
                                     alt="Bukti Pembayaran" 
                                     class="max-w-full h-auto rounded-lg border cursor-pointer"
                                     onclick="openImageModal(this.src)">
                                <p class="text-sm text-gray-500 mt-2">Klik gambar untuk memperbesar</p>
                            </div>
                            @else
                            <div class="text-center py-8">
                                <i class="fas fa-image text-4xl text-gray-400 mb-2"></i>
                                <p class="text-gray-500">Bukti pembayaran belum diupload</p>
                            </div>
                            @endif
                        </div>

                        <!-- Verification Form -->
                        <div class="bg-white border border-gray-200 rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-800 mb-4">Verifikasi Pembayaran</h3>
                            
                            <form action="{{ route('cs.verification.verify', $transaksi->idTransaksiPenjualan) }}" method="POST">
                                @csrf
                                
                                <div class="mb-6">
                                    <label class="block text-sm font-medium text-gray-700 mb-3">
                                        Status Verifikasi <span class="text-red-500">*</span>
                                    </label>
                                    
                                    <div class="space-y-3">
                                        <label class="flex items-center p-4 border border-green-200 rounded-lg cursor-pointer hover:bg-green-50">
                                            <input type="radio" name="status_verifikasi" value="valid" 
                                                   class="h-4 w-4 text-green-600 focus:ring-green-500" required>
                                            <div class="ml-3">
                                                <div class="font-medium text-green-800">Pembayaran Valid</div>
                                                <div class="text-sm text-green-600">Bukti transfer sesuai dan pembayaran berhasil</div>
                                            </div>
                                        </label>
                                        
                                        <label class="flex items-center p-4 border border-red-200 rounded-lg cursor-pointer hover:bg-red-50">
                                            <input type="radio" name="status_verifikasi" value="tidak_valid" 
                                                   class="h-4 w-4 text-red-600 focus:ring-red-500" required>
                                            <div class="ml-3">
                                                <div class="font-medium text-red-800">Pembayaran Tidak Valid</div>
                                                <div class="text-sm text-red-600">Bukti transfer tidak sesuai atau bermasalah</div>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="mb-6">
                                    <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                                        Catatan (Opsional)
                                    </label>
                                    <textarea name="catatan" id="catatan" rows="3" 
                                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                              placeholder="Tambahkan catatan verifikasi...">{{ old('catatan') }}</textarea>
                                    @error('catatan')
                                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                                
                                <div class="flex space-x-3">
                                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-check mr-2"></i>
                                        Simpan Verifikasi
                                    </button>
                                    
                                    <a href="{{ route('cs.verification.index') }}" 
                                       class="flex-1 text-center bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
                                        <i class="fas fa-times mr-2"></i>
                                        Batal
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Image Modal -->
<div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
    <div class="max-w-4xl max-h-full p-4">
        <div class="relative">
            <button onclick="closeImageModal()" 
                    class="absolute top-4 right-4 text-white bg-black bg-opacity-50 rounded-full p-2 hover:bg-opacity-75">
                <i class="fas fa-times text-xl"></i>
            </button>
            <img id="modalImage" src="" alt="Bukti Pembayaran" class="max-w-full max-h-full object-contain">
        </div>
    </div>
</div>

<script>
function openImageModal(src) {
    document.getElementById('modalImage').src = src;
    document.getElementById('imageModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeImageModal() {
    document.getElementById('imageModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Close modal when clicking outside image
document.getElementById('imageModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeImageModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeImageModal();
    }
});
</script>
@endsection