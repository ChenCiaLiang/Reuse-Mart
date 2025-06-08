<!DOCTYPE html>
<html>
<head>
    <title>Laporan Transaksi Penitip</title>
    <style>
        body { 
            font-family: 'Arial', sans-serif; 
            margin: 20px; 
            color: #333;
            line-height: 1.4;
        }
        .header { 
            text-align: center; 
            margin-bottom: 30px; 
            border-bottom: 3px solid #4CAF50;
            padding-bottom: 15px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px; 
            color: #2E7D32;
            font-weight: bold;
        }
        .header p { 
            margin: 5px 0; 
            font-size: 14px; 
            color: #666;
        }
        .header h2 {
            margin: 15px 0 5px 0;
            font-size: 20px;
            color: #2E7D32;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-section {
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #4CAF50;
        }
        .info-table { 
            width: 100%; 
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table td { 
            padding: 8px 12px; 
            border: none;
            font-size: 14px;
        }
        .info-table td:first-child {
            font-weight: bold;
            width: 25%;
            color: #2E7D32;
        }
        
        .main-table { 
            width: 100%; 
            border-collapse: collapse; 
            font-size: 12px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .main-table th, .main-table td { 
            border: 1px solid #ddd; 
            padding: 10px 8px; 
            text-align: left; 
        }
        .main-table th { 
            background-color: #4CAF50; 
            color: white;
            font-weight: bold;
            text-align: center;
            font-size: 11px;
            text-transform: uppercase;
        }
        .main-table tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .main-table tbody tr:hover {
            background-color: #e8f5e8;
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .total-row {
            background-color: #e8f5e8 !important;
            font-weight: bold;
            border-top: 2px solid #4CAF50;
        }
        .total-row td {
            padding: 12px 8px;
            font-size: 13px;
        }
        
        .summary-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f0f8f0;
            border-radius: 8px;
            border: 1px solid #4CAF50;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table td {
            padding: 8px 15px;
            border-bottom: 1px solid #ddd;
            font-size: 14px;
        }
        .summary-table td:first-child {
            font-weight: bold;
            color: #2E7D32;
            width: 40%;
        }
        .summary-table td:last-child {
            text-align: right;
            font-weight: bold;
        }
        
        .footer { 
            text-align: right; 
            font-size: 12px; 
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px;
            color: #666;
            font-style: italic;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        @media print {
            .header { page-break-inside: avoid; }
            .total-row { page-break-inside: avoid; }
            .footer { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ReUse Mart</h1>
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
        <h2>LAPORAN TRANSAKSI PENITIP</h2>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>ID Penitip</td>
                <td>: T{{ str_pad($penitip->idPenitip, 2, '0', STR_PAD_LEFT) }}</td>
                <td>Periode Laporan</td>
                <td>: Tahun {{ $tahun }}</td>
            </tr>
            <tr>
                <td>Nama Penitip</td>
                <td>: {{ $penitip->nama }}</td>
                <td>Tanggal Cetak</td>
                <td>: {{ \Carbon\Carbon::now()->format('d F Y') }}</td>
            </tr>
            <tr>
                <td>Email</td>
                <td>: {{ $penitip->email }}</td>
                <td>Total Transaksi</td>
                <td>: {{ $transaksi->count() }} transaksi</td>
            </tr>
        </table>
    </div>

    @if($transaksi->count() > 0)
        <div class="summary-section">
            <h3 style="margin-top: 0; color: #2E7D32;">Ringkasan Transaksi</h3>
            <table class="summary-table">
                <tr>
                    <td>Total Harga Jual</td>
                    <td>Rp {{ number_format($totals['harga_jual'], 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Total Komisi ReUseMart</td>
                    <td>Rp {{ number_format($totals['komisi'], 0, ',', '.') }}</td>
                </tr>
                <tr style="border-top: 2px solid #4CAF50;">
                    <td><strong>Total Pendapatan Bersih</strong></td>
                    <td><strong>Rp {{ number_format($totals['pendapatan'], 0, ',', '.') }}</strong></td>
                </tr>
            </table>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 30%;">Nama Produk</th>
                    <th style="width: 12%;">Tgl Masuk</th>
                    <th style="width: 12%;">Tgl Laku</th>
                    <th style="width: 15%;">Harga Jual</th>
                    <th style="width: 13%;">Komisi</th>
                    <th style="width: 13%;">Pendapatan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($transaksi as $index => $item)
                    @php
                        $komisiTotal = $item->komisiReuse + $item->komisiHunter;
                        $pendapatanBersih = $item->komisiPenitip;
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>{{ $item->nama_produk }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggalMasukPenitipan)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggalLaku)->format('d/m/Y') }}</td>
                        <td class="text-right">{{ number_format($item->hargaJual, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($komisiTotal, 0, ',', '.') }}</td>
                        <td class="text-right">{{ number_format($pendapatanBersih, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-center"><strong>TOTAL</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals['harga_jual'], 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals['komisi'], 0, ',', '.') }}</strong></td>
                    <td class="text-right"><strong>{{ number_format($totals['pendapatan'], 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        </table>

        <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
            <p style="margin: 0; font-size: 12px; color: #856404;">
                <strong>Catatan:</strong> Komisi dihitung berdasarkan ketentuan ReUseMart. 
                Pendapatan bersih adalah jumlah yang diterima penitip setelah dipotong komisi.
            </p>
        </div>
    @else
        <div class="empty-state">
            <p><strong>Tidak ada transaksi untuk dilaporkan</strong></p>
            <p>Penitip {{ $penitip->nama }} belum memiliki transaksi yang selesai pada tahun {{ $tahun }}</p>
        </div>
    @endif
    
    <div class="footer">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y, H:i') }} WIB</p>
        <p>Dicetak oleh: {{ auth()->user()->nama ?? 'Sistem' }}</p>
        <hr style="margin: 10px 0; border: none; border-top: 1px solid #ddd;">
        <p style="font-size: 10px;">Dokumen ini digenerate secara otomatis oleh sistem ReUseMart</p>
    </div>
</body>
</html>