<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kunjungan;
use App\Models\Pengunjung;
use App\Models\Survey;
use App\Models\DetailSurvey;
use App\Models\User;
use App\Models\MasterRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\MasterKeperluan;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

public function index() 
{
    $stats = [
        'total_tamu' => Pengunjung::count(),
        'tamu_hari_ini' => Kunjungan::whereDate('tanggal', today())->count(),
        // Menghitung rata-rata p1 sampai p5 sekaligus
        'rata_rata_puas' => DetailSurvey::selectRaw('AVG((p1+p2+p3+p4+p5)/5) as total')->first()->total ?: 0,
    ];

    // AMBIL DATA UNTUK GRAFIK RADAR (P1 - P5)
    $avg_aspek = DetailSurvey::selectRaw('AVG(p1) as p1, AVG(p2) as p2, AVG(p3) as p3, AVG(p4) as p4, AVG(p5) as p5')->first();

    $kunjungan_terbaru = Kunjungan::with('pengunjung')
                        ->orderBy('created_at', 'desc')
                        ->limit(10)
                        ->get();

    $grafik = Kunjungan::select(DB::raw('tanggal, count(*) as jumlah'))
            ->where('tanggal', '>', today()->subDays(7))
            ->groupBy('tanggal')
            ->get();

    // PASTIKAN $avg_aspek MASUK KE DALAM COMPACT
    return view('admin.dashboard', compact('stats', 'kunjungan_terbaru', 'grafik', 'avg_aspek'));
}

    // --- DATA KUNJUNGAN ---
    public function kunjungan(Request $request)
    {
        $query = Kunjungan::with('pengunjung');
        
        if ($request->search) {
            $query->whereHas('pengunjung', function($q) use ($request) {
                $q->where('nama_lengkap', 'like', '%'.$request->search.'%');
            })->orWhere('nomor_kunjungan', 'like', '%'.$request->search.'%');
        }

        $kunjungan = $query->latest()->paginate(10);
        return view('admin.kunjungan.index', compact('kunjungan'));
    }

    public function storeKunjungan(Request $request)
    {
        // Logika simpan manual oleh admin
        $request->validate(['identitas_no' => 'required', 'nama_lengkap' => 'required', 'keperluan' => 'required']);
        
        $pengunjung = Pengunjung::updateOrCreate(
            ['identitas_no' => $request->identitas_no],
            ['nama_lengkap' => $request->nama_lengkap, 'asal_instansi' => $request->asal_instansi]
        );

        $kunjungan = Kunjungan::create([
            'pengunjung_id' => $pengunjung->id,
            'keperluan' => $request->keperluan,
            'tanggal' => date('Y-m-d'),
            'nomor_kunjungan' => 'C0-'.date('Ymd').'-'.rand(100,999)
        ]);

        return back()->with('success', 'Kunjungan berhasil ditambahkan.');
    }

    public function updateKunjungan(Request $request, $id)
    {
        $kunjungan = Kunjungan::findOrFail($id);
        $kunjungan->update($request->only(['keperluan', 'detail_keperluan']));
        return back()->with('success', 'Data berhasil diperbarui.');
    }

    public function destroyKunjungan($id)
    {
        Kunjungan::destroy($id);
        return back()->with('success', 'Data kunjungan berhasil dihapus.');
    }

    // --- DATA USER ---
    public function users()
    {
        $users = User::with('role')->get();
        $roles = DB::table('master_role')->get();
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function storeUser(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|unique:master_user', 'password' => 'required', 'role_id' => 'required']);
        
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id
        ]);
        return back()->with('success', 'User berhasil ditambahkan.');
    }

        public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $data = $request->only(['name', 'email', 'role_id']);
        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }
        $user->update($data);
        return back()->with('success', 'Data user berhasil diperbarui.');
    }

    public function destroyUser($id)
    {
        User::destroy($id);
        return back()->with('success', 'User berhasil dihapus.');
    }

    // --- DATA SURVEY ---
    public function survey()
{
    // 1. Ambil data survey (Header + Relasi)
    $surveys = Survey::with(['kunjungan.pengunjung', 'detail'])->latest()->paginate(10);

    // 2. HITUNG RATA-RATA DARI TABEL DETAIL_SURVEY (Bukan dari tabel Survey)
    $avgScores = [
        DetailSurvey::avg('p1') ?? 0,
        DetailSurvey::avg('p2') ?? 0,
        DetailSurvey::avg('p3') ?? 0,
        DetailSurvey::avg('p4') ?? 0,
        DetailSurvey::avg('p5') ?? 0,
    ];

    // 3. Ambil nama aspek untuk label (opsional agar dinamis)
    $aspekLabels = DB::table('master_aspek_survey')->pluck('nama_aspek')->toArray();

    return view('admin.survey.index', compact('surveys', 'avgScores', 'aspekLabels'));
}

// --- DATA PENGUNJUNG ---
public function pengunjung(Request $request)
{
    $query = Pengunjung::query();
    if ($request->search) {
        $query->where('nama_lengkap', 'like', '%'.$request->search.'%')
              ->orWhere('identitas_no', 'like', '%'.$request->search.'%');
    }
    $pengunjung = $query->latest()->paginate(10);
    return view('admin.pengunjung.index', compact('pengunjung'));
}

// --- MASTER KEPERLUAN ---
public function masterKeperluan()
{
    $keperluan = MasterKeperluan::all();
    return view('admin.master.keperluan', compact('keperluan'));
}

public function storeKeperluan(Request $request)
{
    $request->validate(['keterangan' => 'required']);
    MasterKeperluan::create($request->all());
    return back()->with('success', 'Keperluan berhasil ditambah.');
}

// --- LAPORAN ---
public function laporan()
{
    return view('admin.laporan.index');
}

public function exportLaporan(Request $request)
{
    $request->validate([
        'tgl_mulai' => 'required',
        'tgl_selesai' => 'required',
        'jenis' => 'required'
    ]);

    // Logika filter data untuk export
    if($request->jenis == 'kunjungan') {
        $data = Kunjungan::with('pengunjung')
                ->whereBetween('tanggal', [$request->tgl_mulai, $request->tgl_selesai])->get();
    } else {
        $data = Survey::with(['kunjungan.pengunjung', 'detail'])
                ->whereBetween('created_at', [$request->tgl_mulai, $request->tgl_selesai])->get();
    }

    // Untuk sementara kita tampilkan data mentah, nanti bisa pakai Excel/PDF
    return $data; 
}

}

