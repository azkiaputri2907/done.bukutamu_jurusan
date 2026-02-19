<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\User; // User tetap di Database Lokal demi keamanan
use Illuminate\Support\Facades\DB;
use App\Services\GoogleSheetService;

class DashboardController extends Controller
{
    protected $googleSheetService;

public function __construct(GoogleSheetService $googleSheetService)
{
    $this->googleSheetService = $googleSheetService;

    $this->middleware(function ($request, $next) {
        if (!session()->has('user')) {
            return redirect()->route('login');
        }
        return $next($request);
    });
}


    // Fungsi pembantu untuk mengambil semua data dari Google Sheets
private function fetchCloudData($sheetName)
{
    try {
        $url = env('GOOGLE_SCRIPT_URL') . "?action=read&sheet=" . $sheetName;
        $response = Http::withoutVerifying()->timeout(10)->get($url);

        if ($response->successful()) {
            $json = $response->json();
            $rows = $json['data'] ?? [];
            $result = [];

            foreach ($rows as $row) {
                // Ambil ID dengan mencoba berbagai kemungkinan nama kolom
                $id = $row['Id'] ?? ($row['id'] ?? ($row['No Identitas'] ?? null));
                
                // PENTING: Jika ID tetap null/kosong, jangan masukkan ke array
                // Ini mencegah route() meledak karena ID kosong
                if (!$id) continue;

                $result[] = (object) [
                    'id'         => $id,
                    'keterangan' => $row['Keterangan'] ?? ($row['keterangan'] ?? 'Tanpa Keterangan'),
                ];
            }
            return $result;
        }
    } catch (\Exception $e) {
        Log::error("Gagal Fetch: " . $e->getMessage());
    }
    return [];
}

    public function index()
    {
        $stats = ['total_tamu' => 0, 'tamu_hari_ini' => 0, 'rata_rata_puas' => 0];
        $grafik = collect([]);
        $avg_aspek = (object)['p1'=>0, 'p2'=>0, 'p3'=>0, 'p4'=>0, 'p5'=>0];

        try {
            $response = Http::get(env('GOOGLE_SCRIPT_URL'), ['action' => 'getDashboardData']);
            if ($response->successful()) {
                $d = $response->json()['data'];
                $stats = [
                    'total_tamu' => $d['totalKunjungan'],
                    'tamu_hari_ini' => $d['kunjunganHariIni'],
                    'rata_rata_puas' => $d['rataRataSurvey'],
                ];
                $grafik = collect($d['grafikMingguan'])->map(fn($i) => (object)$i);
                $avg_aspek = (object)$d['rataAspek'];
            }
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
        }

        return view('admin.dashboard', compact('stats', 'grafik', 'avg_aspek'));
    }

// --- DATA KUNJUNGAN ---
    public function kunjungan(Request $request)
    {
        $kunjungan = collect([]);
        $keperluan_master = collect([]); // Inisialisasi variabel keperluan

        try {
            $response = Http::get(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'getAllData'
            ]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                
                // 1. Proses Data Bukutamu (Kunjungan)
                $rawKunjungan = $data['bukutamu'] ?? [];
                if (count($rawKunjungan) > 1) {
                    array_shift($rawKunjungan); // hapus header
                }

                $kunjungan = collect($rawKunjungan)->map(fn($row) => (object)[
                    'nomor_kunjungan' => $row[0] ?? '-',
                    'tanggal'         => $row[1] ?? '-',
                    'hari'            => $row[2] ?? '-',
                    'nama_lengkap'    => $row[3] ?? '-',
                    'asal_instansi'   => $row[4] ?? '-',
                    'keperluan'       => $row[5] ?? '-',

                ])->reverse();

                // 2. PROSES DATA MASTER KEPERLUAN (TAMBAHAN BARU)
                $rawKeperluan = $data['master_keperluan'] ?? [];
                if (count($rawKeperluan) > 1) {
                    array_shift($rawKeperluan); // hapus header
                    $keperluan_master = collect($rawKeperluan)->map(fn($row) => (object)[
                        'id' => $row[0] ?? null,
                        'keterangan' => $row[1] ?? null
                    ])->filter(fn($item) => !empty($item->keterangan));
                }
            }

        } catch (\Exception $e) {
            Log::error('Kunjungan Error: ' . $e->getMessage());
        }

        // filter search
        if ($request->search) {
            $kunjungan = $kunjungan->filter(fn($item) =>
                str_contains(strtolower($item->nama_lengkap), strtolower($request->search)) ||
                str_contains(strtolower($item->nomor_kunjungan), strtolower($request->search))
            );
        }

        // filter prodi
        if ($request->prodi) {
            $kunjungan = $kunjungan->where('asal_instansi', $request->prodi);
        }

        // Kirim kedua variabel ke view
        return view('admin.kunjungan.index', compact('kunjungan', 'keperluan_master'));
    }


    // --- DATA SURVEY ---
public function survey(Request $request)
{
    $surveys = collect([]);
    $avgScores = [0, 0, 0, 0, 0];
    
    // 1. Definisikan daftar prodi agar muncul di dropdown filter
    $prodis = [
        'D3 Teknik Listrik', 
        'D3 Teknik Elektronika', 
        'D3 Teknik Informatika', 
        'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi', 
        'Sarjana Terapan Sistem Informasi Kota Cerdas', 
        'Lainnya (Umum/Tamu Luar)'
    ];

    try {
        $response = Http::get(env('GOOGLE_SCRIPT_URL'), ['action' => 'getAllData']);

        if ($response->successful()) {
            $allData = $response->json()['data'] ?? [];
            $rawSurvey = $allData['survey'] ?? [];
            $rawBukutamu = $allData['bukutamu'] ?? [];

            if (count($rawSurvey) > 1) {
                array_shift($rawSurvey); // Hapus header
                array_shift($rawBukutamu); // Hapus header

                // 2. Buat Map untuk mencocokkan ID Kunjungan dengan Prodi
                // Asumsi: Bukutamu Index 0 = ID, Index 4 = Asal Instansi/Prodi
                $mapProdi = collect($rawBukutamu)->pluck(4, 0); 

                $surveys = collect($rawSurvey)->map(function($row) use ($mapProdi) {
                    $idKunjungan = $row[1] ?? '-';
                    return (object)[
                        'waktu'         => $row[0] ?? '-',
                        'id_kunjungan'  => $idKunjungan,
                        'nama_tamu'     => $row[2] ?? 'Anonim',
                        'prodi'         => $mapProdi[$idKunjungan] ?? 'Lainnya (Umum/Tamu Luar)', // Join data di sini
                        'p1' => (int)($row[3]??0), 
                        'p2' => (int)($row[4]??0), 
                        'p3' => (int)($row[5]??0), 
                        'p4' => (int)($row[6]??0), 
                        'p5' => (int)($row[7]??0),
                        'kritik_saran'  => $row[8] ?? '-',
                    ];
                });

                // 3. Logika Filter Nama
                if ($request->search) {
                    $surveys = $surveys->filter(fn($s) => 
                        str_contains(strtolower($s->nama_tamu), strtolower($request->search))
                    );
                }

                // 4. Logika Filter Prodi (Sekarang sudah bisa karena data prodi sudah di-join)
                if ($request->prodi) {
                    $surveys = $surveys->where('prodi', $request->prodi);
                }

                $surveys = $surveys->reverse(); // Urutan terbaru di atas

                // 5. Hitung Rata-rata setelah difilter
                if ($surveys->count() > 0) {
                    $avgScores = [
                        round($surveys->avg('p1'), 1),
                        round($surveys->avg('p2'), 1),
                        round($surveys->avg('p3'), 1),
                        round($surveys->avg('p4'), 1),
                        round($surveys->avg('p5'), 1),
                    ];
                }
            }
        }
    } catch (\Exception $e) {
        Log::error('Gagal filter survey: ' . $e->getMessage());
    }

    // Pastikan $prodis dikirim ke view
    return view('admin.survey.index', compact('surveys', 'avgScores', 'prodis'));
}

    // --- DATA PENGUNJUNG ---
public function pengunjung(Request $request)
{
    // 1. Ambil data dengan action 'getAllData' agar konsisten
    $response = Http::get(env('GOOGLE_SCRIPT_URL'), [
        'action' => 'getAllData'
    ]);

    $pengunjung = collect([]);

    if ($response->successful()) {
        $allData = $response->json()['data'] ?? [];
        $raw = $allData['pengunjung'] ?? []; // Pastikan nama sheet sesuai: 'pengunjung'

        if (count($raw) > 1) {
            array_shift($raw); // Hapus header

            $pengunjung = collect($raw)->map(function($row) {
                // Pastikan index [0],[1],[2],[3] sesuai urutan kolom di Google Sheets Anda
                return (object)[
                    'identitas_no'      => $row[0] ?? '-',
                    'nama_lengkap'      => $row[1] ?? 'Tanpa Nama',
                    'asal_instansi'     => $row[2] ?? 'Umum',
                    'terakhir_kunjungan'=> $row[3] ?? '-',
                ];
            });

            // 2. Filter berdasarkan Pencarian (Nama atau NIK)
            if ($request->search) {
                $search = strtolower($request->search);
                $pengunjung = $pengunjung->filter(fn($p) => 
                    str_contains(strtolower($p->nama_lengkap), $search) ||
                    str_contains(strtolower($p->identitas_no), $search)
                );
            }

            // 3. Filter berdasarkan Prodi (PENTING: Harus ada agar sinkron dengan dropdown Blade)
            if ($request->prodi) {
                $pengunjung = $pengunjung->where('asal_instansi', $request->prodi);
            }
            
            // Urutkan berdasarkan yang terbaru (jika ada data waktu) atau biarkan default
            $pengunjung = $pengunjung->values(); 
        }
    }

    return view('admin.pengunjung.index', compact('pengunjung'));
}

    // --- DATA USER (Tetap di Local Database untuk Keamanan Login) ---
    public function users()
{
    // 1. Ambil data dari Google Sheets (sesuaikan dengan service yang Anda gunakan)
    // Pastikan sheet yang dituju adalah 'master_users'
    $usersData = $this->googleSheetService->readSheet('master_user'); 

    // 2. Mapping data agar formatnya konsisten (biasanya Google Sheets mengembalikan array of arrays)
    $users = collect($usersData)->map(function($item) {
    return (object) [
        'role_id' => $item['role_id'] ?? null,
        'name'    => $item['name'] ?? 'User',
        'email'   => $item['email'] ?? '-',
        'foto'    => $item['foto'] ?? null, // ← INI WAJIB ADA
        'role'    => (object)[
            'nama_role' => ($item['role_id'] == 1) 
                ? 'Administrator' 
                : 'Ketua Jurusan'
        ],
    ];
});


    return view('admin.users.index', compact('users'));
}

    public function storeUser(Request $request)
    {
        $request->validate(['name' => 'required', 'email' => 'required|unique:users', 'password' => 'required']);
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id ?? 2
        ]);
        return back()->with('success', 'User berhasil ditambahkan.');
    }
    

    // --- MASTER KEPERLUAN ---
    public function masterKeperluan()
{
    // Cukup panggil sekali saja untuk efisiensi
    $keperluan = $this->fetchCloudData('master_keperluan');

    return view('admin.master.keperluan', compact('keperluan'));
}

    // --- LAPORAN ---
public function laporan()
{
    // Membuat data manual (statis) pengganti DB::table
    $prodi = collect([
        ['nama' => 'D3 Teknik Listrik', 'jenis' => 'Prodi'],
        ['nama' => 'D3 Teknik Elektronika', 'jenis' => 'Prodi'],
        ['nama' => 'D3 Teknik Informatika', 'jenis' => 'Prodi'],
        ['nama' => 'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi', 'jenis' => 'Prodi'],
        ['nama' => 'Sarjana Terapan Sistem Informasi Kota Cerdas', 'jenis' => 'Prodi'],
        ['nama' => 'Instansi Luar / Umum', 'jenis' => 'Instansi'],
    ])->map(function($item) {
        // Kita ubah ke object agar pemanggilan di View tetap menggunakan $p->nama
        return (object) $item;
    });

    return view('admin.laporan.index', compact('prodi'));
}

public function exportLaporan(Request $request)
    {
        // Validasi input sedikit agar aman
        $request->validate([
            'jenis' => 'required',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'prodi_id' => 'required',
            'format' => 'required'
        ]);

        // ID Spreadsheet (Bisa ditaruh di .env biar lebih rapi: GOOGLE_SHEET_ID)
        $spreadsheetId = "1ssiGHaeQaMD4NAywV_wBP9lMVkNEkoW4o8SFx-UJdvA"; 

        // Mapping nama sheet: 'kunjungan' di Laravel -> 'bukutamu' di GAS
        $sheetName = $request->jenis == 'kunjungan' ? 'bukutamu' : $request->jenis;

        try {
            // 1. Kirim perintah filter ke GAS
            $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
                'action'      => 'prepareExport',
                'sheetName'   => $sheetName,
                'tgl_mulai'   => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'prodi'       => $request->prodi_id, // 'all' atau nama prodi
                'formatType'  => $request->format    // 'excel' atau 'pdf' (PENTING!)
            ]);

            $resData = $response->json();

            // 2. Cek Status dari GAS
            if (!isset($resData['status']) || $resData['status'] !== 'success') {
                return back()->with('error', 'Gagal memproses data: ' . ($resData['message'] ?? 'Unknown Error'));
            }

            // 3. Cek apakah data kosong
            if (isset($resData['count']) && $resData['count'] === 0) {
                return back()->with('info', 'Tidak ada data ditemukan pada rentang tanggal/filter tersebut.');
            }

            // 4. Redirect ke URL Download
            // GAS versi baru sudah mengirimkan 'url' yang spesifik (Excel/PDF)
            if (!empty($resData['url'])) {
                return redirect()->away($resData['url']);
            }

            // --- FALLBACK (Jaga-jaga jika URL dari GAS kosong) ---
            $gid = $resData['gid'] ?? 0;
            $format = ($request->format == 'excel') ? 'xlsx' : 'pdf';
            
            // Parameter PDF Manual (Sama seperti di GAS)
            $pdfParams = "&size=A4&portrait=true&fitw=true&gridlines=false&fzr=false&horizontal_alignment=CENTER";
            
            $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format={$format}&gid={$gid}";
            
            if ($format == 'pdf') {
                $exportUrl .= $pdfParams;
            }

            return redirect()->away($exportUrl);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan server: ' . $e->getMessage());
        }
    }

    public function updateKunjungan(Request $request, $id)
{
    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action'    => 'updateKunjungan',
            'id'        => $id,
            'keperluan' => $request->keperluan,
        ]);

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Data berhasil diupdate.');
        }

        return redirect()->back()->with('error', 'Gagal update data.');
    } catch (\Exception $e) {
        Log::error('Update Kunjungan Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat update.');
    }
}

public function destroyKunjungan($id)
{
    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action' => 'deleteKunjungan',
            'id'     => $id,
        ]);

        if ($response->successful()) {
            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data.');
    } catch (\Exception $e) {
        Log::error('Delete Kunjungan Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan saat hapus.');
    }
}

// --- ACTION UPDATE SURVEY ---
public function updateSurvey(Request $request)
{
    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action'        => 'updateSurvey',
            'id_kunjungan'  => $request->id_kunjungan,
            'data'          => [
                (int)$request->p1,
                (int)$request->p2,
                (int)$request->p3,
                (int)$request->p4,
                (int)$request->p5,
                $request->kritik_saran
            ],
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return redirect()->back()->with('success', 'Data survey berhasil diperbarui.');
        }

        return redirect()->back()->with('error', 'Gagal update survey di Google Sheets.');
    } catch (\Exception $e) {
        Log::error('Update Survey Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
    }
}

// --- ACTION DELETE SURVEY ---
public function destroySurvey(Request $request)
{
    try {
        // Kita gunakan id_kunjungan sebagai acuan hapus sesuai Logika F di GAS
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action'       => 'deleteSurvey',
            'id_kunjungan' => $request->id_kunjungan,
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return redirect()->back()->with('success', 'Data survey berhasil dihapus.');
        }

        return redirect()->back()->with('error', 'Gagal menghapus data di Google Sheets.');
    } catch (\Exception $e) {
        Log::error('Delete Survey Error: ' . $e->getMessage());
        return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
    }
}
public function updatePengunjung(Request $request, $id)
{
    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action'        => 'updatePengunjung',
            'id'            => $id,
            'nama_lengkap'  => $request->nama_lengkap,
            'asal_instansi' => $request->asal_instansi,
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return redirect()->back()->with('success', 'Data berhasil diupdate.');
        }
        return redirect()->back()->with('error', 'Gagal update data.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Kesalahan: ' . $e->getMessage());
    }
}

public function destroyPengunjung($id)
{
    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action' => 'deletePengunjung',
            'id'     => $id,
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return redirect()->back()->with('success', 'Data berhasil dihapus.');
        }
        return redirect()->back()->with('error', 'Gagal menghapus data.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Kesalahan: ' . $e->getMessage());
    }
}

// --- ACTION TAMBAH KEPERLUAN ---
public function storeKeperluan(Request $request)
{
    $request->validate(['keterangan' => 'required']);

    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action' => 'storeKeperluan',
            'keterangan' => $request->keterangan,
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return redirect()->back()->with('success', 'Keperluan berhasil ditambahkan.');
        }
        return redirect()->back()->with('error', 'Gagal menambah data ke Google Sheets.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

// --- ACTION UPDATE KEPERLUAN ---
public function updateKeperluan(Request $request, $id)
{
    $request->validate(['keterangan' => 'required']);

    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action' => 'updateKeperluan',
            'id' => $id,
            'keterangan' => $request->keterangan,
        ]);

        if ($response->successful() && $response->json()['status'] === 'success') {
            return redirect()->back()->with('success', 'Keperluan berhasil diperbarui.');
        }
        return redirect()->back()->with('error', 'Gagal memperbarui data.');
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

// --- ACTION DELETE KEPERLUAN ---
public function destroyKeperluan($id)
{
    try {
        $response = Http::withoutVerifying()
            ->asJson() // Mengirimkan sebagai application/json
            ->post(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'deleteKeperluan',
                'id'     => (string)$id, // Memastikan ID adalah string
            ]);

        $res = $response->json();

        if ($response->successful() && ($res['status'] ?? '') === 'success') {
            return redirect()->back()->with('success', 'Keperluan berhasil dihapus.');
        }
        
        return redirect()->back()->with('error', 'Gagal: ' . ($res['message'] ?? 'Respons tidak valid'));
    } catch (\Exception $e) {
        return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}

public function dataKunjungan() {
    // Ambil data kunjungan dari sheet 'bukutamu'
    $kunjungan = $this->fetchSheetsData('bukutamu'); 

    // WAJIB: Ambil data master untuk dropdown di Modal Edit
    $keperluan_master = $this->fetchSheetsData('master_keperluan');

    return view('admin.kunjungan', compact('kunjungan', 'keperluan_master'));
}

}