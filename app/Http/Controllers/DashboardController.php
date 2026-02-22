<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use App\Models\User; 
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

    private function getProdiFilter()
    {
        $user = session('user');
        
        // Role ID 1 = Super Admin, 2 = Ketua Jurusan (Bisa melihat semua)
        if (in_array((int)($user['role_id'] ?? 0), [1, 2])) {
            return 'all';
        }
        
        $prodiId = $user['prodi_id'] ?? 'all';

        $prodiMapping = [
            'C01' => 'D3 Teknik Listrik',
            'C02' => 'D3 Teknik Elektronika',
            'C03' => 'D3 Teknik Informatika',
            'C04' => 'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi',
            'C05' => 'Sarjana Terapan Sistem Informasi Kota Cerdas',
        ];

        return $prodiMapping[$prodiId] ?? $prodiId;
    }

    /**
     * Helper universal untuk fetch data sheet dari GAS
     */
    private function fetchCloudData($sheetName)
    {
        try {
            $url = env('GOOGLE_SCRIPT_URL') . "?action=read&sheet=" . $sheetName;
            $response = Http::withoutVerifying()->timeout(15)->get($url);

            if ($response->successful()) {
                $json = $response->json();
                $rows = $json['data'] ?? [];
                $result = [];

                foreach ($rows as $row) {
                    $id = $row['Id'] ?? ($row['id'] ?? ($row['No Identitas'] ?? null));
                    if (!$id) continue;

                    $result[] = (object) [
                        'id'         => $id,
                        'keterangan' => $row['Keterangan'] ?? ($row['keterangan'] ?? 'Tanpa Keterangan'),
                        'data_raw'   => $row
                    ];
                }
                return $result;
            }
        } catch (\Exception $e) {
            Log::error("Gagal Fetch Sheet {$sheetName}: " . $e->getMessage());
        }
        return [];
    }

    // --- DASHBOARD ---
    public function index()
    {
        $prodi_id = $this->getProdiFilter();

        try {
            $response = Http::withoutVerifying()->get(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'getDashboardData',
                'prodi_id' => $prodi_id
            ]);

            if (!$response->successful()) throw new \Exception("Koneksi GAS Gagal");

            $res = $response->json();
            if (($res['status'] ?? '') !== 'success') throw new \Exception($res['message'] ?? "Data error");

            $data = $res['data'];
            $stats = [
                'total_tamu'     => $data['totalKunjungan'] ?? 0,
                'tamu_hari_ini'  => $data['kunjunganHariIni'] ?? 0,
                'rata_rata_puas' => round($data['rataRataSurvey'] ?? 0, 1),
            ];

            // Ganti bagian $avg_aspek di DashboardController.php
            $dataRaw = $res['data']['rataAspek'] ?? [];
            $avg_aspek = (object) [
                'p1' => floatval($dataRaw['p1'] ?? 0),
                'p2' => floatval($dataRaw['p2'] ?? 0),
                'p3' => floatval($dataRaw['p3'] ?? 0),
                'p4' => floatval($dataRaw['p4'] ?? 0),
                'p5' => floatval($dataRaw['p5'] ?? 0),
            ];

            $grafik = collect($data['grafikMingguan'] ?? [])->map(function ($g) {
                return (object)[
                    // [PERUBAHAN]: Format ke Asia/Makassar
                    'tanggal' => Carbon::parse($g['tanggal'])->timezone('Asia/Makassar')->translatedFormat('d M'),
                    'jumlah'  => $g['jumlah']
                ];
            });

            return view('admin.dashboard', compact('stats', 'grafik', 'avg_aspek'));
        } catch (\Exception $e) {
            Log::error('Dashboard Error: ' . $e->getMessage());
            return view('admin.dashboard', [
                'stats' => ['total_tamu' => 0, 'tamu_hari_ini' => 0, 'rata_rata_puas' => 0],
                'grafik' => collect(),
                'avg_aspek' => (object)['p1' => 0, 'p2' => 0, 'p3' => 0, 'p4' => 0, 'p5' => 0]
            ])->with('error', 'Gagal sinkronisasi dengan Google Sheets.');
        }
    }

    // --- DATA KUNJUNGAN ---
public function kunjungan(Request $request)
{
    // 1. Ambil input dari request (dari form filter yang kita buat sebelumnya)
    $search = strtolower($request->query('search'));
    $filterProdi = $request->query('prodi'); // Tangkap parameter 'prodi'

    // 2. Tentukan prodi_id untuk API
    // Jika user adalah Super Admin (Role 1), gunakan pilihan dropdown. 
    // Jika user adalah Admin Prodi, tetap gunakan filter default mereka.
    $prodi_id = ((int)session('user')['role_id'] === 1 && $filterProdi) 
                ? $filterProdi 
                : $this->getProdiFilter();

    try {
        $response = Http::withoutVerifying()
            ->timeout(15)
            ->get(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'getAllData',
                'prodi_id' => $prodi_id // Kirim ke Google Script
            ]);

        $allData = $response->json()['data'] ?? [];
        $rawKunjungan = $allData['bukutamu'] ?? [];

        if (count($rawKunjungan) > 0) array_shift($rawKunjungan); // Hapus header

        $kunjungan = collect($rawKunjungan)->map(function($row) {
            return (object)[
                'nomor_kunjungan' => $row[0] ?? '-',
                'tanggal'         => !empty($row[1]) ? Carbon::parse($row[1])->timezone('Asia/Makassar')->translatedFormat('d F Y') : '-',
                'nama_lengkap'    => $row[3] ?? '-',
                'asal_instansi'   => $row[4] ?? '-', // Ini biasanya berisi nama prodi/instansi
                'keperluan'       => $row[5] ?? '-',
            ];
        });

        // 3. Logika Filter Tambahan (Jika API tidak memfilter prodi dengan sempurna)
        if ($filterProdi && (int)session('user')['role_id'] === 1) {
            $kunjungan = $kunjungan->filter(function($item) use ($filterProdi) {
                // Sesuaikan 'asal_instansi' dengan kolom mana yang menyimpan nama Prodi di Sheets Anda
                return str_contains($item->asal_instansi, $filterProdi);
            });
        }

        // 4. Logika Pencarian Nama/Nomor
        if ($search) {
            $kunjungan = $kunjungan->filter(function($item) use ($search) {
                return str_contains(strtolower($item->nama_lengkap), $search) || 
                       str_contains(strtolower($item->nomor_kunjungan), $search);
            });
        }

        $kunjungan = $kunjungan->reverse()->values();

        return view('admin.kunjungan.index', compact('kunjungan'));

    } catch (\Exception $e) {
        Log::error("Kunjungan Error: " . $e->getMessage());
        return back()->with('error', 'Gagal mengambil data kunjungan.');
    }
}

    // --- DATA SURVEY ---
public function survey(Request $request)
{
    $user = session('user');
    $roleId = (int)($user['role_id'] ?? 0);
    $isSuperAdminOrKajur = in_array($roleId, [1, 2]);

    $filterProdi = $request->query('prodi');
    $search = strtolower($request->query('search'));

    $prodi_id_api = ($isSuperAdminOrKajur && $filterProdi) ? $filterProdi : $this->getProdiFilter();

try {
        $response = Http::withoutVerifying()->timeout(15)->get(env('GOOGLE_SCRIPT_URL'), [
            'action' => 'getAllData',
            'prodi_id' => $prodi_id_api 
        ]);

        $allData = $response->json()['data'] ?? [];
        
        // 1. BUAT DATABASE LOKAL (MAP) DARI BUKU TAMU
        $rawKunjungan = $allData['bukutamu'] ?? [];
        if (count($rawKunjungan) > 0) array_shift($rawKunjungan);
        
        // Map ini kunci utamanya adalah ID Kunjungan
        $kunjunganMap = collect($rawKunjungan)->keyBy(0); 

        // 2. PROSES DATA SURVEY
        $rawSurvey = $allData['survey'] ?? [];
        if (count($rawSurvey) > 0) array_shift($rawSurvey);

$surveys = collect($rawSurvey)->map(function ($row) use ($isSuperAdminOrKajur, $kunjunganMap) {
    $idKunjungan = $row[1] ?? null;
    $namaDariSurvey = $row[2] ?? null;
    
    // 1. Coba cari berdasarkan ID Kunjungan
    $dataKunjungan = $kunjunganMap->get($idKunjungan);
    
    // 2. Jika tidak ketemu ID-nya, coba cari berdasarkan Nama (Fallback)
    if (!$dataKunjungan && $namaDariSurvey) {
        $dataKunjungan = $kunjunganMap->first(function($item) use ($namaDariSurvey) {
            return strtolower(trim($item[3] ?? '')) === strtolower(trim($namaDariSurvey));
        });
    }

    // Tentukan Prodi
    $prodiNama = 'Umum';
    if ($dataKunjungan && isset($dataKunjungan[4])) {
        $prodiNama = trim($dataKunjungan[4]);
    } elseif (isset($row[10])) {
        $prodiNama = trim($row[10]);
    }

    return (object)[
        'waktu'         => !empty($row[0]) ? Carbon::parse($row[0])->timezone('Asia/Makassar')->translatedFormat('d M Y') : '-',
        'id_kunjungan'  => $isSuperAdminOrKajur ? ($idKunjunganAsli ?? '-') : 'HIDDEN',        'nama_tamu'     => $isSuperAdminOrKajur ? ($dataKunjungan[3] ?? $namaDariSurvey) : 'Pengunjung (Disamarkan)',
        'prodi_nama'    => $prodiNama, 
        'p1' => (int)($row[3] ?? 0),
        'p2' => (int)($row[4] ?? 0),
        'p3' => (int)($row[5] ?? 0),
        'p4' => (int)($row[6] ?? 0),
        'p5' => (int)($row[7] ?? 0),
        'kritik_saran'  => $row[8] ?? '-',
    ];
});

        // 3. FILTER AGRESIF (Mencari sebagian kata)
        if ($filterProdi && $filterProdi !== 'all') {
            $surveys = $surveys->filter(function($s) use ($filterProdi) {
                $val = strtolower(trim($s->prodi_nama));
                $target = strtolower(trim($filterProdi));

                // Jika target filter "D3 Teknik Informatika", 
                // data dengan prodi "D3 Teknik Inform" (terpotong) akan TETAP LOLOS filter.
                return str_contains($val, $target) || str_contains($target, $val);
            });
        }

        if ($search && $isSuperAdminOrKajur) {
            $surveys = $surveys->filter(fn($s) => str_contains(strtolower($s->nama_tamu), $search));
        }

        $surveys = $surveys->reverse()->values();

        // Hitung Statistik
        $avgScores = [0, 0, 0, 0, 0];
        if ($surveys->count() > 0) {
            for ($i = 1; $i <= 5; $i++) {
                $avgScores[$i - 1] = round($surveys->avg('p' . $i), 1);
            }
        }

        $prodis = [
            'D3 Teknik Listrik',
            'D3 Teknik Elektronika',
            'D3 Teknik Informatika',
            'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi',
            'Sarjana Terapan Sistem Informasi Kota Cerdas'
        ];

        $aspekLabels = ['Kecepatan', 'Etika', 'Kompetensi', 'Fasilitas', 'Kualitas'];

        return view('admin.survey.index', compact('surveys', 'avgScores', 'prodis', 'aspekLabels'));

    } catch (\Exception $e) {
        Log::error("Survey Error: " . $e->getMessage());
        return back()->with('error', 'Gagal memuat data survey.');
    }
}

    // --- DATA PENGUNJUNG ---
public function pengunjung(Request $request)
{
    // 1. Ambil data user dari session untuk pengecekan Role
    $userSession = session('user');
    $roleId = (int)($userSession['role_id'] ?? 0);
    $prodiUser = $userSession['prodi_nama'] ?? '';

    // 2. Ambil data dari Google Apps Script
    $response = Http::get(env('GOOGLE_SCRIPT_URL'), ['action' => 'getAllData']);
    $pengunjung = collect([]);

    if ($response->successful()) {
        $allData = $response->json()['data'] ?? [];
        $raw = $allData['pengunjung'] ?? [];

        if (count($raw) > 1) {
            array_shift($raw); // Hapus header row
            
            $pengunjung = collect($raw)->map(function ($row) {
                // Parsing tanggal terakhir kunjungan
                $terakhir = !empty($row[3]) ? \Carbon\Carbon::parse($row[3])->timezone('Asia/Makassar')->translatedFormat('d F Y') : '-';

                return (object)[
                    'identitas_no'       => $row[0] ?? '-',
                    'nama_lengkap'       => $row[1] ?? 'Tanpa Nama',
                    'asal_instansi'      => $row[2] ?? 'Umum',
                    'terakhir_kunjungan' => $terakhir,
                ];
            });

            // 3. LOGIKA PENGUNCIAN & FILTER PRODI
            $selectedProdi = $request->prodi;

            // Jika BUKAN Super Admin (Role != 1), PAKSA filter ke prodi user tersebut
            if ($roleId !== 1) {
                $selectedProdi = $prodiUser;
            }

            // Jalankan filter Prodi jika ada (baik dari request maupun paksaan session)
            if ($selectedProdi) {
                $pengunjung = $pengunjung->filter(function ($p) use ($selectedProdi) {
                    // Cek jika asal_instansi cocok dengan prodi yang dipilih
                    return strtolower($p->asal_instansi) === strtolower($selectedProdi);
                });
            }

            // 4. LOGIKA SEARCH
            if ($request->search) {
                $search = strtolower($request->search);
                $pengunjung = $pengunjung->filter(fn($p) =>
                    str_contains(strtolower($p->nama_lengkap), $search) ||
                    str_contains(strtolower($p->identitas_no), $search));
            }

            // Reset index setelah difilter
            $pengunjung = $pengunjung->values();
        }
    }

    return view('admin.pengunjung.index', compact('pengunjung'));
}

    // --- DATA USER (FULL GOOGLE SHEETS) ---
    public function users()
    {
        $usersData = $this->googleSheetService->readSheet('master_user'); 
        
        $users = collect($usersData)->map(function ($item) {
            return (object) [
                'role_id' => $item['role_id'] ?? null,
                'name'    => $item['name'] ?? 'User',
                'email'   => $item['email'] ?? '-',
                'foto'    => $item['foto'] ?? null,
                'prodi_id'=> $item['prodi_id'] ?? 'all',
                'role'    => (object)[
                    'nama_role' => ($item['role_id'] == 1) ? 'Administrator' : 'Ketua Jurusan'
                ],
            ];
        });

        return view('admin.users.index', compact('users'));
    }

    public function storeUser(Request $request)
    {
        try {
            $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
                'action'   => 'storeUser',
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password), 
                'role_id'  => $request->role_id ?? 2,
                'prodi_id' => $request->prodi_id
            ]);

            if ($response->successful() && ($response->json()['status'] ?? '') === 'success') {
                return back()->with('success', 'User berhasil disimpan ke Google Sheets.');
            }
            return back()->with('error', 'Gagal menyimpan user ke Sheets.');
        } catch (\Exception $e) {
            return back()->with('error', 'Kesalahan: ' . $e->getMessage());
        }
    }

    // --- MASTER KEPERLUAN ---
    public function masterKeperluan()
    {
        $keperluan = $this->fetchCloudData('master_keperluan');
        return view('admin.master.keperluan', compact('keperluan'));
    }

    // --- LAPORAN & EXPORT ---
    public function laporan()
    {
        $prodi = collect([
            ['nama' => 'D3 Teknik Listrik', 'jenis' => 'Prodi'],
            ['nama' => 'D3 Teknik Elektronika', 'jenis' => 'Prodi'],
            ['nama' => 'D3 Teknik Informatika', 'jenis' => 'Prodi'],
            ['nama' => 'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi', 'jenis' => 'Prodi'],
            ['nama' => 'Sarjana Terapan Sistem Informasi Kota Cerdas', 'jenis' => 'Prodi'],
            ['nama' => 'Instansi Luar / Umum', 'jenis' => 'Instansi'],
        ])->map(fn($item) => (object) $item);

        return view('admin.laporan.index', compact('prodi'));
    }

public function exportLaporan(Request $request)
{
    // 1. Ambil data session
    $userSession = session('user');
    $roleId = (int)($userSession['role_id'] ?? 0);
    $userProdiNama = $userSession['prodi_nama'] ?? '';

    // 2. Logika Penentuan Prodi untuk Export
    if ($roleId === 1) {
        // Jika Super Admin, ambil dari input dropdown (bisa 'all' atau prodi tertentu)
        $prodiFilter = $request->prodi_id;
    } else {
        // Jika selain Super Admin (Kajur/Admin Prodi), PAKSA gunakan prodi mereka sendiri
        // Ini memastikan mereka tidak bisa tembus lewat manipulasi inspect element
        $prodiFilter = $userProdiNama;
    }

    // 3. Validasi
    $request->validate([
        'jenis' => 'required',
        'tgl_mulai' => 'required|date',
        'tgl_selesai' => 'required|date',
        'format' => 'required'
    ]);

    $spreadsheetId = "1ssiGHaeQaMD4NAywV_wBP9lMVkNEkoW4o8SFx-UJdvA";
    
    // Mapping Nama Sheet
    $sheetName = $request->jenis;
    if ($request->jenis == 'kunjungan') $sheetName = 'bukutamu';
    if ($request->jenis == 'survey') $sheetName = 'survey'; 

    try {
        $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
            'action'      => 'prepareExport',
            'sheetName'   => $sheetName,
            'tgl_mulai'   => $request->tgl_mulai,
            'tgl_selesai' => $request->tgl_selesai,
            'prodi'       => $prodiFilter, // Kirim variabel yang sudah kita filter di atas
            'formatType'  => $request->format
        ]);

        $resData = $response->json();

            if (!isset($resData['status']) || $resData['status'] !== 'success') {
            return back()->with('error', 'Gagal memproses data: ' . ($resData['message'] ?? 'Unknown Error'));
        }

        if (isset($resData['count']) && $resData['count'] === 0) {
            return back()->with('info', 'Tidak ada data ditemukan pada rentang tanggal tersebut.');
        }

        if (!empty($resData['url'])) {
            return redirect()->away($resData['url']);
        }

            $gid = $resData['gid'] ?? 0;
            $format = ($request->format == 'excel') ? 'xlsx' : 'pdf';
            $pdfParams = "&size=A4&portrait=true&fitw=true&gridlines=false&fzr=false&horizontal_alignment=CENTER";
            $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format={$format}&gid={$gid}";

            if ($format == 'pdf') $exportUrl .= $pdfParams;

            return redirect()->away($exportUrl);
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan ekspor: ' . $e->getMessage());
        }
    }

    public function updateKunjungan(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'keperluan' => 'required|string|max:500'
        ]);

        try {
            $response = Http::withoutVerifying()->post(env('GOOGLE_SCRIPT_URL'), [
                'action'    => 'updateKunjungan',
                'id'        => $id,
                'keperluan' => $request->keperluan,
                'admin'     => session('user')['name'] // Opsional: untuk log di GAS
            ]);

            if ($response->successful() && ($response->json()['status'] ?? '') === 'success') {
                return redirect()->back()->with('success', 'Data kunjungan #' . $id . ' berhasil diperbarui.');
            }

            return redirect()->back()->with('error', 'Gagal update data di Cloud.');
        } catch (\Exception $e) {
            Log::error('Update Kunjungan Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }

    public function destroyKunjungan($id)
    {
        try {
            $response = Http::withoutVerifying()->post(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'deleteKunjungan',
                'id'     => $id,
            ]);

            if ($response->successful() && ($response->json()['status'] ?? '') === 'success') {
                return redirect()->back()->with('success', 'Data kunjungan berhasil dihapus dari Cloud.');
            }

            return redirect()->back()->with('error', 'Gagal menghapus data di Cloud.');
        } catch (\Exception $e) {
            Log::error('Delete Kunjungan Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan koneksi.');
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
                ->asJson() 
                ->post(env('GOOGLE_SCRIPT_URL'), [
                    'action' => 'deleteKeperluan',
                    'id'     => (string)$id, 
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
        $kunjungan = $this->fetchCloudData('bukutamu'); 
        $keperluan_master = $this->fetchCloudData('master_keperluan');

        return view('admin.kunjungan', compact('kunjungan', 'keperluan_master'));
    }
public function checkNotification()
{
    $user = session('user');
    
    // Jika Super Admin, kirim 'all'
    // Jika Admin Prodi, kirim nama prodi (misal: "D3 Teknik Informatika")
    $filter = ((int)$user['role_id'] === 1) ? 'all' : ($user['prodi_nama'] ?? 'all');

    try {
        $response = Http::withoutVerifying()->get(env('GOOGLE_SCRIPT_URL'), [
            'action' => 'getDashboardData',
            'prodi_id' => $filter // Sekarang isinya teks nama prodi
        ]);

        if ($response->successful()) {
            $res = $response->json();
            $latest = $res['data']['latest_tamu'] ?? null;

            if ($latest) {
                return response()->json([
                    'status' => 'success',
                    'latest_id' => $latest['id'],
                    'nama' => $latest['nama'],
                    'keperluan' => $latest['keperluan']
                ]);
            }
        }
    } catch (\Exception $e) {
        return response()->json(['status' => 'error']);
    }
    return response()->json(['status' => 'empty']);
}

}