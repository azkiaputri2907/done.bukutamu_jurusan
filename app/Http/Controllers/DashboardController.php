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
use App\Models\MasterProdiInstansi;

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
    // Eager load relasi pengunjung
    $query = Kunjungan::with('pengunjung');
    
    // 1. Logic Pencarian (Search)
    if ($request->search) {
        $query->whereHas('pengunjung', function($q) use ($request) {
            $q->where('nama_lengkap', 'like', '%'.$request->search.'%');
        })->orWhere('nomor_kunjungan', 'like', '%'.$request->search.'%');
    }

    // 2. Logic Filter Prodi (TAMBAHAN PENTING)
    if ($request->has('prodi') && $request->prodi != '') {
        // Jika filter "Lainnya" dipilih (opsional, tergantung kebutuhan)
        if ($request->prodi == 'Lainnya') {
             $query->whereHas('pengunjung', function($q) {
                $q->whereNotIn('asal_instansi', [
                    'D3 Teknik Listrik', 
                    'D3 Teknik Elektronika', 
                    'D3 Teknik Informatika', 
                    'D4 Teknologi Rekayasa Pembangkit Energi', 
                    'D4 Sistem Informasi Kota Cerdas'
                ]);
            });
        } else {
            // Filter sesuai nama prodi yang dipilih
            $query->whereHas('pengunjung', function($q) use ($request) {
                $q->where('asal_instansi', $request->prodi);
            });
        }
    }

    $kunjungan = $query->latest()->paginate(10);
    
    // Penting: Append query string agar saat pindah halaman (pagination), filter tidak hilang
    $kunjungan->appends($request->all());

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
public function survey(Request $request)
{
    // 1. Ambil daftar prodi untuk dropdown filter
    $prodis = MasterProdiInstansi::pluck('nama'); // Sesuaikan 'nama_prodi' dengan kolom di tabel Anda

    // 2. Mulai Query dengan Eager Loading
    $query = Survey::with(['kunjungan.pengunjung', 'detail']);

    // 3. Logika Pencarian Nama Pengunjung
    if ($request->filled('search')) {
        $query->whereHas('kunjungan.pengunjung', function($q) use ($request) {
            $q->where('nama_lengkap', 'like', '%' . $request->search . '%');
        });
    }

    // 4. LOGIKA FILTER PRODI (ASAL INSTANSI)
    if ($request->filled('prodi')) {
        $query->whereHas('kunjungan.pengunjung', function($q) use ($request) {
            $q->where('asal_instansi', $request->prodi);
        });
    }

    // 5. Logika Filter Rating
    if ($request->filled('rating')) {
        if ($request->rating == 'low') {
            $query->whereHas('detail', function($q) {
                $q->whereRaw('(p1+p2+p3+p4+p5)/5 < 3');
            });
        } elseif ($request->rating == 'high') {
            $query->whereHas('detail', function($q) {
                $q->whereRaw('(p1+p2+p3+p4+p5)/5 >= 4');
            });
        }
    }

    // 6. Eksekusi Query
    $surveys = $query->latest()->paginate(10)->appends($request->all());

    // 7. Hitung Rata-rata Skor (Sidebar)
    $avgScores = [
        DetailSurvey::avg('p1') ?? 0,
        DetailSurvey::avg('p2') ?? 0,
        DetailSurvey::avg('p3') ?? 0,
        DetailSurvey::avg('p4') ?? 0,
        DetailSurvey::avg('p5') ?? 0,
    ];

    return view('admin.survey.index', compact('surveys', 'avgScores', 'prodis'));
}

// --- DATA PENGUNJUNG ---
// public function pengunjung(Request $request)
// {
//     $query = Pengunjung::query();
//     if ($request->search) {
//         $query->where('nama_lengkap', 'like', '%'.$request->search.'%')
//               ->orWhere('identitas_no', 'like', '%'.$request->search.'%');
//     }
//     $pengunjung = $query->latest()->paginate(10);
//     return view('admin.pengunjung.index', compact('pengunjung'));
// }

// --- MASTER KEPERLUAN ---
public function masterKeperluan()
{
    $keperluan = MasterKeperluan::all();
    return view('admin.master.keperluan', compact('keperluan'));
}

// public function storeKeperluan(Request $request)
// {
//     $request->validate(['keterangan' => 'required']);
//     MasterKeperluan::create($request->all());
//     return back()->with('success', 'Keperluan berhasil ditambah.');
// }

// --- LAPORAN ---
public function laporan()
{
    $prodi = \App\Models\MasterProdiInstansi::all(); 
    
    return view('admin.laporan.index', compact('prodi'));
}

public function exportLaporan(Request $request)
{
    $request->validate([
        'tgl_mulai'   => 'required|date',
        'tgl_selesai' => 'required|date',
        'jenis'       => 'required',
        'prodi_id'    => 'required'
    ]);

    $start = $request->tgl_mulai;
    $end   = $request->tgl_selesai;
    $prodi = $request->prodi_id;

    if ($request->jenis == 'kunjungan') {
        $data = Kunjungan::with('pengunjung')
            ->whereBetween('tanggal', [$start, $end])
            ->when($prodi !== 'all', function ($query) use ($prodi) {
                // Filter berdasarkan nama prodi/instansi di tabel pengunjung
                return $query->whereHas('pengunjung', function ($q) use ($prodi) {
                    $q->where('asal_instansi', $prodi);
                });
            })
            ->get();

    } elseif ($request->jenis == 'pengunjung') {
        $data = Pengunjung::whereBetween('created_at', [$start, $end])
            ->when($prodi !== 'all', function ($query) use ($prodi) {
                return $query->where('asal_instansi', $prodi);
            })
            ->get();

    } else {
        // Jenis: Survey
        $data = Survey::with(['detail', 'kunjungan.pengunjung'])
            ->whereBetween('created_at', [$start, $end])
            ->when($prodi !== 'all', function ($query) use ($prodi) {
                return $query->whereHas('kunjungan.pengunjung', function ($q) use ($prodi) {
                    $q->where('asal_instansi', $prodi);
                });
            })
            ->get();
    }

    return $data; // Lanjutkan ke proses download Excel/PDF
}

public function updateSurvey(Request $request, $id)
    {
        $request->validate([
            'saran' => 'nullable|string',
            'p1' => 'required|integer|min:1|max:5',
            'p2' => 'required|integer|min:1|max:5',
            'p3' => 'required|integer|min:1|max:5',
            'p4' => 'required|integer|min:1|max:5',
            'p5' => 'required|integer|min:1|max:5',
        ]);

        // Cari data survey
        $survey = Survey::findOrFail($id);

        // Update tabel detail_survey (Skor)
        // Kita gunakan updateOrCreate atau update biasa tergantung relasi
        if($survey->detail) {
            $survey->detail()->update([
                'p1' => $request->p1,
                'p2' => $request->p2,
                'p3' => $request->p3,
                'p4' => $request->p4,
                'p5' => $request->p5,
            ]);
        }

        // Update tabel survey (Saran)
        // Kolom di database Anda mungkin 'saran' atau 'kritik_saran', sesuaikan di sini:
        $survey->update([
            'kritik_saran' => $request->saran // Sesuaikan dengan nama kolom di DB (saran/kritik_saran)
        ]);

        return redirect()->route('admin.survey')->with('success', 'Data survey berhasil diperbarui!');
    }

    // 2. Fungsi Destroy (Hapus Data)
    public function destroySurvey($id)
    {
        $survey = Survey::findOrFail($id);
        
        // Hapus data (Detail akan terhapus otomatis jika sudah diset CASCADE di migration)
        // Jika tidak cascade, hapus manual: $survey->detail()->delete();
        $survey->delete();

        return redirect()->route('admin.survey')->with('success', 'Data survey berhasil dihapus!');
    }

    public function pengunjung(Request $request)
{
    $query = Pengunjung::query();

    // Filter Pencarian (Nama atau No Identitas)
    if ($request->filled('search')) {
        $query->where(function($q) use ($request) {
            $q->where('nama_lengkap', 'like', '%' . $request->search . '%')
              ->orWhere('identitas_no', 'like', '%' . $request->search . '%');
        });
    }

    // Filter Prodi / Instansi
    if ($request->filled('prodi')) {
        if ($request->prodi == 'Lainnya') {
            // Logika jika prodi lainnya (tidak termasuk daftar utama)
            $prodis = ['D3 Teknik Listrik', 'D3 Teknik Elektronika', 'D3 Teknik Informatika', 'D4 Teknologi Rekayasa Pembangkit Energi', 'D4 Sistem Informasi Kota Cerdas'];
            $query->whereNotIn('asal_instansi', $prodis);
        } else {
            $query->where('asal_instansi', $request->prodi);
        }
    }

    $pengunjung = $query->latest('updated_at')->paginate(10)->appends($request->all());

    return view('admin.pengunjung.index', compact('pengunjung'));
}

public function updatePengunjung(Request $request, $id)
{
    $request->validate([
        'nama_lengkap' => 'required',
        'asal_instansi' => 'required',
    ]);

    $p = Pengunjung::findOrFail($id);
    $p->update($request->only(['nama_lengkap', 'asal_instansi']));

    return redirect()->back()->with('success', 'Data pengunjung berhasil diperbarui');
}

public function destroyPengunjung($id)
{
    $p = Pengunjung::findOrFail($id);
    $p->delete();

    return redirect()->back()->with('success', 'Data pengunjung berhasil dihapus');
}

public function storeKeperluan(Request $request)
{
    $request->validate(['keterangan' => 'required|string|max:255']);
    
    MasterKeperluan::create(['keterangan' => $request->keterangan]);

    return redirect()->back()->with('success', 'Keperluan berhasil ditambahkan!');
}

public function updateKeperluan(Request $request, $id)
{
    $request->validate(['keterangan' => 'required|string|max:255']);
    
    $keperluan = MasterKeperluan::findOrFail($id);
    $keperluan->update(['keterangan' => $request->keterangan]);

    return redirect()->back()->with('success', 'Keperluan berhasil diperbarui!');
}

public function destroyKeperluan($id)
{
    $keperluan = MasterKeperluan::findOrFail($id);
    $keperluan->delete();

    return redirect()->back()->with('success', 'Keperluan berhasil dihapus!');
}

}

