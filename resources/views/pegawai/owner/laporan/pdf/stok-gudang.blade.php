<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Stok Gudang</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
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
            font-size: 8px;
        }
        
        th, td {
            border: 1px solid #000;
            padding: 4px;
            text-align: left;
        }
        
        th {
            background-color: #f0f0f0;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
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
        
        .status-ya {
            background-color: #fef3c7;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
        }
        
        .status-tidak {
            background-color: #d1fae5;
            padding: 2px 4px;
            border-radius: 2px;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">ReUse Mart</div>
        <div class="company-address">Jl. Green Eco Park No. 456 Yogyakarta</div>
        <div class="report-title">LAPORAN STOK GUDANG</div>
        <div class="report-info">Tanggal cetak: {{ \Carbon\Carbon::now()->format('d F Y') }}</div>
    </div>

    <div class="content">
        @php
            $totalBarang = $dataStok->count();
            $totalPenitip = $dataStok->unique('idPenitip')->count();
            $totalPerpanjangan = $dataStok->where('statusPerpanjangan', 1)->count();
            $totalHunter = $dataStok->whereNotNull('id_hunter')->count();
            $totalNilai = $dataStok->sum('harga');
        @endphp

        <!-- Summary Section -->
        <div class="summary">
            <div class="summary-item"><strong>Total Barang di Gudang:</strong> {{ $totalBarang }} item</div>
            <div class="summary-item"><strong>Total Penitip Aktif:</strong> {{ $totalPenitip }} orang</div>
            <div class="summary-item"><strong>Barang dengan Perpanjangan:</strong> {{ $totalPerpanjangan }} item</div>
            <div class="summary-item"><strong>Barang Hasil Hunting:</strong> {{ $totalHunter }} item</div>
            <div class="summary-item"><strong>Total Nilai Stok:</strong> Rp {{ number_format($totalNilai, 0, ',', '.') }}</div>
        </div>

        <!-- Important Note -->
        <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 10px; margin: 15px 0; font-size: 9px;">
            <strong>Catatan Penting:</strong> Stok yang bisa dilihat adalah stok per hari ini (sama dengan tanggal cetak). 
            Tidak bisa dilihat stok yang kemarin-kemarin.
        </div>

        <!-- Table Section -->
        @if($dataStok->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Kode Produk</th>
                    <th style="width: 20%;">Nama Produk</th>
                    <th style="width: 8%;">Id Penitip</th>
                    <th style="width: 15%;">Nama Penitip</th>
                    <th style="width: 10%;">Tanggal Masuk</th>
                    <th style="width: 8%;">Perpanjangan</th>
                    <th style="width: 8%;">ID Hunter</th>
                    <th style="width: 13%;">Nama Hunter</th>
                    <th style="width: 10%;">Harga</th>
                </tr>
            </thead>
            <tbody>
                @foreach($dataStok as $stok)
                <tr>
                    <td>{{ $stok->kode_produk }}</td>
                    <td>{{ $stok->nama_produk }}</td>
                    <td>T{{ $stok->idPenitip }}</td>
                    <td>{{ $stok->nama_penitip }}</td>
                    <td>{{ \Carbon\Carbon::parse($stok->tanggal_masuk)->format('d/m/Y') }}</td>
                    <td>
                        @if($stok->statusPerpanjangan)
                            <span class="status-ya">Ya</span>
                        @else
                            <span class="status-tidak">Tidak</span>
                        @endif
                    </td>
                    <td>{{ $stok->id_hunter ? 'P' . $stok->id_hunter : '-' }}</td>
                    <td>{{ $stok->nama_hunter ?? '-' }}</td>
                    <td class="number">{{ number_format($stok->harga, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div style="text-align: center; padding: 50px; border: 1px solid #ddd; background-color: #f9f9f9;">
            <strong>Tidak ada barang dalam stok gudang</strong>
        </div>
        @endif

        <!-- Notes Section -->
        <div class="note">
            <div class="note-title">Keterangan:</div>
            <ul style="margin: 5px 0; padding-left: 15px;">
                <li><strong>Kode Produk:</strong> 1 huruf inisial barang + nomor urut dari inisial tersebut.</li>
                <li><strong>Perpanjangan "Ya":</strong> Barang sudah ada perpanjangan penitipan (komisi 30%)</li>
                <li><strong>Perpanjangan "Tidak":</strong> Penitipan pertama (komisi 20%)</li>
                <li><strong>Hunter:</strong> Pegawai yang melakukan hunting barang (mendapat komisi 5% jika barang laku)</li>
                <li><strong>Stok Real-time:</strong> Data stok sesuai dengan waktu cetak laporan ini</li>
            </ul>
        </div>

        <!-- Analysis Section -->
        @if($dataStok->count() > 0)
        <div style="margin-top: 20px;">
            <h3 style="font-size: 12px;">Analisis Stok Gudang:</h3>
            @php
                $persentasePerpanjangan = $totalBarang > 0 ? round(($totalPerpanjangan / $totalBarang) * 100, 1) : 0;
                $persentaseHunter = $totalBarang > 0 ? round(($totalHunter / $totalBarang) * 100, 1) : 0;
                $rataHarga = $totalBarang > 0 ? $totalNilai / $totalBarang : 0;
            @endphp
            
            <ul style="margin-left: 20px; font-size: 10px;">
                <li>Persentase barang dengan perpanjangan: <strong>{{ $persentasePerpanjangan }}%</strong></li>
                <li>Persentase barang hasil hunting: <strong>{{ $persentaseHunter }}%</strong></li>
                <li>Rata-rata harga per barang: <strong>Rp {{ number_format($rataHarga, 0, ',', '.') }}</strong></li>
                <li>Total nilai investasi stok: <strong>Rp {{ number_format($totalNilai, 0, ',', '.') }}</strong></li>
                @if($totalPenitip > 0)
                <li>Rata-rata barang per penitip: <strong>{{ round($totalBarang / $totalPenitip, 1) }}</strong> item</li>
                @endif
            </ul>
        </div>

        <!-- Recommendation Section -->
        <div style="margin-top: 20px; background-color: #e8f5e8; padding: 10px; border: 1px solid #4caf50;">
            <h4 style="font-size: 11px; margin: 0 0 5px 0; color: #2e7d32;">Rekomendasi:</h4>
            <ul style="margin: 0; padding-left: 15px; font-size: 9px;">
                @if($persentasePerpanjangan > 50)
                <li>Tingginya persentase perpanjangan ({{ $persentasePerpanjangan }}%) menunjukkan banyak barang yang sulit terjual. Pertimbangkan strategi pemasaran yang lebih agresif.</li>
                @endif
                @if($persentaseHunter < 20)
                <li>Persentase barang hunting masih rendah ({{ $persentaseHunter }}%). Tingkatkan aktivitas hunting untuk mendapatkan barang berkualitas.</li>
                @endif
                @if($totalBarang > 100)
                <li>Stok gudang cukup banyak ({{ $totalBarang }} item). Pastikan rotasi barang berjalan dengan baik.</li>
                @endif
            </ul>
        </div>
        @endif
    </div>

    <div style="margin-top: 30px; text-align: right; font-size: 10px;">
        <p>Dicetak pada: {{ \Carbon\Carbon::now()->format('d F Y H:i:s') }}</p>
    </div>
</body>
</html>