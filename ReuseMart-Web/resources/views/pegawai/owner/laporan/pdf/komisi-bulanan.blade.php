<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Komisi Bulanan</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
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
            font-size: 9px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 6px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 8px;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }
        
        .number {
            text-align: right;
        }
        
        .summary {
            margin-top: 20px;
            padding: 15px;
            background-color: #f0f8ff;
            border: 1px solid #000;
            font-size: 11px;
        }
        
        .summary-item {
            margin: 5px 0;
        }
        
        .note {
            margin-top: 20px;
            padding: 10px;
            background-color: #fffacd;
            border: 1px solid #ddd;
            font-size: 9px;
        }
        
        .note-title {
            font-weight: bold;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">ReUse Mart</div>
        <div class="company-address">Jl. Green Eco Park No. 456 Yogyakarta</div>
        <div class="report-title">LAPORAN KOMISI BULANAN</div>
        <div class="report-info">Bulan : {{ $namaBulan }}</div>
        <div class="report-info">Tahun : {{ $tahun }}</div>
        <div class="report-info">Tanggal cetak: {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
    </div>

    <div class="content">
        @php
            $totalKomisiHunter = $dataKomisi->sum('komisiHunter');
            $totalKomisiReuse = $dataKomisi->sum('komisiReuse');
            $totalBonus = $dataKomisi->sum('bonus');
        @endphp

        <!-- Summary Section -->
        <div class="summary">
            <div class="summary-item"><strong>Total Produk Terjual:</strong> {{ $dataKomisi->count() }} item</div>
            <div class="summary-item"><strong>Total Komisi Hunter:</strong> Rp {{ number_format($totalKomisiHunter, 0, ',', '.') }}</div>
            <div class="summary-item"><strong>Total Komisi ReUseMart:</strong> Rp {{ number_format($totalKomisiReuse, 0, ',', '.') }}</div>
            <div class="summary-item"><strong>Total Bonus Penitip:</strong> Rp {{ number_format($totalBonus, 0, ',', '.') }}</div>
        </div>

        <!-- Table Section -->
        @if($dataKomisi->count() > 0)
        <table>
            <thead>
                <tr>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Harga Jual</th>
                    <th>Tanggal Masuk</th>
                    <th>Tanggal Laku</th>
                    <th>Komisi Hunter</th>
                    <th>Komisi ReUse Mart</th>
                    <th>Bonus Penitip</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataKomisi as $komisi)
                <tr>
                    <td>{{ $komisi->kode_produk }}</td>
                    <td>{{ $komisi->nama_produk }}</td>
                    <td class="number">{{ number_format($komisi->harga_jual, 0, ',', '.') }}</td>
                    <td>{{ \Carbon\Carbon::parse($komisi->tanggal_masuk)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($komisi->tanggal_laku)->format('d/m/Y') }}</td>
                    <td class="number">{{ number_format($komisi->komisiHunter, 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($komisi->komisiReuse, 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($komisi->bonus, 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="5"><strong>Total</strong></td>
                    <td class="number"><strong>{{ number_format($totalKomisiHunter, 0, ',', '.') }}</strong></td>
                    <td class="number"><strong>{{ number_format($totalKomisiReuse, 0, ',', '.') }}</strong></td>
                    <td class="number"><strong>{{ number_format($totalBonus, 0, ',', '.') }}</strong></td>
                </tr>
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 50px; border: 1px solid #ddd; background-color: #f9f9f9;">
            <strong>Tidak ada data komisi untuk bulan {{ $namaBulan }} {{ $tahun }}</strong>
        </div>
        @endif

        <!-- Notes Section -->
        <div class="note">
            <div class="note-title">Keterangan:</div>
            <ul style="margin: 5px 0; padding-left: 15px;">
                <li><strong>Kode Produk:</strong> 1 huruf inisial barang + nomor urut dari inisial tersebut.</li>
                <li><strong>Komisi Hunter:</strong> 5% dari harga jual jika barang hasil hunting dan terjual.</li>
                <li><strong>Komisi ReUseMart:</strong> 
                    <ul style="margin: 2px 0; padding-left: 15px;">
                        <li>20% untuk penitipan pertama</li>
                        <li>30% untuk penitipan yang sudah diperpanjang</li>
                        <li>Dikurangi komisi hunter 5% jika ada</li>
                    </ul>
                </li>
                <li><strong>Bonus Penitip:</strong> 10% dari komisi ReUseMart jika barang terjual dalam waktu kurang dari 7 hari.</li>
            </ul>
        </div>

        <!-- Analysis Section -->
        @if($dataKomisi->count() > 0)
        <div style="margin-top: 20px;">
            <h3 style="font-size: 12px;">Analisis Komisi {{ $namaBulan }} {{ $tahun }}:</h3>
            @php
                $barangCepat = $dataKomisi->where('bonus', '>', 0)->count();
                $barangHunter = $dataKomisi->where('komisiHunter', '>', 0)->count();
                $rataKomisiReuse = $dataKomisi->avg('komisiReuse');
            @endphp
            
            <ul style="margin-left: 20px; font-size: 10px;">
                <li>Barang yang terjual cepat (< 7 hari): <strong>{{ $barangCepat }}</strong> dari {{ $dataKomisi->count() }} barang</li>
                <li>Barang hasil hunting: <strong>{{ $barangHunter }}</strong> dari {{ $dataKomisi->count() }} barang</li>
                <li>Rata-rata komisi ReUseMart per produk: <strong>Rp {{ number_format($rataKomisiReuse, 0, ',', '.') }}</strong></li>
                <li>Persentase bonus yang diberikan: <strong>{{ $dataKomisi->count() > 0 ? round(($barangCepat / $dataKomisi->count()) * 100, 1) : 0 }}%</strong></li>
            </ul>
        </div>
        @endif
    </div>

    <div style="margin-top: 30px; text-align: right; font-size: 10px;">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>