@extends('layouts.gudang')

@section('content')
    <!-- Main Content -->
    <main class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex flex-col md:flex-row">
                <!-- Product Images -->
                <div class="w-full md:w-1/2 mb-6 md:mb-0 md:pr-6">
                    <!-- Main Image -->
                    <div class="bg-gray-100 rounded-lg p-2 mb-4">
                        <img id="mainImage" src="{{ asset('images/produk/' . $gambarArray[0]) }}" 
                            alt="Foto Produk" class="w-full rounded-lg"
                            onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                    </div>
                    
                    <!-- Thumbnail Images -->
                    @if(count($gambarArray) > 1)
                    <div class="flex flex-wrap gap-2">
                        @foreach($gambarArray as $index => $gambar)
                        <div class="thumbnail cursor-pointer w-20 h-20 rounded-md overflow-hidden border-2 
                                {{ $index === 0 ? 'border-green-600' : 'border-transparent' }}"
                            onclick="changeImage('{{ asset('images/produk/' . $gambar) }}', this)">
                            <img src="{{ asset('images/produk/' . $gambar) }}" 
                                alt="Foto Produk"
                                class="w-full h-full object-cover"
                                onerror="this.src='{{ asset('images/produk/default.jpg') }}'">
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
                
                <div class="w-full md:w-1/2">
                    <div class="mb-4">
                        <span class="text-gray-700">Produk: </span>
                        <span class="font-medium">{{ $pengiriman->produk }}</span>
                    </div>

                    <div class="mb-4">
                        <span class="text-gray-700">Status: </span>
                        <span class="font-medium">{{ $pengiriman->status }}</span>
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-gray-700">Tanggal Laku: </span>
                        <span class="font-medium">{{ $pengiriman->tanggalLaku }}</span>
                    </div>

                    <div class="mb-4">
                        <span class="text-gray-700">Tanggal Kirim: </span>
                        <span class="font-medium">{{ $pengiriman->tanggalKirim }}</span>
                    </div>

                    <div class="mb-4">
                        <span class="text-gray-700">Tanggal Ambil: </span>
                        <span class="font-medium">{{ $pengiriman->tanggalAmbil }}</span>
                    </div>
                    
                    <div class="mb-4">
                        <span class="text-gray-700">Pembeli: </span>
                        <span class="font-medium">{{ $pengiriman->namaPembeli }}</span>
                    </div>

                    <div class="mb-4">
                        <span class="text-gray-700">Kurir: </span>
                        <span class="font-medium">{{ $pengiriman->namaPegawai }}</span>
                    </div>
                </div>
            </div>
            <div class="flex item-center mt-6">
                <a href="{{ route('gudang.pengiriman.penjadwalanPage', $pengiriman->idTransaksiPenjualan) }}" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md">
                    <i class="fa fa-calendar" aria-hidden="true"></i> Penjadwalan
                </a>
                <a href="{{ route('gudang.pengiriman.konfirmasiAmbil', $pengiriman->idTransaksiPenjualan) }}" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md ml-4">
                    <i class="fa-solid fa-circle-check"></i> Konfirmasi
                </a>
            </div>
        </div>
    </main>
    <script>
        function changeImage(src, element) {
            // Ubah gambar utama
            document.getElementById('mainImage').src = src;
            
            // Atur kelas aktif pada thumbnail
            const thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => {
                thumb.classList.remove('border-green-600');
                thumb.classList.add('border-transparent');
            });
            
            element.classList.remove('border-transparent');
            element.classList.add('border-green-600');
        }
    </script>
</body>
@endsection