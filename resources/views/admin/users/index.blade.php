@extends('layouts.admin')

@section('content')

{{-- Script Pendukung --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Script Pendukung --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- === HEADER SECTION === --}}
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            {{-- Breadcrumb Modern --}}
            <nav class="flex items-center gap-2 mb-3">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Manajemen User</span>
            </nav>
            
            {{-- Title --}}
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                Akses <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Pengguna</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Kelola hak akses dan kredensial administrator sistem secara terpusat.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            {{-- Card Total User Terintegrasi --}}
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total User</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-gray-800">{{ count($users) }}</span>
                        <span class="text-xs font-bold text-gray-400 italic">akun</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                    <i class="fas fa-users"></i>
                </div>
            </div>
            
            {{-- Refresh/Reload Button --}}
            <a href="{{ route('admin.users') }}" 
               class="w-14 h-14 bg-white text-gray-500 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 hover:shadow-md transition-all duration-300 group">
                <i class="fas fa-sync-alt text-lg group-hover:rotate-180 transition-transform duration-500"></i>
            </a>
        </div>
    </div>
</div>

{{-- Content Table --}}
<div class="bg-white rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto overflow-y-hidden">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-[9px] md:text-[10px] uppercase tracking-[0.15em] text-gray-400 font-black">
                    <th class="px-4 md:px-8 py-5">Pengguna</th>
                    <th class="hidden md:table-cell px-8 py-5">Email</th>
                    <th class="px-4 md:px-8 py-5 text-center">Role</th>
                    <th class="px-4 md:px-8 py-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                @php
                    $roleId = (int)$user->role_id;
                    
                    // Definisi Nama Display
                    $roleDisplay = match($roleId) {
                        1 => 'Administrator',
                        2 => 'Ketua Jurusan',
                        3 => 'Admin Prodi',
                        default => 'Staff'
                    };

                    // Definisi Warna
                    $color = match($roleId) {
                        1 => 'text-red-600 bg-red-50 border-red-100',
                        2 => 'text-purple-600 bg-purple-50 border-purple-100',
                        3 => 'text-blue-600 bg-blue-50 border-blue-100',
                        default => 'text-gray-500 bg-gray-50 border-gray-100'
                    };

                    // Status Lock (Proteksi hapus)
                    $isLocked = (session('user_email') === $user->email) || in_array($roleId, [1, 2, 3]);
                @endphp

                <tr class="hover:bg-gray-50/80 transition duration-150" x-data="{ detailUserModal: false }">
                    {{-- Kolom Pengguna --}}
                    <td class="px-4 md:px-8 py-5">
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="shrink-0 w-8 h-8 md:w-10 md:h-10 rounded-full overflow-hidden shadow-sm border border-gray-100">
                                @if($user->foto && file_exists(public_path($user->foto)))
                                    <img src="{{ asset($user->foto) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs md:text-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-700 text-xs md:text-sm leading-tight">{{ $user->name }}</span>
                                <span class="md:hidden text-[10px] text-gray-400 italic font-medium truncate max-w-[120px]">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom Email --}}
                    <td class="hidden md:table-cell px-8 py-5 text-sm text-gray-500 font-medium italic">
                        {{ $user->email }}
                    </td>

                    {{-- Kolom Role --}}
                    <td class="px-4 md:px-8 py-5 text-center">
                        <span class="text-[8px] md:text-[10px] font-black uppercase px-2 md:px-3 py-1 rounded-lg border {{ $color }} whitespace-nowrap">
                            {{ $roleDisplay }}
                        </span>
                    </td>

                    {{-- Kolom Aksi --}}
                    <td class="px-4 md:px-8 py-5">
                        <div class="flex justify-end md:justify-center items-center gap-2 md:gap-3">
                            <button @click="detailUserModal = true" class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                <i class="fas fa-eye text-[10px] md:text-xs"></i>
                            </button>

                            @if(!$isLocked)
                                <form id="delete-user-{{ $user->email }}" action="{{ route('admin.users.destroy', ['email' => $user->email]) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDeleteUser('{{ $user->email }}', '{{ $user->name }}')"
                                            class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                        <i class="fas fa-trash text-[10px] md:text-xs"></i>
                                    </button>
                                </form>
                            @else
                                <button class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-gray-100 text-gray-400 cursor-not-allowed flex items-center justify-center">
                                    <i class="fas fa-user-lock text-[10px] md:text-xs"></i>
                                </button>
                            @endif
                        </div>

                        {{-- MODAL DETAIL --}}
                        <div x-show="detailUserModal" 
                             x-transition:enter="transition ease-out duration-300"
                             x-transition:enter-start="opacity-0 scale-90"
                             x-transition:enter-end="opacity-100 scale-100"
                             style="display: none;" 
                             class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 text-left">
                            
                            <div @click.away="detailUserModal = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden">
                                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 p-8 text-white flex flex-col items-center text-center">
                                    {{-- Foto Detail --}}
                                    <div class="w-20 h-20 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center overflow-hidden border border-white/30 mb-4 rotate-3 shadow-xl">
                                        @if($user->foto && file_exists(public_path($user->foto)))
                                            <img src="{{ asset($user->foto) }}" class="w-full h-full object-cover">
                                        @else
                                            <span class="text-3xl font-black">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                        @endif
                                    </div>
                                    <h3 class="text-xl font-extrabold">{{ $user->name }}</h3>
                                    <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest mt-1">{{ $roleDisplay }}</p>
                                </div>
                                
                                <div class="p-6 md:p-8 space-y-4">
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl">
                                        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0"><i class="fas fa-envelope"></i></div>
                                        <div class="min-w-0">
                                            <p class="text-[9px] font-black text-gray-400 uppercase">Email Address</p>
                                            <p class="text-xs md:text-sm font-bold text-gray-700 truncate">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl">
                                        <div class="w-10 h-10 rounded-xl bg-green-100 text-green-600 flex items-center justify-center shrink-0"><i class="fas fa-shield-alt"></i></div>
                                        <div>
                                            <p class="text-[9px] font-black text-gray-400 uppercase">Account Status</p>
                                            <p class="text-xs md:text-sm font-bold text-green-600">Terverifikasi & Aktif</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6 bg-gray-50 flex justify-center">
                                    <button @click="detailUserModal = false" class="w-full py-3 bg-white text-gray-600 rounded-xl text-xs font-bold shadow-sm hover:bg-gray-100 transition-all border border-gray-200">Tutup Detail</button>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDeleteUser(email, nama) {
        Swal.fire({
            title: 'Hapus User?',
            text: "Akses login " + nama + " akan ditarik.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: { popup: 'rounded-[1.5rem] md:rounded-[2rem]' }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-user-' + email).submit();
            }
        })
    }
</script>

@endsection