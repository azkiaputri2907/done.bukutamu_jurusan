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
        $prodi_id = $this->getProdiFilter();
        try {
            $response = Http::withoutVerifying()
            ->timeout(10)
            ->get(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'getAllData',
                'prodi_id' => $prodi_id
            ]);

            $allData = $response->json()['data'] ?? [];
            $rawKunjungan = $allData['bukutamu'] ?? [];

            if (count($rawKunjungan) > 0) array_shift($rawKunjungan);

            $kunjungan = collect($rawKunjungan)->map(function($row) {
                // [PERUBAHAN]: Parsing tanggal kunjungan ke format yang rapi & sesuai zona waktu
                $tgl = !empty($row[1]) ? Carbon::parse($row[1])->timezone('Asia/Makassar')->translatedFormat('d F Y') : '-';

                return (object)[
                    'nomor_kunjungan' => $row[0] ?? '-',
                    'tanggal'         => $tgl,
                    'nama_lengkap'    => $row[3] ?? '-',
                    'asal_instansi'   => $row[4] ?? '-',
                    'keperluan'       => $row[5] ?? '-',
                ];
            })->reverse();

            return view('admin.kunjungan.index', compact('kunjungan'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengambil data kunjungan dari Sheets.');
        }
    }

    // --- DATA SURVEY ---
    public function survey(Request $request)
    {
        $prodi_id = $this->getProdiFilter();
        try {
            $response = Http::withoutVerifying()
            ->timeout(10)
            ->get(env('GOOGLE_SCRIPT_URL'), [
                'action' => 'getAllData',
                'prodi_id' => $prodi_id
            ]);

            $allData = $response->json()['data'] ?? [];
            $rawSurvey = $allData['survey'] ?? [];
            if (count($rawSurvey) > 0) array_shift($rawSurvey);

            $surveys = collect($rawSurvey)->map(function ($row) use ($prodi_id) {
                // [PERUBAHAN]: Parsing tanggal & jam survey ke zona waktu Makassar
                $waktu = !empty($row[0]) ? Carbon::parse($row[0])->timezone('Asia/Makassar')->translatedFormat('d M Y, H:i') : '-';

                return (object)[
                    'waktu'         => $waktu,
                    'id_kunjungan'  => $row[1] ?? '-',
                    'nama_tamu'     => ($prodi_id === 'all') ? ($row[2] ?? 'Anonim') : 'Pengunjung (Disamarkan)',
                    'p1' => (int)($row[3] ?? 0),
                    'p2' => (int)($row[4] ?? 0),
                    'p3' => (int)($row[5] ?? 0),
                    'p4' => (int)($row[6] ?? 0),
                    'p5' => (int)($row[7] ?? 0),
                    'kritik_saran'  => $row[8] ?? '-',
                ];
            })->reverse();

            return view('admin.survey.index', compact('surveys'));
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memuat data survey.');
        }
    }

    // --- DATA PENGUNJUNG ---
    public function pengunjung(Request $request)
    {
        $response = Http::get(env('GOOGLE_SCRIPT_URL'), ['action' => 'getAllData']);
        $pengunjung = collect([]);

        if ($response->successful()) {
            $allData = $response->json()['data'] ?? [];
            $raw = $allData['pengunjung'] ?? [];

            if (count($raw) > 1) {
                array_shift($raw);
                $pengunjung = collect($raw)->map(function ($row) {
                    // [PERUBAHAN]: Parsing tanggal terakhir kunjungan
                    $terakhir = !empty($row[3]) ? Carbon::parse($row[3])->timezone('Asia/Makassar')->translatedFormat('d F Y') : '-';

                    return (object)[
                        'identitas_no'       => $row[0] ?? '-',
                        'nama_lengkap'       => $row[1] ?? 'Tanpa Nama',
                        'asal_instansi'      => $row[2] ?? 'Umum',
                        'terakhir_kunjungan' => $terakhir,
                    ];
                });

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
        $request->validate([
            'jenis' => 'required',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date',
            'prodi_id' => 'required',
            'format' => 'required'
        ]);

        $spreadsheetId = "1ssiGHaeQaMD4NAywV_wBP9lMVkNEkoW4o8SFx-UJdvA";
        $sheetName = $request->jenis == 'kunjungan' ? 'bukutamu' : $request->jenis;

        try {
            $response = Http::post(env('GOOGLE_SCRIPT_URL'), [
                'action'      => 'prepareExport',
                'sheetName'   => $sheetName,
                'tgl_mulai'   => $request->tgl_mulai,
                'tgl_selesai' => $request->tgl_selesai,
                'prodi'       => $request->prodi_id,
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

}