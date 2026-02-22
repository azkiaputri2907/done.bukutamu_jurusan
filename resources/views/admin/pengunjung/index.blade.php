@extends('layouts.admin')

@section('content')

{{-- Script SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $userSession = session('user');
    $roleId = (int)($userSession['role_id'] ?? 0);
    $isSuperAdmin = ($roleId === 1);
    $prodiUser = $userSession['prodi_nama'] ?? '';
    
    // Sinkronisasi list prodi dengan halaman kunjungan
    $listProdis = ['D3 Teknik Listrik', 'D3 Teknik Elektronika', 'D3 Teknik Informatika', 'Sarjana Terapan Teknologi Rekayasa Pembangkit Energi', 'Sarjana Terapan Sistem Informasi Kota Cerdas', 'Umum'];
@endphp

{{-- === HEADER SECTION === --}}
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            {{-- Breadcrumb Modern --}}
            <nav class="flex items-center gap-2 mb-3">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Master Data</span>
            </nav>
            
            {{-- Title --}}
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                Data <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Pengunjung</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Manajemen identitas dan profil tamu terdaftar.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total User</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-gray-800">{{ count($pengunjung) }}</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                    <i class="fas fa-id-card text-sm"></i>
                </div>
            </div>
            
            <a href="{{ route('admin.pengunjung') }}" 
               class="w-14 h-14 bg-white text-gray-500 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 transition-all group">
                <i class="fas fa-sync-alt text-lg group-hover:rotate-180 transition-transform duration-500"></i>
            </a>
        </div>
    </div>
</div>

{{-- === SEARCH & FILTER SECTION === --}}
<div class="bg-white/60 backdrop-blur-md p-3 rounded-[2rem] shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col md:flex-row gap-3">
        {{-- Form Pencarian --}}
        <form action="{{ route('admin.pengunjung') }}" method="GET" class="relative flex-1 group">
            @if(!$isSuperAdmin)
                <input type="hidden" name="prodi" value="{{ $prodiUser }}">
            @elseif(request('prodi'))
                <input type="hidden" name="prodi" value="{{ request('prodi') }}">
            @endif

            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau No. Identitas..." 
                   class="w-full pl-12 pr-6 py-4 bg-white rounded-2xl border-none outline-none focus:ring-4 focus:ring-indigo-500/10 transition font-medium text-gray-700 placeholder:text-gray-400 shadow-sm">
        </form>
        
        {{-- Filter Prodi --}}
        @if($isSuperAdmin)
        <div class="relative min-w-[280px]">
            <i class="fas fa-filter absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 z-10 pointer-events-none"></i>
            <select onchange="window.location.href=this.value" 
                    class="w-full pl-12 pr-10 py-4 bg-white rounded-2xl border-none text-gray-700 font-bold outline-none cursor-pointer hover:bg-gray-50 transition shadow-sm appearance-none focus:ring-4 focus:ring-indigo-500/10">
                <option value="{{ route('admin.pengunjung') }}">Semua Program Studi</option>
                @foreach($listProdis as $p)
                    <option value="{{ route('admin.pengunjung', ['prodi' => $p, 'search' => request('search')]) }}" 
                        {{ request('prodi') == $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
        </div>
        @else
        <div class="flex items-center px-6 py-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">
                    {{ $prodiUser }}
                </span>
                <i class="fas fa-lock text-indigo-300 text-[10px] ml-1"></i>
            </div>
        </div>
        @endif
        
        @if(request()->anyFilled(['search', 'prodi']))
            <a href="{{ route('admin.pengunjung') }}" class="flex items-center justify-center px-6 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-500 hover:text-white transition-all duration-300 font-bold shadow-sm text-sm">
                <i class="fas fa-times-circle mr-2"></i> Reset
            </a>
        @endif
    </div>
</div>

{{-- Content Table --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] uppercase tracking-wider text-gray-500 font-bold">
                    <th class="px-6 py-4">Informasi Pengunjung</th>
                    <th class="px-6 py-4">Asal Instansi / Prodi</th>
                    <th class="px-6 py-4">Terakhir Berkunjung</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pengunjung as $p)
                <tr class="hover:bg-gray-50/80 transition duration-150" x-data="{ editModalOpen: false, detailModalOpen: false }">
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-black text-sm border border-indigo-100">
                                {{ substr($p->nama_lengkap, 0, 1) }}
                            </div>
                            <div>
                                <span class="block font-bold text-gray-700 text-sm">{{ $p->nama_lengkap }}</span>
                                <span class="text-[10px] text-gray-400 font-semibold font-mono tracking-tighter">{{ $p->identitas_no }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-lg text-[10px] font-bold bg-gray-50 text-gray-600 border border-gray-100 uppercase">
                            {{ $p->asal_instansi ?? 'Umum' }}
                        </span>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-2">
                            <i class="far fa-calendar-alt text-gray-300 text-xs"></i>
                            <span class="text-xs font-semibold text-gray-600">
                                {{ $p->terakhir_kunjungan && $p->terakhir_kunjungan != '-' 
                                    ? \Carbon\Carbon::parse($p->terakhir_kunjungan)->isoFormat('D MMM Y') 
                                    : 'N/A' }}
                            </span>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4 text-center">
                        <div class="flex justify-center items-center gap-2">
                            <button @click="detailModalOpen = true" class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center hover:bg-blue-600 hover:text-white transition-all">
                                <i class="fas fa-eye text-[10px]"></i>
                            </button>

                            {{-- 
                                <button @click="editModalOpen = true" class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center hover:bg-amber-600 hover:text-white transition-all">
                                    <i class="fas fa-edit text-[10px]"></i>
                                </button> --}}
                                @if($isSuperAdmin)
                                <form id="delete-form-{{ $p->identitas_no }}" action="{{ route('admin.pengunjung.destroy', $p->identitas_no) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete('{{ $p->identitas_no }}', '{{ $p->nama_lengkap }}')"
                                            class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center hover:bg-rose-600 hover:text-white transition-all">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- MODAL DETAIL --}}
                        <div x-show="detailModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" x-transition>
                            <div @click.away="detailModalOpen = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden">
                                <div class="px-6 py-5 bg-gradient-to-r from-indigo-600 to-purple-600 text-white">
                                    <h3 class="font-black uppercase tracking-tight text-xs">Profil Pengunjung</h3>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                        <div class="w-12 h-12 rounded-xl bg-white flex items-center justify-center text-lg font-black text-indigo-600 shadow-sm">{{ substr($p->nama_lengkap, 0, 1) }}</div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm">{{ $p->nama_lengkap }}</h4>
                                            <p class="text-[10px] text-gray-400 font-mono">{{ $p->identitas_no }}</p>
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-3">
                                        <div class="p-3 bg-gray-50 rounded-xl">
                                            <span class="block text-[8px] font-black text-gray-400 uppercase">Instansi</span>
                                            <span class="text-[10px] font-bold text-gray-700">{{ $p->asal_instansi ?? '-' }}</span>
                                        </div>
                                        <div class="p-3 bg-gray-50 rounded-xl">
                                            <span class="block text-[8px] font-black text-gray-400 uppercase">Terakhir Masuk</span>
                                            <span class="text-[10px] font-bold text-gray-700">{{ $p->terakhir_kunjungan ?? 'Belum ada' }}</span>
                                        </div>
                                    </div>
                                    <button @click="detailModalOpen = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl font-black text-[10px] uppercase tracking-widest transition">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDIT --}}
                        @if($isSuperAdmin)
                        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" x-transition>
                            <div @click.away="editModalOpen = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm overflow-hidden">
                                <form action="{{ route('admin.pengunjung.update', $p->identitas_no) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="px-6 py-5 bg-amber-500 text-white">
                                        <h3 class="font-black uppercase tracking-tight text-xs">Edit Identitas</h3>
                                    </div>
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" value="{{ $p->nama_lengkap }}" class="w-full border-gray-100 rounded-xl focus:ring-2 focus:ring-amber-500 text-xs bg-gray-50 p-3 font-bold">
                                        </div>
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Update Instansi</label>
                                            <select name="asal_instansi" class="w-full border-gray-100 rounded-xl text-xs bg-gray-50 p-3 font-bold">
                                                @foreach($listProdis as $opt)
                                                    <option value="{{ $opt }}" {{ $p->asal_instansi == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="flex gap-2 pt-2">
                                            <button type="button" @click="editModalOpen = false" class="flex-1 py-3 bg-gray-50 text-gray-400 rounded-xl font-black text-[10px] uppercase tracking-widest transition">Batal</button>
                                            <button type="submit" class="flex-[2] py-3 bg-amber-500 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-amber-200 transition">Update Data</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 text-gray-300 mb-4"><i class="fas fa-user-slash text-xl"></i></div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tidak ada pengunjung terdaftar</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/20 flex justify-between items-center">
        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Database: Cloud Spreadsheet</span>
        <span class="text-[10px] font-bold text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-100">Total: {{ count($pengunjung) }} Record</span>
    </div>
</div>

<script>
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Akun?',
            text: "Profil " + nama + " akan dihapus dari sistem.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            confirmButtonText: 'Hapus Permanen',
            cancelButtonText: 'Batal',
            customClass: {
                popup: 'rounded-[1.5rem]',
                confirmButton: 'rounded-xl px-5 py-2.5 text-xs font-bold uppercase tracking-widest',
                cancelButton: 'rounded-xl px-5 py-2.5 text-xs font-bold uppercase tracking-widest'
            }
        }).then((result) => {
            if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
        })
    }
</script>

@endsection