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
                // Ambil ID dan bersihkan dari spasi/karakter aneh
                $idRaw = $row['Id'] ?? ($row['id'] ?? ($row['No Identitas'] ?? null));
                
                // FILTER: Jangan masukkan jika ID kosong, atau berisi error #NUM!
                if (!$idRaw || str_contains($idRaw, '#')) continue;

                $result[] = (object) [
                    'id'         => trim($idRaw), // Pastikan ID bersih dari spasi
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
                'total_tamu'     => (int)($data['totalKunjungan'] ?? 0),
                'tamu_hari_ini'  => (int)($data['kunjunganHariIni'] ?? 0),
                'rata_rata_puas' => floatval($data['rataRataSurvey'] ?? 0), // Pastikan float
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
    // 1. Ambil input dari request
    $search = strtolower($request->query('search'));
    $filterProdi = $request->query('prodi'); 

    // 2. Tentukan prodi_id untuk API
    $prodi_id = ((int)session('user')['role_id'] === 1 && $filterProdi) 
                ? $filterProdi 
                : $this->getProdiFilter();

    try {
        $response = Http::withoutVerifying()
            ->timeout(15)
            ->get(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'getAllData',
                'prodi_id' => $prodi_id 
            ]);

        $allData = $response->json()['data'] ?? [];
        // Di GAS, bukutamu sudah difilter dan dipetakan menjadi array of objects
        $rawKunjungan = $allData['bukutamu'] ?? [];

        // TIDAK PERLU array_shift($rawKunjungan) karena sudah dibuang di GAS

        $kunjungan = collect($rawKunjungan)->map(function($row) {
            // Kita gunakan nama field (key) sesuai yang didefinisikan di GAS:
            // no_kunjungan, tanggal, hari, nama, instansi, keperluan, prodi_id
            return (object)[
                'nomor_kunjungan' => $row['no_kunjungan'] ?? '-',
                'tanggal'         => !empty($row['tanggal']) 
                                     ? Carbon::parse($row['tanggal'])->timezone('Asia/Makassar')->translatedFormat('d F Y') 
                                     : '-',
                'nama_lengkap'    => $row['nama'] ?? '-',
                'asal_instansi'   => $row['instansi'] ?? '-', 
                'keperluan'       => $row['keperluan'] ?? '-',
                'prodi_id'        => $row['prodi_id'] ?? '-',
            ];
        });

        // 3. Logika Pencarian Nama/Nomor (dilakukan di sisi Laravel)
        if ($search) {
            $kunjungan = $kunjungan->filter(function($item) use ($search) {
                return str_contains(strtolower($item->nama_lengkap), $search) || 
                       str_contains(strtolower($item->nomor_kunjungan), $search);
            });
        }

        // 4. Urutkan dari yang terbaru (karena GAS sudah memfilter, kita tinggal membalik urutan)
        $kunjungan = $kunjungan->reverse()->values();

        return view('admin.kunjungan.index', compact('kunjungan'));

    } catch (\Exception $e) {
        Log::error("Kunjungan Error: " . $e->getMessage());
        return back()->with('error', 'Gagal mengambil data kunjungan: ' . $e->getMessage());
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
        
        // 1. Ambil data dari GAS (Sudah berbentuk Object)
        $rawKunjungan = $allData['bukutamu'] ?? [];
        $rawSurvey = $allData['survey'] ?? [];

        // Buat Map berdasarkan no_kunjungan untuk lookup cepat
        $kunjunganMap = collect($rawKunjungan)->keyBy('no_kunjungan'); 

        // 2. PROSES DATA SURVEY
        $surveys = collect($rawSurvey)->map(function ($row) use ($isSuperAdminOrKajur, $kunjunganMap) {
            $idKunjungan = $row['id_kunjungan'] ?? null;
            $namaDariSurvey = $row['nama'] ?? null;
            
            // Cari data kunjungan untuk mendapatkan nama instansi/prodi
            $dataKunjungan = $kunjunganMap->get($idKunjungan);
            
            // Fallback cari berdasarkan nama jika ID tidak match
            if (!$dataKunjungan && $namaDariSurvey) {
                $dataKunjungan = $kunjunganMap->first(function($item) use ($namaDariSurvey) {
                    return strtolower(trim($item['nama'] ?? '')) === strtolower(trim($namaDariSurvey));
                });
            }

            $prodiNama = $dataKunjungan['instansi'] ?? 'Umum';

            return (object)[
                'waktu'         => !empty($row['waktu']) ? Carbon::parse($row['waktu'])->timezone('Asia/Makassar')->translatedFormat('d M Y') : '-',
                'id_kunjungan'  => $isSuperAdminOrKajur ? ($idKunjungan ?? '-') : 'HIDDEN',
                'nama_tamu'     => $isSuperAdminOrKajur ? ($dataKunjungan['nama'] ?? $namaDariSurvey) : 'Pengunjung (Disamarkan)',
                'prodi_nama'    => $prodiNama, 
                'p1' => (int)($row['p1'] ?? 0),
                'p2' => (int)($row['p2'] ?? 0),
                'p3' => (int)($row['p3'] ?? 0),
                'p4' => (int)($row['p4'] ?? 0),
                'p5' => (int)($row['p5'] ?? 0),
                'kritik_saran'  => $row['saran'] ?? '-',
            ];
        });

        // 3. FILTERING (Jika diperlukan tambahan di Laravel)
        if ($filterProdi && $filterProdi !== 'all') {
            $surveys = $surveys->filter(function($s) use ($filterProdi) {
                return str_contains(strtolower($s->prodi_nama), strtolower($filterProdi));
            });
        }

        if ($search && $isSuperAdminOrKajur) {
            $surveys = $surveys->filter(fn($s) => str_contains(strtolower($s->nama_tamu), $search));
        }

        $surveys = $surveys->reverse()->values();

        // Hitung Statistik Rata-rata per Aspek
        $avgScores = [0, 0, 0, 0, 0];
        if ($surveys->count() > 0) {
            for ($i = 1; $i <= 5; $i++) {
                $avgScores[$i - 1] = round($surveys->avg('p' . $i), 1);
            }
        }

        $prodis = ['D3 Teknik Listrik', 'D3 Teknik Elektronika', 'D3 Teknik Informatika', 'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi', 'Sarjana Terapan Sistem Informasi Kota Cerdas'];
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
    $userSession = session('user');
    $roleId = (int)($userSession['role_id'] ?? 0);
    $prodiUser = $userSession['prodi_nama'] ?? '';

    $response = Http::withoutVerifying()->get(env('GOOGLE_SCRIPT_URL'), ['action' => 'getAllData']);
    $pengunjung = collect([]);

    if ($response->successful()) {
        $allData = $response->json()['data'] ?? [];
        $raw = $allData['pengunjung'] ?? [];

        if (count($raw) > 0) {
            // Cek jika baris pertama adalah header, jika ya maka buang
            if (isset($raw[0][0]) && (strtolower($raw[0][0]) == 'id' || str_contains(strtolower($raw[0][0]), 'identitas'))) {
                array_shift($raw);
            }

            $pengunjung = collect($raw)->map(function ($row) {
                // Mengikuti format yang Anda minta
                $terakhir = !empty($row[3]) 
                    ? \Carbon\Carbon::parse($row[3])->timezone('Asia/Makassar')->translatedFormat('d F Y') 
                    : '-';

                return (object)[
                    'identitas_no'       => $row[0] ?? '-',
                    'nama_lengkap'       => $row[1] ?? 'Tanpa Nama',
                    'asal_instansi'      => $row[2] ?? 'Umum',
                    'terakhir_kunjungan' => $terakhir,
                ];
            });

            // --- LOGIKA FILTER AGAR KAJUR TIDAK KOSONG ---
            
            $selectedProdi = $request->query('prodi');

            if ($roleId === 1 || $roleId === 2) {
                // Super Admin (1) dan Kajur (2) bisa melihat semua prodi.
                // Filter hanya jalan jika mereka memilih prodi tertentu di dropdown.
                if ($selectedProdi && $selectedProdi !== 'all') {
                    $pengunjung = $pengunjung->filter(function ($p) use ($selectedProdi) {
                        return strtolower(trim($p->asal_instansi)) === strtolower(trim($selectedProdi));
                    });
                }
            } else {
                // Selain Role 1 & 2 (misal Admin Prodi), paksa filter sesuai prodi mereka.
                $pengunjung = $pengunjung->filter(function ($p) use ($prodiUser) {
                    return strtolower(trim($p->asal_instansi)) === strtolower(trim($prodiUser));
                });
            }

            // --- LOGIKA SEARCH ---
            if ($request->search) {
                $search = strtolower($request->search);
                $pengunjung = $pengunjung->filter(fn($p) =>
                    str_contains(strtolower($p->nama_lengkap), $search) ||
                    str_contains(strtolower($p->identitas_no), $search));
            }

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

    // 2. LOGIKA PERBAIKAN: Tentukan Prodi untuk Export
    // Role 1 = Super Admin, Role 2 = Kajur
    if ($roleId === 1 || $roleId === 2) {
        // Jika Super Admin atau Kajur, ambil dari input dropdown (bisa 'all' atau prodi tertentu)
        $prodiFilter = $request->prodi_id;
    } else {
        // Jika Admin Prodi (Role 3, dll), PAKSA gunakan prodi mereka sendiri dari session
        $prodiFilter = $userProdiNama;
    }

    // 3. Validasi input lainnya
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
            'prodi'       => $prodiFilter, // Menggunakan variabel filter yang sudah diperbaiki
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