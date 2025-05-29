@extends('layouts.cs')

@section('content')
<div class="container px-6 mx-auto grid">
    <div class="flex items-center justify-between my-6">
        <h2 class="text-2xl font-semibold text-gray-700">
            Detail Klaim Merchandise
        </h2>
        <a href="{{ route('cs.merchandise.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <!-- Main Card -->
    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <!-- Header Card -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-xl font-bold">Klaim #MER-{{ str_pad($klaim->idPenukaran, 3, '0', STR_PAD_LEFT) }}</h3>
                    <p class="text-green-100 mt-1">{{ $klaim->namaMerchandise }}</p>
                </div>
                <div class="text-right">
                    @if($klaim->statusPenukaran == 'belum diambil')
                        <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-clock mr-1"></i>Belum Diambil
                        </span>
                    @elseif($klaim->statusPenukaran == 'sudah diambil')
                        <span class="bg-green-800 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-check mr-1"></i>Sudah Diambil
                        </span>
                    @elseif($klaim->statusPenukaran == 'dibatalkan')
                        <span class="bg-red-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-times mr-1"></i>Dibatalkan
                        </span>
                    @else
                        <span class="bg-gray-500 text-white px-3 py-1 rounded-full text-sm font-semibold">
                            <i class="fas fa-question mr-1"></i>{{ ucfirst($klaim->statusPenukaran) }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Content Card -->
        <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Data Pembeli -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-700 border-b pb-2">Data Pembeli</h4>
                    
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-gray-600 font-medium">Nama:</span>
                            <span class="text-gray-800">{{ $klaim->namaPembeli }}</span>
                        </div>
                        
                        <div class="flex">
                            <span class="w-32 text-gray-600 font-medium">Email:</span>
                            <span class="text-gray-800">{{ $klaim->email }}</span>
                        </div>
                    </div>
                </div>

                <!-- Data Merchandise -->
                <div class="space-y-4">
                    <h4 class="text-lg font-semibold text-gray-700 border-b pb-2">Data Merchandise</h4>
                    
                    <div class="space-y-3">
                        <div class="flex">
                            <span class="w-32 text-gray-600 font-medium">Merchandise:</span>
                            <span class="text-gray-800 font-semibold">{{ $klaim->namaMerchandise }}</span>
                        </div>
                        
                        <div class="flex">
                            <span class="w-32 text-gray-600 font-medium">Poin Digunakan:</span>
                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm font-semibold">
                                {{ $klaim->jumlahPoin }} poin
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timeline Klaim -->
            <div class="mt-8">
                <h4 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Timeline Klaim</h4>
                
                <div class="relative">
                    <div class="absolute left-4 top-0 h-full w-0.5 bg-gray-300"></div>
                    
                    <!-- Tanggal Pengajuan -->
                    <div class="relative flex items-center mb-6">
                        <div class="bg-blue-500 rounded-full w-8 h-8 flex items-center justify-center text-white text-sm font-bold z-10">
                            1
                        </div>
                        <div class="ml-4">
                            <h5 class="font-semibold text-gray-700">Klaim Diajukan</h5>
                            <p class="text-gray-600 text-sm">{{ \Carbon\Carbon::parse($klaim->tanggalPengajuan)->format('d F Y, H:i') }} WIB</p>
                            <p class="text-gray-500 text-xs">Pembeli mengajukan klaim untuk {{ $klaim->namaMerchandise }}</p>
                        </div>
                    </div>

                    <!-- Tanggal Penerimaan -->
                    <div class="relative flex items-center">
                        @if($klaim->statusPenukaran == 'sudah diambil' && $klaim->tanggalPenerimaan)
                            <div class="bg-green-500 rounded-full w-8 h-8 flex items-center justify-center text-white text-sm font-bold z-10">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            <div class="ml-4">
                                <h5 class="font-semibold text-gray-700">Merchandise Diambil</h5>
                                <p class="text-gray-600 text-sm">{{ \Carbon\Carbon::parse($klaim->tanggalPenerimaan)->format('d F Y, H:i') }} WIB</p>
                                <p class="text-gray-500 text-xs">Pembeli telah mengambil merchandise di kantor ReUseMart</p>
                            </div>
                        @elseif($klaim->statusPenukaran == 'dibatalkan')
                            <div class="bg-red-500 rounded-full w-8 h-8 flex items-center justify-center text-white text-sm font-bold z-10">
                                <i class="fas fa-times text-xs"></i>
                            </div>
                            <div class="ml-4">
                                <h5 class="font-semibold text-red-700">Klaim Dibatalkan</h5>
                                <p class="text-red-600 text-sm">Klaim merchandise telah dibatalkan</p>
                                <p class="text-red-500 text-xs">Pembeli membatalkan pengambilan merchandise</p>
                            </div>
                        @else
                            <div class="bg-gray-300 rounded-full w-8 h-8 flex items-center justify-center text-white text-sm font-bold z-10">
                                2
                            </div>
                            <div class="ml-4">
                                <h5 class="font-semibold text-gray-500">Menunggu Pengambilan</h5>
                                <p class="text-gray-400 text-sm">Belum diambil</p>
                                <p class="text-gray-400 text-xs">Pembeli belum mengambil merchandise</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            @if($klaim->statusPenukaran == 'belum diambil')
                <div class="mt-8 pt-6 border-t">
                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('cs.merchandise.konfirmasi.form', $klaim->idPenukaran) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-md font-semibold">
                            <i class="fas fa-check mr-2"></i>Konfirmasi Pengambilan
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection