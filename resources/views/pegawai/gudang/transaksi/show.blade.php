@extends('layouts.gudang')

@section('title', 'Detail Transaksi Penitipan')

@section('breadcrumb')
<nav class="text-sm text-gray-500 mt-1">
    <ol class="list-none p-0 inline-flex">
        <li class="flex items-center">
            <a href="{{ route('gudang.dashboard') }}" class="hover:text-green-600">Dashboard</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="flex items-center">
            <a href="{{ route('gudang.transaksi.index') }}" class="hover:text-green-600">Transaksi Penitipan</a>
            <i class="fa-solid fa-chevron-right mx-2 text-xs"></i>
        </li>
        <li class="text-gray-600">Detail #{{ $transaksi->idTransaksiPenitipan }}</li>
    </ol>
</nav>
@endsection

@section('content')
<div class="space-y-6">
    <!-- Page Header -->
    <div class="bg-white rounded-lg shadow-sm p-6">
        <div class="flex justify-between items-start">
            <div>
                <h2 class="text-xl font-semibold text-gray-800 flex items-center">
                    <i class="fa-solid fa-file-invoice mr-2 text-green-600"></i>
                    Detail Transaksi #{{ $transaksi->idTransaksiPenitipan }}
                </h2>
                <p class="text-gray-600 mt-1">Informasi lengkap transaksi penitipan</p>
                <div class="flex items-center space-x-4 mt-3">
                    <span class="px-3 py-1 text-sm font-semibold rounded-full 
                        @if($transaksi->statusPenitipan == 'Aktif') bg-green-100 text-green-800
                        @elseif($transaksi->statusPenitipan == 'Selesai') bg-blue-100 text-blue-800
                        @else bg-red-100 text-red-800 @endif">
                        <i class="fa-solid fa-circle text-xs mr-1"></i>
                        {{ $transaksi->statusPenitipan }}
                    </span>
                    @if($transaksi->statusPerpanjangan)
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">
                            <i class="fa-solid fa-clock text-xs mr-1"></i>
                            Diperpanjang
                        </span>
                    @endif
                    @if(\Carbon\Carbon::parse($transaksi->batasAmbil)->isPast() && $transaksi->statusPenitipan == 'Aktif')
                        <span class="px-3 py-1 text-sm font-semibold rounded-full bg-red-100 text-red-800">
                            <i class="fa-solid fa-exclamation-triangle text-xs mr-1"></i>
                            Expired
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex space-x-3 no-print">
                <a href="{{ route('gudang.transaksi.edit', $transaksi->idTransaksiPenitipan) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-edit"></i>
                    <span>Edit</span>
                </a>
                <a href="{{ route('gudang.transaksi.print-nota', $transaksi->idTransaksiPenitipan) }}" 
                   target="_blank"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-file-pdf"></i>
                    <span>Cetak PDF</span>
                </a>
                <button onclick="window.print()" 
                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fa-solid fa-print"></i>
                    <span>Cetak</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Transaction Overview -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Transaction Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Transaction Details -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-info-circle text-blue-600 mr-2"></i>
                        Informasi Transaksi
                    </h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="text-sm font-medium text-gray-500">ID Transaksi</label>
                            <p class="text-lg font-semibold text-gray-900 mt-1">#{{ $transaksi->idTransaksiPenitipan }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Pendapatan</label>
                            <p class="text-lg font-semibold text-green-600 mt-1">Rp {{ number_format($transaksi->pendapatan, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Masuk Gudang</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-calendar text-green-600 mr-2"></i>
                                {{ \Carbon\Carbon::parse($transaksi->tanggalMasukPenitipan)->format('d F Y, H:i') }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Akhir Penitipan</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-calendar-check text-blue-600 mr-2"></i>
                                {{ \Carbon\Carbon::parse($transaksi->tanggalAkhirPenitipan)->format('d F Y, H:i') }}
                            </p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="text-sm font-medium text-gray-500">Batas Pengambilan</label>
                            <div class="flex items-center mt-1">
                                <i class="fa-solid fa-calendar-times text-red-600 mr-2"></i>
                                <span class="text-gray-900">{{ \Carbon\Carbon::parse($transaksi->batasAmbil)->format('d F Y, H:i') }}</span>
                                @if(\Carbon\Carbon::parse($transaksi->batasAmbil)->isPast() && $transaksi->statusPenitipan == 'Aktif')
                                    <span class="ml-2 text-red-500 text-sm font-medium">
                                        (Expired {{ \Carbon\Carbon::parse($transaksi->batasAmbil)->diffForHumans() }})
                                    </span>
                                @elseif($transaksi->statusPenitipan == 'Aktif')
                                    <span class="ml-2 text-blue-500 text-sm font-medium">
                                        ({{ \Carbon\Carbon::parse($transaksi->batasAmbil)->diffForHumans() }})
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-box text-green-600 mr-2"></i>
                        Produk yang Dititipkan ({{ $detail->count() }} item)
                    </h3>
                </div>
                @if($detail->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Foto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kategori</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga Jual</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Garansi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($detail as $index => $item)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($item->gambar)
                                                @php
                                                    $gambarArray = explode(',', $item->gambar);
                                                    $firstImage = trim($gambarArray[0]);
                                                @endphp
                                                <img src="{{ asset('uploads/produk/' . $firstImage) }}" 
                                                     alt="Foto Produk" 
                                                     class="h-12 w-12 object-cover rounded-lg border border-gray-200">
                                            @else
                                                <div class="h-12 w-12 bg-gray-100 rounded-lg flex items-center justify-center">
                                                    <i class="fa-solid fa-image text-gray-400"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->namaProduk }}</div>
                                            <div class="text-xs text-gray-500 mt-1">{{ $item->deskripsi ?? $item->namaProduk }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                                {{ $item->kategori }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium text-green-600">Rp {{ number_format($item->harga, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <span class="font-medium text-blue-600">Rp {{ number_format($item->hargaJual, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            {{ number_format($item->berat, 2) }} kg
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                                @if($item->status == 'Tersedia') bg-green-100 text-green-800
                                                @elseif($item->status == 'Terjual') bg-blue-100 text-blue-800
                                                @elseif($item->status == 'Didonasikan') bg-purple-100 text-purple-800
                                                @else bg-gray-100 text-gray-800 @endif">
                                                {{ $item->status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                            @if($item->tanggalGaransi)
                                                @php
                                                    $garansiDate = \Carbon\Carbon::parse($item->tanggalGaransi);
                                                    $isActive = $garansiDate->isFuture();
                                                @endphp
                                                <div class="flex items-center">
                                                    <i class="fa-solid fa-shield-alt mr-1 {{ $isActive ? 'text-green-500' : 'text-red-500' }}"></i>
                                                    <span class="text-xs {{ $isActive ? 'text-green-600' : 'text-red-600' }}">
                                                        {{ $garansiDate->format('d/m/Y') }}
                                                        @if($isActive)
                                                            <br><span class="text-green-500">(Aktif)</span>
                                                        @else
                                                            <br><span class="text-red-500">(Expired)</span>
                                                        @endif
                                                    </span>
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-xs">
                                                    <i class="fa-solid fa-times mr-1"></i>
                                                    Tidak ada
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="4" class="px-6 py-4 text-sm font-medium text-gray-900">
                                        <i class="fa-solid fa-calculator mr-2"></i>
                                        Total:
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-green-600">
                                        Rp {{ number_format($detail->sum('harga'), 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-blue-600">
                                        Rp {{ number_format($detail->sum('hargaJual'), 0, ',', '.') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-bold text-gray-900">
                                        {{ number_format($detail->sum('berat'), 2) }} kg
                                    </td>
                                    <td colspan="2" class="px-6 py-4 text-sm font-medium text-gray-600">
                                        {{ $detail->count() }} item
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <div class="p-6 text-center">
                        <div class="text-gray-400 mb-3">
                            <i class="fa-solid fa-box-open text-4xl"></i>
                        </div>
                        <p class="text-gray-500 text-sm">Tidak ada detail produk yang tercatat.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer Info -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-user text-blue-600 mr-2"></i>
                        Informasi Penitip
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Penitip</label>
                            <p class="text-gray-900 mt-1 font-medium">{{ $transaksi->namaPenitip }}</p>
                        </div>
                        @if(isset($transaksi->emailPenitip))
                        <div>
                            <label class="text-sm font-medium text-gray-500">Email</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-envelope text-blue-600 mr-2"></i>
                                {{ $transaksi->emailPenitip }}
                            </p>
                        </div>
                        @endif
                        @if(isset($transaksi->nikPenitip))
                        <div>
                            <label class="text-sm font-medium text-gray-500">NIK</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-id-card text-green-600 mr-2"></i>
                                {{ $transaksi->nikPenitip }}
                            </p>
                        </div>
                        @endif
                        <div>
                            <label class="text-sm font-medium text-gray-500">Alamat</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-map-marker-alt text-red-600 mr-2"></i>
                                {{ $transaksi->alamatPenitip }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Staff Info -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-user-tie text-green-600 mr-2"></i>
                        Informasi Pegawai QC
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="text-sm font-medium text-gray-500">Nama Pegawai</label>
                            <p class="text-gray-900 mt-1 font-medium">{{ $transaksi->namaPegawai }}</p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Tanggal Input</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-calendar-plus text-blue-600 mr-2"></i>
                                {{ \Carbon\Carbon::parse($transaksi->created_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Terakhir Update</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-calendar-edit text-yellow-600 mr-2"></i>
                                {{ \Carbon\Carbon::parse($transaksi->updated_at)->format('d/m/Y H:i') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Duration Info -->
            <div class="bg-white rounded-lg shadow-sm">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-hourglass-half text-orange-600 mr-2"></i>
                        Info Waktu
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        @php
                            $masuk = \Carbon\Carbon::parse($transaksi->tanggalMasukPenitipan);
                            $akhir = \Carbon\Carbon::parse($transaksi->tanggalAkhirPenitipan);
                            $batas = \Carbon\Carbon::parse($transaksi->batasAmbil);
                            $sekarang = \Carbon\Carbon::now();
                            
                            $durasiPenitipan = $masuk->diffInDays($akhir);
                            $durasiGrace = $akhir->diffInDays($batas);
                        @endphp
                        
                        <div>
                            <label class="text-sm font-medium text-gray-500">Durasi Penitipan</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-clock text-blue-600 mr-2"></i>
                                {{ $durasiPenitipan }} hari
                            </p>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Grace Period</label>
                            <p class="text-gray-900 mt-1">
                                <i class="fa-solid fa-hourglass text-orange-600 mr-2"></i>
                                {{ $durasiGrace }} hari setelah expired
                            </p>
                        </div>
                        
                        @if($transaksi->statusPenitipan == 'Aktif')
                            @if($sekarang->lt($akhir))
                                <div class="p-3 bg-green-50 rounded-lg border border-green-200">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-check-circle text-green-500 mr-2"></i>
                                        <span class="text-sm font-medium text-green-800">Masih Aktif</span>
                                    </div>
                                    <p class="text-sm text-green-600 mt-1">
                                        Berakhir {{ $akhir->diffForHumans() }}
                                    </p>
                                </div>
                            @elseif($sekarang->between($akhir, $batas))
                                <div class="p-3 bg-yellow-50 rounded-lg border border-yellow-200">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-exclamation-triangle text-yellow-500 mr-2"></i>
                                        <span class="text-sm font-medium text-yellow-800">Grace Period</span>
                                    </div>
                                    <p class="text-sm text-yellow-600 mt-1">
                                        Harus diambil {{ $batas->diffForHumans() }}
                                    </p>
                                </div>
                            @else
                                <div class="p-3 bg-red-50 rounded-lg border border-red-200">
                                    <div class="flex items-center">
                                        <i class="fa-solid fa-times-circle text-red-500 mr-2"></i>
                                        <span class="text-sm font-medium text-red-800">Expired</span>
                                    </div>
                                    <p class="text-sm text-red-600 mt-1">
                                        Expired {{ $batas->diffForHumans() }}
                                    </p>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow-sm no-print">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fa-solid fa-bolt text-yellow-600 mr-2"></i>
                        Aksi Cepat
                    </h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('gudang.transaksi.edit', $transaksi->idTransaksiPenitipan) }}" 
                       class="w-full flex items-center justify-center px-4 py-2 border border-blue-300 rounded-md shadow-sm text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100">
                        <i class="fa-solid fa-edit mr-2"></i>
                        Edit Transaksi
                    </a>
                    <a href="{{ route('gudang.transaksi.print-nota', $transaksi->idTransaksiPenitipan) }}" 
                       target="_blank"
                       class="w-full flex items-center justify-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100">
                        <i class="fa-solid fa-file-pdf mr-2"></i>
                        Cetak PDF
                    </a>
                    <button onclick="window.print()" 
                            class="w-full flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100">
                        <i class="fa-solid fa-print mr-2"></i>
                        Cetak Detail
                    </button>
                    <a href="{{ route('gudang.transaksi.index') }}" 
                       class="w-full flex items-center justify-center px-4 py-2 border border-green-300 rounded-md shadow-sm text-sm font-medium text-green-700 bg-green-50 hover:bg-green-100">
                        <i class="fa-solid fa-list mr-2"></i>
                        Daftar Transaksi
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        nav, aside, .no-print {
            display: none !important;
        }
        body {
            background: white !important;
            -webkit-print-color-adjust: exact;
        }
        .ml-64 {
            margin-left: 0 !important;
        }
        .shadow-sm {
            box-shadow: none !important;
        }
        .rounded-lg {
            border: 1px solid #e5e7eb !important;
        }
    }
</style>
@endsection