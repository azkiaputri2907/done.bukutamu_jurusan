@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <h2 class="fw-bold text-secondary">Laporan Sistem</h2>
    <div class="card shadow-sm border-0 col-md-6">
        <div class="card-body">
            <form action="{{ route('admin.laporan.export') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label>Jenis Laporan</label>
                    <select name="jenis" class="form-control" required>
                        <option value="kunjungan">Data Kunjungan Tamu</option>
                        <option value="survey">Data Survey Kepuasan</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Dari Tanggal</label>
                        <input type="date" name="tgl_mulai" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Sampai Tanggal</label>
                        <input type="date" name="tgl_selesai" class="form-control" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-download me-2"></i> Generate Laporan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection