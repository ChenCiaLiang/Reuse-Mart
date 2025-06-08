<!DOCTYPE html>
<html>
<head>
    <title>Laporan Request Donasi</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 24px; }
        .header p { margin: 0; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; }
        table, th, td { border: 1px solid black; }
        th, td { padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        .footer { text-align: right; font-size: 12px; margin-top: 20px;}
    </style>
</head>
<body>
    <div class="header">
        <h1>ReUse Mart</h1>
        <p>Jl. Green Eco Park No. 456 Yogyakarta</p>
        <hr>
        <h2>LAPORAN REQUEST DONASI</h2>
    </div>

    <p style="font-size: 12px;">Tanggal Cetak: {{ date('d F Y') }}</p>
    @if($search)
    <p style="font-size: 12px;">Filter Pencarian: {{ $search }}</p>
    @endif


    <table>
        <thead>
            <tr>
                <th>ID Request</th>
                <th>Nama Organisasi</th>
                <th>Judul Request</th>
                <th>Deskripsi</th>
                <th>Tanggal Request</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($requests as $request)
                <tr>
                    <td>{{ $request->idRequest }}</td>
                    <td>{{ $request->organisasi->nama_org }}</td>
                    <td>{{ $request->judul }}</td>
                    <td>{{ $request->deskripsi }}</td>
                    <td>{{ \Carbon\Carbon::parse($request->tgl_request)->format('d/m/Y') }}</td>
                    <td>{{ $request->status }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="text-align: center;">Tidak ada data untuk dilaporkan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

     <div class="footer">
        <p>Dicetak oleh: {{ Auth::user()->pegawai->nama }}</p>
    </div>
</body>
</html>