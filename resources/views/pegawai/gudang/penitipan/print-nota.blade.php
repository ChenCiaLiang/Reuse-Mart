<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Nota Penitipan #{{ $transaksi->idTransaksiPenitipan }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 20px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .company-address {
            font-size: 11px;
            color: #666;
            margin-bottom: 10px;
        }
        .nota-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        .nota-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .nota-left, .nota-right {
            width: 48%;
        }
        .info-group {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 3px;
        }
        .info-value {
            color: #555;
            margin-bottom: 8px;
        }
        .products-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        .products-table th,
        .products-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .products-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .products-table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .total-row {
            background-color: #e3f2fd !important;
            font-weight: bold;
        }
        .status-badge {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-aktif {
            background-color: #d4edda;
            color: #155724;
        }
        .status-selesai {
            background-color: #cce7ff;
            color: #004085;
        }
        .status-expired {
            background-color: #f8d7da;
            color: #721c24;
        }
        .footer {
            margin-top: 30px;
            border-top: 1px solid #ddd;
            padding-top: 20px;
        }
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        .signature-box {
            width: 200px;
            text-align: center;
        }
        .signature-line {
            border-bottom: 1px solid #333;
            height: 60px;
            margin-bottom: 10px;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .mb-10 {
            margin-bottom: 10px;
        }
        .mb-20 {
            margin-bottom: 20px;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-green {
            color: #059669;
        }
        .text-blue {
            color: #2563eb;
        }
        .text-red {
            color: #dc2626;
        }
        .bg-gray {
            background-color: #f3f4f6;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="company-name">ReUseMart</div>
        <div class="company-address">
            Jl. Green Eco Park No. 456 Yogyakarta<br>
            Telp: (0274) 123456 | Email: info@reusemart.com
        </div>
        <div class="nota-title">Nota Penitipan Barang</div>
    </div>

    <!-- Nota Information -->
    <div class="nota-info">
        <div class="nota-left">
            <div class="info-group">
                <div class="info-label">No Nota:</div>
                <div class="info-value font-bold">{{ $nomor_nota }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">Tanggal Penitipan:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($transaksi->tanggalMasukPenitipan)->format('d F Y, H:i') }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">Masa Penitipan Sampai:</div>
                <div class="info-value">{{ \Carbon\Carbon::parse($transaksi->tanggalAkhirPenitipan)->format('d F Y, H:i') }}</div>
            </div>
            <div class="info-group">
                <div class="info-label">Batas Pengambilan:</div>
                <div class="info-value text-red">{{ \Carbon\Carbon::parse($transaksi->batasAmbil)->format('d F Y, H:i') }}</div>
            </div>
        </div>
        <div class="nota-right">
            <div class="info-group">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge status-{{ strtolower($transaksi->statusPenitipan) }}">
                        {{ $transaksi->statusPenitipan }}
                    </span>
                    @if($transaksi->statusPerpanjangan)
                        <span class="status-badge" style="background-color: #fff3cd; color: #856404;">
                            Diperpanjang
                        </span>
                    @endif
                </div>
            </div>
            <div class="info-group">
                <div class="info-label">Penitip:</div>
                <div class="info-value font-bold">{{ $transaksi->namaPenitip }}</div>
                <div class="info-value">{{ $transaksi->alamatPenitip }}</div>
                @if(isset($transaksi->emailPenitip))
                    <div class="info-value">{{ $transaksi->emailPenitip }}</div>
                @endif
            </div>
        </div>
    </div>

    <!-- Important Notes -->
    <div class="bg-gray">
        <div class="font-bold mb-10">PENTING - KETENTUAN PENITIPAN:</div>
        <ul style="margin: 0; padding-left: 20px;">
            <li>Masa penitipan adalah <strong>30 hari</strong> dari tanggal masuk gudang</li>
            <li>Setelah masa penitipan berakhir, ada <strong>grace period 7 hari</strong> untuk pengambilan</li>
            <li>Barang yang tidak diambil setelah batas waktu akan <strong>didonasikan</strong> ke organisasi sosial</li>
            <li>Pendapatan akan diberikan setelah barang berhasil terjual</li>
            <li>Komisi ReUseMart: <strong>20%</strong> (30% jika diperpanjang)</li>
        </ul>
    </div>

    <!-- Products Table -->
    <table class="products-table">
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="35%">Nama Produk</th>
                <th width="15%">Kategori</th>
                <th width="15%">Harga (Rp)</th>
                <th width="15%">Harga Jual (Rp)</th>
                <th width="10%">Berat (kg)</th>
                <th width="5%">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($detail as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->namaProduk }}</td>
                    <td>{{ $item->kategori ?? '-' }}</td>
                    <td class="text-right">{{ number_format($item->harga, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->hargaJual, 0, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($item->berat, 1) }}</td>
                    <td class="text-center">
                        <span class="status-badge status-{{ strtolower($item->status) }}">
                            {{ $item->status }}
                        </span>
                    </td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-center font-bold">TOTAL</td>
                <td class="text-right font-bold">{{ number_format($detail->sum('harga'), 0, ',', '.') }}</td>
                <td class="text-right font-bold">{{ number_format($detail->sum('hargaJual'), 0, ',', '.') }}</td>
                <td class="text-center font-bold">{{ number_format($detail->sum('berat'), 1) }}</td>
                <td class="text-center font-bold">{{ $detail->count() }} item</td>
            </tr>
        </tfoot>
    </table>

    <!-- Additional Info -->
    <div style="margin: 20px 0;">
        <div class="info-group">
            <div class="info-label">Diterima dan QC oleh:</div>
            <div class="info-value font-bold">{{ $transaksi->namaPegawai }}</div>
        </div>
        <div class="info-group">
            <div class="info-label">Tanggal Cetak:</div>
            <div class="info-value">{{ $tanggal_cetak->format('d F Y, H:i') }}</div>
        </div>
    </div>

    <!-- Signature Section -->
    <div class="signature-section">
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="font-bold">Penitip</div>
            <div>{{ $transaksi->namaPenitip }}</div>
        </div>
        <div class="signature-box">
            <div class="signature-line"></div>
            <div class="font-bold">Pegawai ReUseMart</div>
            <div>{{ $transaksi->namaPegawai }}</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="text-center" style="font-size: 10px; color: #666;">
            <p>Terima kasih atas kepercayaan Anda kepada ReUseMart</p>
            <p>Simpan nota ini sebagai bukti penitipan barang</p>
            <p>Untuk informasi lebih lanjut, hubungi customer service kami</p>
        </div>
    </div>
</body>
</html>