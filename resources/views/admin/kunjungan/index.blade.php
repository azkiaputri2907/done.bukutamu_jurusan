@extends('layouts.admin')

@section('content')

@section('content')

{{-- Script SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- === HEADER SECTION === --}}
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            {{-- Breadcrumb Modern --}}
            <nav class="flex items-center gap-2 mb-3">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Data Kunjungan</span>
            </nav>
            
            {{-- Title --}}
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                Riwayat <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Kunjungan</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                {{ (int)session('user')['role_id'] === 1 
                    ? 'Memantau seluruh aktivitas tamu yang masuk ke sistem.' 
                    : 'Riwayat tamu khusus unit layanan ' . session('user')['prodi_nama'] }}
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            {{-- Info Total (Statis/Counter Gaya Baru) --}}
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Record</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-gray-800">{{ count($kunjungan) }}</span>
                        <span class="text-xs font-bold text-gray-400 italic">Tamu</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                    <i class="fas fa-users text-sm"></i>
                </div>
            </div>
            
            {{-- Refresh Button --}}
            <a href="{{ route('admin.kunjungan') }}" 
               class="w-14 h-14 bg-white text-gray-500 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 hover:shadow-md transition-all duration-300 group">
                <i class="fas fa-sync-alt text-lg group-hover:rotate-180 transition-transform duration-500"></i>
            </a>
        </div>
    </div>
</div>

{{-- === SEARCH & FILTER SECTION === --}}
<div class="bg-white/60 backdrop-blur-md p-3 rounded-[2rem] shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col md:flex-row gap-3">
        {{-- Form Pencarian --}}
        <form action="{{ route('admin.kunjungan') }}" method="GET" class="relative flex-1 group">
            {{-- Hidden input untuk menjaga filter prodi saat search --}}
            @if((int)session('user')['role_id'] !== 1)
                <input type="hidden" name="prodi" value="{{ session('user')['prodi_nama'] }}">
            @elseif(request('prodi'))
                <input type="hidden" name="prodi" value="{{ request('prodi') }}">
            @endif

            <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pengunjung..." 
                   class="w-full pl-12 pr-6 py-4 bg-white rounded-2xl border-none outline-none focus:ring-4 focus:ring-indigo-500/10 transition font-medium text-gray-700 placeholder:text-gray-400 shadow-sm">
        </form>
        
        {{-- Filter Prodi --}}
        @if((int)session('user')['role_id'] === 1)
        <div class="relative min-w-[280px]">
            <i class="fas fa-filter absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 z-10 pointer-events-none"></i>
            <select onchange="window.location.href=this.value" 
                    class="w-full pl-12 pr-10 py-4 bg-white rounded-2xl border-none text-gray-700 font-bold outline-none cursor-pointer hover:bg-gray-50 transition shadow-sm appearance-none focus:ring-4 focus:ring-indigo-500/10">
                <option value="{{ route('admin.kunjungan') }}">Semua Program Studi</option>
                @php
                    $prodis = ['D3 Teknik Listrik', 'D3 Teknik Elektronika', 'D3 Teknik Informatika', 'D4 Teknologi Rekayasa Pembangkit Energi', 'D4 Sistem Informasi Kota Cerdas', 'Umum'];
                @endphp
                @foreach($prodis as $p)
                    <option value="{{ route('admin.kunjungan', ['prodi' => $p, 'search' => request('search')]) }}" 
                        {{ request('prodi') == $p ? 'selected' : '' }}>
                        {{ $p }}
                    </option>
                @endforeach
            </select>
            <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
        </div>
        @else
        {{-- Badge Info Admin Prodi (Tampilan Lebih Clean) --}}
        <div class="flex items-center px-6 py-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-2 h-2 rounded-full bg-indigo-500"></div>
                <span class="text-xs font-bold text-indigo-700 uppercase tracking-wider">
                    {{ session('user')['prodi_nama'] }}
                </span>
            </div>
        </div>
        @endif
        
        {{-- Reset Button --}}
        @if(request()->anyFilled(['search', 'prodi']))
            <a href="{{ route('admin.kunjungan') }}" class="flex items-center justify-center px-6 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-500 hover:text-white transition-all duration-300 font-bold shadow-sm">
                <i class="fas fa-times-circle mr-2"></i> Reset
            </a>
        @endif
    </div>
</div>

{{-- Content Card --}}
<div class="bg-white rounded-[1.5rem] md:rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto scrollbar-hide"> {{-- Tambahkan scrollbar-hide jika perlu --}}
        <table class="w-full text-left border-collapse min-w-[600px]"> {{-- min-w agar tabel tidak hancur di mobile sangat kecil --}}
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] md:text-xs uppercase tracking-wider text-gray-500 font-bold">
                    <th class="px-4 md:px-6 py-4">Nomor</th>
                    <th class="px-4 md:px-6 py-4">Pengunjung</th>
                    <th class="px-4 md:px-6 py-4">Keperluan</th>
                    <th class="px-4 md:px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($kunjungan as $row)
                <tr class="hover:bg-gray-50/80 transition duration-150" x-data="{ editModalOpen: false, viewModalOpen: false }">
                    <td class="px-4 md:px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-[10px] font-bold bg-purple-50 text-[#a044ff] border border-purple-100 whitespace-nowrap">
                            #{{ $row->nomor_kunjungan }}
                        </span>
                    </td>

                    <td class="px-4 md:px-6 py-4">
                        <div class="flex items-center gap-2 md:gap-3">
                            <div class="hidden sm:flex w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 items-center justify-center font-bold text-xs uppercase border border-indigo-100 shrink-0">
                                {{ substr($row->nama_lengkap, 0, 1) }}
                            </div>
                            <div class="max-w-[120px] md:max-w-none">
                                <span class="block font-bold text-gray-700 text-xs md:text-sm truncate">{{ $row->nama_lengkap }}</span>
                                <span class="text-[9px] md:text-[10px] text-gray-400 font-medium uppercase tracking-tighter truncate block">{{ $row->asal_instansi ?? 'Umum' }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="px-4 md:px-6 py-4 text-xs md:text-sm text-gray-500">
                        <p class="line-clamp-1 max-w-[150px] md:max-w-xs font-medium" title="{{ $row->keperluan }}">
                            {{ Str::limit($row->keperluan, 30) }}
                        </p>
                    </td>

                    <td class="px-4 md:px-6 py-4">
                        <div class="flex justify-center items-center gap-1.5 md:gap-2">
                            {{-- Tombol Lihat (Semua Bisa) --}}
                            <button @click="viewModalOpen = true" class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center">
                                <i class="fas fa-eye text-[10px]"></i>
                            </button>

                            {{-- MODIFIKASI: Izinkan Role 1 (Super) dan Role Prodi lainnya untuk Edit & Hapus --}}
                            @if(in_array((int)session('user')['role_id'], [1, 2, 3, 4, 5, 6]))
                                {{-- Tombol Edit --}}
                                <button @click="editModalOpen = true" class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center">
                                    <i class="fas fa-edit text-[10px]"></i>
                                </button>

                                {{-- Tombol Hapus --}}
                                <form id="delete-form-{{ $row->nomor_kunjungan }}" action="{{ route('admin.kunjungan.destroy', $row->nomor_kunjungan) }}" method="POST" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="button" onclick="confirmDelete('{{ $row->nomor_kunjungan }}', '{{ $row->nama_lengkap }}')"
                                            class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center">
                                        <i class="fas fa-trash text-[10px]"></i>
                                    </button>
                                </form>
                            @endif
                        </div>

                        {{-- MODAL VIEW DETAIL --}}
                        {{-- Modifikasi: max-w-sm di mobile, rounded lebih kecil sedikit --}}
                        <div x-show="viewModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" style="display: none;" x-transition>
                            <div @click.away="viewModalOpen = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm md:max-w-md overflow-hidden">
                                <div class="px-6 py-5 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex justify-between items-center">
                                    <div>
                                        <h3 class="font-black uppercase tracking-tight text-xs">Detail Kunjungan</h3>
                                        <p class="text-[9px] text-blue-100 font-bold tracking-widest uppercase">ID: #{{ $row->nomor_kunjungan }}</p>
                                    </div>
                                    <button @click="viewModalOpen = false" class="text-white/50 hover:text-white transition px-2 py-1"><i class="fas fa-times"></i></button>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                                        <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center text-base font-black">{{ substr($row->nama_lengkap, 0, 1) }}</div>
                                        <div>
                                            <h4 class="font-bold text-gray-800 text-sm">{{ $row->nama_lengkap }}</h4>
                                            <p class="text-[10px] text-blue-600 font-bold uppercase tracking-wider">{{ $row->asal_instansi ?? 'Umum' }}</p>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50/50 p-4 rounded-2xl border border-gray-100">
                                        <label class="text-[9px] font-black text-gray-400 uppercase tracking-widest block mb-1">Keperluan</label>
                                        <p class="text-xs text-gray-700 font-medium leading-relaxed italic">"{{ $row->keperluan }}"</p>
                                    </div>
                                    <button @click="viewModalOpen = false" class="w-full py-3 bg-gray-100 hover:bg-gray-200 text-gray-500 rounded-xl font-black text-[10px] uppercase tracking-widest transition">Tutup Detail</button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDIT (DINAMIS) --}}
                        @if(in_array((int)session('user')['role_id'], [1, 2, 3, 4, 5, 6]))                        <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" style="display: none;" x-transition>
                            <div @click.away="editModalOpen = false" class="bg-white rounded-[2rem] shadow-2xl w-full max-w-sm md:max-w-md overflow-hidden">
                                <form action="{{ route('admin.kunjungan.update', $row->nomor_kunjungan) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="px-6 py-5 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex justify-between items-center">
                                        <div>
                                            <h3 class="font-black uppercase tracking-tight text-xs">Edit Kunjungan</h3>
                                            <p class="text-[9px] text-amber-100 font-bold tracking-widest uppercase">ID: #{{ $row->nomor_kunjungan }}</p>
                                        </div>
                                        <button type="button" @click="editModalOpen = false" class="text-white/50 hover:text-white transition px-2 py-1"><i class="fas fa-times"></i></button>
                                    </div>

                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Nama Pengunjung</label>
                                            <input type="text" value="{{ $row->nama_lengkap }}" disabled class="w-full bg-gray-100 border-none rounded-xl px-4 py-2.5 text-xs text-gray-500 font-bold cursor-not-allowed">
                                        </div>
                                        
                                        {{-- <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Pilih Keperluan</label>
                                            <select name="keperluan_master" class="w-full border-gray-100 rounded-xl focus:ring-2 focus:ring-amber-500 text-xs bg-gray-50 p-2.5 font-medium">
                                                <option value="">-- Pilih Keperluan --</option>
                                                @if(isset($keperluan_master) && count($keperluan_master) > 0)
                                                    @foreach($keperluan_master as $km)
                                                        <option value="{{ $km->keterangan }}" 
                                                            {{ ($row->keperluan == $km->keterangan) ? 'selected' : '' }}>
                                                            {{ $km->keterangan }}
                                                        </option>
                                                    @endforeach
                                                @endif
                                                <option value="Lainnya">Lainnya / Manual</option>
                                            </select>
                                        </div> --}}

                                        <div>
                                            <label class="block text-[9px] font-black text-gray-400 uppercase tracking-widest mb-1.5">Detail Keperluan</label>
                                            <textarea name="keperluan" rows="3" class="w-full border-gray-100 rounded-xl focus:ring-2 focus:ring-amber-500 text-xs bg-gray-50 p-3" placeholder="Tulis detail...">{{ $row->keperluan }}</textarea>
                                        </div>

                                        <div class="flex flex-col sm:flex-row gap-2 pt-2">
                                            <button type="button" @click="editModalOpen = false" class="order-2 sm:order-1 flex-1 py-3 bg-gray-50 text-gray-400 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-gray-100 transition">Batal</button>
                                            <button type="submit" class="order-1 sm:order-2 flex-[2] py-3 bg-amber-500 text-white rounded-xl font-black text-[10px] uppercase tracking-widest shadow-lg shadow-amber-200 transition">Simpan</button>
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
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-gray-50 text-gray-300 mb-4"><i class="fas fa-inbox text-xl"></i></div>
                        <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tidak ada data ditemukan</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-6 py-4 border-t border-gray-50 bg-gray-50/20 flex flex-col sm:flex-row justify-between items-center gap-2">
        <span class="text-[9px] font-black text-gray-400 uppercase tracking-widest">Source: Google Sheets Cloud</span>
        <span class="text-[10px] font-bold text-gray-500 bg-white px-3 py-1 rounded-full border border-gray-100">Total: {{ count($kunjungan) }} Baris</span>
    </div>
</div>

{{-- Script SweetAlert (Custom responsif) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus?',
            text: nama + " akan dihapus dari Cloud!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#e11d48',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Hapus',
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