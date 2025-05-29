@extends('layouts.cs')

@section('content')
<div class="container px-6 mx-auto grid">
    <div class="flex items-center justify-between my-6">
        <h2 class="text-2xl font-semibold text-gray-700">
            Konfirmasi Pengambilan Merchandise
        </h2>
        <a href="{{ route('cs.merchandise.show', $klaim->idPenukaran) }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Alert Error -->
    @if($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Data Klaim -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">
                <i class="fas fa-info-circle mr-2 text-blue-500"></i>Informasi Klaim
            </h3>
            
            <div class="space-y-4">
                <div class="bg-gray-50 p-4 rounded-lg">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-gray-600">ID Klaim</label>
                            <p class="text-lg font-bold text-gray-800">MER-{{ str_pad($klaim->idPenukaran, 3, '0', STR_PAD_LEFT) }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-600">Status</label>
                            <p>
                                @if($klaim->statusPenukaran == 'belum diambil')
                                    <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-sm font-semibold">
                                        <i class="fas fa-clock mr-1"></i>Belum Diambil
                                    </span>
                                @else
                                    <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded text-sm font-semibold">
                                        {{ ucfirst($klaim->statusPenukaran) }}
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <div class="space-y-3">
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">Nama Pembeli:</span>
                        <span class="text-gray-800 font-semibold">{{ $klaim->namaPembeli }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">Email:</span>
                        <span class="text-gray-800">{{ $klaim->email }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">Merchandise:</span>
                        <span class="text-gray-800 font-semibold">{{ $klaim->namaMerchandise }}</span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">Poin Digunakan:</span>
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-semibold">
                            {{ $klaim->jumlahPoin }} poin
                        </span>
                    </div>
                    
                    <div class="flex justify-between">
                        <span class="text-gray-600 font-medium">Tanggal Klaim:</span>
                        <span class="text-gray-800">{{ \Carbon\Carbon::parse($klaim->tanggalPengajuan)->format('d F Y, H:i') }} WIB</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Konfirmasi -->
        <div class="bg-white shadow-md rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b pb-2">
                <i class="fas fa-check-circle mr-2 text-green-500"></i>Form Konfirmasi Pengambilan
            </h3>

            <form action="{{ route('cs.merchandise.konfirmasi', $klaim->idPenukaran) }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label for="tanggalPenerimaan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tanggal & Waktu Pengambilan <span class="text-red-500">*</span>
                    </label>
                    <input type="datetime-local" 
                           id="tanggalPenerimaan" 
                           name="tanggalPenerimaan" 
                           value="{{ old('tanggalPenerimaan', now()->format('Y-m-d\TH:i')) }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('tanggalPenerimaan') border-red-500 @enderror"
                           required>
                    @error('tanggalPenerimaan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="catatan" class="block text-sm font-medium text-gray-700 mb-2">
                        Catatan (Opsional)
                    </label>
                    <textarea id="catatan" 
                              name="catatan" 
                              rows="4"
                              placeholder="Masukkan catatan jika diperlukan..."
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 @error('catatan') border-red-500 @enderror">{{ old('catatan') }}</textarea>
                    @error('catatan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-500 text-xs mt-1">Contoh: KTP sesuai data, merchandise dalam kondisi baik, dll.</p>
                </div>

                <!-- Checklist Konfirmasi -->
                <div class="bg-blue-50 p-4 rounded-lg">
                    <h4 class="font-semibold text-blue-800 mb-2">Checklist Konfirmasi:</h4>
                    <div class="space-y-2 text-sm text-blue-700">
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2" required>
                            <span>Saya telah memverifikasi identitas pembeli</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2" required>
                            <span>Merchandise telah diserahkan dalam kondisi baik</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" class="mr-2" required>
                            <span>Data waktu pengambilan sudah benar</span>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="{{ route('cs.merchandise.show', $klaim->idPenukaran) }}" 
                       class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" 
                            class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold">
                        <i class="fas fa-check mr-2"></i>Konfirmasi Pengambilan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-fill current datetime
document.addEventListener('DOMContentLoaded', function() {
    const now = new Date();
    const localDateTime = new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 16);
    document.getElementById('tanggalPenerimaan').value = localDateTime;
});

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const checkboxes = document.querySelectorAll('input[type="checkbox"][required]');
    let allChecked = true;
    
    checkboxes.forEach(function(checkbox) {
        if (!checkbox.checked) {
            allChecked = false;
        }
    });
    
    if (!allChecked) {
        e.preventDefault();
        alert('Harap centang semua checklist konfirmasi sebelum melanjutkan.');
        return false;
    }
    
    // Konfirmasi terakhir
    if (!confirm('Apakah Anda yakin ingin mengkonfirmasi pengambilan merchandise ini?')) {
        e.preventDefault();
        return false;
    }
});
</script>
@endsection