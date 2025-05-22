@extends('layouts.owner')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Daftar Request Donasi
    </h2>

    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4">
        {{ session('success') }}
    </div>
    @endif

    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <div class="overflow-x-auto">
            <div>
                <form action="{{ route('owner.donasi.request') }}" method="GET" class="flex">
                    <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari request..." 
                           class="border rounded-l-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-600 w-64">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-r-md">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            <table class="min-w-full bg-white">
                <thead class="bg-gray-50 text-gray-600 uppercase text-sm leading-normal">
                    <tr>
                        <th class="py-3 px-6 text-left">ID Request</th>
                        <th class="py-3 px-6 text-left">Organisasi</th>
                        <th class="py-3 px-6 text-left">Tanggal Request</th>
                        <th class="py-3 px-6 text-left">Request</th>
                        <th class="py-3 px-6 text-left">Status</th>
                        <th class="py-3 px-6 text-left">Penerima</th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 text-sm">
                    @forelse($requestDonasi as $request)
                    <tr class="border-b border-gray-200 hover:bg-gray-50">
                        <td class="py-3 px-6">{{ $request->idRequest }}</td>
                        <td class="py-3 px-6">{{ $request->organisasi->nama }}</td>
                        <td class="py-3 px-6">{{ $request->tanggalRequest->format('d/m/Y') }}</td>
                        <td class="py-3 px-6">{{ $request->request }}</td>
                        <td class="py-3 px-6">
                            <span class="px-2 py-1 text-xs rounded-full 
                                {{ $request->status == 'Terpenuhi' ? 'bg-green-100 text-green-800' : 
                                ($request->status == 'Ditolak' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                {{ $request->status }}
                            </span>
                        </td>
                        <td class="py-3 px-6">{{ $request->penerima }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-3 px-6 text-center">Tidak ada data request donasi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection