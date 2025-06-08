<!DOCTYPE html>
<html>
<head>
    <title>Laporan Donasi Barang</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { text-align: right; font-size: 12px; margin-top: 20px;}
    </style>
</head>
<body>
    <div class="header">
        <h1>ReUse Mart</h1>
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
        <hr>
        <h2>LAPORAN DONASI BARANG</h2>
    </div>
    
    <p style="font-size: 12px;">Tanggal Cetak: {{ date('d F Y') }}</p>
     @if($search)
    <p style="font-size: 12px;">Filter Pencarian: {{ $search }}</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>ID Donasi</th>
                <th>Nama Organisasi</th>
                <th>Nama Produk</th>
                <th>Jumlah</th>
                <th>Tanggal Donasi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($donations as $donasi)
                <tr>
                    <td>{{ $donasi->idDonasi }}</td>
                    <td>{{ $donasi->requestDonasi->organisasi->nama_org }}</td>
                    <td>{{ $donasi->produk->nama }}</td>
                    <td>{{ $donasi->jumlah }}</td>
                    <td>{{ \Carbon\Carbon::parse($donasi->tgl_donasi)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center;">Tidak ada data untuk dilaporkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
    
     <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->pegawai->nama }}</p>
    </div>
</body>
</html>