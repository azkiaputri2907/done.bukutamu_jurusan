@extends('layouts.admin')

@section('content')

{{-- Header Section --}}
{{-- Modifikasi: Teks rata kiri di mobile, tombol full width di mobile --}}
<div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6">
    <div>
        <h2 class="text-xl md:text-2xl font-extrabold text-gray-800 tracking-tight">Data Kunjungan</h2>
        <p class="text-xs md:text-sm text-gray-500 font-medium">Daftar riwayat tamu yang berkunjung (Cloud Data)</p>
    </div>

    @can('admin-only')
    <a href="{{ route('guest.form') }}" target="_blank" 
       class="w-full md:w-auto group flex items-center justify-center gap-2 bg-gradient-to-r from-[#3366ff] to-[#a044ff] text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 transition transform active:scale-95 hover:scale-105">
        <div class="bg-white/20 p-1 rounded-md">
            <i class="fas fa-plus text-[10px]"></i>
        </div>
        <span class="text-sm">Tambah Manual</span>
    </a>
    @endcan
</div>

{{-- Search & Filter Section --}}
{{-- Modifikasi: Gap lebih rapat di mobile, padding disesuaikan --}}
<div class="bg-white p-3 md:p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-3">
    <form action="{{ route('admin.kunjungan') }}" method="GET" class="relative flex-1 w-full">
        @if(request('prodi'))
            <input type="hidden" name="prodi" value="{{ request('prodi') }}">
        @endif
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama..." 
               class="w-full pl-10 pr-4 py-2.5 md:py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition font-medium text-gray-700 text-sm">
    </form>
    
    <div class="w-full md:w-auto">
        <select onchange="window.location.href=this.value" class="w-full md:w-64 bg-gray-50 px-4 py-2.5 md:py-3 rounded-xl text-gray-600 font-bold outline-none cursor-pointer hover:bg-gray-100 transition text-sm focus:ring-2 focus:ring-[#a044ff]">
            <option value="{{ route('admin.kunjungan') }}">Semua Prodi / Instansi</option>
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
                            <button @click="viewModalOpen = true" class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-blue-50 text-blue-600 border border-blue-100 flex items-center justify-center">
                                <i class="fas fa-eye text-[10px]"></i>
                            </button>

                            @can('admin-only')
                            <button @click="editModalOpen = true" class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-amber-50 text-amber-600 border border-amber-100 flex items-center justify-center">
                                <i class="fas fa-edit text-[10px]"></i>
                            </button>

                            <form id="delete-form-{{ $row->nomor_kunjungan }}" action="{{ route('admin.kunjungan.destroy', $row->nomor_kunjungan) }}" method="POST" class="inline">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete('{{ $row->nomor_kunjungan }}', '{{ $row->nama_lengkap }}')"
                                        class="w-8 h-8 md:w-9 md:h-9 rounded-lg md:rounded-xl bg-rose-50 text-rose-600 border border-rose-100 flex items-center justify-center">
                                    <i class="fas fa-trash text-[10px]"></i>
                                </button>
                            </form>
                            @endcan
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
                        @can('admin-only')
                        <div x-show="editModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" style="display: none;" x-transition>
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
                                        
                                        <div>
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
                                        </div>

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
                        @endcan

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