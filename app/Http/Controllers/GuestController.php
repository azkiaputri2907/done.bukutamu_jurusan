<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http; 
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GuestController extends Controller
{
    // ==========================================
    // 1. LANDING PAGE (Ambil Ringkasan Statistik)
    // ==========================================
public function index() {
    $totalPengunjung = 0;
    $totalKunjungan = 0;
    $rataRataSurvey = 0;

    try {
        $scriptUrl = env('GOOGLE_SCRIPT_URL');
        // Tambahkan timeout yang lebih lama (10 detik) jika koneksi lambat
        $response = Http::timeout(10)->get($scriptUrl);
        
        if ($response->successful()) {
            $result = $response->json();
            if (isset($result['status']) && $result['status'] == 'success') {
                $totalPengunjung = $result['data']['totalPengunjung'];
                $totalKunjungan  = $result['data']['totalKunjungan'];
                $rataRataSurvey  = $result['data']['rataRataSurvey'];
            }
        }
    } catch (\Exception $e) {
        Log::error('Gagal mengambil statistik: ' . $e->getMessage());
        // Data tetap 0 agar halaman tidak crash
    }

    return view('guest.landing', compact('totalPengunjung', 'totalKunjungan', 'rataRataSurvey'));
}

    // ==========================================
    // 2. FORM KUNJUNGAN
    // ==========================================
    public function formKunjungan() {
    // AMBIL DARI GOOGLE SHEETS
        $keperluan_master = $this->fetchSheetsData('master_keperluan');

        // Jika Sheets kosong/error, sediakan fallback (pilihan darurat)
        // 2. FALLBACK: Jika Sheets kosong/error/koneksi putus
        if (empty($keperluan_master)) {
            $keperluan_master = [
                (object)['keterangan' => 'Legalisir Ijazah'],
                (object)['keterangan' => 'Konsultasi Akademik'],
                (object)['keterangan' => 'Pengajuan Judul TA'],
                (object)['keterangan' => 'Tamu Dinas / Instansi Luar'],
                (object)['keterangan' => 'Peminjaman Laboratorium'],
                (object)['keterangan' => 'Urusan Administrasi Jurusan']
                // Jangan masukkan 'Lainnya' disini jika di View sudah di-hardcode
            ];
        }

        $master_prodi = [
            (object)['nama' => 'D3 Teknik Listrik', 'jenis' => 'Prodi'],
            (object)['nama' => 'D3 Teknik Elektronika', 'jenis' => 'Prodi'],
            (object)['nama' => 'D3 Teknik Informatika', 'jenis' => 'Prodi'],
            (object)['nama' => 'D4 Teknologi Rekayasa Pembangkit Energi', 'jenis' => 'Prodi'],
            (object)['nama' => 'D4 Sistem Informasi Kota Cerdas', 'jenis' => 'Prodi'],
            (object)['nama' => 'Lainnya (Umum/Tamu Luar)', 'jenis' => 'Umum']
        ];

        return view('guest.form-kunjungan', compact('keperluan_master', 'master_prodi'));
    }

// ==========================================
    // PERBAIKAN: CEK DATA LANGSUNG KE GOOGLE SHEETS
    // ==========================================
public function check(Request $request)
{
    try {
        $scriptUrl = env('GOOGLE_SCRIPT_URL');
        
        if (!$scriptUrl) {
            return response()->json(['status' => 'error', 'message' => 'URL tidak ditemukan.'], 500);
        }

        // Kita gunakan asForm() atau kirim parameter langsung di URL untuk GET
        $response = Http::withHeaders([
            'Accept' => 'application/json',
        ])
        ->timeout(30)
        ->get($scriptUrl . '?action=searchPengunjung&no_id=' . $request->no_id);

        if ($response->successful()) {
            return response()->json($response->json());
        }

        // Jika gagal, kita tangkap body error-nya untuk debug
        return response()->json([
            'status' => 'error', 
            'message' => 'Server Google tidak merespon.',
            'debug' => $response->body() 
        ], 500);

    } catch (\Exception $e) {
        return response()->json([
            'status' => 'error', 
            'message' => 'Koneksi terputus: ' . $e->getMessage()
        ], 500);
    }
}

    public function storeKunjungan(Request $request) {
        $request->validate([
            'identitas_no' => 'required',
            'nama_lengkap' => 'required',
            'asal_instansi' => 'required',
            'keperluan'    => 'required', 
        ]);

        $keperluanFinal = $request->keperluan === 'Lainnya' ? $request->keperluan_lainnya : $request->keperluan;
        $today = date('Y-m-d');
        $hari = Carbon::now()->locale('id')->isoFormat('dddd');
        $detikUnik = str_pad(time() % 1000, 3, '0', STR_PAD_LEFT);
        $nomorKunjungan = 'C0-' . date('Ymd') . '-' . $detikUnik; 

        try {
            $scriptUrl = env('GOOGLE_SCRIPT_URL'); 

            // Simpan ke sheet 'bukutamu'
            Http::timeout(10)->post($scriptUrl, [
                'action'    => 'append',
                'sheetName' => 'bukutamu',
                'data'      => [$nomorKunjungan, $today, $hari, $request->nama_lengkap, $request->asal_instansi, $keperluanFinal]
            ]);

            // Simpan/Update ke sheet 'pengunjung'
            // Gunakan action 'upsertPengunjung' agar tidak double datanya di sheet
            Http::timeout(10)->post($scriptUrl, [
                'action'    => 'upsertPengunjung',
                'data'      => [
                    $request->identitas_no, 
                    $request->nama_lengkap, 
                    $request->asal_instansi, 
                    $today
                ]
            ]);

            $dataKunjungan = [
                'hari_kunjungan' => $hari,
                'tanggal'        => $today,
                'keperluan'      => $keperluanFinal,
                'nama_lengkap'   => $request->nama_lengkap,
                'identitas_no'   => $request->identitas_no,
                'asal_instansi'  => $request->asal_instansi,
            ];

            return redirect()->route('guest.konfirmasi', ['id' => $nomorKunjungan])
                             ->with('kunjungan_data', $dataKunjungan)
                             ->with('nama_tamu', $request->nama_lengkap);

        } catch (\Exception $e) {
            return back()->with('error', 'Gagal simpan data.');
        }
    }

    public function halamanKonfirmasi($id) {
        $data = session('kunjungan_data');

        // Mapping objek agar Blade tidak error
        $kunjungan = (object)[
            'id' => $id,
            'nomor_kunjungan' => $id,
            'hari_kunjungan' => $data['hari_kunjungan'] ?? Carbon::now()->locale('id')->isoFormat('dddd'),
            'tanggal' => $data['tanggal'] ?? date('Y-m-d'),
            'keperluan' => $data['keperluan'] ?? '-',
            'pengunjung' => (object)[
                'nama_lengkap' => $data['nama_lengkap'] ?? session('nama_tamu', 'Tamu'),
                'identitas_no' => $data['identitas_no'] ?? '-',
                'asal_instansi' => $data['asal_instansi'] ?? '-',
            ]
        ];

        return view('guest.konfirmasi', compact('kunjungan'));
    }

    // ==========================================
    // 4. FORM SURVEY
    // ==========================================
    public function formSurvey($id) {
        $nama_tamu = session('nama_tamu', 'Tamu');

        $pertanyaan = [
            'Kecepatan Pelayanan' => [(object)['id' => 1, 'pertanyaan' => 'Bagaimana kecepatan petugas dalam memberikan pelayanan?']],
            'Sikap Petugas'       => [(object)['id' => 2, 'pertanyaan' => 'Bagaimana keramahan dan kesopanan petugas saat melayani?']],
            'Kualitas Informasi'  => [(object)['id' => 3, 'pertanyaan' => 'Apakah petugas memberikan informasi atau solusi yang jelas?']],
            'Sarana & Prasarana'  => [(object)['id' => 4, 'pertanyaan' => 'Bagaimana kenyamanan dan kebersihan fasilitas pelayanan?']],
            'Kepuasan Umum'       => [(object)['id' => 5, 'pertanyaan' => 'Seberapa puas Anda dengan pelayanan kami secara keseluruhan?']],
        ];

        $kunjungan = (object)['id' => $id];

        return view('guest.form-survey', compact('pertanyaan', 'kunjungan', 'nama_tamu'));
    }

    // ==========================================
    // 5. SUBMIT SURVEY (SIMPAN KE SHEET 'survey')
    // ==========================================
    public function storeSurvey(Request $request, $id) {
        $request->validate([
            'jawaban' => 'required|array',
            'kritik_saran' => 'nullable'
        ]);

        try {
            $jawaban = collect($request->jawaban)->values()->all();
            $nama_tamu = $request->input('nama_tamu', 'Anonim');
            $scriptUrl = env('GOOGLE_SCRIPT_URL'); 

            Http::timeout(10)->post($scriptUrl, [
                'action'    => 'append',
                'sheetName' => 'survey',
                'data'      => [
                    date('Y-m-d H:i:s'), // Waktu Isi
                    $id,                 // ID Kunjungan (Ref)
                    $nama_tamu,          // Nama Pengunjung
                    $jawaban[0] ?? 0,    // P1
                    $jawaban[1] ?? 0,    // P2
                    $jawaban[2] ?? 0,    // P3
                    $jawaban[3] ?? 0,    // P4
                    $jawaban[4] ?? 0,    // P5
                    $request->kritik_saran ?? '-'
                ]
            ]);

            return redirect()->route('guest.index')->with('success', 'Terima kasih! Penilaian Anda sangat berarti bagi kami.');
            
        } catch (\Exception $e) {
            Log::error('Survey Error: ' . $e->getMessage());
            return redirect()->route('guest.index')->with('error', 'Penilaian gagal dikirim ke server.');
        }
    }

private function fetchSheetsData($sheetName = 'master_keperluan')
    {
        try {
            $scriptUrl = env('GOOGLE_SCRIPT_URL');
            
            if (!$scriptUrl) {
                Log::warning("GOOGLE_SCRIPT_URL belum diset di .env");
                return [];
            }

            $response = Http::timeout(5)->get($scriptUrl, [
                'action' => 'read',
                'sheet'  => $sheetName
            ]);

            if ($response->successful()) {
                $json = $response->json();
                $rows = $json['data'] ?? []; // Asumsi format JSON: { "data": [ ["id", "ket"], ... ] }
                $result = [];

                foreach ($rows as $index => $row) {
                    // Skip Header (index 0)
                    if ($index === 0) continue;
                    
                    // Pastikan row memiliki data keterangan (index 1)
                    if (!empty($row[1])) {
                        $result[] = (object) [
                            'id' => $row[0] ?? uniqid(),
                            'keterangan' => trim($row[1]) // Trim spasi berlebih
                        ];
                    }
                }
                return $result;
            }
        } catch (\Exception $e) {
            Log::error("Gagal fetch $sheetName: " . $e->getMessage());
        }
        return [];
    }
}