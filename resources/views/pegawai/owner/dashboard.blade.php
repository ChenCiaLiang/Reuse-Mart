@extends('layouts.owner')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Dashboard Donasi
    </h2>

    <!-- Card Statistik -->
    <div class="grid gap-6 mb-8 md:grid-cols-4">
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-blue-500 bg-blue-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Total Donasi</p>
                <p class="text-lg font-semibold text-gray-700">{{ $totalDonasi ?? 0 }}</p>
            </div>
        </div>
        
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-green-500 bg-green-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Donasi Bulan Ini</p>
                <p class="text-lg font-semibold text-gray-700">{{ $donasiBulanIni ?? 0 }}</p>
            </div>
        </div>
        
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-orange-500 bg-orange-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M8 5a1 1 0 100 2h5.586l-1.293 1.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L13.586 5H8zM12 15a1 1 0 100-2H6.414l1.293-1.293a1 1 0 10-1.414-1.414l-3 3a1 1 0 000 1.414l3 3a1 1 0 001.414-1.414L6.414 15H12z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Request Menunggu</p>
                <p class="text-lg font-semibold text-gray-700">{{ $requestMenunggu ?? 0 }}</p>
            </div>
        </div>
        
        <div class="flex items-center p-4 bg-white rounded-lg shadow-md">
            <div class="p-3 mr-4 text-purple-500 bg-purple-100 rounded-full">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"></path>
                </svg>
            </div>
            <div>
                <p class="mb-2 text-sm font-medium text-gray-600">Organisasi Aktif</p>
                <p class="text-lg font-semibold text-gray-700">{{ $organisasiAktif ?? 0 }}</p>
            </div>
        </div>
    </div>
    
    <!-- Barang untuk Donasi & Request Terbaru -->
    <div class="grid gap-6 mb-8 md:grid-cols-2">
        <!-- Barang untuk Donasi -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-md">
            <h4 class="mb-4 font-semibold text-gray-800">Barang Siap Didonasikan</h4>
            <div class="overflow-hidden overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">ID</th>
                            <th class="px-4 py-3">Deskripsi</th>
                            <th class="px-4 py-3">Kategori</th>
                            <th class="px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse($barangDonasi ?? [] as $barang)
                        <tr class="text-gray-700">
                            <td class="px-4 py-3 text-sm">{{ $barang->idProduk }}</td>
                            <td class="px-4 py-3 text-sm">{{ $barang->deskripsi }}</td>
                            <td class="px-4 py-3 text-sm">{{ $barang->kategori->nama }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('owne.barang') }}" class="text-blue-600 hover:text-blue-900">Alokasikan</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-3 text-sm text-center text-gray-500">
                                Tidak ada barang untuk didonasikan
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if(count($barangDonasi ?? []) > 0)
                <div class="text-right mt-4">
                    <a href="{{ route('owner.donasi.barang') }}" class="text-sm font-medium text-blue-600 hover:underline">
                        Lihat Semua <span class="ml-1">&rarr;</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
        
        <!-- Request Terbaru -->
        <div class="min-w-0 p-4 bg-white rounded-lg shadow-md">
            <h4 class="mb-4 font-semibold text-gray-800">Request Donasi Terbaru</h4>
            <div class="overflow-hidden overflow-x-auto">
                <table class="w-full whitespace-no-wrap">
                    <thead>
                        <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                            <th class="px-4 py-3">Organisasi</th>
                            <th class="px-4 py-3">Request</th>
                            <th class="px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y bg-white">
                        @forelse($requestTerbaru ?? [] as $request)
                        <tr class="text-gray-700">
                            <td class="px-4 py-3 text-sm">{{ $request->organisasi->nama }}</td>
                            <td class="px-4 py-3 text-sm">{{ $request->request }}</td>
                            <td class="px-4 py-3 text-sm">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    {{ $request->status == 'Terpenuhi' ? 'bg-green-100 text-green-800' : 
                                    ($request->status == 'Ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                    {{ $request->status }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-4 py-3 text-sm text-center text-gray-500">
                                Tidak ada request donasi terbaru
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @if(count($requestTerbaru ?? []) > 0)
                <div class="text-right mt-4">
                    <a href="{{ route('owner.donasi.request') }}" class="text-sm font-medium text-blue-600 hover:underline">
                        Lihat Semua <span class="ml-1">&rarr;</span>
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
    
    <!-- History Donasi Terbaru -->
    <div class="w-full p-4 bg-white rounded-lg shadow-md">
        <h4 class="mb-4 font-semibold text-gray-800">Riwayat Donasi Terakhir</h4>
        <div class="overflow-hidden overflow-x-auto">
            <table class="w-full whitespace-no-wrap">
                <thead>
                    <tr class="text-xs font-semibold tracking-wide text-left text-gray-500 uppercase border-b bg-gray-50">
                        <th class="px-4 py-3">Tanggal</th>
                        <th class="px-4 py-3">Barang</th>
                        <th class="px-4 py-3">Organisasi</th>
                        <th class="px-4 py-3">Penerima</th>
                    </tr>
                </thead>
                <tbody class="divide-y bg-white">
                    @forelse($historyDonasi ?? [] as $donasi)
                    <tr class="text-gray-700">
                        <td class="px-4 py-3 text-sm">{{ $donasi->tanggalPemberian->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm">{{ $donasi->produk->deskripsi }}</td>
                        <td class="px-4 py-3 text-sm">{{ $donasi->request->organisasi->nama }}</td>
                        <td class="px-4 py-3 text-sm">{{ $donasi->namaPenerima }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-sm text-center text-gray-500">
                            Belum ada riwayat donasi
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if(count($historyDonasi ?? []) > 0)
            <div class="text-right mt-4">
                <a href="{{ route('owner.donasi.history') }}" class="text-sm font-medium text-blue-600 hover:underline">
                    Lihat Semua <span class="ml-1">&rarr;</span>
                </a>
            </div>
            @endif
        </div>
    </div>
    
    <!-- Quick Links -->
    <div class="mt-8 flex flex-wrap gap-4">
        <a href="{{ route('owner.donasi.request') }}" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
            <i class="fas fa-list mr-2"></i>Lihat Request Donasi
        </a>
        <a href="{{ route('owner.donasi.barang') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
            <i class="fas fa-box mr-2"></i>Alokasikan Barang
        </a>
        <a href="{{ route('owner.donasi.history') }}" class="px-4 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700">
            <i class="fas fa-history mr-2"></i>Riwayat Donasi
        </a>
    </div>
</div>
@endsection