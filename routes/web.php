<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Auth\LoginController;

/*
|--------------------------------------------------------------------------
| Web Routes - Buku Tamu Jurusan Elektro
|--------------------------------------------------------------------------
*/

// --- 1. SEKTOR PENGUNJUNG (Public) ---
Route::controller(GuestController::class)->group(function () {
    Route::get('/', 'index')->name('guest.index');
    Route::get('/isi-tamu', 'formKunjungan')->name('guest.form');
    Route::post('/simpan-tamu', 'storeKunjungan')->name('guest.store');
    Route::get('/guest/check-pengunjung', 'check')->name('guest.check');
    Route::get('/kunjungan/konfirmasi/{id}', 'halamanKonfirmasi')->name('guest.konfirmasi');
    Route::get('/survey/{id}', 'formSurvey')->name('guest.survey');
    Route::post('/survey-simpan/{id}', 'storeSurvey')->name('guest.survey.store');
});

// --- 2. AUTENTIKASI (Manual Session) ---
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- 3. SEKTOR PANEL ADMIN & KAJUR ---
// Kita tidak menggunakan middleware 'auth' karena pengecekan dilakukan di __construct DashboardController
Route::prefix('admin')->name('admin.')->group(function () {
    
    // Dashboard & Statistik
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // PERBAIKAN DI SINI: Cukup tulis 'check-notification' saja
    Route::get('/check-notification', [DashboardController::class, 'checkNotification'])->name('check-notification');

    // Manajemen Kunjungan, Survey, & Pengunjung (Bisa diakses Admin & Kajur)
    Route::controller(DashboardController::class)->group(function () {
        // Kunjungan
        Route::get('/kunjungan', 'kunjungan')->name('kunjungan');
        Route::post('/kunjungan', 'storeKunjungan')->name('kunjungan.store');
        Route::put('/kunjungan/{id}', 'updateKunjungan')->name('kunjungan.update');
        Route::delete('/kunjungan/{id}', 'destroyKunjungan')->name('kunjungan.destroy');

        // Survey
        Route::get('/survey', 'survey')->name('survey'); 
        Route::put('/survey/update', 'updateSurvey')->name('survey.update');
        Route::delete('/survey/delete', 'destroySurvey')->name('survey.destroy');

        // Pengunjung
        Route::get('/pengunjung', 'pengunjung')->name('pengunjung');
        Route::put('/pengunjung/{id}', 'updatePengunjung')->name('pengunjung.update');
        Route::delete('/pengunjung/{id}', 'destroyPengunjung')->name('pengunjung.destroy');

        // Reporting
        Route::get('/laporan', 'laporan')->name('laporan');
        Route::post('/laporan/export', 'exportLaporan')->name('laporan.export');
        
        // User Management (Proteksi role nanti diatur di dalam Controller)
        Route::get('/users', 'users')->name('users');
        Route::post('/users', 'storeUser')->name('users.store');
        Route::put('/users/{id}', 'updateUser')->name('users.update');
        Route::delete('/users/{id}', 'destroyUser')->name('users.destroy');

        // Keperluan Management
        Route::get('/master/keperluan', 'masterKeperluan')->name('keperluan.index');
        Route::post('/master/keperluan', 'storeKeperluan')->name('keperluan.store');
        Route::put('/master/keperluan/{id}', 'updateKeperluan')->name('keperluan.update');
        Route::delete('/master/keperluan/{id}', 'destroyKeperluan')->name('keperluan.destroy');
    });
});