@extends('layouts.owner')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Barang untuk Donasi
    </h2>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4">
        {{ session('error') }}
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">ID Produk</th>
                        <th class="py-3 px-6 text-left">Deskripsi</th>
                        <th class="py-3 px-6 text-left">Kategori</th>
                        <th class="py-3 px-6 text-left">Tanggal Masuk</th>
                        <th class="py-3 px-6 text-left">Status</th>
                        <th class="py-3 px-6 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($barang as $b)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6">{{ $b->idProduk }}</td>
                        <td class="py-3 px-6">{{ $b->deskripsi }}</td>
                        <td class="py-3 px-6">{{ $b->kategori->nama }}</td>
                        <td class="py-3 px-6">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $b->status }}
                            </span>
                        </td>
                        <td class="py-3 px-6">
                            <button class="text-blue-600 hover:text-blue-900" onclick="showAllocationModal('{{ $b->idProduk }}')">
                                Alokasikan ke Organisasi
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-3 px-6 text-center">Tidak ada barang dengan status "untuk donasi"</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Alokasi Barang -->
    <div id="allocationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
        <div class="bg-white rounded-lg p-6 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4">Alokasikan Barang ke Organisasi</h3>
            <form action="{{ route('owner.donasi.alokasi') }}" method="POST">
                @csrf
                <input type="hidden" id="idProduk" name="idProduk">
                
                <div class="mb-4">
                    <label for="idRequest" class="block text-sm font-medium text-gray-700 mb-1">Pilih Request Donasi:</label>
                    <select id="idRequest" name="idRequest" class="w-full px-3 py-2 border border-gray-300 rounded-md" required>
                        <option value="">-- Pilih Request Donasi --</option>
                            @foreach($requestDonasi as $req)
                                <option value="{{ $req->idRequest }}">
                                    {{ $req->request }} ({{ \Carbon\Carbon::parse($req->tanggalRequest)->format('d/m/Y') }})
                                </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" class="px-4 py-2 bg-gray-300 text-gray-800 rounded-md" onclick="hideAllocationModal()">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Alokasikan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function showAllocationModal(idProduk) {
        document.getElementById('idProduk').value = idProduk;
        document.getElementById('allocationModal').classList.remove('hidden');
    }
    
    function hideAllocationModal() {
        document.getElementById('allocationModal').classList.add('hidden');
    }
</script>
@endsection