@extends('layouts.admin')

@section('content')

{{-- Script SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@php
    $userSession = session('user');
    $roleId = (int)($userSession['role_id'] ?? 0);
    $isSuperAdmin = ($roleId === 1);
    $prodiUser = $userSession['prodi_nama'] ?? '';
@endphp

{{-- === HEADER SECTION === --}}
<div class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            {{-- Breadcrumb Modern --}}
            <nav class="flex items-center gap-2 mb-3">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Pusat Laporan</span>
            </nav>
            
            {{-- Title --}}
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                Laporan <span class="bg-clip-text text-transparent bg-gradient-to-r from-indigo-600 to-purple-600">Sistem</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                Ekspor data riwayat kunjungan dan hasil survei kepuasan.
            </p>
        </div>

        {{-- Icon Laporan --}}
        <div class="hidden md:flex items-center gap-4">
            <div class="bg-white px-6 py-4 rounded-2xl shadow-sm border border-gray-100 flex items-center gap-4">
                <div class="text-right">
                    <span class="text-[10px] font-black text-gray-400 uppercase tracking-widest block">Status Server</span>
                    <span class="text-sm font-bold text-emerald-500 flex items-center justify-end gap-1">
                        <i class="fas fa-check-circle"></i> Ready to Export
                    </span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center shadow-lg shadow-indigo-100">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="max-w-3xl">
    {{-- === FORM SECTION (Glassmorphism Style) === --}}
    <div class="bg-white rounded-[2.5rem] shadow-sm border border-gray-100 overflow-hidden transition-all duration-300 hover:shadow-md">
        
        <div class="bg-gradient-to-r from-gray-50/50 to-white px-10 py-8 border-b border-gray-100">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 flex items-center justify-center text-indigo-600 border border-indigo-100 shadow-inner">
                    <i class="fas fa-sliders-h text-lg"></i>
                </div>
                <div>
                    <h5 class="font-black text-gray-800 uppercase tracking-tight text-sm">Konfigurasi Laporan</h5>
                    <p class="text-xs text-gray-400 font-medium">Tentukan parameter data yang ingin diunduh</p>
                </div>
            </div>
        </div>

        <div class="p-10">
            <form id="exportForm" action="{{ route('admin.laporan.export') }}" method="POST">
                @csrf
                
                <div class="space-y-8">
                    {{-- Row 1: Jenis & Prodi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Jenis Data</label>
                            <div class="relative group">
                                <i class="fas fa-database absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
                                <select name="jenis" required 
                                        class="w-full bg-gray-50 border-none rounded-2xl pl-12 pr-4 py-4 text-sm font-bold text-gray-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition shadow-sm appearance-none">
                                    <option value="kunjungan">Data Kunjungan Tamu</option>
                                    <option value="pengunjung">Data Master Pengunjung</option>
                                    <option value="survey">Data Survey Kepuasan</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs"></i>
                            </div>
                        </div>

{{-- Filter Wilayah/Prodi --}}
<div>
    <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Filter Wilayah/Prodi</label>
    @if($isSuperAdmin)
        <div class="relative group">
            <i class="fas fa-university absolute left-4 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-indigo-500 transition-colors"></i>
            <select name="prodi_id" id="prodi_id" required
                    class="w-full bg-gray-50 border-none rounded-2xl pl-12 pr-4 py-4 text-sm font-bold text-gray-700 outline-none focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition shadow-sm appearance-none">
                <option value="all">Semua Program Studi</option>
                @foreach($prodi as $p)
                    <option value="{{ $p->nama }}">{{ $p->nama }}</option>
                @endforeach
            </select>
            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-300 pointer-events-none text-xs"></i>
        </div>
    @else
        {{-- Kunci filter untuk non-admin --}}
        <div class="flex items-center justify-between w-full px-5 py-4 bg-indigo-50/50 border border-indigo-100 rounded-2xl text-sm font-bold text-indigo-700 shadow-sm">
            <span class="flex items-center gap-3">
                <i class="fas fa-graduation-cap"></i>
                {{ $prodiUser }}
            </span>
            <i class="fas fa-lock text-indigo-200 text-xs"></i>
        </div>
        {{-- Pastikan input hidden ini memiliki nilai $prodiUser --}}
        <input type="hidden" name="prodi_id" value="{{ $prodiUser }}">
    @endif
</div>
                    </div>

                    {{-- Row 2: Format Output --}}
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1 text-center">Format Dokumen</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer group">
                                <input type="radio" name="format" value="excel" class="peer sr-only" checked>
                                <div class="rounded-[1.5rem] border-2 border-gray-50 bg-gray-50 p-5 group-hover:bg-white peer-checked:border-emerald-500 peer-checked:bg-emerald-50 peer-checked:text-emerald-700 transition-all shadow-sm">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm">
                                            <i class="fas fa-file-excel text-lg"></i>
                                        </div>
                                        <div class="text-[10px] font-black uppercase tracking-widest">MS Excel</div>
                                    </div>
                                </div>
                            </label>
                            
                            <label class="cursor-pointer group">
                                <input type="radio" name="format" value="pdf" class="peer sr-only">
                                <div class="rounded-[1.5rem] border-2 border-gray-50 bg-gray-50 p-5 group-hover:bg-white peer-checked:border-rose-500 peer-checked:bg-rose-50 peer-checked:text-rose-700 transition-all shadow-sm">
                                    <div class="flex flex-col items-center gap-2">
                                        <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center shadow-sm">
                                            <i class="fas fa-file-pdf text-lg"></i>
                                        </div>
                                        <div class="text-[10px] font-black uppercase tracking-widest">Adobe PDF</div>
                                    </div>
                                </div>
                            </label>
                        </div>
                    </div>

                    {{-- Row 3: Tanggal --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Dari Tanggal</label>
                            <div class="relative">
                                <i class="far fa-calendar-alt absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                <input type="date" name="tgl_mulai" required 
                                       class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition shadow-sm">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-3 ml-1">Sampai Tanggal</label>
                            <div class="relative">
                                <i class="fas fa-history absolute left-4 top-1/2 -translate-y-1/2 text-gray-300"></i>
                                <input type="date" name="tgl_selesai" required 
                                       class="w-full pl-12 pr-4 py-4 bg-gray-50 border-none rounded-2xl text-sm font-bold text-gray-700 focus:ring-4 focus:ring-indigo-500/10 focus:bg-white transition shadow-sm">
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="pt-6">
<button type="submit" id="btnSubmit"
        class="group w-full flex items-center justify-center gap-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-8 py-5 rounded-[1.5rem] font-black text-xs uppercase tracking-[0.2em] shadow-xl shadow-indigo-100 hover:shadow-2xl hover:scale-[1.02] active:scale-95 transition-all duration-300">
    <i class="fas fa-cloud-download-alt text-lg group-hover:animate-bounce"></i>
    <span>Generate Document</span>
</button>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="px-10 py-5 bg-gray-50/50 border-t border-gray-100 flex justify-between items-center">
            <span class="text-[9px] font-black text-gray-300 uppercase tracking-[0.2em]">© 2026 Elektro Report Engine</span>
            <div class="flex gap-4">
                <i class="fab fa-google-drive text-gray-300 text-xs"></i>
                <i class="fas fa-shield-alt text-gray-300 text-xs"></i>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        const tglMulai = this.tgl_mulai.value;
        const tglSelesai = this.tgl_selesai.value;

        // Validasi
        if(!tglMulai || !tglSelesai) {
            e.preventDefault(); // Batalkan submit jika kosong
            Swal.fire({
                icon: 'warning',
                title: 'Tanggal Kosong',
                text: 'Silahkan tentukan rentang tanggal laporan!',
                confirmButtonColor: '#4f46e5',
                customClass: { popup: 'rounded-[2rem]' }
            });
            return;
        }

        // Jika validasi lolos, form akan otomatis lanjut submit (karena type="submit")
        // Kita hanya "menitipkan" tampilan loading saja di sini
        Swal.fire({
            title: 'Memproses Data',
            html: 'Dokumen sedang disiapkan. Unduhan akan dimulai otomatis...',
            allowOutsideClick: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            },
            customClass: { popup: 'rounded-[2rem]' }
        });

        // Tutup loading otomatis setelah 5 detik agar tombol bisa diklik lagi nanti
        setTimeout(() => {
            Swal.close();
        }, 5000);
    });
</script>

@endsection