@extends('layouts.admin')

@section('content')

{{-- SweetAlert2 CDN --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Laporan Sistem</h2>
    <p class="text-sm text-gray-500 font-medium">Ekspor data riwayat kunjungan dan hasil survei ke format Excel atau PDF.</p>
</div>

<div class="max-w-2xl">
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        
        <div class="bg-gradient-to-r from-gray-50 to-white px-8 py-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center text-[#a044ff]">
                    <i class="fas fa-file-export text-lg"></i>
                </div>
                <div>
                    <h5 class="font-bold text-gray-800">Filter Ekspor Data</h5>
                    <p class="text-xs text-gray-400">Pilih jenis, format, dan rentang tanggal laporan</p>
                </div>
            </div>
        </div>

        <div class="p-8">
            {{-- Gunakan ID yang jelas --}}
            <form id="exportForm" action="{{ route('admin.laporan.export') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Jenis Data</label>
                            <select name="jenis" required 
                                    class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                                <option value="kunjungan">📊 Data Kunjungan Tamu</option>
                                <option value="pengunjung">👤 Data Pengunjung</option>
                                <option value="survey">⭐️ Data Survey & Kepuasan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Filter Program Studi</label>
                            <div class="relative">
                                <select name="prodi_id" required
                                        class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                                    <option value="all">🌐 Semua Program Studi</option>
                                    @foreach($prodi as $p)
                                        <option value="{{ $p->nama }}">{{ $p->jenis == 'Prodi' ? '🎓' : '🏢' }} {{ $p->nama }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Format Output</label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="cursor-pointer">
                                <input type="radio" name="format" value="excel" class="peer sr-only" checked>
                                <div class="rounded-xl border-2 border-gray-200 bg-gray-50 p-3 hover:bg-white peer-checked:border-green-500 peer-checked:bg-green-50 peer-checked:text-green-700 transition-all text-center">
                                    <div class="text-xl mb-1"><i class="fas fa-file-excel"></i></div>
                                    <div class="text-xs font-bold">Microsoft Excel</div>
                                </div>
                            </label>
                            <label class="cursor-pointer">
                                <input type="radio" name="format" value="pdf" class="peer sr-only">
                                <div class="rounded-xl border-2 border-gray-200 bg-gray-50 p-3 hover:bg-white peer-checked:border-red-500 peer-checked:bg-red-50 peer-checked:text-red-700 transition-all text-center">
                                    <div class="text-xl mb-1"><i class="fas fa-file-pdf"></i></div>
                                    <div class="text-xs font-bold">Dokumen PDF</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">🗓️ Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" required 
                                   class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">⌛ Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" required 
                                   class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                        </div>
                    </div>

                    <div class="pt-4">
                        {{-- Ubah type menjadi button agar kita kendalikan sepenuhnya lewat JS --}}
                        <button type="button" onclick="handleExport()"
                                class="group w-full flex items-center justify-center gap-3 bg-gradient-to-r from-[#3366ff] to-[#a044ff] text-white px-6 py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:shadow-xl hover:scale-[1.02] active:scale-95 transition-all duration-300">
                            <i class="fas fa-cloud-download-alt"></i>
                            <span>Generate & Download Laporan</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- SCRIPT PERBAIKAN --}}
<script>
    function handleExport() {
        const form = document.getElementById('exportForm');
        
        // 1. Tampilkan Pop-up dulu
        Swal.fire({
            title: 'Menyiapkan Laporan',
            html: 'Data sedang diproses. Unduhan akan segera dimulai...',
            timer: 5000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
            },
            showConfirmButton: false,
            allowOutsideClick: false
        });

        // 2. Kirim Form secara manual tepat setelah pop-up muncul
        // Ini menjamin form terkirim di klik pertama
        form.submit();
    }
</script>

@endsection

<style>
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .group:hover .fa-cloud-download-alt {
        animation: bounce 0.8s infinite;
    }
</style>