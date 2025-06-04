@extends('layouts.owner')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Laporan Stok Gudang</h2>
        <a href="{{ route('owner.laporan.stok-gudang.download') }}" 
           class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm flex items-center">
            <i class="fas fa-download mr-2"></i>
            Download PDF
        </a>
    </div>

    <!-- Info Header -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-blue-800">
            Stok Gudang per {{ \Carbon\Carbon::now()->format('d F Y') }}
        </h3>
        <p class="text-sm text-blue-600 mt-1">
            Stok yang bisa dilihat adalah stok per hari ini (sama dengan tanggal cetak). Tidak bisa dilihat stok yang kemarin-kemarin.
        </p>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-500 rounded-lg">
                    <i class="fas fa-boxes text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Total Barang</p>
                    <p class="text-lg font-bold text-green-800">{{ $dataStok->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-500 rounded-lg">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Penitip</p>
                    <p class="text-lg font-bold text-blue-800">{{ $dataStok->unique('idPenitip')->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-500 rounded-lg">
                    <i class="fas fa-sync-alt text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-600">Perpanjangan</p>
                    <p class="text-lg font-bold text-yellow-800">{{ $dataStok->where('statusPerpanjangan', 1)->count() }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-purple-500 rounded-lg">
                    <i class="fas fa-search text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-purple-600">Hunter</p>
                    <p class="text-lg font-bold text-purple-800">{{ $dataStok->whereNotNull('id_hunter')->count() }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Kode Produk
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Produk
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Penitip
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Penitip
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Masuk
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Perpanjangan
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        ID Hunter
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Nama Hunter
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Harga
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($dataStok as $stok)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $stok->kode_produk }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $stok->nama_produk }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        T{{ $stok->idPenitip }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $stok->nama_penitip }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($stok->tanggal_masuk)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($stok->statusPerpanjangan)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                Ya
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Tidak
                            </span>
                        @endif
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $stok->id_hunter ? 'P' . $stok->id_hunter : '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $stok->nama_hunter ?? '-' }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($stok->harga, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p>Tidak ada barang dalam stok gudang</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dataStok->count() > 0)
    <!-- Legend -->
    <div class="mt-6 bg-gray-50 p-4 rounded-lg">
        <h4 class="text-sm font-semibold text-gray-800 mb-2">Keterangan:</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-xs text-gray-600">
            <p>• Kode Produk: 1 huruf inisial barang + nomor urut dari inisial tersebut</p>
            <p>• Perpanjangan "Ya": Barang sudah ada perpanjangan penitipan (komisi 30%)</p>
            <p>• Perpanjangan "Tidak": Penitipan pertama (komisi 20%)</p>
            <p>• Hunter: Pegawai yang melakukan hunting barang (mendapat komisi 5% jika barang laku)</p>
        </div>
    </div>
    @endif
</div>
@endsection