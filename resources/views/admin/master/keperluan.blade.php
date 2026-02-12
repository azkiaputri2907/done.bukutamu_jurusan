@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Master Keperluan</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">Tambah Data</button>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Keterangan Keperluan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($keperluan as $k)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $k->keterangan }}</td>
                        <td><button class="btn btn-sm btn-danger">Hapus</button></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection