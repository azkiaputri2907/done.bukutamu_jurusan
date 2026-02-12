@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="fw-bold text-secondary">Data Survey Kepuasan</h2>
            <p class="text-muted">Hasil penilaian pengunjung terhadap layanan Jurusan Elektro.</p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-primary"></i>Daftar Feedback</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Nama Pengunjung</th>
                                    <th>P1</th><th>P2</th><th>P3</th><th>P4</th><th>P5</th>
                                    <th>Saran</th>
                                </tr>
                            </thead>
                            <tbody>
@foreach($surveys as $s)
<tr>
    <td>{{ \Carbon\Carbon::parse($s->created_at)->format('d/m/Y') }}</td>
    <td>{{ $s->kunjungan->pengunjung->nama_lengkap ?? 'Anonim' }}</td>
    
    <td><span class="badge bg-info text-dark">{{ $s->detail->p1 ?? '-' }}</span></td>
    <td><span class="badge bg-info text-dark">{{ $s->detail->p2 ?? '-' }}</span></td>
    <td><span class="badge bg-info text-dark">{{ $s->detail->p3 ?? '-' }}</span></td>
    <td><span class="badge bg-info text-dark">{{ $s->detail->p4 ?? '-' }}</span></td>
    <td><span class="badge bg-info text-dark">{{ $s->detail->p5 ?? '-' }}</span></td>
    
    <td>
        <small class="text-muted">
            {{ $s->saran ?? ($s->kritik_saran ?? '-') }}
        </small>
    </td>
</tr>
@endforeach                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $surveys->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white">
                    <h6 class="mb-0 fw-bold">Rata-rata Nilai Per Aspek</h6>
                </div>
                <div class="card-body">
                    @php
                        $aspekLabels = ['Kecepatan', 'Etika', 'Kompetensi', 'Fasilitas', 'Kualitas'];
                    @endphp
                    @foreach($avgScores as $index => $score)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="small fw-bold">{{ $aspekLabels[$index] }}</span>
                                <span class="small fw-bold text-primary">{{ number_format($score, 1) }}/5</span>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div class="progress-bar bg-primary" role="progressbar" 
                                     style="width: {{ ($score/5)*100 }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="alert alert-info border-0 shadow-sm">
                <i class="fas fa-info-circle me-2"></i>
                Skor maksimal adalah <strong>5.0</strong>. Semakin tinggi skor, semakin baik layanan yang diberikan.
            </div>
        </div>
    </div>
</div>
@endsection