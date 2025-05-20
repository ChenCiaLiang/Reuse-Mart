@extends('layouts.admin')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Edit Organisasi: {{ $organisasi->nama }}
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-4 mb-8">
        <form action="{{ route('admin.organisasi.update', $organisasi->idOrganisasi) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID Organisasi (Readonly) -->
                <div>
                    <label for="idOrganisasi" class="block text-sm font-medium text-gray-700 mb-1">ID Organisasi</label>
                    <input type="text" id="idOrganisasi" 
                           value="{{ $organisasi->idOrganisasi }}" 
                           class="w-full border-gray-300 rounded-md shadow-sm bg-gray-100" 
                           readonly>
                </div>
                
                <!-- Nama -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-1">Nama Organisasi</label>
                    <input type="text" name="nama" id="nama" 
                           value="{{ old('nama', $organisasi->nama) }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Nama organisasi">
                    @error('nama')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" name="email" id="email" 
                           value="{{ old('email', $organisasi->email) }}"
                           class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                           placeholder="Email organisasi">
                    @error('email')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Hapus field Password berikut -->
                <!-- 
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
                </div>
                -->
                
                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea name="alamat" id="alamat" rows="3"
                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                              placeholder="Alamat lengkap organisasi">{{ old('alamat', $organisasi->alamat) }}</textarea>
                    @error('alamat')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Logo -->
                <div class="md:col-span-2">
                    <label for="logo" class="block text-sm font-medium text-gray-700 mb-1">Logo Organisasi</label>
                    <div class="flex items-start space-x-4">
                        <div class="flex-shrink-0">
                            <img src="{{ asset($organisasi->logo) }}" alt="{{ $organisasi->nama }}" class="h-24 w-24 object-cover rounded-md">
                        </div>
                        <div class="flex-grow">
                            <input type="file" name="logo" id="logo" 
                                   class="w-full border-gray-300 rounded-md shadow-sm focus:ring-green-500 focus:border-green-500"
                                   accept="image/*">
                            <p class="text-xs text-gray-500 mt-1">Unggah logo baru jika ingin mengubah (JPEG, PNG, JPG, GIF. Max: 2MB)</p>
                            @error('logo')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Buttons -->
            <div class="flex justify-end mt-6 space-x-3">
                <a href="{{ route('admin.organisasi.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
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