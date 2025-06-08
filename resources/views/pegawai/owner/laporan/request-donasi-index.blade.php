@extends('layouts.owner')

@section('content')
    <div class="container-fluid">
        <h1 class="h3 mb-2 text-gray-800">Laporan Request Donasi</h1>
        <p class="mb-4">Daftar semua permintaan donasi yang telah dibuat oleh organisasi.</p>

        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Data Request Donasi</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-3">
                    <form action="{{ route('owner.laporan.request-donasi') }}" method="GET" class="d-none d-sm-inline-block form-inline mr-auto ml-md-3 my-2 my-md-0 mw-100 navbar-search">
                        <div class="input-group">
                            <input type="text" class="form-control bg-light border-0 small" placeholder="Cari berdasarkan judul, nama organisasi, atau status..." name="search" value="{{ request('search') }}">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">
                                    <i class="fas fa-search fa-sm"></i>
                                </button>
                            </div>
                        </div>
                    </form>
                    <a href="{{ route('owner.laporan.request-donasi.pdf', ['search' => request('search')]) }}" class="btn btn-danger btn-icon-split">
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
                                    <td>{{ Str::limit($request->deskripsi, 50) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($request->tgl_request)->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-{{ $request->status == 'Tersedia' ? 'success' : ($request->status == 'Diajukan' ? 'warning' : 'secondary') }}">
                                            {{ $request->status }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada data request donasi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                 <div class="d-flex justify-content-end">
                    {{ $requests->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection