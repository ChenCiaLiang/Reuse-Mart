@extends('layouts.cs')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Edit Penitip: {{ $penitip->nama }}
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <form action="{{ route('cs.penitip.update', $penitip->idPenitip) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input type="text" name="nama" id="nama" 
                           value="{{ old('nama', $penitip->nama) }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Nama lengkap penitip">
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="text" name="email" id="email" 
                           value="{{ old('email', $penitip->email) }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Email">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                {{-- <!-- Password (Optional) -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                        Password <span class="text-gray-500 text-xs">(kosongkan jika tidak ingin mengubah)</span>
                    </label>
                    <input type="password" name="password" id="password" 
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Password baru (opsional)">
                    @error('password')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div> --}}
                
                <!-- Alamat -->
                <div>
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <input type="text" name="alamat" id="alamat" 
                           value="{{ old('alamat', $penitip->alamat) }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Alamat baru">
                    @error('alamat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                <a href="{{ route('cs.penitip.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                    Batal
                </a>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection