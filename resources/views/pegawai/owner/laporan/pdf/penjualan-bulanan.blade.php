<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Penjualan Bulanan</title>
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
        
        .company-info {
            text-align: center;
            margin-bottom: 20px;
        }
        
        .summary-box {
            border: 1px solid #333;
            padding: 15px;
            margin: 20px 0;
            background-color: #f5f5f5;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        table, th, td {
            border: 1px solid #333;
        }
        
        th, td {
            padding: 8px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        /* SIMPLE BAR CHART USING TABLE */
        .chart-table {
            border: 1px solid #333;
            margin: 20px 0;
            background-color: #fafafa;
        }
        
        .chart-table td {
            padding: 2px;
            vertical-align: bottom;
            text-align: center;
            width: 8.33%; /* 100% / 12 months */
        }
        
        .chart-bar {
            background-color: #4a90e2;
            color: white;
            font-size: 8px;
            font-weight: bold;
            margin: 2px auto;
            writing-mode: vertical-lr;
            text-orientation: mixed;
        }
        
        .chart-label {
            font-size: 9px;
            font-weight: bold;
            padding: 5px 2px;
        }
        
        .chart-value {
            font-size: 8px;
            color: #666;
            padding: 2px;
        }
        
        .chart-info {
            margin-top: 20px;
            font-size: 11px;
            padding: 10px;
            background-color: #f9f9f9;
            border: 1px solid #ddd;
        }
        
        .analysis {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 20px;
        }
        
        .footer {
            margin-top: 50px;
            text-align: right;
            font-size: 10px;
            color: #666;
        }
        
        /* Page break */
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>
    <!-- HALAMAN 1 -->
    <div class="company-info">
        <h2>ReUse Mart</h2>
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
    </div>

    <div class="header">
        <h3>LAPORAN PENJUALAN BULANAN</h3>
        <p>Tahun : {{ $tahun }}</p>
        <p>Tanggal cetak: {{ date('d F Y') }}</p>
    </div>

    <div class="summary-box">
        <strong>Total Barang Terjual:</strong> {{ $totalBarang }} item<br>
        <strong>Total Penjualan Kotor:</strong> Rp {{ number_format($totalPenjualan, 0, ',', '.') }}<br>
        <strong>Periode:</strong> Januari - Desember {{ $tahun }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Bulan</th>
                <th class="text-center">Jumlah Barang Terjual</th>
                <th class="text-right">Jumlah Penjualan Kotor</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataFormatted as $data)
            <tr>
                <td>{{ $data['bulan'] }}</td>
                <td class="text-center">{{ $data['jumlah_barang'] }}</td>
                <td class="text-right">{{ number_format($data['jumlah_penjualan'], 0, ',', '.') }}</td>
            </tr>
            @endforeach
            
            <tr style="font-weight: bold; background-color: #f0f0f0;">
                <td>Total</td>
                <td class="text-center">{{ $totalBarang }}</td>
                <td class="text-right">{{ number_format($totalPenjualan, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <!-- HALAMAN 2 - GRAFIK -->
    <div class="page-break">
        <h3 style="text-align: center; margin-bottom: 20px;">Grafik Penjualan Bulanan {{ $tahun }}</h3>
        
        @php
            $maxPenjualan = collect($dataFormatted)->max('jumlah_penjualan');
            $maxHeight = 100; // tinggi maksimal dalam pixel
        @endphp
        
        <table class="chart-table">
            <!-- ROW 1: BARS -->
            <tr style="height: 120px;">
                @foreach($dataFormatted as $data)
                    @php
                        $height = $maxPenjualan > 0 ? round(($data['jumlah_penjualan'] / $maxPenjualan) * $maxHeight) : 0;
                        // Format raw untuk display di dalam chart (tanpa desimal)
                        $displayValue = $data['jumlah_penjualan'] >= 1000000 ? 
                            round($data['jumlah_penjualan'] / 1000000) . 'M' : 
                            ($data['jumlah_penjualan'] >= 1000 ? 
                                round($data['jumlah_penjualan'] / 1000) . 'K' : 
                                $data['jumlah_penjualan']);
                    @endphp
                    <td style="vertical-align: bottom;">
                        @if($height > 0)
                            <div class="chart-bar" style="height: {{ $height }}px; width: 20px;">
                                {{ $displayValue }}
                            </div>
                        @else
                            <div style="height: 5px; width: 20px; background-color: #ddd; margin: 2px auto;"></div>
                        @endif
                    </td>
                @endforeach
            </tr>
            
            <!-- ROW 2: VALUES -->
            <tr>
                @foreach($dataFormatted as $data)
                    <td class="chart-value">
                        {{ $data['jumlah_barang'] }} item<br>
                        @php
                            // Format raw untuk values di chart (tanpa desimal)
                            $valueDisplay = $data['jumlah_penjualan'] >= 1000 ? 
                                round($data['jumlah_penjualan'] / 1000) . 'K' : 
                                $data['jumlah_penjualan'];
                        @endphp
                        {{ $valueDisplay }}
                    </td>
                @endforeach
            </tr>
            
            <!-- ROW 3: LABELS -->
            <tr>
                @foreach($dataFormatted as $data)
                    <td class="chart-label">{{ substr($data['bulan'], 0, 3) }}</td>
                @endforeach
            </tr>
        </table>
        
        <div class="chart-info">
            <strong>Informasi Grafik:</strong><br>
            • Nilai tertinggi: Rp {{ number_format($maxPenjualan, 0, ',', '.') }}<br>
            • Nilai terendah: Rp {{ number_format(collect($dataFormatted)->where('jumlah_penjualan', '>', 0)->min('jumlah_penjualan') ?: 0, 0, ',', '.') }}<br>
            • Rata-rata per bulan: Rp {{ number_format($totalPenjualan / 12, 0, ',', '.') }}
        </div>

        <div class="analysis">
            <h3>Analisis Penjualan:</h3>
            @php
                $tertinggi = collect($dataFormatted)->sortByDesc('jumlah_penjualan')->first();
                $terendah = collect($dataFormatted)->where('jumlah_penjualan', '>', 0)->sortBy('jumlah_penjualan')->first();
            @endphp
            
            <p>• Bulan dengan penjualan tertinggi: <strong>{{ $tertinggi['bulan'] }}</strong> (Rp {{ number_format($tertinggi['jumlah_penjualan'], 0, ',', '.') }})</p>
            
            @if($terendah)
                <p>• Bulan dengan penjualan terendah: <strong>{{ $terendah['bulan'] }}</strong> (Rp {{ number_format($terendah['jumlah_penjualan'], 0, ',', '.') }})</p>
            @endif
            
            <p>• Total barang terjual sepanjang tahun: <strong>{{ $totalBarang }} item</strong></p>
            <p>• Rata-rata penjualan per bulan: <strong>Rp {{ number_format($totalPenjualan / 12, 0, ',', '.') }}</strong></p>
        </div>

        <div class="footer">
            Dicetak pada: {{ date('d F Y H:i:s') }}
        </div>
    </div>
</body>
</html>