@extends('layouts.gudang')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Penjadwalan Pengiriman
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <form action="{{ route('gudang.pengiriman.penjadwalanKirim', $pengiriman->idTransaksiPenjualan) }}" method="POST">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Tanggal Kirim -->
                <div>
                    <label for="tanggalKirim" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Kirim</label>
                    <input type="datetime-local" name="tanggalKirim" id="tanggalKirim" 
                           value="{{ old('tanggalKirim') }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                    @error('tanggalKirim')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Kurir -->
                <div>
                    <label for="kurir" class="block text-sm font-medium text-gray-700 mb-1">Kurir</label>
                    <select name="kurir" id="kurir" 
                            class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500">
                        <option value="" disabled selected>-- Pilih Kurir --</option>
                        @foreach($kurir as $k)
                            <option value="{{ $k->idJabatan }}" {{ old('idJabatan') == $k->idJabatan ? 'selected' : '' }}>
                                {{ $k->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('kurir')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                <a href="{{ route('gudang.pengiriman.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Batal
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection