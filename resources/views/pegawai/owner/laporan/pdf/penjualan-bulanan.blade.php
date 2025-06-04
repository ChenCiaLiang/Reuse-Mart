<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan Bulanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
        }
        .company-name {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 10px;
            margin-bottom: 15px;
        }
        .report-title {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .report-info {
            font-size: 10px;
            margin-bottom: 5px;
        }
        
        .content {
            margin-top: 20px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        .number {
            text-align: right;
        }
        
        .chart-section {
            margin: 30px 0;
            text-align: center;
        }
        
        .chart-placeholder {
            border: 2px dashed #ccc;
            height: 200px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f9f9f9;
            margin: 20px 0;
        }
        
        .summary {
            margin-top: 30px;
            padding: 15px;
            background-color: #f0f8ff;
            border: 1px solid #000;
        }
        
        .summary-item {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">ReUse Mart</div>
        <div class="company-address">Jl. Green Eco Park No. 456 Yogyakarta</div>
        <div class="report-title">LAPORAN PENJUALAN BULANAN</div>
        <div class="report-info">Tahun : {{ $tahun }}</div>
        <div class="report-info">Tanggal cetak: {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
    </div>

    <div class="content">
        <!-- Summary Section -->
        <div class="summary">
            <div class="summary-item"><strong>Total Barang Terjual:</strong> {{ $totalBarang }} item</div>
            <div class="summary-item"><strong>Total Penjualan Kotor:</strong> Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</div>
            <div class="summary-item"><strong>Periode:</strong> Januari - Desember {{ $tahun }}</div>
        </div>

        <!-- Table Section -->
        <table>
            <thead>
                <tr>
                    <th>Bulan</th>
                    <th>Jumlah Barang Terjual</th>
                    <th>Jumlah Penjualan Kotor</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataFormatted as $data)
                <tr>
                    <td>{{ $data['bulan'] }}</td>
                    <td class="number">{{ $data['jumlah_barang'] }}</td>
                    <td class="number">{{ number_format($data['jumlah_penjualan'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td><strong>Total</strong></td>
                    <td class="number"><strong>{{ $totalBarang }}</strong></td>
                    <td class="number"><strong>{{ number_format($totalPenjualan, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- Chart Section -->
        <div class="chart-section">
            <h3>Grafik Penjualan Bulanan {{ $tahun }}</h3>
            <div class="chart-placeholder">
                <div>
                    <strong>Grafik Penjualan Bulanan</strong><br>
                    <small>Nilai tertinggi: Rp {{ number_format(collect($dataFormatted)->max('jumlah_penjualan'), 0, ',', '.') }}</small><br>
                    <small>Nilai terendah: Rp {{ number_format(collect($dataFormatted)->min('jumlah_penjualan'), 0, ',', '.') }}</small><br>
                    <small>Rata-rata: Rp {{ number_format(collect($dataFormatted)->avg('jumlah_penjualan'), 0, ',', '.') }}</small>
                </div>
            </div>
        </div>

        <!-- Analysis Section -->
        <div style="margin-top: 30px;">
            <h3>Analisis Penjualan:</h3>
            @php
                $bestMonth = collect($dataFormatted)->sortByDesc('jumlah_penjualan')->first();
                $worstMonth = collect($dataFormatted)->sortBy('jumlah_penjualan')->where('jumlah_penjualan', '>', 0)->first();
            @endphp
            
            <ul style="margin-left: 20px;">
                @if($bestMonth && $bestMonth['jumlah_penjualan'] > 0)
                <li>Bulan dengan penjualan tertinggi: <strong>{{ $bestMonth['bulan'] }}</strong> (Rp {{ number_format($bestMonth['jumlah_penjualan'], 0, ',', '.') }})</li>
                @endif
                
                @if($worstMonth && $worstMonth['jumlah_penjualan'] > 0)
                <li>Bulan dengan penjualan terendah: <strong>{{ $worstMonth['bulan'] }}</strong> (Rp {{ number_format($worstMonth['jumlah_penjualan'], 0, ',', '.') }})</li>
                @endif
                
                <li>Total barang terjual sepanjang tahun: <strong>{{ $totalBarang }}</strong> item</li>
                <li>Rata-rata penjualan per bulan: <strong>Rp {{ number_format($totalPenjualan / 12, 0, ',', '.') }}</strong></li>
            </ul>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: right; font-size: 10px;">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>