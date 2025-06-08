<!DOCTYPE html>
<html>
<head>
    <title>Laporan Request Donasi</title>
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
            border-bottom: 3px solid #2196F3;
            padding-bottom: 15px;
        }
        .header h1 { 
            margin: 0; 
            font-size: 28px; 
            color: #1565C0;
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
            color: #1565C0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        .info-section {
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #2196F3;
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
            color: #1565C0;
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
            background-color: #2196F3; 
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
            background-color: #e3f2fd;
        }
        
        .text-center { text-align: center; }
        
        .summary-section {
            margin: 30px 0;
            padding: 20px;
            background-color: #e3f2fd;
            border-radius: 8px;
            border: 1px solid #2196F3;
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
        
        .kode-request {
            background-color: #e3f2fd;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
            color: #1976d2;
        }
        
        .status-terpenuhi {
            background-color: #c8e6c9;
            color: #2e7d32;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
        
        .status-menunggu {
            background-color: #fff3cd;
            color: #856404;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
            text-align: center;
        }
        
        .request-description {
            max-width: 300px;
            word-wrap: break-word;
            line-height: 1.3;
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
        <h2>LAPORAN REQUEST DONASI</h2>
    </div>

    <div class="info-section">
        <table class="info-table">
            <tr>
                <td>Tanggal Cetak</td>
                <td>: {{ \Carbon\Carbon::now()->format('d F Y') }}</td>
                <td>Total Request</td>
                <td>: {{ $requests->count() }} request</td>
            </tr>
            @if($search)
            <tr>
                <td>Filter Pencarian</td>
                <td colspan="3">: "{{ $search }}"</td>
            </tr>
            @endif
        </table>
    </div>

    @if($requests->count() > 0)
        @php
            $totalRequest = $requests->count();
            $requestTerpenuhi = $requests->where('status', 'Terpenuhi')->count();
            $requestMenunggu = $requests->where('status', 'Belum Terpenuhi')->count();
            $totalOrganisasi = $requests->pluck('organisasi.nama')->unique()->count();
            $persentaseTerpenuhi = $totalRequest > 0 ? round(($requestTerpenuhi / $totalRequest) * 100, 1) : 0;
        @endphp

        <div class="summary-section">
            <h3 style="margin-top: 0; color: #1565C0; text-align: center;">Ringkasan Request Donasi</h3>
            <div style="display: table; width: 100%; margin-top: 15px;">
                <div style="display: table-cell; text-align: center; width: 20%;">
                    <div style="font-size: 24px; font-weight: bold; color: #1565C0;">{{ $totalRequest }}</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Total Request</div>
                </div>
                <div style="display: table-cell; text-align: center; width: 20%;">
                    <div style="font-size: 24px; font-weight: bold; color: #2e7d32;">{{ $requestTerpenuhi }}</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Terpenuhi</div>
                </div>
                <div style="display: table-cell; text-align: center; width: 20%;">
                    <div style="font-size: 24px; font-weight: bold; color: #f57c00;">{{ $requestMenunggu }}</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Menunggu</div>
                </div>
                <div style="display: table-cell; text-align: center; width: 20%;">
                    <div style="font-size: 24px; font-weight: bold; color: #7b1fa2;">{{ $totalOrganisasi }}</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Organisasi</div>
                </div>
                <div style="display: table-cell; text-align: center; width: 20%;">
                    <div style="font-size: 24px; font-weight: bold; color: #1565C0;">{{ $persentaseTerpenuhi }}%</div>
                    <div style="font-size: 12px; color: #666; margin-top: 5px;">Tingkat Penyelesaian</div>
                </div>
            </div>
        </div>

        <table class="main-table">
            <thead>
                <tr>
                    <th style="width: 5%;">No</th>
                    <th style="width: 12%;">Kode Request</th>
                    <th style="width: 20%;">Organisasi</th>
                    <th style="width: 35%;">Deskripsi Request</th>
                    <th style="width: 15%;">Penerima</th>
                    <th style="width: 8%;">Tanggal</th>
                    <th style="width: 5%;">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requests as $index => $request)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="text-center">
                            <span class="kode-request">REQ-{{ str_pad($request->idRequest, 4, '0', STR_PAD_LEFT) }}</span>
                        </td>
                        <td>
                            <strong>{{ $request->organisasi->nama }}</strong><br>
                            <small style="color: #666;">{{ Str::limit($request->organisasi->alamat, 30) }}</small>
                        </td>
                        <td>
                            <div class="request-description">
                                {{ $request->request }}
                            </div>
                        </td>
                        <td class="text-center">
                            {{ $request->penerima }}
                        </td>
                        <td class="text-center">{{ \Carbon\Carbon::parse($request->tanggalRequest)->format('d/m/Y') }}</td>
                        <td class="text-center">
                            @if($request->status == 'Terpenuhi')
                                <span class="status-terpenuhi">TERPENUHI</span>
                            @elseif($request->status == 'Belum Terpenuhi')
                                <span class="status-menunggu">MENUNGGU</span>
                            @else
                                <span style="font-size: 10px;">{{ strtoupper($request->status) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <div style="display: table; width: 100%;">
                <div style="display: table-cell; width: 50%; padding-right: 10px;">
                    <div style="padding: 15px; background-color: #e8f5e8; border: 1px solid #4caf50; border-radius: 5px;">
                        <h4 style="margin: 0 0 10px 0; color: #2e7d32;">Status Terpenuhi</h4>
                        <p style="margin: 0; font-size: 12px; color: #2e7d32;">
                            Request yang sudah berhasil dipenuhi dengan donasi barang dari ReUseMart
                        </p>
                    </div>
                </div>
                <div style="display: table-cell; width: 50%; padding-left: 10px;">
                    <div style="padding: 15px; background-color: #fff3cd; border: 1px solid #ffc107; border-radius: 5px;">
                        <h4 style="margin: 0 0 10px 0; color: #856404;">Status Menunggu</h4>
                        <p style="margin: 0; font-size: 12px; color: #856404;">
                            Request yang masih menunggu untuk dipenuhi dengan barang donasi yang sesuai
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="empty-state">
            <p><strong>Tidak ada data request donasi untuk dilaporkan</strong></p>
            @if($search)
                <p>Tidak ditemukan request donasi yang sesuai dengan pencarian "{{ $search }}"</p>
            @else
                <p>Belum ada request donasi yang diajukan oleh organisasi</p>
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