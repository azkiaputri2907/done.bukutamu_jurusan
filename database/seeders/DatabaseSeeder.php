<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. ISI MASTER ROLE
        DB::table('master_role')->insert([
            ['nama_role' => 'Administrator'],
            ['nama_role' => 'Ketua Jurusan'],
        ]);

        // 2. ISI MASTER USER
        // Login Admin: admin@poliban.ac.id | admin123
        DB::table('master_user')->insert([
            'role_id' => 1,
            'name' => 'Admin Jurusan Teknik Elektro',
            'email' => 'admin@poliban.ac.id',
            'password' => Hash::make('admin123'),
            'foto' => 'img/avatar_admin.jpg', // Mengarah ke public/img/avatar_admin.jpg
            'created_at' => now()->toDateString(),
            'updated_at' => now()->toDateString(),
        ]);
        
        // Login Ketua
        DB::table('master_user')->insert([
            'role_id' => 2,
            'name' => 'Bpk. M.Helmy Noor, S.ST., M.T.',
            'email' => 'ketua@poliban.ac.id',
            'password' => Hash::make('ketua123'),
            'foto' => 'img/kajur.png', // Mengarah ke public/img/kajur.png
            'created_at' => now()->toDateString(),
            'updated_at' => now()->toDateString(),
        ]);

        // 3. ISI MASTER PRODI / INSTANSI (Disesuaikan dengan pilihan prodi kaku)
        DB::table('master_prodi_instansi')->insert([
            ['nama' => 'D3 Teknik Listrik', 'jenis' => 'Prodi'],
            ['nama' => 'D3 Teknik Elektronika', 'jenis' => 'Prodi'],
            ['nama' => 'D3 Teknik Informatika', 'jenis' => 'Prodi'],
            ['nama' => 'D4 Teknologi Rekayasa Otomasi', 'jenis' => 'Prodi'],
            ['nama' => 'D4 Sistem Informasi Kota Cerdas', 'jenis' => 'Prodi'],
            ['nama' => 'D4 Teknologi Rekayasa Pembangkit Energi', 'jenis' => 'Prodi'],
            ['nama' => 'Instansi Luar / Umum', 'jenis' => 'Instansi'],
        ]);

// 4. ISI MASTER ASPEK SURVEY (Dibuat 5 aspek berbeda)
        $aspekData = [
            ['nama_aspek' => 'Kecepatan'],      // Untuk P1
            ['nama_aspek' => 'Sikap/Etika'],    // Untuk P2
            ['nama_aspek' => 'Kompetensi'],     // Untuk P3
            ['nama_aspek' => 'Sarana Prasarana'], // Untuk P4
            ['nama_aspek' => 'Kualitas Layanan'], // Untuk P5
        ];

        $aspekIds = [];
        foreach ($aspekData as $aspek) {
            $aspekIds[] = DB::table('master_aspek_survey')->insertGetId([
                'nama_aspek' => $aspek['nama_aspek'],
                'created_at' => now()->toDateString(),
                'updated_at' => now()->toDateString(),
            ]);
        }

        // 5. ISI MASTER PERTANYAAN (1 Pertanyaan per Aspek)
        $pertanyaan = [
            [
                'aspek_id' => $aspekIds[0], 
                'pertanyaan' => 'Bagaimana kecepatan petugas dalam memberikan pelayanan?'
            ],
            [
                'aspek_id' => $aspekIds[1], 
                'pertanyaan' => 'Bagaimana keramahan dan kesopanan petugas saat melayani?'
            ],
            [
                'aspek_id' => $aspekIds[2], 
                'pertanyaan' => 'Apakah petugas memberikan informasi atau solusi yang jelas?'
            ],
            [
                'aspek_id' => $aspekIds[3], 
                'pertanyaan' => 'Bagaimana kenyamanan dan kebersihan fasilitas pelayanan?'
            ],
            [
                'aspek_id' => $aspekIds[4], 
                'pertanyaan' => 'Seberapa puas Anda dengan pelayanan kami secara keseluruhan?'
            ],
        ];

        foreach ($pertanyaan as $item) {
            DB::table('master_pertanyaan')->insert([
                'aspek_id' => $item['aspek_id'],
                'pertanyaan' => $item['pertanyaan'],
                'created_at' => now()->toDateString(),
                'updated_at' => now()->toDateString(),
            ]);
        }

        // 6. ISI MASTER KEPERLUAN
        DB::table('master_keperluan')->insert([
            ['keterangan' => 'Legalisir Ijazah'],
            ['keterangan' => 'Konsultasi Akademik'],
            ['keterangan' => 'Pengajuan Judul TA'],
            ['keterangan' => 'Tamu Dinas / Instansi Luar'],
            ['keterangan' => 'Peminjaman Laboratorium'],
            ['keterangan' => 'Urusan Administrasi Jurusan'],
        ]);
    }
}