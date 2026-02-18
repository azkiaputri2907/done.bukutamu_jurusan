<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Buku Tamu Poliban</title>

    {{-- Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    {{-- CSS Libraries --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; scroll-behavior: smooth; }
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 20px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .flatpickr-calendar { margin-top: 8px !important; border-radius: 16px !important; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1) !important; border: 1px solid #f1f5f9 !important; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-[#f8f9fd] text-gray-800 antialiased">

    <div class="flex h-screen overflow-hidden" x-data="{ sideBarOpen: false }">
        
        {{-- SIDEBAR --}}
        <aside :class="sideBarOpen ? 'translate-x-0' : '-translate-x-full'"
               class="fixed inset-y-0 left-0 z-50 w-72 bg-gradient-to-b from-[#ff3366] via-[#a044ff] to-[#3366ff] 
                      lg:m-4 lg:relative lg:translate-x-0 lg:rounded-[2rem] text-white flex flex-col shadow-2xl transition-transform duration-500 ease-in-out">
            
            <div class="p-8">
                <h1 class="font-extrabold text-2xl tracking-tight leading-none">Buku Tamu</h1>
                <p class="text-[10px] opacity-80 uppercase tracking-[0.2em] mt-2 font-medium">Politeknik Negeri Banjarmasin</p>
            </div>

{{-- User Profile Badge --}}
<div class="mx-6 p-4 bg-white/10 rounded-2xl flex items-center gap-3 mb-6 border border-white/5 backdrop-blur-sm">
    <div class="shrink-0">
        {{-- Ganti Auth::user()->foto menjadi session('user')['foto'] --}}
        @if(session('user')['foto'])
            <img src="{{ asset(session('user')['foto']) }}" alt="Profile" class="w-12 h-12 rounded-xl object-cover border-2 border-white/20 shadow-sm">
        @else
            <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center border border-white/10">
                {{-- Ambil inisial dari session --}}
                <span class="font-bold text-xl">{{ substr(session('user')['name'], 0, 1) }}</span>
            </div>
        @endif
    </div>
    <div class="truncate">
        {{-- Ganti nama dan role --}}
        <p class="text-sm font-bold truncate">{{ session('user')['name'] }}</p>
        <p class="text-[10px] opacity-70 uppercase tracking-wider">{{ session('user')['role_nama'] ?? 'User' }}</p>
    </div>
</div>

            {{-- Navigation --}}
            <nav class="flex-1 px-4 space-y-1.5 overflow-y-auto custom-scrollbar">
                <a href="{{ route('admin.dashboard') }}" 
                   class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.dashboard') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
                    <i class="fas fa-th-large w-5 text-center"></i> <span>Dashboard</span>
                </a>

                <div class="pt-4 pb-2 px-4 text-[10px] uppercase tracking-widest opacity-50 font-bold">Data Master</div>

                <a href="{{ route('admin.kunjungan') }}" 
                   class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.kunjungan*') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
                    <i class="fas fa-book-open w-5 text-center"></i> <span>Data Kunjungan</span>
                </a>

                <a href="{{ route('admin.survey') }}" 
                   class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.survey*') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
                    <i class="fas fa-poll w-5 text-center"></i> <span>Data Survey</span>
                </a>

                <a href="{{ route('admin.pengunjung') }}" 
                   class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.pengunjung*') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
                    <i class="fas fa-users w-5 text-center"></i> <span>Pengunjung</span>
                </a>

                <div class="pt-4 pb-2 px-4 text-[10px] uppercase tracking-widest opacity-50 font-bold">Laporan & Setting</div>

<a href="{{ route('admin.laporan') }}" 
   class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.laporan*') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
    <i class="fas fa-file-export w-5 text-center"></i> <span>Laporan</span>
</a>

{{-- Admin Only Area - Menggunakan Session Manual --}}
@if(session('user')['role_nama'] === 'Administrator')
    <a href="{{ route('admin.users') }}" 
       class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.users*') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
        <i class="fas fa-user-cog w-5 text-center"></i> <span>Manajemen User</span>
    </a>
    
    <a href="{{ route('admin.keperluan.index') }}" 
       class="flex items-center gap-3 py-3.5 px-4 rounded-xl transition-all duration-300 {{ request()->routeIs('admin.keperluan*') ? 'bg-white text-[#a044ff] font-bold shadow-lg' : 'opacity-70 hover:opacity-100 hover:bg-white/10' }}">
        <i class="fas fa-tags w-5 text-center"></i> <span>Keperluan</span>
    </a>
@endif
            </nav>

            {{-- Logout --}}
            <div class="p-6 mt-auto">
                <button type="button" onclick="confirmLogout()"
                    class="flex items-center gap-2 text-white/80 hover:text-white transition w-full group cursor-pointer border-none bg-transparent outline-none">
                    <div class="w-8 h-8 rounded-lg bg-white/10 flex items-center justify-center group-hover:bg-red-500 transition-colors">
                        <i class="fas fa-sign-out-alt text-xs"></i> 
                    </div>
                    <span class="font-medium text-sm">Logout</span>
                </button>
            </div>
        </aside>

        {{-- MOBILE OVERLAY --}}
        <div x-show="sideBarOpen" x-cloak @click="sideBarOpen = false" x-transition.opacity class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"></div>

        {{-- CONTENT WRAPPER --}}
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden transition-all duration-500"
             :class="sideBarOpen ? 'lg:scale-100' : ''">
            
            <header class="bg-white/80 backdrop-blur-md border-b border-gray-100 lg:hidden px-6 py-4 flex justify-between items-center sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-gradient-to-tr from-[#ff3366] to-[#a044ff] rounded-lg flex items-center justify-center shadow-md">
                         <i class="fas fa-book text-white text-xs"></i>
                    </div>
                    <span class="font-bold text-gray-800 tracking-tight">Buku Tamu</span>
                </div>
                <button @click.stop="sideBarOpen = true" class="p-2.5 text-[#a044ff] bg-purple-50 rounded-xl shadow-sm active:scale-95 transition-transform">
                    <i class="fas fa-bars text-lg"></i>
                </button>
            </header>

            <main class="flex-1 p-4 md:p-8 lg:p-10 overflow-y-auto custom-scrollbar">
                @if(session('success'))
                    <div class="mb-6 bg-green-50 text-green-700 border border-green-200 rounded-2xl p-4 flex items-center gap-3 shadow-sm">
                        <i class="fas fa-check-circle text-xl animate-bounce"></i>
                        <span class="font-bold text-sm">{{ session('success') }}</span>
                    </div>
                @endif
                
                @yield('content')
            </main>
        </div>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Keluar dari Sistem?',
                text: "Pastikan semua pekerjaan Anda telah disimpan.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3366',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Logout',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                customClass: { popup: 'rounded-[2rem]', confirmButton: 'rounded-xl px-5 py-3 font-bold', cancelButton: 'rounded-xl px-5 py-3 font-bold' }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logout-form').submit();
                }
            })
        }
    </script>
</body>
</html>