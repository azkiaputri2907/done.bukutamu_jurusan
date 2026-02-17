@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-pink-500 via-purple-500 to-blue-500 py-8 px-4 sm:px-6 flex flex-col items-center">
    
    {{-- Header Section --}}
    <div class="max-w-3xl w-full mb-8 text-center">
        <h1 class="text-3xl md:text-4xl font-bold text-white drop-shadow-md">Survei Kepuasan</h1>
        <p class="text-white/90 mt-2 font-light">Masukan Anda sangat berharga untuk peningkatan kualitas pelayanan kami.</p>
    </div>

    {{-- Main Card --}}
    <div class="max-w-4xl w-full bg-white/95 backdrop-blur-md rounded-[2rem] shadow-2xl overflow-hidden border border-white/20">
        
        {{-- Banner Top --}}
        <div class="bg-blue-50/50 border-b border-blue-100 p-6 text-center">
            <div class="inline-flex items-center justify-center space-x-8 text-xs md:text-sm text-gray-500 font-medium uppercase tracking-wider">
                <div class="flex items-center"><span class="w-6 h-6 rounded-full border-2 border-red-300 bg-red-50 text-red-500 flex items-center justify-center mr-2 font-bold">1</span> Sangat Buruk</div>
                <div class="w-12 h-px bg-gray-300"></div>
                <div class="flex items-center"><span class="w-6 h-6 rounded-full border-2 border-green-300 bg-green-50 text-green-500 flex items-center justify-center mr-2 font-bold">5</span> Sangat Baik</div>
            </div>
        </div>

        <div class="p-6 md:p-10">
{{-- Ganti bagian <form> sampai </form> dengan ini --}}
<form action="{{ route('guest.survey.store', $kunjungan->id) }}" method="POST">
    @csrf
    
    {{-- Masukkan nama tamu di sini agar hanya terkirim 1 kali --}}
    <input type="hidden" name="nama_tamu" value="{{ $nama_tamu }}">

    @foreach($pertanyaan as $aspek => $daftarPertanyaan)
        {{-- Section Aspek --}}
        <div class="mb-10 last:mb-0">
            <div class="flex items-center mb-6 pb-2 border-b border-purple-100">
                <div class="bg-gradient-to-r from-pink-500 to-purple-500 w-1.5 h-8 rounded-full mr-3"></div>
                <h3 class="text-xl font-bold text-gray-800">{{ $aspek }}</h3>
            </div>

            <div class="grid gap-4">
                @foreach($daftarPertanyaan as $q)
                    {{-- Card Pertanyaan --}}
                    <div class="bg-gray-50 hover:bg-white border border-gray-100 hover:border-purple-200 rounded-xl p-4 transition-all duration-300 shadow-sm hover:shadow-md">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            {{-- Teks Pertanyaan --}}
                            <div class="flex-1">
                                <p class="text-gray-700 font-medium leading-relaxed">{{ $q->pertanyaan }}</p>
                            </div>

                            {{-- Pilihan Skor (Radio Buttons) --}}
                            <div class="flex items-center justify-between md:justify-end gap-2 md:gap-3 min-w-[200px]">
                                @for($i = 1; $i <= 5; $i++)
                                    <label class="relative cursor-pointer group">
                                        {{-- Gunakan ID pertanyaan sebagai key array --}}
                                        <input type="radio" name="jawaban[{{ $q->id }}]" value="{{ $i }}" class="peer sr-only" required>
                                        
                                        <div class="w-10 h-10 md:w-11 md:h-11 rounded-full border-2 border-gray-200 bg-white text-gray-400 flex items-center justify-center font-bold text-sm md:text-base transition-all duration-200 
                                            group-hover:border-purple-300 group-hover:scale-105
                                            peer-checked:border-purple-600 peer-checked:bg-purple-600 peer-checked:text-white peer-checked:scale-110 peer-checked:shadow-lg">
                                            {{ $i }}
                                        </div>
                                        
                                        <span class="absolute -bottom-6 left-1/2 transform -translate-x-1/2 text-[10px] text-purple-600 font-bold opacity-0 peer-checked:opacity-100 transition-opacity whitespace-nowrap">
                                            @if($i==1) Buruk @elseif($i==5) Puas @endif
                                        </span>
                                    </label>
                                @endfor
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach

    {{-- Kritik Saran --}}
    <div class="mt-8 bg-blue-50/50 rounded-2xl p-6 border border-blue-100">
        <label class="flex items-center text-gray-800 font-bold mb-3">
            <svg class="w-5 h-5 mr-2 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
            Kritik & Saran (Opsional)
        </label>
        <textarea name="kritik_saran" rows="4" 
            class="w-full rounded-xl border-gray-300 focus:border-purple-500 focus:ring focus:ring-purple-200 transition-all text-gray-700 bg-white p-4"
            placeholder="Tuliskan pengalaman Anda atau saran perbaikan untuk kami..."></textarea>
    </div>

    {{-- Submit Button --}}
    <div class="mt-8">
        <button type="submit" class="w-full bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 text-white font-bold py-4 px-6 rounded-2xl shadow-lg hover:shadow-2xl hover:-translate-y-1 active:scale-95 transition-all duration-300 flex items-center justify-center text-lg tracking-wide">
            <svg class="w-6 h-6 mr-2 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
            Kirim Penilaian
        </button>
    </div>
</form>
        </div>
    </div>
    
    <p class="text-center text-white/60 text-xs mt-8 italic">© 2026 Jurusan Teknik Elektro. Guest Book System.</p>
</div>


@endsection