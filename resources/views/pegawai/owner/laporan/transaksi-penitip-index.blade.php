@extends('layouts.owner')

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Laporan Transaksi Penitip</h1>
            <p class="mb-0">Penitip: <strong>{{ $penitip->nama }}</strong></p>
            <p class="mb-0">Tahun: <strong>{{ $tahun }}</strong></p>
        </div>
        <div>
            <a href="{{ route('owner.laporan.transaksi-penitip') }}" class="btn btn-secondary btn-icon-split mr-2">
                <span class="text">Kembali ke Filter</span>
            </a>
            <form action="{{ route('owner.laporan.transaksi-penitip.generate') }}" method="POST" class="d-inline">
                @csrf
                <input type="hidden" name="idPenitip" value="{{ $penitip->idPenitip }}">
                <input type="hidden" name="tahun" value="{{ $tahun }}">
                <button type="submit" name="download_pdf" value="true" class="btn btn-danger btn-icon-split">
                    <span class="icon text-white-50">
                        <i class="fas fa-file-pdf"></i>
                    </span>
                    <span class="text">Unduh PDF</span>
                </button>
            </form>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Detail Transaksi</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>Nama Produk</th>
                            <th>Tgl Masuk</th>
                            <th>Tgl Terjual</th>
                            <th>Harga Jual (Rp)</th>
                            <th>Komisi (Rp)</th>
                            <th>Pendapatan (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transaksi as $item)
                            <tr>
                                <td>{{ $item->nama_produk }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggalMasukPenitipan)->format('d/m/Y') }}</td>
                                <td>{{ \Carbon\Carbon::parse($item->tanggalLaku)->format('d/m/Y') }}</td>
                                <td class="text-right">{{ number_format($item->hargaJual, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($item->komisiReuse + $item->komisiHunter, 0, ',', '.') }}</td>
                                <td class="text-right">{{ number_format($item->komisiPenitip, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada transaksi untuk penitip ini pada tahun {{ $tahun }}.</td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="font-weight-bold bg-light">
                            <td colspan="3" class="text-right">Total</td>
                            <td class="text-right">Rp {{ number_format($totals['harga_jual'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($totals['komisi'], 0, ',', '.') }}</td>
                            <td class="text-right">Rp {{ number_format($totals['pendapatan'], 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection