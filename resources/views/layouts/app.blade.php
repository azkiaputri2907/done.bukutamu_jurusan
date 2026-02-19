<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Buku Tamu Elektro</title>

    {{-- TAMBAHKAN LOGO DISINI (Favicon) --}}
    <link rel="shortcut icon" href="{{ asset('images/logo_poliban.png') }}" type="image/x-icon">
    <link rel="icon" href="{{ asset('images/logo_poliban.png') }}" type="image/x-icon">

    {{-- CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        /* Custom Scrollbar - Tetap dipertahankan agar estetik */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        
        .animate__faster { --animate-duration: 0.3s; }
    </style>

    {{-- Jika tidak menggunakan Vite di server production, baris ini bisa di-comment --}}
    @vite(['resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    
    {{-- Container Utama --}}
    <div id="app" class="min-h-screen flex flex-col">
        
        {{-- Main Content --}}
        {{-- Tidak ada padding/margin paksa disini, agar landing page bisa full width --}}
        <main class="flex-1 w-full">
            @yield('content')
        </main>

        {{-- Footer (Opsional: Bisa ditambahkan disini jika ingin tampil di semua halaman publik) --}}
        {{-- <footer class="bg-white py-4 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Buku Tamu Poliban
        </footer> --}}

    </div>

    {{-- Script Alert Modern (Untuk Notifikasi Sukses Input Tamu) --}}
    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: '<span class="text-3xl font-bold text-gray-800">Berhasil!</span>',
                html: '<p class="text-gray-600">{{ session("success") }}</p>',
                icon: 'success',
                background: '#ffffffcc',
                backdrop: `rgba(0,0,0,0.4) backdrop-blur-sm`,
                showConfirmButton: true,
                confirmButtonText: 'OK',
                buttonsStyling: false,
                customClass: {
                    popup: 'rounded-[2rem] shadow-2xl border border-white/50',
                    confirmButton: 'bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 text-white font-bold py-3 px-8 rounded-full hover:shadow-lg hover:scale-105 transition-all duration-300 focus:outline-none',
                    icon: 'text-purple-500'
                },
                showClass: { popup: 'animate__animated animate__fadeInUp animate__faster' },
                hideClass: { popup: 'animate__animated animate__fadeOutDown animate__faster' }
            });
        });
    </script>
    @endif
</body>
</html>