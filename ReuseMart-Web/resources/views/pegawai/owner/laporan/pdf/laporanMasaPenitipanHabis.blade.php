<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Barang yang Masa Penitipannya Sudah Habis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .address {
            font-size: 12px;
            margin-bottom: 20px;
        }
        
        .report-title {
            font-size: 14px;
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 20px;
        }
        
        .report-info {
            margin-bottom: 20px;
        }
        
        .report-info div {
            margin-bottom: 3px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary {
            margin-top: 20px;
            font-weight: bold;
        }
        
        .page-break {
            page-break-after: always;
        }
        
        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">ReUse Mart</div>
        <div class="address">Jl. Green Eco Park No. 456 Yogyakarta</div>
        
        <div class="report-title">LAPORAN Barang yang Masa Penitipannya Sudah Habis</div>
        
        <div class="report-info">
            <div><strong>Tanggal cetak: {{ $tanggalCetak }}</strong></div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Kode Produk</th>
                <th style="width: 25%;">Nama Produk</th>
                <th style="width: 10%;">Id Penitip</th>
                <th style="width: 20%;">Nama Penitip</th>
                <th style="width: 11%;">Tanggal Masuk</th>
                <th style="width: 11%;">Tanggal Akhir</th>
                <th style="width: 11%;">Batas Ambil</th>
            </tr>
        </thead>
        <tbody>
            @if($dataBarang->count() > 0)
                @foreach($dataBarang as $item)
                    <tr>
                        <td class="text-center">{{ $item->kodeProduk }}</td>
                        <td>{{ $item->namaProduk }}</td>
                        <td class="text-center">T{{ $item->idPenitip }}</td>
                        <td>{{ $item->namaPenitip }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggalMasukPenitipan)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->tanggalAkhirPenitipan)->format('d/m/Y') }}</td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($item->batasAmbil)->format('d/m/Y') }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="7" class="text-center">Tidak ada barang yang masa penitipannya sudah habis</td>
                </tr>
            @endif
        </tbody>
    </table>

    @if($dataBarang->count() > 0)
        <div class="summary">
            <p>Total Barang: {{ $totalBarang }} item</p>
        </div>
    @endif
</body>
</html>