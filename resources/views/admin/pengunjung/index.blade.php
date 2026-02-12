@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold text-secondary">Data Pengunjung</h2>
            <p class="text-muted">Manajemen seluruh data pengunjung yang pernah terdaftar di sistem.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="row align-items-center">
                <div class="col">
                    <h5 class="mb-0 fw-bold text-primary"><i class="fas fa-users me-2"></i>Daftar Pengunjung</h5>
                </div>
                <div class="col-md-4">
                    <form action="{{ route('admin.pengunjung') }}" method="GET">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Cari Nama / No. Identitas..." value="{{ request('search') }}">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>No. Identitas</th>
                            <th>Nama Lengkap</th>
                            <th>Asal Instansi</th>
                            <th>Terakhir Berkunjung</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pengunjung as $p)
                        <tr>
                            <td>{{ ($pengunjung->currentPage() - 1) * $pengunjung->perPage() + $loop->iteration }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $p->identitas_no }}</span></td>
                            <td class="fw-bold">{{ $p->nama_lengkap }}</td>
                            <td>{{ $p->asal_instansi }}</td>
                            <td>{{ \Carbon\Carbon::parse($p->updated_at)->diffForHumans() }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" title="Detail History">
                                    <i class="fas fa-history"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Data pengunjung tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $pengunjung->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
</div>
@endsection