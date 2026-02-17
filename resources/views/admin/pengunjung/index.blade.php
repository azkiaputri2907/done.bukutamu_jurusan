@extends('layouts.admin')

@section('content')

{{-- Script Pendukung --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Header Section --}}
<div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
    <div>
        <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Data Pengunjung</h2>
        <p class="text-sm text-gray-500 font-medium">Manajemen seluruh data pengunjung yang terdaftar.</p>
    </div>

    @can('admin-only')
    <div class="hidden md:flex items-center gap-2 bg-blue-50 text-blue-600 px-4 py-2 rounded-xl text-xs font-bold border border-blue-100">
        <i class="fas fa-users"></i>
        <span>Total: {{ $pengunjung->count() }} Orang</span>
    </div>
    @endcan
</div>

{{-- Search & Filter Section --}}
<div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 mb-6 flex flex-col md:flex-row gap-3 md:gap-4">
    
    {{-- Form Pencarian --}}
    <form action="{{ route('admin.pengunjung') }}" method="GET" class="relative flex-1 w-full">
        @if(request('prodi'))
            <input type="hidden" name="prodi" value="{{ request('prodi') }}">
        @endif
        <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama atau No. Identitas..." 
               class="w-full pl-10 pr-4 py-3 bg-gray-50 rounded-xl outline-none focus:ring-2 focus:ring-[#a044ff]/20 focus:bg-white transition font-medium text-gray-700 text-sm border-none">
    </form>
    
    {{-- Dropdown Filter Prodi --}}
    <div class="w-full md:w-auto">
        <select onchange="window.location.href=this.value" class="w-full md:w-72 bg-gray-50 px-4 py-3 rounded-xl text-gray-600 font-bold outline-none cursor-pointer hover:bg-gray-100 transition text-sm focus:ring-2 focus:ring-[#a044ff]/20 border-none">
            <option value="{{ route('admin.pengunjung') }}">Semua Prodi / Instansi</option>
            @php
                $prodis = ['D3 Teknik Listrik', 'D3 Teknik Elektronika', 'D3 Teknik Informatika', 'D4 Teknologi Rekayasa Pembangkit Energi', 'D4 Sistem Informasi Kota Cerdas', 'Lainnya'];
            @endphp
            @foreach($prodis as $item)
                <option value="{{ route('admin.pengunjung', ['prodi' => $item, 'search' => request('search')]) }}" 
                    {{ request('prodi') == $item ? 'selected' : '' }}>
                    {{ $item }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Content Table --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-bold">
                    <th class="px-6 py-4">No</th>
                    <th class="px-6 py-4">Informasi Pengunjung</th>
                    <th class="px-6 py-4">Asal Instansi / Prodi</th>
                    <th class="px-6 py-4">Terakhir Berkunjung</th>
                    <th class="px-6 py-4 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($pengunjung as $p)
                <tr class="hover:bg-gray-50/80 transition duration-150" x-data="{ editModalOpen: false, detailModalOpen: false }">
                    {{-- FIX 1: Gunakan loop iteration biasa --}}
                    <td class="px-6 py-4 text-sm font-medium text-gray-400">
                        {{ $loop->iteration }}
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold text-sm shadow-sm">
                                {{ substr($p->nama_lengkap, 0, 1) }}
                            </div>
                            <div>
                                <span class="block font-bold text-gray-700 text-sm">{{ $p->nama_lengkap }}</span>
                                <span class="text-[11px] px-1.5 py-0.5 bg-gray-100 text-gray-500 rounded font-mono">{{ $p->identitas_no }}</span>
                            </div>
                        </div>
                    </td>

                    <td class="px-6 py-4">
                        <span class="text-sm text-gray-600 font-medium">{{ $p->asal_instansi ?? 'Umum' }}</span>
                    </td>

                    <td class="px-6 py-4">
                        <div class="flex flex-col">
                            {{-- FIX 3: Gunakan variabel terakhir_kunjungan --}}
                            <span class="text-sm font-medium text-gray-700">
                                {{ $p->terakhir_kunjungan && $p->terakhir_kunjungan != '-' 
                                    ? \Carbon\Carbon::parse($p->terakhir_kunjungan)->isoFormat('D MMMM Y') 
                                    : 'Belum Ada Data' }}
                            </span>
                        </div>
                    </td>
                    
                    <td class="px-6 py-4">
                        <div class="flex justify-center items-center gap-2">
                            {{-- Button Lihat --}}
                            <button @click="detailModalOpen = true" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-100 hover:scale-110 transition flex items-center justify-center shadow-sm" title="Lihat Detail">
                                <i class="fas fa-eye text-xs"></i>
                            </button>

                        @can('admin-only')
                            {{-- Button Edit --}}
                            {{-- <button @click="editModalOpen = true" class="w-8 h-8 rounded-lg bg-yellow-50 text-yellow-600 hover:bg-yellow-100 hover:scale-110 transition flex items-center justify-center shadow-sm" title="Edit">
                                <i class="fas fa-edit text-xs"></i>
                            </button> --}}

                            {{-- Form Delete --}}
                            {{-- FIX 2: Gunakan identitas_no sebagai ID unik --}}
                            <form id="delete-form-{{ $p->identitas_no }}" action="{{ route('admin.pengunjung.destroy', $p->identitas_no) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="button" onclick="confirmDelete('{{ $p->identitas_no }}', '{{ $p->nama_lengkap }}')"
                                        class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-100 hover:scale-110 transition flex items-center justify-center shadow-sm" title="Hapus">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        @endcan
                        </div>

                        {{-- MODAL DETAIL --}}
                        <div x-show="detailModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
                            <div @click.away="detailModalOpen = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden text-left">
                                <div class="bg-gradient-to-r from-indigo-500 to-purple-600 p-6 text-white">
                                    <div class="flex items-center gap-4">
                                        <div class="w-14 h-14 rounded-xl bg-white/20 backdrop-blur-md flex items-center justify-center text-xl font-bold shadow-inner">
                                            {{ substr($p->nama_lengkap, 0, 1) }}
                                        </div>
                                        <div>
                                            <h3 class="text-lg font-bold leading-tight">{{ $p->nama_lengkap }}</h3>
                                            <p class="text-indigo-100 text-xs">{{ $p->asal_instansi ?? 'Umum / Luar' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="p-6 space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase">No. Identitas</label>
                                            <p class="text-sm font-mono font-bold text-gray-700">{{ $p->identitas_no }}</p>
                                        </div>
                                        <div>
                                            {{-- FIX 3: Ganti created_at dengan terakhir_kunjungan --}}
                                            <label class="block text-[10px] font-bold text-gray-400 uppercase">Terakhir Masuk</label>
                                            <p class="text-sm font-bold text-gray-700">
                                                {{ $p->terakhir_kunjungan && $p->terakhir_kunjungan != '-' 
                                                ? \Carbon\Carbon::parse($p->terakhir_kunjungan)->format('d M Y') 
                                                : '-' }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="pt-4 border-t border-gray-100">
                                        <div class="flex justify-between items-center py-2">
                                            <span class="text-xs text-gray-500">Status Akun</span>
                                            <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-[10px] font-bold uppercase">Terverifikasi</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="bg-gray-50 p-4 flex justify-end">
                                    <button @click="detailModalOpen = false" class="px-5 py-2 text-xs font-bold text-white bg-gray-800 rounded-lg hover:bg-black transition">Tutup Detail</button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDIT --}}
                        <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4 text-left">
                            <div @click.away="editModalOpen = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                                <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                                    <h3 class="font-bold text-gray-800 italic">Edit Profil Pengunjung</h3>
                                    <button @click="editModalOpen = false" class="text-gray-400 hover:text-red-500"><i class="fas fa-times"></i></button>
                                </div>
                                {{-- FIX 2: Route update pakai identitas_no --}}
                                <form action="{{ route('admin.pengunjung.update', $p->identitas_no) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="p-6 space-y-4">
                                        <div>
                                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Nama Lengkap</label>
                                            <input type="text" name="nama_lengkap" value="{{ $p->nama_lengkap }}" class="w-full border-gray-200 rounded-xl focus:ring-[#a044ff] text-sm py-3">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-bold text-gray-400 uppercase mb-1">Asal Instansi / Prodi</label>
                                            <select name="asal_instansi" class="w-full border-gray-200 rounded-xl focus:ring-[#a044ff] text-sm py-3 cursor-pointer">
                                                @foreach($prodis as $opt)
                                                    @if($opt != 'Lainnya')
                                                        <option value="{{ $opt }}" {{ $p->asal_instansi == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endif
                                                @endforeach
                                                <option value="Lainnya" {{ !in_array($p->asal_instansi, array_diff($prodis, ['Lainnya'])) ? 'selected' : '' }}>Lainnya</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="bg-gray-50 p-4 flex justify-end gap-2">
                                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500">Batal</button>
                                        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-lg shadow-blue-100">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <i class="fas fa-folder-open text-4xl mb-3 opacity-20"></i>
                            <p class="font-medium">Data pengunjung tidak ditemukan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    
    {{-- FIX 4: Pagination dihapus karena tidak didukung --}}
    <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/30 text-xs text-gray-400 text-center">
        Menampilkan seluruh data dari Spreadsheet
    </div>
</div>

{{-- SweetAlert Logic --}}
<script>
    function confirmDelete(id, nama) {
        Swal.fire({
            title: 'Hapus Pengunjung?',
            text: "Data " + nama + " akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: { popup: 'rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                // ID di sini adalah No Identitas (NIK/NIM)
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>

@endsection