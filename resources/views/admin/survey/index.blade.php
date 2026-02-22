@extends('layouts.admin')

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
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Survey Kepuasan</span>
            </nav>
            
            {{-- Title --}}
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                Data <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Survey</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Hasil penilaian unit layanan dan kepuasan pengunjung secara real-time.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            {{-- Card Skor Terintegrasi --}}
            <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Skor Maksimal</span>
                    <div class="flex items-baseline gap-1">
                        <span class="text-2xl font-black text-gray-800">5.0</span>
                        <span class="text-xs font-bold text-gray-400 italic">pts</span>
                    </div>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-md shadow-indigo-200">
                    <i class="fas fa-star"></i>
                </div>
            </div>
            
            {{-- Refresh Button --}}
            <a href="{{ route('admin.survey') }}" 
               class="w-14 h-14 bg-white text-gray-500 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center hover:text-indigo-600 hover:border-indigo-200 hover:shadow-md transition-all duration-300 group">
                <i class="fas fa-sync-alt text-lg group-hover:rotate-180 transition-transform duration-500"></i>
            </a>
        </div>
    </div>
</div>

<div class="flex flex-col lg:flex-row gap-8">

    {{-- === KOLOM KIRI: DAFTAR SURVEY === --}}
    <div class="flex-1 w-full lg:w-2/3">

{{-- === SEARCH & FILTER SECTION === --}}
<div class="bg-white/60 backdrop-blur-md p-3 rounded-[2rem] shadow-sm border border-gray-100 mb-8">
    <div class="flex flex-col md:flex-row gap-3">
        
        @php
            // Ambil data user dari session
            $userSession = session('user');
            $roleId = (int)($userSession['role_id'] ?? 0);
            
            // Definisikan isSuperAdmin agar tidak error di baris 85
            $isSuperAdmin = ($roleId === 1);
            $prodiUser = $userSession['prodi_nama'] ?? '';
            
            // List prodi jika tidak dikirim dari controller
            $listProdis = $prodis ?? ['D3 Teknik Listrik', 'D3 Teknik Elektronika', 'D3 Teknik Informatika', 'D4 Teknologi Rekayasa Pembangkit Energi', 'D4 Sistem Informasi Kota Cerdas', 'Umum'];
        @endphp

        {{-- Form Pencarian --}}
        {{-- <form action="{{ route('admin.survey') }}" method="GET" class="relative flex-1 group">
            {{-- Kunci Pencarian: Jika bukan Super Admin, paksa kirim prodi user --}}
            {{-- @if(!$isSuperAdmin)
                <input type="hidden" name="prodi" value="{{ $prodiUser }}">
            @elseif(request('prodi'))
                <input type="hidden" name="prodi" value="{{ request('prodi') }}">
            @endif --}}

            {{-- <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-indigo-500 transition-colors"></i>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama pengunjung..." 
                   class="w-full pl-12 pr-6 py-4 bg-white rounded-2xl border-none outline-none focus:ring-4 focus:ring-indigo-500/10 transition font-medium text-gray-700 placeholder:text-gray-400 shadow-sm">
        </form> --}} 
        
        {{-- Filter Prodi --}}
        @if($isSuperAdmin)
            {{-- Tampilan Dropdown untuk Super Admin --}}
            <div class="relative min-w-[280px]">
                <i class="fas fa-filter absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 z-10 pointer-events-none"></i>
                <select onchange="window.location.href=this.value" 
                        class="w-full pl-12 pr-10 py-4 bg-white rounded-2xl border-none text-gray-700 font-bold outline-none cursor-pointer hover:bg-gray-50 transition shadow-sm appearance-none focus:ring-4 focus:ring-indigo-500/10">
                    <option value="{{ route('admin.survey') }}">Semua Program Studi</option>
                    @foreach($listProdis as $p)
                        <option value="{{ route('admin.survey', ['prodi' => $p, 'search' => request('search')]) }}" 
                            {{ request('prodi') == $p ? 'selected' : '' }}>
                            {{ $p }}
                        </option>
                    @endforeach
                </select>
                <i class="fas fa-chevron-down absolute right-5 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none"></i>
            </div>
        @else
            {{-- Tampilan Terkunci (Badge) untuk Admin Prodi --}}
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
        
        {{-- Reset Button --}}
        @if(request()->anyFilled(['search', 'prodi']))
            <a href="{{ route('admin.survey') }}" class="flex items-center justify-center px-6 bg-rose-50 text-rose-500 rounded-2xl hover:bg-rose-500 hover:text-white transition-all duration-300 font-bold shadow-sm">
                <i class="fas fa-times-circle mr-2"></i> Reset
            </a>
        @endif
    </div>
</div>

        {{-- Content Table --}}
        <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 border-b border-gray-100">
                            <th class="px-6 py-5 text-[11px] uppercase tracking-widest text-gray-400 font-black">Tanggal</th>
                            <th class="px-6 py-5 text-[11px] uppercase tracking-widest text-gray-400 font-black">Pengunjung</th>
                            <th class="px-6 py-5 text-[11px] uppercase tracking-widest text-gray-400 font-black text-center">Detail Skor</th>
                            <th class="px-6 py-5 text-[11px] uppercase tracking-widest text-gray-400 font-black text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($surveys as $s)
                        <tr class="hover:bg-gray-50/50 transition duration-150" x-data="{ viewModalOpen: false }">
                            
                            {{-- PERBAIKAN 1: Menampilkan Tahun-Bulan-Hari saja --}}
                            <td class="px-6 py-4">
                                <div class="flex flex-col">
                                    <span class="text-sm font-bold text-gray-700">
                                        @php
                                            try {
                                                // Mengonversi ke format: 2026-02-15 (Tahun-Bulan-Hari)
                                                echo \Carbon\Carbon::parse($s->waktu)->format('Y-m-d');
                                            } catch (\Exception $e) {
                                                // Jika format di Google Sheets aneh, tampilkan apa adanya
                                                echo $s->waktu;
                                            }
                                        @endphp
                                    </span>
                                    <span class="text-[10px] text-gray-400 uppercase tracking-tighter">Tanggal Survey</span>
                                </div>
                            </td>

                            {{-- PERBAIKAN 2: Gunakan $s->nama_tamu langsung (Flat Object) --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-500 to-[#a044ff] flex items-center justify-center text-white font-bold text-sm shadow-sm uppercase">
                                        {{-- Inisial: Jika disamarkan jadi 'P', jika tidak ambil inisial nama --}}
                                        {{ substr($s->nama_tamu ?? 'P', 0, 1) }}
                                    </div>
                                    <span class="font-bold text-gray-700 text-sm tracking-tight">
                                        {{ $s->nama_tamu }}
                                    </span>
                                </div>
                            </td>

                            {{-- PERBAIKAN 3: Akses P1-P5 langsung dari $s (bukan $s->detail) --}}
                            <td class="px-6 py-4">
                                <div class="flex justify-center gap-1.5">
                                    @foreach(['p1', 'p2', 'p3', 'p4', 'p5'] as $p)
                                        @php $val = $s->$p ?? 0; @endphp
                                        <div class="w-6 h-6 flex items-center justify-center rounded-lg text-[10px] font-black shadow-sm
                                            {{ $val >= 4 ? 'bg-emerald-50 text-emerald-600 border border-emerald-100' : ($val >= 3 ? 'bg-amber-50 text-amber-600 border border-amber-100' : 'bg-rose-50 text-rose-600 border border-rose-100') }}">
                                            {{ $val }}
                                        </div>
                                    @endforeach
                                </div>
                            </td>

                            <td class="px-6 py-4" x-data="{ editModalOpen: false, viewModalOpen: false }"> {{-- x-data digabung di sini --}}
                                <div class="flex justify-center items-center gap-2">
                                    
                                {{-- Tombol View (Semua Role Bisa Lihat) --}}
                                        <button @click="viewModalOpen = true" title="Lihat Detail" 
                                            class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 border border-blue-100 hover:bg-blue-600 hover:text-white transition-all duration-300 shadow-sm flex items-center justify-center">
                                            <i class="fas fa-eye text-xs"></i>
                                        </button>

                                        @php
                                            $user = session('user');
                                            $roleId = (int)($user['role_id'] ?? 0);
                                            $userProdiId = (int)($user['prodi_id'] ?? 0);
                                            
                                            // Logika Akses:
                                            // 1. Super Admin (Role 1) & Kajur (Role 2) bisa hapus semua
                                            // 2. Admin Prodi (Role 3) hanya bisa hapus jika prodi_id di data survey cocok
                                            // Catatan: Pastikan di Controller Anda sudah menyertakan 'prodi_id' dalam object $s
                                            $canDelete = ($roleId === 1 || $roleId === 2) || ($roleId === 3 && $userProdiId == ($s->prodi_id ?? 0));
                                        @endphp

                                        @if($canDelete)
                                            {{-- Tombol Delete --}}
                                            <form id="delete-form-{{ $loop->index }}" action="{{ route('admin.survey.destroy') }}" method="POST" class="inline">
                                                @csrf @method('DELETE')
                                                <input type="hidden" name="id_kunjungan" value="{{ $s->id_kunjungan }}">
                                                <button type="button" onclick="confirmDelete('{{ $loop->index }}', '{{ $s->nama_tamu }}')" 
                                                    class="w-9 h-9 rounded-xl bg-rose-50 text-rose-600 border border-rose-100 hover:bg-rose-600 hover:text-white transition-all duration-300 shadow-sm flex items-center justify-center">
                                                    <i class="fas fa-trash text-xs"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>

{{-- --- MODAL VIEW (Detail Informasi) --- --}}
<div x-show="viewModalOpen" 
     class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" 
     style="display: none;" x-transition>
    <div @click.away="viewModalOpen = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
        <div class="px-8 py-6 bg-gradient-to-r from-blue-600 to-indigo-600 text-white flex justify-between items-center">
            <h3 class="font-black uppercase tracking-tight text-sm">Detail Penilaian</h3>
            <button @click="viewModalOpen = false" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
        </div>
        <div class="p-8 space-y-6">
            <div class="flex items-center gap-4 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center text-xl font-black">
                    {{ substr($s->nama_tamu ?? 'P', 0, 1) }}
                </div>
                <div>
                    <h4 class="font-black text-gray-800">{{ $s->nama_tamu }}</h4>
                    
                    {{-- Tampilkan ID Kunjungan dengan pengecekan samaran --}}
                    <p class="text-[10px] {{ $s->id_kunjungan === 'HIDDEN' ? 'text-red-400' : 'text-gray-400' }} font-bold uppercase tracking-widest">
                        @if($s->id_kunjungan === 'HIDDEN')
                            <i class="fas fa-lock mr-1"></i> ID DISAMARKAN
                        @else
                            ID: {{ $s->id_kunjungan }}
                        @endif
                    </p>
                </div>
            </div>

            <div class="grid grid-cols-5 gap-2">
                @foreach(['p1', 'p2', 'p3', 'p4', 'p5'] as $index => $p)
                <div class="text-center">
                    <label class="text-[9px] font-black text-gray-400 uppercase">{{ $aspekLabels[$index] ?? $p }}</label>
                    <div class="w-full py-2 bg-blue-50 text-blue-700 rounded-lg font-black mt-1">{{ $s->$p }}</div>
                </div>
                @endforeach
            </div>

            <div class="space-y-1">
                <label class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Kritik & Saran</label>
                <div class="p-4 bg-gray-50 rounded-2xl text-sm text-gray-600 italic border border-gray-100">
                    "{{ $s->kritik_saran ?? 'Tidak ada pesan.' }}"
                </div>
            </div>
        </div>
    </div>
</div>
                                {{-- --- MODAL EDIT --- --}}
                                <div x-show="editModalOpen" 
                                    class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 backdrop-blur-sm p-4 text-left" 
                                    style="display: none;" x-transition>
                                    <div @click.away="editModalOpen = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
                                        <form action="{{ route('admin.survey.update') }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="id_kunjungan" value="{{ $s->id_kunjungan }}">
                                            <div class="px-8 py-6 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex justify-between items-center">
                                                <h3 class="font-black uppercase tracking-tight text-sm">Edit Skor Survey</h3>
                                                <button type="button" @click="editModalOpen = false" class="text-white/50 hover:text-white"><i class="fas fa-times"></i></button>
                                            </div>
                                            <div class="p-8 space-y-6">
                                                <div class="grid grid-cols-5 gap-2">
                                                    @foreach(['p1', 'p2', 'p3', 'p4', 'p5'] as $p)
                                                    <div>
                                                        <label class="text-[10px] font-bold text-gray-400 uppercase">{{ $p }}</label>
                                                        <input type="number" name="{{ $p }}" value="{{ $s->$p }}" min="1" max="5" 
                                                            class="w-full p-2 bg-gray-50 rounded-lg border-none focus:ring-2 focus:ring-amber-500 text-center font-bold">
                                                    </div>
                                                    @endforeach
                                                </div>
                                                <textarea name="kritik_saran" class="w-full p-4 bg-gray-50 rounded-2xl border-none focus:ring-2 focus:ring-amber-500 text-sm" rows="3">{{ $s->kritik_saran }}</textarea>
                                                <button type="submit" class="w-full py-4 bg-amber-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest shadow-lg hover:bg-amber-600 transition-all">Simpan Perubahan</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-12 text-center text-gray-400 font-medium italic text-sm">Tidak ada data survey ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PERBAIKAN 4: Hapus Pagination Link karena Data API berupa Collection biasa --}}
            <div class="px-6 py-4 bg-gray-50/20 text-xs text-gray-400 text-center">
                Menampilkan {{ count($surveys) }} data terbaru dari Google Sheets.
            </div>
        </div>
    </div>

    {{-- === KOLOM KANAN: STATISTIK === --}}
    <div class="w-full lg:w-1/3">
        <div class="bg-white p-8 rounded-[2rem] shadow-sm border border-gray-100 sticky top-8">
            <div class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-[#a044ff] flex items-center justify-center shadow-sm">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3 class="text-base font-black text-gray-800 uppercase tracking-tight">Analisis Skor</h3>
            </div>
            
            <div class="space-y-6">
                @php $aspekLabels = ['Kecepatan', 'Etika', 'Kompetensi', 'Fasilitas', 'Kualitas']; @endphp
                @foreach($avgScores as $index => $score)
                <div class="group">
                    <div class="flex justify-between items-center mb-2.5">
                        <span class="text-[11px] font-black text-gray-400 uppercase tracking-widest group-hover:text-purple-500 transition">{{ $aspekLabels[$index] ?? 'Aspek '.($index+1) }}</span>
                        <div class="bg-purple-50 px-2 py-1 rounded-md">
                            <span class="text-xs font-black text-[#a044ff]">{{ number_format($score, 1) }}</span>
                        </div>
                    </div>
                    <div class="w-full bg-gray-50 rounded-full h-2 overflow-hidden border border-gray-100">
                        <div class="bg-gradient-to-r from-indigo-500 to-[#a044ff] h-full rounded-full transition-all duration-1000 ease-out" 
                             style="width: {{ ($score/5)*100 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="mt-10 p-5 bg-gradient-to-br from-gray-50 to-white rounded-2xl border border-gray-100">
                <p class="text-[10px] text-gray-400 font-bold uppercase mb-2 tracking-widest leading-none">Info</p>
                <p class="text-xs text-gray-500 leading-relaxed italic">Data ini disinkronisasi secara real-time dari Google Sheets.</p>
            </div>
        </div>
    </div>

</div>
<script>
function confirmDelete(index, nama) { // Tambahkan parameter index
    Swal.fire({
        title: 'Hapus Data?',
        text: "Data milik " + nama + " akan dihapus secara permanen!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#f43f5e',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, Hapus!',
        cancelButtonText: 'Batal',
        customClass: {
            popup: 'rounded-[2rem]',
            confirmButton: 'rounded-xl px-4 py-2',
            cancelButton: 'rounded-xl px-4 py-2'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Submit form berdasarkan ID yang unik (menggunakan loop index)
            document.getElementById('delete-form-' + index).submit();
        }
    })
}
</script>
@endsection