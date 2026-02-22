<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoogleSheetService;

class LoginController extends Controller
{
    protected $sheetService;

    public function __construct(GoogleSheetService $sheetService) {
        $this->sheetService = $sheetService;
    }

    public function showLoginForm() {
        if (session()->has('user')) {
            return redirect()->route('admin.dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request) {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $result = $this->sheetService->loginCheck($request->email, $request->password);

        if (isset($result['status']) && $result['status'] === 'success') {
            $userData = $result['user'];

            // Mapping prodi_id ke Nama Lengkap Prodi
            $prodiMapping = [
                'C01' => 'D3 Teknik Listrik',
                'C02' => 'D3 Teknik Elektronika',
                'C03' => 'D3 Teknik Informatika',
                'C04' => 'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi',
                'C05' => 'Sarjana Terapan Sistem Informasi Kota Cerdas',
                'all' => 'Seluruh Program Studi'
            ];

            // Mapping role_nama berdasarkan role_id dari Google Sheet
            $roleMapping = [
                1 => 'Super Admin',
                2 => 'Ketua Jurusan',
                3 => 'Admin Prodi'
            ];

            // Tambahkan informasi tambahan ke array userData
            $userData['prodi_nama'] = $prodiMapping[$userData['prodi_id']] ?? 'Administrator';
            $userData['role_nama'] = $roleMapping[(int)$userData['role_id']] ?? 'User';

            // Simpan data lengkap ke session
            // Ini akan berisi: id, name, email, role_id, foto, prodi_id, prodi_nama, role_nama
            session(['user' => $userData]);

            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang, ' . $userData['name']);
        }

        return back()->with('error', 'Email atau password salah.');
    }

    public function logout() {
        session()->forget('user');
        session()->flush();
        return redirect()->route('guest.index')->with('success', 'Anda telah berhasil logout.');
    }
}