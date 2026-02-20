<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. MASTER ROLE
        Schema::create('master_role', function (Blueprint $table) {
            $table->id();
            $table->string('nama_role'); 
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });

        // 2. MASTER USER
        Schema::create('master_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('master_role');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('foto')->nullable(); // <-- TAMBAHKAN BARIS INI
            $table->rememberToken();
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });

        // 3. MASTER PRODI INSTANSI
        Schema::create('master_prodi_instansi', function (Blueprint $table) {
            $table->id();
            $table->string('nama'); 
            $table->enum('jenis', ['Prodi', 'Instansi']);
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });

        // 5. MASTER ASPEK SURVEY
        Schema::create('master_aspek_survey', function (Blueprint $table) {
            $table->id();
            $table->string('nama_aspek'); 
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });

        // 6. MASTER PERTANYAAN
        Schema::create('master_pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('aspek_id')->constrained('master_aspek_survey');
            $table->string('pertanyaan');
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });

        // 4. MASTER KEPERLUAN (Tambahkan ini!)
        Schema::create('master_keperluan', function (Blueprint $table) {
            $table->id();
            $table->string('keterangan');
            $table->date('created_at')->nullable();
            $table->date('updated_at')->nullable();
        });

        // --- TABEL TRANSAKSI ---

// 7. PENGUNJUNG
    Schema::create('pengunjung', function (Blueprint $table) {
        $table->id();
        $table->string('identitas_no')->unique(); 
        $table->string('nama_lengkap');
        $table->string('asal_instansi');
        $table->string('no_telpon')->nullable(); 
        $table->date('created_at')->nullable(); // Ubah ke date
        $table->date('updated_at')->nullable(); // Ubah ke date
    });

    // 8. KUNJUNGAN
    Schema::create('kunjungan', function (Blueprint $table) {
        $table->id();
        $table->string('nomor_kunjungan')->unique(); 
        $table->foreignId('pengunjung_id')->constrained('pengunjung')->onDelete('cascade');
        $table->string('keperluan'); 
        $table->string('hari_kunjungan');
        $table->date('tanggal'); // Ini sudah date
        $table->date('created_at')->nullable();
        $table->date('updated_at')->nullable();
    });

    // 9. SURVEY
    Schema::create('survey', function (Blueprint $table) {
        $table->id();
        $table->foreignId('kunjungan_id')->constrained('kunjungan')->onDelete('cascade');
        $table->text('kritik_saran')->nullable();
        $table->date('created_at')->nullable();
        $table->date('updated_at')->nullable();
    });

    // 10. DETAIL SURVEY
    Schema::create('detail_survey', function (Blueprint $table) {
        $table->id();
        $table->foreignId('survey_id')->constrained('survey')->onDelete('cascade');
        $table->integer('p1');
        $table->integer('p2');
        $table->integer('p3');
        $table->integer('p4');
        $table->integer('p5');
        $table->date('created_at')->nullable();
        $table->date('updated_at')->nullable();
    });

        // --- TRIGGER AUTO NUMBER (MySQL) ---
        DB::unprepared('
            CREATE TRIGGER tr_generate_no_kunjungan BEFORE INSERT ON kunjungan
            FOR EACH ROW
            BEGIN
                DECLARE last_urutan INT;
                DECLARE today_str VARCHAR(8);
                DECLARE new_urutan VARCHAR(3);
                
                SET today_str = DATE_FORMAT(NEW.tanggal, "%Y%m%d");
                
                SELECT IFNULL(MAX(CAST(RIGHT(nomor_kunjungan, 3) AS UNSIGNED)), 0) 
                INTO last_urutan 
                FROM kunjungan 
                WHERE DATE_FORMAT(tanggal, "%Y%m%d") = today_str;
                
                SET new_urutan = LPAD(last_urutan + 1, 3, "0");
                SET NEW.nomor_kunjungan = CONCAT("C0-", today_str, "-", new_urutan);
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS tr_generate_no_kunjungan');
        Schema::dropIfExists('detail_survey');
        Schema::dropIfExists('survey');
        Schema::dropIfExists('kunjungan');
        Schema::dropIfExists('pengunjung');
        Schema::dropIfExists('master_pertanyaan');
        Schema::dropIfExists('master_aspek_survey');
        Schema::dropIfExists('master_keperluan');
        Schema::dropIfExists('master_prodi_instansi');
        Schema::dropIfExists('master_user');
        Schema::dropIfExists('master_role');
    }
};