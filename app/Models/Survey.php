<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $table = 'survey'; // Pastikan nama tabel benar
    protected $fillable = ['kunjungan_id', 'saran', 'kritik_saran'];

    // Relasi ke tabel detail_survey
    public function detail()
    {
        // 'survey_id' adalah kolom di tabel detail_survey yang nyambung ke id di tabel survey
        return $this->hasOne(DetailSurvey::class, 'survey_id');
    }

    public function kunjungan()
    {
        return $this->belongsTo(Kunjungan::class);
    }
}