<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan per Kategori Barang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 15px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin: 0;
        }
        .company-address {
            font-size: 12px;
            margin: 3px 0;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin: 15px 0 10px 0;
            text-decoration: underline;
        }
        .report-info {
            margin-bottom: 20px;
        }
        .report-info p {
            margin: 3px 0;
            font-weight: bold;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 11px;
        }
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .text-left {
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="company-name">ReUse Mart</p>
        <p class="company-address">Jl. Green Eco Park No. 456 Yogyakarta</p>
    </div>
    
    <div class="report-title">LAPORAN PENJUALAN PER KATEGORI BARANG</div>
    
    <div class="report-info">
        <p>Tahun : {{ $tahun }}</p>
        <p>Tanggal cetak: {{ $tanggal_cetak }}</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Jumlah item<br>terjual</th>
                <th>Jumlah item<br>gagal terjual</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td class="text-left">{{ $item['kategori'] }}</td>
                <td>{{ number_format($item['item_terjual']) }}</td>
                <td>{{ number_format($item['item_gagal_terjual']) }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td class="text-left"><strong>Total</strong></td>
                <td><strong>{{ number_format($total_terjual) }}</strong></td>
                <td><strong>{{ number_format($total_gagal_terjual) }}</strong></td>
            </tr>
        </tbody>
    </table>
</body>
</html>