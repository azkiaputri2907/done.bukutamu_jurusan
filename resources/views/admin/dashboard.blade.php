@extends('layouts.admin')

@section('content')

{{-- HEADER SECTION --}}
<div class="mb-10 flex flex-col sm:flex-row sm:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
        {{-- Animated Icon Box --}}
        <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-tr from-[#ff3366] to-[#a044ff] rounded-2xl flex items-center justify-center shadow-lg shadow-purple-200 shrink-0 transform -rotate-3 hover:rotate-0 transition-transform duration-300">
            <i class="fas fa-chart-pie text-white text-xl md:text-3xl"></i>
        </div>
        
        <div>
            <h2 class="text-2xl md:text-4xl font-extrabold text-gray-800 tracking-tight leading-tight">
                Dashboard <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#a044ff] to-[#3366ff]">{{ Auth::user()->role->nama_role }}</span>
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-8 h-1 bg-gradient-to-r from-[#ff3366] to-[#a044ff] rounded-full"></span>
                <p class="text-gray-500 font-medium tracking-wide text-xs md:text-sm uppercase">Overview Statistik Hari Ini</p>
            </div>
        </div>
    </div>

    {{-- Date Display --}}
    <div class="relative">
        <div id="calendar-trigger" class="flex items-center gap-3 bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:bg-gray-50 transition-colors duration-200">
            <div class="text-right hidden sm:block">
                <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Hari Ini</p>
                <p class="text-sm font-bold text-gray-700">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-[#a044ff]">
                <i class="far fa-calendar-alt text-lg"></i>
            </div>
            {{-- Hapus input manual yang pakai inline style tadi, ganti dengan ini saja --}}
            <input type="text" id="datepicker" class="absolute inset-0 opacity-0 cursor-pointer">
        </div>
    </div>
</div>

{{-- STATS CARDS GRID --}}
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">
    
    <div class="bg-gradient-to-r from-[#ff3366] to-[#ff5e84] p-6 rounded-[2rem] text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm"><i class="fas fa-users text-lg"></i></div>
            </div>
            <h3 class="text-3xl md:text-4xl font-extrabold">{{ $stats['total_tamu'] }}</h3>
            <p class="text-xs md:text-sm opacity-90 font-medium mt-1">Total Kunjungan</p>
        </div>
    </div>

    <div class="bg-gradient-to-r from-[#a044ff] to-[#be7dff] p-6 rounded-[2rem] text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm"><i class="fas fa-clock text-lg"></i></div>
            </div>
            <h3 class="text-3xl md:text-4xl font-extrabold">{{ $stats['tamu_hari_ini'] }}</h3>
            <p class="text-xs md:text-sm opacity-90 font-medium mt-1">Tamu Hari Ini</p>
        </div>
    </div>

    <div class="bg-gradient-to-r from-[#3366ff] to-[#5e84ff] p-6 rounded-[2rem] text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm"><i class="fas fa-star text-lg"></i></div>
            </div>
            <h3 class="text-3xl md:text-4xl font-extrabold">{{ number_format($stats['rata_rata_puas'], 1) }}</h3>
            <p class="text-xs md:text-sm opacity-90 font-medium mt-1">Indeks Kepuasan (Skala 5)</p>
        </div>
    </div>

    <div class="bg-gradient-to-r from-[#00c6fb] to-[#005bea] p-6 rounded-[2rem] text-white shadow-lg relative overflow-hidden group hover:scale-[1.02] transition-all duration-300">
        <div class="absolute right-0 top-0 w-32 h-32 bg-white/10 rounded-full blur-2xl -mr-10 -mt-10 pointer-events-none"></div>
        <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
                <div class="bg-white/20 p-2.5 rounded-xl backdrop-blur-sm"><i class="fas fa-comment-dots text-lg"></i></div>
            </div>
            <h3 class="text-3xl md:text-4xl font-extrabold">{{ $grafik->sum('jumlah') }}</h3>
            <p class="text-xs md:text-sm opacity-90 font-medium mt-1">Data 7 Hari Terakhir</p>
        </div>
    </div>
</div>

{{-- CHARTS SECTION --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    
    <div class="lg:col-span-2 bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Tren Kunjungan</h3>
                <p class="text-xs text-gray-400 font-medium mt-1">Statistik jumlah tamu dalam seminggu terakhir</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-500">
                <i class="fas fa-chart-line"></i>
            </div>
        </div>
        <div class="relative h-72 w-full flex-1">
            <canvas id="lineChart"></canvas>
        </div>
    </div>

    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Analisa Kepuasan</h3>
                <p class="text-xs text-gray-400 font-medium mt-1">Skor per aspek pelayanan</p>
            </div>
            <div class="w-10 h-10 bg-pink-50 rounded-full flex items-center justify-center text-pink-500">
                <i class="fas fa-bullseye"></i>
            </div>
        </div>
        <div class="relative h-64 w-full flex-1 flex justify-center items-center">
            <canvas id="radarChart"></canvas>
        </div>
    </div>
</div>

{{-- CHART SCRIPTS --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#datepicker", {
            locale: "id",
            dateFormat: "Y-m-d",
            defaultDate: "today",
            // altInput dimatikan agar tidak muncul 2 input
            altInput: false, 
            // Agar bulan & tahun tetap bisa diklik (dropdown)
            monthSelectorType: "dropdown", 
            // Mengunci posisi kalender ke kotak trigger
            positionElement: document.getElementById('calendar-trigger'),
            position: "below right", 
            onChange: function(selectedDates, dateStr, instance) {
                console.log("Tanggal dipilih: " + dateStr);
                // window.location.href = "?date=" + dateStr;
            }
        });
        

        // Trigger tetap sama
        document.getElementById('calendar-trigger').addEventListener('click', function() {
            document.getElementById('datepicker')._flatpickr.open();
        });
    });


    document.addEventListener('DOMContentLoaded', function() {
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = '#94a3b8';

        // 1. Line Chart
        const ctxLine = document.getElementById('lineChart').getContext('2d');
        let gradientLine = ctxLine.createLinearGradient(0, 0, 0, 400);
        gradientLine.addColorStop(0, 'rgba(51, 102, 255, 0.2)'); // #3366ff low opacity
        gradientLine.addColorStop(1, 'rgba(51, 102, 255, 0)');

        new Chart(ctxLine, {
            type: 'line',
            data: {
                labels: [@foreach($grafik as $g) "{{ $g->tanggal }}", @endforeach],
                datasets: [{
                    label: 'Pengunjung',
                    data: [@foreach($grafik as $g) {{ $g->jumlah }}, @endforeach],
                    borderColor: '#3366ff',
                    borderWidth: 3,
                    backgroundColor: gradientLine,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3366ff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { borderDash: [2, 4], color: '#f1f5f9' },
                        ticks: { stepSize: 1, color: '#64748b' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748b' }
                    }
                }
            }
        });

        // 2. Radar Chart
        const ctxRadar = document.getElementById('radarChart');
        new Chart(ctxRadar, {
            type: 'radar',
            data: {
                labels: ['Fasilitas', 'Pelayanan', 'Respon', 'Informasi', 'Kebersihan'],
                datasets: [{
                    label: 'Skor',
                    data: [
                        {{ $avg_aspek->p1 ?? 0 }}, 
                        {{ $avg_aspek->p2 ?? 0 }}, 
                        {{ $avg_aspek->p3 ?? 0 }}, 
                        {{ $avg_aspek->p4 ?? 0 }}, 
                        {{ $avg_aspek->p5 ?? 0 }}
                    ],
                    backgroundColor: 'rgba(255, 51, 102, 0.2)', // #ff3366
                    borderColor: '#ff3366',
                    borderWidth: 2,
                    pointBackgroundColor: '#ff3366',
                    pointHoverBackgroundColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    r: {
                        angleLines: { color: '#e2e8f0' },
                        grid: { color: '#f1f5f9' },
                        pointLabels: {
                            font: { size: 11, weight: '600' },
                            color: '#475569'
                        },
                        suggestedMin: 0,
                        suggestedMax: 5,
                        ticks: { display: false }
                    }
                },
                plugins: { legend: { display: false } }
            }
        });
    });
</script>
@endsection