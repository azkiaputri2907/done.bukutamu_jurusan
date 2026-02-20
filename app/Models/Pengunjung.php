<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengunjung extends Model
{
    protected $table = 'pengunjung';
    protected $fillable = ['identitas_no', 'nama_lengkap', 'asal_instansi', 'no_telpon'];
// Tambahkan ini di dalam setiap class Model
protected $dateFormat = 'Y-m-d';

// Agar Laravel otomatis mengisi tanggal saat create
public $timestamps = true;
    public function kunjungan()
    {
        return $this->hasMany(Kunjungan::class, 'pengunjung_id');
    }
}