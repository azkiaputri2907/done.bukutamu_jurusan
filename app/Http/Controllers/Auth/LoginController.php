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
            // Simpan data user hasil return GAS ke session
            session(['user' => $result['user']]);
            return redirect()->route('admin.dashboard')->with('success', 'Selamat datang, ' . $result['user']['name']);
        }

        return back()->with('error', 'Email atau password salah.');
    }

    public function logout() {
        session()->forget('user');
        session()->flush();
        return redirect()->route('guest.index');
    }
}