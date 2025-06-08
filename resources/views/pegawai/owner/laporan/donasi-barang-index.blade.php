@extends('layouts.owner')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Laporan Donasi Barang</h1>
        <p class="mb-4">Daftar semua barang yang telah didonasikan kepada organisasi.</p>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Donasi Barang</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <form action="{{ route('owner.laporan.donasi-barang') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Cari berdasarkan nama produk atau organisasi..." name="search" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="{{ route('owner.laporan.donasi-barang.pdf', ['search' => request('search')]) }}" class="btn btn-danger btn-icon-split">
                        <span class="icon text-white-50">
                            <i class="fas fa-file-pdf"></i>
                        </span>
                        <span class="text">Unduh PDF</span>
                    </a>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID Donasi</th>
                                <th>Nama Organisasi</th>
                                <th>Nama Produk</th>
                                <th>Jumlah</th>
                                <th>Tanggal Donasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($donations as $donasi)
                                <tr>
                                    <td>{{ $donasi->idDonasi }}</td>
                                    <td>{{ $donasi->requestDonasi->organisasi->nama_org }}</td>
                                    <td>{{ $donasi->produk->nama }}</td>
                                    <td>{{ $donasi->jumlah }}</td>
                                    <td>{{ \Carbon\Carbon::parse($donasi->tgl_donasi)->format('d/m/Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada data donasi barang.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="d-flex justify-content-end">
                    {{ $donations->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection