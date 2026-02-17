@extends('layouts.admin')

@section('content')

{{-- Header Section --}}
<div class="mb-6">
    <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Laporan Sistem</h2>
    <p class="text-sm text-gray-500 font-medium">Ekspor data riwayat kunjungan dan hasil survei ke format Excel atau PDF.</p>
</div>

<div class="max-w-2xl">
    {{-- Card Utama --}}
    <div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- Card Header dengan Gradien --}}
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

        {{-- Form Body --}}
        <div class="p-8">
            <form action="{{ route('admin.laporan.export') }}" method="POST">
                @csrf
                
                <div class="space-y-6">
                    
                    {{-- Row 1: Jenis Laporan & Filter Prodi --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Input Jenis Laporan --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">Jenis Data</label>
                            <select name="jenis" required 
                                    class="w-full bg-gray-50 border-gray-200 rounded-xl px-4 py-3 text-sm font-semibold text-gray-700 outline-none focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                                <option value="kunjungan">📊 Data Kunjungan Tamu</option>
                                <option value="pengunjung">👤 Data Pengunjung</option>
                                <option value="survey">⭐️ Data Survey & Kepuasan</option>
                            </select>
                        </div>

                        {{-- Input Filter Prodi --}}
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

                    {{-- Row 2: Format File (Full Width) --}}
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

                    {{-- Row 3: Baris Tanggal --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Dari Tanggal --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">🗓️ Dari Tanggal</label>
                            <input type="date" name="tgl_mulai" required 
                                   class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                        </div>

                        {{-- Sampai Tanggal --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2 ml-1">⌛ Sampai Tanggal</label>
                            <input type="date" name="tgl_selesai" required 
                                   class="w-full px-4 py-3 bg-gray-50 border-gray-200 rounded-xl text-sm font-medium text-gray-700 focus:ring-2 focus:ring-[#a044ff] focus:bg-white transition">
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-4">
                        <button type="submit" 
                                class="group w-full flex items-center justify-center gap-3 bg-gradient-to-r from-[#3366ff] to-[#a044ff] text-white px-6 py-4 rounded-2xl font-bold shadow-lg shadow-blue-100 hover:shadow-xl hover:scale-[1.02] active:scale-95 transition-all duration-300">
                            <i class="fas fa-cloud-download-alt group-hover:bounce"></i>
                            <span>Generate & Download Laporan</span>
                        </button>
                    </div>

                </div> {{-- End Space-y-6 --}}
            </form>
        </div>

        {{-- Card Footer --}}
        <div class="bg-blue-50/50 px-8 py-4 border-t border-gray-100">
            <div class="flex items-start gap-2 text-blue-600">
                <i class="fas fa-info-circle text-xs mt-1"></i>
                <p class="text-[11px] font-medium leading-relaxed">
                    Pilih format <b>Excel</b> jika Anda ingin mengolah data lebih lanjut (sortir/filter manual), atau pilih <b>PDF</b> untuk laporan resmi siap cetak dengan tanda tangan.
                </p>
            </div>
        </div>
    </div>
</div>

@endsection

{{-- CSS Animasi --}}
<style>
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-4px); }
    }
    .group:hover .fa-cloud-download-alt {
        animation: bounce 0.8s infinite;
    }
</style>