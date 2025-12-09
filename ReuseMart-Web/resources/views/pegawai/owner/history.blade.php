@extends('layouts.owner')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        History Donasi
    </h2>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
        {{ session('success') }}
    </div>
    @endif

    <!-- Filter Organisasi -->
    <div class="mb-6">
        <form action="{{ route('owner.donasi.history') }}" method="GET" class="flex items-center space-x-4">
            <div class="flex-grow">
                <label for="idOrganisasi" class="block text-sm font-medium text-gray-700 mb-1">Filter berdasarkan organisasi:</label>
                <select id="idOrganisasi" name="idOrganisasi" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Organisasi</option>
                    @foreach($organisasi as $org)
                        <option value="{{ $org->idOrganisasi }}" {{ $idOrganisasi == $org->idOrganisasi ? 'selected' : '' }}>
                            {{ $org->nama }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="pt-6">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Filter
                </button>
            </div>
        </form>
    </div>

    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">ID Transaksi</th>
                        <th class="py-3 px-6 text-left">Tanggal Donasi</th>
                        <th class="py-3 px-6 text-left">Barang</th>
                        <!-- <th class="py-3 px-6 text-left">Organisasi</th> -->
                        <th class="py-3 px-6 text-left">Penerima</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($donasi as $d)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6">{{ $d->idTransaksiDonasi }}</td>
                        <td class="py-3 px-6">{{ \Carbon\Carbon::parse(time: $d->tanggalPemberian)->format('d/m/Y') }}</td>
                        <td class="py-3 px-6">{{ $d->produk->deskripsi }}</td>
                        <!-- <td class="py-3 px-6">{{ $d->request}}</td> -->
                        <td class="py-3 px-6">{{ $d->namaPenerima }}</td>
                        <td class="py-3 px-6">
                            <a href="{{ route('owner.donasi.edit', $d->idTransaksiDonasi) }}" class="text-blue-600 hover:text-blue-900">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-3 px-6 text-center">Tidak ada data transaksi donasi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection