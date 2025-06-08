<!DOCTYPE html>
<html>
<head>
    <title>Laporan Donasi Barang</title>
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
            margin-bottom: 10px;
        }
        .info-table td { 
            padding: 6px 12px; 
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
        
        .text-center { text-align: center; }
        
        .summary-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #f0f8f0;
            border-radius: 8px;
            border: 1px solid #4CAF50;
        }
        .summary-stats {
            display: flex;
            justify-content: space-around;
            text-align: center;
        }
        .stat-item {
            flex: 1;
            padding: 10px;
        }
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #2E7D32;
        }
        .stat-label {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
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
        
        .kode-donasi {
            background-color: #e3f2fd;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .status-terpenuhi {
            background-color: #c8e6c9;
            color: #2e7d32;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }
        
        @media print {
            .header { page-break-inside: avoid; }
            .footer { page-break-inside: avoid; }
            .summary-section { page-break-inside: avoid; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>ReUse Mart</h1>
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
        <h2>LAPORAN DONASI BARANG</h2>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ \Carbon\Carbon::now()->format('d F Y') }}</td>
                <td>Total Donasi</td>
                <td>: {{ $donations->count() }} donasi</td>
            </tr>
            @if($search)
            <tr>
                <td>Filter Pencarian</td>
                <td colspan="3">: "{{ $search }}"</td>
            </tr>
            @endif
        </table>
    </div>

    @if($donations->count() > 0)
        @php
            $totalOrganisasi = $donations->pluck('requestDonasi.organisasi.nama')->unique()->count();
            $donasiTahunIni = $donations->filter(function($donasi) {
                return \Carbon\Carbon::parse($donasi->tanggalPemberian)->isCurrentYear();
            })->count();
        @endphp

        <div class="summary-section">
            <h3 style="margin-top: 0; color: #2E7D32; text-align: center;">Ringkasan Donasi Barang</h3>
            <div class="summary-stats" style="display: table; width: 100%;">
                <div style="display: table-cell; text-align: center; width: 33.33%;">
                    <div class="stat-number">{{ $donations->count() }}</div>
                    <div class="stat-label">Total Donasi</div>
                </div>
                <div style="display: table-cell; text-align: center; width: 33.33%;">
                    <div class="stat-number">{{ $totalOrganisasi }}</div>
                    <div class="stat-label">Organisasi Penerima</div>
                </div>
                <div style="display: table-cell; text-align: center; width: 33.33%;">
                    <div class="stat-number">{{ $donasiTahunIni }}</div>
                    <div class="stat-label">Donasi Tahun Ini</div>
                </div>
            </div>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Kode Produk</th>
                    <th style="width: 25%;">Nama Produk</th>
                    <th style="width: 8%;">Id Penitip</th>
                    <th style="width: 15%;">Nama Penitip</th>
                    <th style="width: 12%;">Tanggal Donasi</th>
                    <th style="width: 15%;">Organisasi</th>
                    <th style="width: 8%;">Nama Penerima</th>
                </tr>
            </thead>
            <tbody>
                @foreach($donations as $index => $donasi)
                    @php
                        $penitip = null;
                        $detailPenitipan = $donasi->produk->detailTransaksiPenitipan->first();
                        if ($detailPenitipan && $detailPenitipan->transaksiPenitipan) {
                            $penitip = $detailPenitipan->transaksiPenitipan->penitip;
                        }
                    @endphp
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            <span class="kode-donasi">{{ strtoupper(substr($donasi->produk->deskripsi, 0, 1)) }}{{ $donasi->produk->idProduk }}</span>
                        </td>
                        <td>
                            <strong>{{ $donasi->produk->deskripsi }}</strong><br>
                            <small style="color: #666;">{{ $donasi->produk->kategori->nama }}</small>
                        </td>
                        <td class="text-center">
                            @if($penitip)
                                T{{ str_pad($penitip->idPenitip, 2, '0', STR_PAD_LEFT) }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            {{ $penitip ? $penitip->nama : 'Tidak diketahui' }}
                        </td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($donasi->tanggalPemberian)->format('d/m/Y') }}</td>
                        <td>
                            <strong>{{ $donasi->requestDonasi->organisasi->nama }}</strong><br>
                            <small style="color: #666;">{{ Str::limit($donasi->requestDonasi->organisasi->alamat, 30) }}</small>
                        </td>
                        <td class="text-center">{{ $donasi->namaPenerima }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px; padding: 15px; background-color: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px;">
            <p style="margin: 0; font-size: 12px; color: #856404;">
                <strong>Keterangan:</strong> Laporan ini mencakup semua barang yang telah berhasil didonasikan kepada organisasi sosial. 
                Setiap donasi dilakukan berdasarkan request yang telah diajukan oleh organisasi terkait.
            </p>
        </div>
    @else
        <div class="empty-state">
            <p><strong>Tidak ada data donasi barang untuk dilaporkan</strong></p>
            @if($search)
                <p>Tidak ditemukan donasi barang yang sesuai dengan pencarian "{{ $search }}"</p>
            @else
                <p>Belum ada barang yang didonasikan kepada organisasi</p>
            @endif
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