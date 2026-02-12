<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pengunjung;
use App\Models\Kunjungan;
use App\Models\Survey;
use App\Models\DetailSurvey;
use App\Models\MasterPertanyaan;
use App\Models\MasterKeperluan;
use App\Models\MasterProdiInstansi;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GuestController extends Controller
{
    public function index() {
        return view('guest.landing');
    }

    public function formKunjungan() {
        $keperluan_master = MasterKeperluan::all();
        $master_prodi = MasterProdiInstansi::all();
        return view('guest.form-kunjungan', compact('keperluan_master', 'master_prodi'));
    }

    public function checkVisitor(Request $request) {
        $pengunjung = Pengunjung::where('identitas_no', $request->no_id)->first();
        return response()->json($pengunjung);
    }

    public function storeKunjungan(Request $request) {
        $request->validate([
            'identitas_no' => 'required',
            'nama_lengkap' => 'required',
            'asal_instansi' => 'required',
            'keperluan'    => 'required', 
        ]);

        // Filter keperluan jika memilih 'Lainnya'
        $keperluanFinal = $request->keperluan;
        if ($request->keperluan === 'Lainnya') {
            $keperluanFinal = $request->keperluan_lainnya;
        }

        DB::beginTransaction();
        try {
            $today = Carbon::now()->toDateString();
            $dateCode = Carbon::now()->format('Ymd');

            // 1. Simpan/Update data Pengunjung
            $pengunjung = Pengunjung::updateOrCreate(
                ['identitas_no' => $request->identitas_no],
                [
                    'nama_lengkap' => $request->nama_lengkap,
                    'asal_instansi' => $request->asal_instansi,
                    'updated_at' => $today
                ]
            );

            // 2. Simpan Kunjungan
            $kunjungan = Kunjungan::create([
                'pengunjung_id'    => $pengunjung->id,
                'keperluan'        => $keperluanFinal,
                'detail_keperluan' => $request->detail_keperluan,
                'hari_kunjungan'   => Carbon::now()->locale('id')->isoFormat('dddd'),
                'tanggal'          => $today,
                'nomor_kunjungan'  => 'TEMP-' . microtime(true),
                'created_at'       => $today,
                'updated_at'       => $today,
            ]);

            // 3. Update nomor kunjungan format C0-YYYYMMDD-00X
            $kunjungan->update([
                'nomor_kunjungan' => 'C0-' . $dateCode . '-' . str_pad($kunjungan->id, 3, '0', STR_PAD_LEFT)
            ]);

            DB::commit();
            return redirect()->route('guest.konfirmasi', $kunjungan->id);

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Gagal: ' . $e->getMessage());
        }
    }

    public function halamanKonfirmasi($id) {
        $kunjungan = Kunjungan::with('pengunjung')->findOrFail($id);
        return view('guest.konfirmasi', compact('kunjungan'));
    }

    public function formSurvey($id) {
        $kunjungan = Kunjungan::findOrFail($id);
        // Mengelompokkan pertanyaan berdasarkan aspek agar tampilan di View rapi
        $pertanyaan = MasterPertanyaan::with('aspek')->get()->groupBy('aspek.nama_aspek');
        return view('guest.form-survey', compact('kunjungan', 'pertanyaan'));
    }

public function storeSurvey(Request $request, $id) {
    $request->validate([
        'jawaban' => 'required|array',
        'kritik_saran' => 'nullable'
    ]);

    DB::beginTransaction();
    try {
        $today = now(); // Lebih simpel pakai helper Laravel

        // 1. Simpan Header Survey
        // Pastikan nama kolom di database kamu adalah 'saran'. 
        // Kalau di migrasi namanya 'kritik_saran', ubah 'saran' di bawah jadi 'kritik_saran'
        $survey = Survey::create([
            'kunjungan_id' => $id,
            'kritik_saran'        => $request->kritik_saran, 
            'created_at'   => $today,
            'updated_at'   => $today,
        ]);

        // 2. Ambil semua jawaban dan urutkan
        // Kita ambil hanya nilainya saja secara urut
        $jawaban = collect($request->jawaban)->values()->all();

        // 3. Simpan ke DetailSurvey
        // Pastikan $jawaban[0] adalah P1, dst.
        DetailSurvey::create([
            'survey_id' => $survey->id,
            'p1' => $jawaban[0] ?? 0,
            'p2' => $jawaban[1] ?? 0,
            'p3' => $jawaban[2] ?? 0,
            'p4' => $jawaban[3] ?? 0,
            'p5' => $jawaban[4] ?? 0,
            'created_at' => $today,
            'updated_at' => $today,
        ]);

        DB::commit();
        return redirect()->route('guest.landing')->with('success', 'Terima kasih! Penilaian Anda telah kami terima.');
    } catch (\Exception $e) {
        DB::rollback();
        // Log error untuk debug
        Log::error($e->getMessage());
        return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
    }
}
}