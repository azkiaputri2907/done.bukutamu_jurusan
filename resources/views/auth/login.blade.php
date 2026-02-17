@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-10 px-4 relative overflow-hidden">
    
    {{-- Background Blobs (Dari Kode 2 - Agar tetap estetis) --}}
    <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-blue-400/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob"></div>
    <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-pink-400/20 rounded-full blur-3xl mix-blend-multiply opacity-70 animate-blob animation-delay-2000"></div>

    {{-- Card Container (Style Kode 1) --}}
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl overflow-hidden relative z-10 border border-white/50 backdrop-blur-sm">
        
        {{-- Header Gradient (Style Kode 1) --}}
        <div class="bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 pb-12 pt-8 px-4 text-center text-white relative"> 
            {{-- Hiasan Pattern Overlay --}}
            <div class="absolute inset-0 bg-white/5 opacity-30" style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;"></div>
            
            <div class="relative z-10">
                <div class="bg-white/20 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4 backdrop-blur-md shadow-inner ring-2 ring-white/30">
                    <img src="{{ asset('images/logo_poliban.png') }}" alt="Logo Poliban" class="h-10 w-auto">
                </div>
                <h1 class="text-2xl font-bold tracking-wide font-sans">Login Internal</h1>
                <p class="text-sm opacity-90 mt-1 font-medium">Khusus Admin & Ketua Jurusan</p>
            </div>
        </div>

        {{-- Form Body --}}
        <div class="p-8 -mt-6 bg-white rounded-t-[2rem] relative z-20">
            
            {{-- Alert Error (Style Kode 1) --}}
            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-100 rounded-xl p-4 flex items-start gap-3 shadow-sm relative overflow-hidden">
                <div class="absolute top-0 left-0 w-1.5 h-full bg-gradient-to-b from-red-400 to-red-600 rounded-l-xl"></div>
                
                <div class="shrink-0 text-red-500 mt-0.5 bg-white p-1.5 rounded-full shadow-sm border border-red-100">
                    <i class="fas fa-exclamation-triangle text-sm"></i>
                </div>

                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-800 tracking-wide">Login Gagal</h3>
                    <p class="text-xs text-red-600 mt-1 leading-relaxed font-medium">
                        {{ $errors->first() }}
                    </p>
                </div>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf
                
                {{-- Role Selection (Style Kode 1) --}}

                {{-- Input Email --}}
                <div class="mb-5">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Email Address</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-envelope text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                        </div>
                        <input type="email" name="email" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white text-sm font-medium" placeholder="nama@poliban.ac.id" value="{{ old('email') }}" required autofocus>
                    </div>
                </div>

                {{-- Input Password --}}
                <div class="mb-8">
                    <label class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400 group-focus-within:text-blue-500 transition-colors"></i>
                        </div>
                        <input type="password" name="password" class="w-full pl-10 pr-4 py-3 border-2 border-gray-200 rounded-xl focus:outline-none focus:border-blue-500 transition-colors bg-gray-50 focus:bg-white text-sm font-medium" placeholder="••••••••" required>
                    </div>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-600 text-white font-bold py-3.5 rounded-xl shadow-lg hover:shadow-xl hover:opacity-95 transform transition hover:-translate-y-0.5 duration-200 flex items-center justify-center gap-2">
                    <span>Masuk</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
            </form>

            {{-- Footer Link --}}
            <div class="mt-8 text-center pt-6 border-t border-gray-100">
                <a href="{{ url('/') }}" class="text-sm text-gray-400 hover:text-blue-600 transition-colors flex items-center justify-center gap-2 font-medium">
                    <i class="fas fa-long-arrow-alt-left"></i>
                    Kembali ke Halaman Tamu
                </a>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob {
        animation: blob 7s infinite;
    }
    .animation-delay-2000 {
        animation-delay: 2s;
    }
</style>
@endsection