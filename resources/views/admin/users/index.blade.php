@extends('layouts.admin')

@section('content')

{{-- Script Pendukung --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Header Section --}}
{{-- Perubahan: Padding & Alignment yang lebih fleksibel di mobile --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8" x-data="{ addUserModal: false }">
    <div>
        <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 tracking-tight">Akun Pengguna</h2>
        <p class="text-xs md:text-sm text-gray-500 font-medium">Akses untuk akun administrator sistem.</p>
    </div>

    {{-- Uncomment jika ingin mengaktifkan tombol tambah user --}}
    {{-- 
    <button @click="addUserModal = true" class="w-full sm:w-auto flex items-center justify-center gap-2 bg-gradient-to-r from-[#3366ff] to-[#a044ff] text-white px-6 py-3 rounded-xl text-sm font-bold shadow-lg shadow-blue-200 hover:scale-105 transition-transform">
        <i class="fas fa-plus-circle"></i>
        <span>Tambah User</span>
    </button> 
    --}}
    
    {{-- MODAL TAMBAH USER (Tetap sama, sudah cukup responsif) --}}
    <div x-show="addUserModal" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.away="addUserModal = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden">
            {{-- Form Content... --}}
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
                    {{-- Email disembunyikan di mobile sangat kecil, muncul di MD --}}
                    <th class="hidden md:table-cell px-8 py-5">Email</th>
                    <th class="px-4 md:px-8 py-5 text-center">Role</th>
                    <th class="px-4 md:px-8 py-5 text-center text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($users as $user)
                <tr class="hover:bg-gray-50/80 transition duration-150" x-data="{ editUserModal: false, detailUserModal: false }">
                    {{-- Kolom Pengguna --}}
                    <td class="px-4 md:px-8 py-5">
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="shrink-0 w-8 h-8 md:w-10 md:h-10 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-xs md:text-sm shadow-sm">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="flex flex-col">
                                <span class="font-bold text-gray-700 text-xs md:text-sm leading-tight">{{ $user->name }}</span>
                                {{-- Email muncul di bawah nama hanya saat tampilan mobile --}}
                                <span class="md:hidden text-[10px] text-gray-400 italic font-medium truncate max-w-[120px]">{{ $user->email }}</span>
                            </div>
                        </div>
                    </td>

                    {{-- Kolom Email (Hanya muncul di desktop/tablet) --}}
                    <td class="hidden md:table-cell px-8 py-5 text-sm text-gray-500 font-medium italic">
                        {{ $user->email }}
                    </td>

                    {{-- Kolom Role --}}
                    <td class="px-4 md:px-8 py-5 text-center">
                        @php
                            $roleNama = $user->role->nama_role ?? '';
                            $color = match($roleNama) {
                                'Administrator' => 'text-red-600 bg-red-50 border-red-100',
                                'Staff' => 'text-green-600 bg-green-50 border-green-100',
                                default => 'text-gray-500 bg-gray-50 border-gray-100'
                            };
                        @endphp
                        <span class="text-[8px] md:text-[10px] font-black uppercase px-2 md:px-3 py-1 rounded-lg border {{ $color }} whitespace-nowrap">
                            {{ $roleNama ?: 'No Role' }}
                        </span>
                    </td>

                    {{-- Kolom Aksi --}}
                    <td class="px-4 md:px-8 py-5">
                        <div class="flex justify-end md:justify-center items-center gap-2 md:gap-3">
                            {{-- Button Lihat --}}
                            <button @click="detailUserModal = true" class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all flex items-center justify-center shadow-sm">
                                <i class="fas fa-eye text-[10px] md:text-xs"></i>
                            </button>

                            @php
                                $isLocked = (Auth::id() === $user->id) || ($roleNama === 'Administrator') || ($roleNama === 'Ketua Jurusan');
                            @endphp

                            @if(!$isLocked)
                                <form id="delete-user-{{ $user->id }}" action="{{ route('admin.users.destroy', $user->id) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDeleteUser('{{ $user->id }}', '{{ $user->name }}')"
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

                        {{-- MODAL DETAIL (Full screen di mobile, modal di desktop) --}}
                        <div x-show="detailUserModal" style="display: none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 text-left">
                             <div @click.away="detailUserModal = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden animate-in fade-in slide-in-from-bottom-4 duration-300">
                                {{-- Content Modal Detail Tetap Sama --}}
                                <div class="bg-gradient-to-br from-indigo-600 to-purple-700 p-8 text-white flex flex-col items-center text-center">
                                    <div class="w-20 h-20 rounded-3xl bg-white/20 backdrop-blur-md flex items-center justify-center text-3xl font-black border border-white/30 mb-4 rotate-3 shadow-xl">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <h3 class="text-xl font-extrabold">{{ $user->name }}</h3>
                                    <p class="text-indigo-200 text-[10px] font-black uppercase tracking-widest mt-1">{{ $roleNama ?: 'Staff' }}</p>
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

{{-- SweetAlert Logic (Tetap Sama) --}}
<script>
    function confirmDeleteUser(id, nama) {
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
                document.getElementById('delete-user-' + id).submit();
            }
        })
    }
</script>

@endsection