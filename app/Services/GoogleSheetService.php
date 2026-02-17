<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleSheetService
{
    protected $webAppUrl;

    public function __construct()
    {
        // Pastikan variabel ini ada di .env Anda
        $this->webAppUrl = env('GOOGLE_SCRIPT_URL');
    }

    public function loginCheck($email, $password)
    {
        try {
            // Mengirim request POST ke GAS
            $response = Http::withoutVerifying()->post($this->webAppUrl, [
                'action'   => 'login', // Harus 'login' sesuai dengan Apps Script
                'email'    => $email,
                'password' => $password
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error("GAS Response Error: " . $response->body());
        } catch (\Exception $e) {
            Log::error("Koneksi ke Apps Script Gagal: " . $e->getMessage());
        }

        return ['status' => 'error', 'message' => 'Gagal terhubung ke server database.'];
    }
}