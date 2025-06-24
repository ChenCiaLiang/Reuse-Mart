@extends('layouts.admin')

@section('content')
<div class="container px-6 mx-auto grid">
    <h2 class="my-6 text-2xl font-semibold text-gray-700">
        Detail Pegawai
    </h2>

    <!-- Card -->
    <div class="bg-white shadow-md rounded-lg p-6 mb-8">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h3 class="text-xl font-semibold text-gray-800">{{ $pegawai->nama }}</h3>
                <p class="text-sm text-gray-600">{{ $pegawai->jabatan->nama }}</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('admin.pegawai.edit', $pegawai->idPegawai) }}" class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded-md text-sm">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                
                <!-- TAMBAHAN BARU: Reset Password -->
                <form action="{{ route('admin.pegawai.reset-password', $pegawai->idPegawai) }}" method="POST" class="inline-block" onsubmit="return confirmResetPassword();">
                    @csrf
                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded-md text-sm">
                        <i class="fas fa-key mr-1"></i> Reset Password
                    </button>
                </form>
                
                <form action="{{ route('admin.pegawai.destroy', $pegawai->idPegawai) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pegawai ini?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded-md text-sm">
                        <i class="fas fa-trash mr-1"></i> Hapus
                    </button>
                </form>
            </div>
        </div>

        <div class="border-t border-gray-200 pt-4">
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-x-4 gap-y-6">
                <div>
                    <dt class="text-sm font-medium text-gray-500">ID Pegawai</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->idPegawai }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Nama Lengkap</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->nama }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->email }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Nomor Telepon</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->noTelp }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->alamat }}</dd>
                </div>

                <div>
                    <dt class="text-sm font-medium text-gray-500">Tanggal Lahir</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->tanggalLahir }}</dd>
                </div>
                
                <div>
                    <dt class="text-sm font-medium text-gray-500">Jabatan</dt>
                    <dd class="mt-1 text-gray-900">{{ $pegawai->jabatan->nama }}</dd>
                </div>
            </dl>
        </div>
        
        <div class="mt-6">
            <a href="{{ route('admin.pegawai.index') }}" class="text-green-600 hover:text-green-800">
                <i class="fas fa-arrow-left mr-1"></i> Kembali ke daftar pegawai
            </a>
        </div>
    </div>
</div>
@endsection

<script>
function confirmResetPassword() {
    const tanggalLahir = '{{ \Carbon\Carbon::parse($pegawai->tanggalLahir)->format("dmY") }}';
    const nama = '{{ $pegawai->nama }}';
    const tanggalFormatted = '{{ \Carbon\Carbon::parse($pegawai->tanggalLahir)->format("d/m/Y") }}';
    
    const message = `Apakah Anda yakin ingin mereset password untuk ${nama}?\n\nPassword akan direset ke: ${tanggalLahir}\n(Tanggal lahir: ${tanggalFormatted} dalam format ddmmyyyy)`;
    return confirm(message);
}
</script>