@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-pink-500 via-purple-500 to-blue-500 py-6 md:py-12 px-4 sm:px-6 flex flex-col items-center">
    
    {{-- Header --}}
    <div class="max-w-3xl w-full mb-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white drop-shadow-md">Konfirmasi Kunjungan</h1>
        <p class="text-white/90 mt-2 font-light text-sm md:text-base">Jurusan Teknik Elektro - Fakultas Teknik</p>
    </div>

    {{-- Main Card --}}
    <div class="max-w-2xl w-full bg-white/95 backdrop-blur-md rounded-[2rem] shadow-2xl overflow-hidden border border-white/20">
        
        {{-- Banner Sukses --}}
        <div class="bg-gradient-to-r from-pink-500 to-blue-500 py-8 px-6 text-center text-white">
            <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-white mb-4 shadow-lg animate-bounce">
                {{-- Icon Check SVG --}}
                <svg class="h-10 w-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-2xl font-bold mb-1 tracking-wide">Data Berhasil Dikirim!</h2>
            <p class="text-sm text-white/90">Terima kasih, data Anda telah tersimpan dengan aman di sistem kami.</p>
        </div>

        <div class="p-6 md:p-10">
            {{-- Nomor Referensi Box --}}
            <div class="bg-blue-50/50 border-2 border-dashed border-blue-200 rounded-2xl p-5 text-center">
                <p class="text-xs text-gray-500 uppercase font-bold tracking-tighter">Nomor Referensi Kunjungan:</p>
                {{-- Variabel Code 1: $kunjungan->nomor_kunjungan --}}
                <p class="text-2xl font-mono font-bold text-pink-600 tracking-wider mt-1">{{ $kunjungan->nomor_kunjungan }}</p>
                <p class="text-[10px] text-gray-400 mt-2 italic">*Simpan nomor ini untuk verifikasi</p>
            </div>

            <div class="mt-8 space-y-4">
                {{-- Header Detail --}}
                <h3 class="flex items-center text-gray-800 font-bold text-lg mb-4 ml-1">
                    <div class="bg-purple-100 p-2 rounded-lg mr-3">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"></path>
                        </svg>
                    </div>
                    Detail Kunjungan
                </h3>

                <div class="grid grid-cols-1 gap-3">
                    
                    {{-- Item 1: Waktu --}}
                    <div class="flex items-center p-4 bg-gray-50/80 rounded-xl border border-gray-100">
                        <div class="mr-4 text-pink-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black">Waktu</p>
                            {{-- Variabel Code 1 --}}
                            <p class="text-sm font-semibold text-gray-700">
                                {{ $kunjungan->hari_kunjungan }}, {{ \Carbon\Carbon::parse($kunjungan->tanggal)->locale('id')->isoFormat('D MMMM YYYY') }}                            </p>
                        </div>
                    </div>

                    {{-- Item 2: Nama Pengunjung --}}
                    <div class="flex items-center p-4 bg-gray-50/80 rounded-xl border border-gray-100">
                        <div class="mr-4 text-purple-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black">Nama Pengunjung</p>
                            {{-- Variabel Code 1 --}}
                            <p class="text-sm font-semibold text-gray-700">{{ $kunjungan->pengunjung->nama_lengkap }}</p>
                        </div>
                    </div>

                    {{-- Item 3: Identitas --}}
                    <div class="flex items-center p-4 bg-gray-50/80 rounded-xl border border-gray-100">
                        <div class="mr-4 text-blue-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2-2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5"></path></svg></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black">Identitas (NIM/NIP)</p>
                            {{-- Variabel Code 1 --}}
                            <p class="text-sm font-semibold text-gray-700">{{ $kunjungan->pengunjung->identitas_no }}</p>
                        </div>
                    </div>

                    {{-- Item 4: Instansi/Prodi --}}
                    <div class="flex items-center p-4 bg-gray-50/80 rounded-xl border border-gray-100">
                        <div class="mr-4 text-pink-500"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16"></path></svg></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black">Program Studi / Instansi</p>
                            {{-- Variabel Code 1 --}}
                            <p class="text-sm font-semibold text-gray-700">{{ $kunjungan->pengunjung->asal_instansi }}</p>
                        </div>
                    </div>
                    
                    {{-- TAMBAHAN: Item 6: Nomor Telepon --}}
                    <div class="flex items-center p-4 bg-gray-50/80 rounded-xl border border-gray-100">
                        <div class="mr-4 text-green-500">
                            {{-- Icon Phone/WA --}}
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black">Nomor WhatsApp / Telepon</p>
                            <p class="text-sm font-semibold text-gray-700">{{ $kunjungan->pengunjung->no_telpon ?? '-' }}</p>
                        </div>
                    </div>

                    {{-- Item 5: Keperluan --}}
                    <div class="flex items-start p-4 bg-gray-50/80 rounded-xl border border-gray-100">
                        <div class="mr-4 text-green-500 mt-1"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg></div>
                        <div>
                            <p class="text-[10px] text-gray-400 uppercase font-black">Keperluan</p>
                            {{-- Variabel Code 1 --}}
                            <p class="text-sm font-semibold text-gray-700 leading-relaxed">{{ $kunjungan->keperluan }}</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol Survey --}}
            <div class="mt-10 flex flex-col sm:flex-row-reverse gap-4">
                <a href="{{ route('guest.survey', $kunjungan->id) }}" 
                class="w-full sm:flex-1 bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 text-white font-bold py-4 px-6 rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-1 active:scale-95 transition-all duration-300 flex items-center justify-center group text-sm md:text-base">
                    <svg class="w-5 h-5 mr-2 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                    </svg>
                    Isi Survey Kepuasan
                </a>
            </div>
        </div>
    </div>

    <p class="text-center text-white/60 text-xs mt-8 italic">© 2026 Jurusan Teknik Elektro. Guest Book System.</p>
</div>
@endsection