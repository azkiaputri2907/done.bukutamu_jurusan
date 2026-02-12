@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="card shadow-lg border-0">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0 text-center">Survei Kepuasan Pelayanan</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('guest.survey.store', $kunjungan->id) }}" method="POST">
                @csrf
                @foreach($pertanyaan as $aspek => $daftarPertanyaan)
                    <h6 class="fw-bold text-primary">{{ $aspek }}</h6>
                    <table class="table align-middle">
                        <thead>
                            <tr class="table-light">
                                <th>Pertanyaan</th>
                                <th class="text-center">Nilai (1-5)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($daftarPertanyaan as $q)
                            <tr>
                                <td>{{ $q->pertanyaan }}</td>
                                <td>
                                    <div class="d-flex justify-content-between">
                                        @for($i = 1; $i <= 5; $i++)
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="jawaban[{{ $q->id }}]" value="{{ $i }}" required>
                                            <label class="form-check-label">{{ $i }}</label>
                                        </div>
                                        @endfor
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                @endforeach

                <div class="mt-4">
                    <label class="fw-bold">Kritik & Saran (Opsional)</label>
                    <textarea name="kritik_saran" class="form-control" rows="3"></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-3">Kirim Penilaian</button>
            </form>
        </div>
    </div>
</div>
@endsection