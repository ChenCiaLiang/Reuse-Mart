@extends('layouts.owner')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-xl font-bold text-gray-800">Laporan Komisi Bulanan per Produk</h2>
        <div class="flex space-x-3">
            <form method="GET" class="flex items-center space-x-2">
                <label for="bulan" class="text-sm font-medium text-gray-700">Bulan:</label>
                <select name="bulan" id="bulan" class="border border-gray-300 rounded-md px-3 py-1 text-sm" onchange="this.form.submit()">
                    @for ($i = 1; $i <= 12; $i++)
                        @php
                            $bulanIndonesia = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                            ];
                        @endphp
                        <option value="{{ $i }}" {{ $bulan == $i ? 'selected' : '' }}>{{ $bulanIndonesia[$i] }}</option>
                    @endfor
                </select>
                
                <label for="tahun" class="text-sm font-medium text-gray-700 ml-4">Tahun:</label>
                <select name="tahun" id="tahun" class="border border-gray-300 rounded-md px-3 py-1 text-sm" onchange="this.form.submit()">
                    @for ($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}" {{ $tahun == $i ? 'selected' : '' }}>{{ $i }}</option>
                    @endfor
                </select>
            </form>
            <a href="{{ route('owner.laporan.komisi-bulanan.download', ['bulan' => $bulan, 'tahun' => $tahun]) }}" 
               class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm flex items-center">
                <i class="fas fa-download mr-2"></i>
                Download PDF
            </a>
        </div>
    </div>

    <!-- Info Header -->
    <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
        <h3 class="text-lg font-semibold text-green-800">
            Laporan Komisi Bulan {{ $namaBulan }} {{ $tahun }}
        </h3>
        <p class="text-sm text-green-600 mt-1">
            Tanggal cetak: {{ \Carbon\Carbon::now()->format('d F Y') }}
        </p>
    </div>

    <!-- Summary Cards -->
    @php
        $totalKomisiHunter = $dataKomisi->sum('komisiHunter');
        $totalKomisiReuse = $dataKomisi->sum('komisiReuse');
        $totalBonus = $dataKomisi->sum('bonus');
    @endphp

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-500 rounded-lg">
                    <i class="fas fa-users text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-blue-600">Total Komisi Hunter</p>
                    <p class="text-lg font-bold text-blue-800">Rp {{ number_format($totalKomisiHunter, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-500 rounded-lg">
                    <i class="fas fa-store text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-green-600">Total Komisi ReUseMart</p>
                    <p class="text-lg font-bold text-green-800">Rp {{ number_format($totalKomisiReuse, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-500 rounded-lg">
                    <i class="fas fa-gift text-white"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-yellow-600">Total Bonus Penitip</p>
                    <p class="text-lg font-bold text-yellow-800">Rp {{ number_format($totalBonus, 0, ',', '.') }}</p>
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
                        Harga Jual
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Masuk
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Tanggal Laku
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Komisi Hunter
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Komisi ReUseMart
                    </th>
                    <th class="px-4 py-3 border-b border-gray-200 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Bonus Penitip
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($dataKomisi as $komisi)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                        {{ $komisi->kode_produk }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $komisi->nama_produk }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($komisi->harga_jual, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($komisi->tanggal_masuk)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ \Carbon\Carbon::parse($komisi->tanggal_laku)->format('d/m/Y') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($komisi->komisiHunter, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($komisi->komisiReuse, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($komisi->bonus, 0, ',', '.') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                        <p>Tidak ada data komisi untuk bulan {{ $namaBulan }} {{ $tahun }}</p>
                    </td>
                </tr>
                @endforelse
                
                @if($dataKomisi->count() > 0)
                <tr class="bg-gray-100 font-bold">
                    <td colspan="5" class="px-4 py-4 text-right text-sm text-gray-900">
                        Total
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rp {{ number_format($totalKomisiHunter, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rp {{ number_format($totalKomisiReuse, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4 whitespace-nowrap text-sm text-gray-900">
                        Rp {{ number_format($totalBonus, 0, ',', '.') }}
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>
</div>
@endsection