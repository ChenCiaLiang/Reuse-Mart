@extends('layouts.owner')

@section('content')
<div class="container-fluid">
    <h1 class="h3 mb-2 text-gray-800">Laporan Transaksi Penitip</h1>
    <p class="mb-4">Pilih penitip dan tahun untuk membuat laporan transaksi.</p>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Filter Laporan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('owner.laporan.transaksi-penitip.generate') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="idPenitip">Pilih Penitip</label>
                    <select name="idPenitip" id="idPenitip" class="form-control @error('idPenitip') is-invalid @enderror" required>
                        <option value="">-- Pilih Salah Satu --</option>
                        @foreach($penitips as $penitip)
                            <option value="{{ $penitip->idPenitip }}">{{ $penitip->nama }} ({{ $penitip->user->email }})</option>
                        @endforeach
                    </select>
                    @error('idPenitip')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="form-group">
                    <label for="tahun">Masukkan Tahun</label>
                    <input type="number" name="tahun" id="tahun" class="form-control @error('tahun') is-invalid @enderror" placeholder="Contoh: {{ date('Y') }}" value="{{ date('Y') }}" required>
                     @error('tahun')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-cogs fa-sm"></i> Buat Laporan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection