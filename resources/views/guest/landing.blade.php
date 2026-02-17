@extends('layouts.app') 

@section('content')
{{-- CDN & Styles --}}
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap');
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
</style>

<div class="min-h-screen bg-white font-sans text-gray-800 antialiased overflow-x-hidden">
    
    <section class="relative bg-gradient-to-r from-pink-400 via-purple-400 to-blue-500 pb-20 md:pb-32 pt-6 px-4 sm:px-8 overflow-hidden">
        
        <nav class="flex flex-col sm:flex-row justify-between items-center w-full max-w-[95%] mx-auto mb-10 md:mb-20 gap-4 sm:gap-0">
            <div class="flex items-center gap-3 self-start sm:self-auto">
                <div class="w-10 h-10 md:w-12 md:h-12 bg-white/20 rounded-lg backdrop-blur-md flex items-center justify-center border border-white/20 shrink-0">
                    <img src="{{ asset('images/logo_poliban.png') }}" alt="Logo" class="w-7 h-7 md:w-8 md:h-8 object-contain">
                </div>
                <div class="leading-tight">
                    <span class="text-white font-bold text-base sm:text-lg md:text-xl block tracking-wide">Jurusan Teknik Elektro</span>
                    <span class="text-blue-100 text-[10px] sm:text-xs uppercase tracking-wider block sm:inline">Politeknik Negeri Banjarmasin</span>
                </div>
            </div>
            <a href="{{ route('login') }}" class="self-end sm:self-auto bg-white/20 hover:bg-white/30 backdrop-blur-md text-white px-4 py-2 sm:px-5 sm:py-2.5 rounded-full flex items-center gap-2 transition border border-white/30 text-xs sm:text-sm font-medium group">
                Login Admin <span class="group-hover:translate-x-1 transition-transform">→</span>
            </a>
        </nav>

        <div class="w-full max-w-[95%] mx-auto grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-20 items-center">
            
            <div class="text-white space-y-6 text-center lg:text-left order-1">
                <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl xl:text-8xl font-extrabold leading-[1.1] tracking-tight">
                    Buku Tamu <br> 
                    <span class="opacity-80 text-blue-100 italic">Digital</span>
                </h1>
                
                <p class="max-w-2xl mx-auto lg:mx-0 opacity-90 leading-relaxed text-sm sm:text-base md:text-xl text-blue-50">
                    Sistem pencatatan tamu modern yang efisien, aman, dan mudah digunakan untuk meningkatkan pelayanan administrasi di lingkungan kampus.
                </p>
            </div>

            <div class="relative order-2 w-full max-w-lg lg:max-w-xl mx-auto lg:ml-auto">
                <div class="bg-white/20 backdrop-blur-xl border border-white/30 rounded-[2rem] p-6 sm:p-10 shadow-2xl relative overflow-hidden group hover:bg-white/25 transition">
                    <div class="flex justify-center mb-8">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-pink-400 to-blue-500 rounded-2xl flex items-center justify-center shadow-lg transform group-hover:scale-110 transition duration-500">
                            <span class="text-3xl sm:text-4xl text-white">📖</span>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-blue-900/20 p-4 rounded-xl text-center backdrop-blur-md border border-white/10 hover:bg-blue-900/30 transition">
                            <p class="text-2xl sm:text-3xl font-bold text-white">{{ number_format($totalKunjungan) }}</p>
                            <p class="text-[10px] sm:text-xs text-blue-100 uppercase tracking-wider font-semibold">Total Kunjungan</p>
                        </div>
                        
                        <div class="bg-blue-900/20 p-4 rounded-xl text-center backdrop-blur-md border border-white/10 hover:bg-blue-900/30 transition">
                            <p class="text-2xl sm:text-3xl font-bold text-white">24/7</p>
                            <p class="text-[10px] sm:text-xs text-blue-100 uppercase tracking-wider font-semibold">Sistem Aktif</p>
                        </div>

                        <div class="col-span-2 mt-2">
                            <a href="{{ route('guest.form') }}" class="flex justify-center items-center gap-2 bg-white text-blue-600 px-6 py-4 rounded-xl font-bold shadow-xl hover:bg-blue-50 transition transform hover:-translate-y-1 active:scale-95 w-full text-lg">
                                Mulai Catat Kunjungan <span class="text-xl">→</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="absolute bottom-0 left-0 w-full leading-none overflow-hidden">
            <svg class="relative block w-full h-[40px] sm:h-[60px] md:h-[90px]" viewBox="0 0 1200 120" preserveAspectRatio="none">
                <path d="M600,112.77C268.63,112.77,0,65.52,0,7.23V120H1200V7.23C1200,65.52,931.37,112.77,600,112.77Z" fill="#f9fafb"></path>
            </svg>
        </div>
    </section>

    <section class="py-16 md:py-24 bg-gray-50 overflow-hidden">
        <div class="w-full max-w-[95%] mx-auto px-4 sm:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-24 items-center">
                <div class="order-2 lg:order-1 space-y-8">
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="h-px w-8 bg-blue-600"></span>
                            <p class="text-blue-600 font-bold uppercase tracking-widest text-xs">Tentang Sistem</p>
                        </div>
                        <h2 class="text-3xl sm:text-5xl font-extrabold text-gray-800 leading-tight">
                            Mengapa Menggunakan <br>
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-pink-500 to-blue-600">
                                Buku Tamu Digital?
                            </span>
                        </h2>
                        <p class="text-gray-600 leading-relaxed text-base sm:text-lg mt-4 text-justify sm:text-left max-w-3xl">
                            Jurusan Teknik Elektro berkomitmen untuk mengadopsi teknologi digital dalam setiap aspek pelayanan. Buku Tamu Digital adalah implementasi nyata dari visi modernisasi administrasi yang efisien dan berkelanjutan.
                        </p>
                    </div>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-6">
                        @foreach([
                            ['icon' => 'check', 'color' => 'pink', 'text' => 'Paperless (Hemat Kertas)'],
                            ['icon' => 'search', 'color' => 'blue', 'text' => 'Pencarian Data Cepat'],
                            ['icon' => 'shield-alt', 'color' => 'pink', 'text' => 'Data Aman & Terpusat'],
                            ['icon' => 'chart-line', 'color' => 'blue', 'text' => 'Statistik Real-time']
                        ] as $feature)
                        <div class="flex items-center gap-4 group p-4 rounded-xl hover:bg-white hover:shadow-md transition border border-transparent hover:border-gray-100">
                            <div class="w-12 h-12 flex-shrink-0 bg-{{$feature['color']}}-100 text-{{$feature['color']}}-600 rounded-full flex items-center justify-center transition-colors group-hover:bg-{{$feature['color']}}-600 group-hover:text-white">
                                <i class="fas fa-{{$feature['icon']}} text-lg"></i>
                            </div>
                            <span class="text-gray-700 text-base font-medium leading-snug">{{$feature['text']}}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="order-1 lg:order-2 flex flex-col sm:flex-row lg:flex-col gap-6 h-full justify-center max-w-xl mx-auto lg:max-w-none w-full">
                    <div class="bg-gradient-to-br from-pink-500 to-rose-600 text-white p-8 rounded-[2rem] shadow-xl transform hover:scale-105 transition duration-300 flex-1">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm"><span class="text-3xl">👥</span></div>
                        </div>
                        <div>
                            <p class="text-4xl sm:text-5xl font-bold mb-1">{{ $totalPengunjung }}</p>
                            <p class="text-xs opacity-90 uppercase tracking-widest font-semibold">Total Tamu</p>
                        </div>
                    </div>
                    <div class="bg-gradient-to-br from-blue-500 to-indigo-600 text-white p-8 rounded-[2rem] shadow-xl transform hover:scale-105 transition duration-300 flex-1">
                        <div class="flex justify-between items-start mb-4">
                            <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-sm"><span class="text-3xl">⭐</span></div>
                        </div>
                        <div>
                            <p class="text-4xl sm:text-5xl font-bold mb-1">{{ number_format($rataRataSurvey, 1) }}</p>
                            <p class="text-xs opacity-90 uppercase tracking-widest font-semibold">Rating Layanan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="py-12 md:py-24 bg-white relative">
        <div class="w-full max-w-[95%] mx-auto px-4 sm:px-8">
            <div class="text-center mb-10 sm:mb-16">
                <p class="text-blue-600 font-bold uppercase tracking-widest text-[10px] sm:text-xs mb-2">Struktur Organisasi</p>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-gray-800">Jurusan Teknik Elektro</h2>
                <div class="h-1 w-20 bg-gradient-to-r from-pink-500 to-blue-500 mx-auto rounded-full mt-4"></div>
            </div>

            <div class="flex flex-col sm:flex-row justify-center gap-8 mb-16 max-w-5xl mx-auto">
                <div class="flex-1 bg-white p-8 rounded-3xl border border-gray-100 shadow-lg hover:shadow-xl transition-all text-center group relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-pink-500 to-pink-600"></div>
                    <div class="relative w-32 h-32 md:w-40 md:h-40 mx-auto mb-6">
                        <div class="absolute inset-0 bg-pink-100 rounded-full scale-110 group-hover:scale-125 transition-transform duration-500"></div>
                        <img src="{{ asset('images/pimpinan/kajur.png') }}" class="relative w-full h-full object-cover rounded-full border-4 border-white shadow-md" onerror="this.src='https://ui-avatars.com/api/?name=Helmy+Noor&background=random'">
                    </div>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800 group-hover:text-pink-600 transition">M. Helmy Noor, S.ST., M.T.</h3>
                    <p class="text-gray-500 text-sm mt-1">Ketua Jurusan</p>
                </div>

                <div class="flex-1 bg-white p-8 rounded-3xl border border-gray-100 shadow-lg hover:shadow-xl transition-all text-center group relative overflow-hidden">
                    <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-blue-500 to-blue-600"></div>
                    <div class="relative w-32 h-32 md:w-40 md:h-40 mx-auto mb-6">
                        <div class="absolute inset-0 bg-blue-100 rounded-full scale-110 group-hover:scale-125 transition-transform duration-500"></div>
                        <img src="{{ asset('images/pimpinan/sekjur.png') }}" class="relative w-full h-full object-cover rounded-full border-4 border-white shadow-md" onerror="this.src='https://ui-avatars.com/api/?name=Rully+Rezki&background=random'">
                    </div>
                    <h3 class="text-xl md:text-2xl font-bold text-gray-800 group-hover:text-blue-600 transition">Rully Rezki Saputra, S.Pd., M.Pd.</h3>
                    <p class="text-gray-500 text-sm mt-1">Sekretaris Jurusan</p>
                </div>
            </div>

            <div class="text-center mb-12">
                <span class="bg-gray-50 text-gray-600 px-6 py-2 rounded-full text-xs font-bold uppercase tracking-wider border border-gray-200 shadow-sm">
                    Koordinator Program Studi
                </span>
            </div>

            <div class="flex flex-wrap justify-center gap-y-10 gap-x-8 lg:gap-12">
                @php
                    $kaprodis = [
                        ['name' => 'Ir. Lauhil Mahfudz H., S.T, M.T.', 'prodi' => 'D3 Teknik Listrik', 'images' => 'kaprodi1.png'],
                        ['name' => 'Khairunnisa, S.T., M.T.', 'prodi' => 'D3 Teknik Elektronika', 'images' => 'kaprodi2.png'],
                        ['name' => 'Fuad Sholihin, S.T., M.Kom.', 'prodi' => 'D3 Teknik Informatika', 'images' => 'kaprodi3.png'],
                        ['name' => 'Zuraidah, S.T., M.T.', 'prodi' => 'D4 Tek. Rek. Pembangkit Energi', 'images' => 'kaprodi4.png'],
                        ['name' => 'Dr. Kun Nursyaiful Priyo Pamungkas, S.Kom., M.Kom.', 'prodi' => 'D4 Sistem Informasi Kota Cerdas', 'images' => 'kaprodi5.png']
                    ];
                @endphp

                @foreach($kaprodis as $k)
                <div class="w-[45%] sm:w-[30%] lg:w-[15%] flex flex-col items-center text-center group">
                    <div class="relative w-24 h-24 sm:w-28 sm:h-28 mb-4">
                        <div class="absolute inset-0 bg-gradient-to-tr from-pink-400 to-blue-500 rounded-full group-hover:rotate-12 transition-transform opacity-70 blur-sm"></div>
                        <img src="{{ asset('images/pimpinan/' . $k['images']) }}" alt="{{ $k['name'] }}"
                             class="relative w-full h-full object-cover rounded-full border-2 border-white bg-white hover:scale-105 transition duration-300"
                             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($k['name']) }}&background=random&color=fff'">
                    </div>
                    <h4 class="text-xs md:text-sm font-bold text-gray-800 leading-tight px-1 group-hover:text-blue-600 transition min-h-[2.5em] flex items-center justify-center">
                        {{ $k['name'] }}
                    </h4>
                    <div class="mt-2 inline-block bg-blue-50 px-3 py-1 rounded text-[10px] text-blue-600 font-medium leading-tight border border-blue-100">
                        {{ $k['prodi'] }}
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <footer class="bg-[#0a1128] text-gray-400 py-12 md:py-16 border-t-4 border-blue-600">
        <div class="w-full max-w-[95%] mx-auto px-4 sm:px-8 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-12 lg:gap-20">
            
            <div class="text-left">
                <div class="flex items-center justify-start gap-4 mb-6">
                    <div class="w-14 h-14 bg-white rounded-lg flex items-center justify-center p-1">
                        <img src="{{ asset('images/logo_poliban.png') }}" alt="Logo Poliban" class="w-full h-full object-contain">
                    </div>
                    <div>
                        <h4 class="text-white font-bold text-xl leading-none mb-1">Jurusan Teknik Elektro</h4>
                        <p class="text-xs uppercase tracking-wider text-blue-400 font-semibold">Politeknik Negeri Banjarmasin</p>
                    </div>
                </div>
                <div class="space-y-4 mb-6">
                    <div>
                    <p class="text-xs leading-relaxed mb-6">Visi : Mewujudkan jurusan teknik elektro yang UNGGUL dalam menghasilkan INOVASI PRODUK bidang sains terapan untuk kemandirian tata kelola keuangan BADAN LAYANAN UMUM (BLU).</p>
                    <p class="text-xs leading-relaxed mb-6">Misi : <br> 1. Peningkatan mutu lulusan serta optimasi suasana akademik sesuai standar nasional dan kebutuhan dunia usaha, dunia industri, dan dunia kerja (DUDIKA); <br> 2. Peningkatan keahlian sumber daya manusia yang diakui pada tingkat nasional; <br> 3. Pembuatan inovasi produk dari penelitian atau keilmuan yang diterapkan kepada masyarakat; <br> 4. Peningkatan kerjasama nasional dan internasional sebagai upaya branding jurusan teknik elektro ke masyarakat; </p>
                    </div>
                </div>

                <div class="flex gap-3">
                    <a href="https://web.facebook.com/poliban.ac.id" target="_blank" class="w-9 h-9 bg-[#1877F2] rounded-full flex items-center justify-center text-white hover:scale-110 transition">
                        <i class="fa-brands fa-facebook-f text-sm"></i>
                    </a>
                    <a href="https://www.instagram.com/poliban_official/" target="_blank" class="w-9 h-9 bg-gradient-to-tr from-[#f9ce34] via-[#ee2a7b] to-[#6228d7] rounded-full flex items-center justify-center text-white hover:scale-110 transition">
                        <i class="fa-brands fa-instagram text-base"></i>
                    </a>
                    <a href="https://x.com/humaspoliban" target="_blank" class="w-9 h-9 bg-black rounded-full flex items-center justify-center text-white hover:scale-110 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5 fill-current" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>
                    </a>
                    <a href="https://www.youtube.com/channel/UC5CfzvUTqEUPXhwwSLvP53Q" target="_blank" class="w-9 h-9 bg-[#FF0000] rounded-full flex items-center justify-center text-white hover:scale-110 transition">
                        <i class="fa-brands fa-youtube text-sm"></i>
                    </a>
                </div>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 border-l-4 border-pink-500 pl-3 text-lg">Hubungi Kami</h4>
                <div class="space-y-4 text-sm">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-phone mt-1 text-pink-500"></i>
                        <p class="text-gray-300">(0511) 330 5052</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-envelope mt-1 text-pink-500"></i>
                        <p class="text-gray-300">info@poliban.ac.id</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt mt-1 text-pink-500"></i>
                        <p class="text-gray-300">Jl. Brigjen H. Hasan Basri, Kayu Tangi, Banjarmasin 70123</p>
                    </div>
                </div>
            </div>

            <div>
                <h4 class="text-white font-bold mb-6 border-l-4 border-blue-500 pl-3 text-lg">Jam Operasional</h4>
                <div class="space-y-3">
                    @foreach([['Senin - Jumat', '08:00 - 15:00', 'blue'], ['Sabtu - Minggu', 'Tutup', 'red']] as $jam)
                    <div class="flex justify-between items-center bg-white/5 p-4 rounded-lg text-sm border border-white/5 hover:bg-white/10 transition">
                        <span class="font-medium text-gray-300">{{$jam[0]}}</span>
                        <span class="bg-{{$jam[2]}}-600/20 text-{{$jam[2]}}-400 border border-{{$jam[2]}}-600/50 text-xs px-2 py-1 rounded uppercase tracking-wide font-bold">{{$jam[1]}}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="w-full max-w-[95%] mx-auto px-4 sm:px-8 mt-12 md:mt-16 pt-8 border-t border-white/10 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500">
            <p class="text-center md:text-left">&copy; {{ date('Y') }} Jurusan Teknik Elektro. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="#" class="hover:text-white transition">Privasi</a>
                <a href="#" class="hover:text-white transition">Syarat & Ketentuan</a>
            </div>
        </div>
    </footer>
</div>
@endsection