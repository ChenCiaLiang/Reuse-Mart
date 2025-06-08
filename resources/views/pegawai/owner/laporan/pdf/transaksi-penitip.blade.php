<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi Penitip</title>
    <style>
        body { font-family: sans-serif; margin: 20px; color: #333; }
        .header { text-align: center; margin-bottom: 25px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 0; font-size: 14px; }
        .info { font-size: 12px; margin-bottom: 20px; }
        .info table { width: 100%; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        table, th, td { border: 1px solid #ccc; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .text-right { text-align: right; }
        tfoot tr { font-weight: bold; background-color: #f2f2f2; }
        .footer { text-align: right; font-size: 12px; margin-top: 20px;}
    </style>
</head>
<body>
    <div class="header">
        <h1>ReUse Mart</h1>
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
        <hr>
        <h2>LAPORAN TRANSAKSI PENITIP</h2>
    </div>

    <div class="info">
       <table>
           <tr>
               <td><strong>Penitip</strong></td>
               <td>: {{ $penitip->nama }}</td>
               <td><strong>Tahun</strong></td>
               <td>: {{ $tahun }}</td>
           </tr>
           <tr>
                <td><strong>Email</strong></td>
                <td>: {{ $penitip->user->email }}</td>
                <td><strong>Tanggal Cetak</strong></td>
                <td>: {{ date('d F Y') }}</td>
           </tr>
       </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>Nama Produk</th>
                <th>Tgl Masuk</th>
                <th>Tgl Terjual</th>
                <th class="text-right">Harga Jual (Rp)</th>
                <th class="text-right">Komisi (Rp)</th>
                <th class="text-right">Pendapatan (Rp)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($transaksi as $item)
                <tr>
                    <td>{{ $item->produk->nama }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->produk->tgl_penitipan)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($item->transaksiPenjualan->tgl_penjualan)->format('d/m/Y') }}</td>
                    <td class="text-right">{{ number_format($item->harga_produk, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->komisi, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->harga_produk - $item->komisi, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data untuk dilaporkan.</td>
                </tr>
            @endforelse
        </tbody>
         <tfoot>
            <tr>
                <td colspan="3" class="text-right"><strong>Total</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['harga_jual'], 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['komisi'], 0, ',', '.') }}</strong></td>
                <td class="text-right"><strong>{{ number_format($totals['pendapatan'], 0, ',', '.') }}</strong></td>
            </tr>
        </tfoot>
    </table>
    
    <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->pegawai->nama }}</p>
    </div>
</body>
</html>