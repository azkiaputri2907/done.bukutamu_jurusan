<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    protected $table = 'kunjungan';
    // nomor_kunjungan tidak masuk fillable karena diisi oleh Trigger MySQL
    protected $fillable = ['pengunjung_id', 'keperluan', 'hari_kunjungan', 'tanggal'];

    // Tambahkan ini di dalam setiap class Model
protected $dateFormat = 'Y-m-d';

// Agar Laravel otomatis mengisi tanggal saat create
public $timestamps = true;

    public function pengunjung()
    {
        return $this->belongsTo(Pengunjung::class, 'pengunjung_id');
    }

    public function survey()
    {
        return $this->hasOne(Survey::class, 'kunjungan_id');
    }
}