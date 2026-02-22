@extends('layouts.admin')

@section('content')
{{-- HEADER --}}
<div class="mb-8 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 md:w-16 md:h-16 bg-gradient-to-tr from-[#ff3366] to-[#a044ff] rounded-2xl flex items-center justify-center shadow-lg transform -rotate-3 hover:rotate-0 transition-all duration-300">
            <i class="fas fa-chart-pie text-white text-xl md:text-3xl"></i>
        </div>
        <div>
            <h2 class="text-xl md:text-3xl font-extrabold text-gray-800 tracking-tight">
                Dashboard 
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-[#a044ff] to-[#3366ff]">
                    {{ (int)session('user')['role_id'] === 1 ? 'Global' : session('user')['prodi_nama'] }}
                </span>
            </h2>
            <div class="flex items-center gap-2 mt-1">
                <span class="w-8 h-1 bg-gradient-to-r from-[#ff3366] to-[#a044ff] rounded-full"></span>
                <p class="text-gray-500 font-medium text-[10px] md:text-xs uppercase tracking-wider">
                    {{ (int)session('user')['role_id'] === 1 ? 'Ringkasan Seluruh Politeknik' : 'Statistik Khusus Program Studi' }}
                </p>
            </div>
        </div>
    </div>

    {{-- GRUP KANAN: NOTIF & KALENDER --}}
    <div class="flex items-center gap-3 w-full lg:w-auto">
        {{-- NOTIFICATION BELL --}}
        <div id="notif-bell" class="relative w-12 h-12 bg-white rounded-2xl shadow-sm border border-gray-100 flex items-center justify-center cursor-pointer hover:border-purple-200 transition-all shrink-0">
            <i class="fas fa-bell text-gray-400 text-lg"></i>
            <span id="notif-dot" class="hidden absolute top-3 right-3 w-3 h-3 bg-red-500 border-2 border-white rounded-full animate-ping"></span>
        </div>

        {{-- CALENDAR TRIGGER --}}
        <div id="calendar-trigger" class="relative flex-1 lg:flex-none flex items-center justify-between lg:justify-end gap-3 bg-white px-5 py-3 rounded-2xl shadow-sm border border-gray-100 cursor-pointer hover:border-purple-200 transition-all">
            <div class="text-left lg:text-right">
                <p class="text-[9px] text-gray-400 font-bold uppercase tracking-widest">Hari Ini</p>
                <p class="text-xs md:text-sm font-bold text-gray-700">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center text-[#a044ff]">
                <i class="far fa-calendar-alt text-lg"></i>
            </div>
            <input type="text" id="datepicker" class="absolute inset-0 opacity-0 cursor-pointer">
        </div>
    </div>
</div>

{{-- INFORMASI ROLE ALERT (Hanya Admin Prodi) --}}
@if((int)session('user')['role_id'] !== 1)
<div class="mb-6 bg-blue-50 border border-blue-100 rounded-2xl p-4 flex items-center gap-4">
    <div class="w-10 h-10 bg-blue-500 rounded-xl flex items-center justify-center text-white shrink-0">
        <i class="fas fa-info-circle"></i>
    </div>
    <p class="text-sm text-blue-800 leading-tight">
        <span class="font-bold">Mode Terbatas:</span> Saat ini Anda masuk sebagai <strong>Admin {{ session('user')['prodi_nama'] }}</strong>. 
        Data yang ditampilkan di bawah hanya mencakup kunjungan yang ditujukan ke program studi Anda.
    </p>
</div>
@endif

{{-- CARDS --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-10">
    @php
        $statsData = [
            ['title' => 'Total Kunjungan', 'value' => $stats['total_tamu'], 'icon' => 'fas fa-users', 'color' => 'from-[#ff3366] to-[#ff5e84]'],
            ['title' => 'Tamu Hari Ini', 'value' => $stats['tamu_hari_ini'], 'icon' => 'fas fa-clock', 'color' => 'from-[#a044ff] to-[#be7dff]'],
            ['title' => 'Indeks Kepuasan', 'value' => number_format($stats['rata_rata_puas'], 1), 'icon' => 'fas fa-star', 'color' => 'from-[#3366ff] to-[#5e84ff]'],
            ['title' => 'Tren 7 Hari', 'value' => $grafik->sum('jumlah'), 'icon' => 'fas fa-chart-line', 'color' => 'from-[#00c6fb] to-[#005bea]']
        ];
    @endphp

    @foreach($statsData as $s)
    <div class="bg-gradient-to-r {{ $s['color'] }} p-5 md:p-6 rounded-[2rem] text-white shadow-xl relative overflow-hidden group hover:scale-[1.03] transition-all">
        <div class="absolute -right-4 -top-4 w-24 h-24 bg-white/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
        <div class="relative z-10">
            <div class="bg-white/20 w-10 h-10 rounded-xl flex items-center justify-center backdrop-blur-md mb-4">
                <i class="{{ $s['icon'] }} text-lg"></i>
            </div>
            <h3 class="text-2xl md:text-4xl font-black tracking-tight">{{ $s['value'] }}</h3>
            <p class="text-[10px] md:text-xs opacity-90 font-bold uppercase tracking-wide mt-1">{{ $s['title'] }}</p>
        </div>
    </div>
    @endforeach
</div>

{{-- CHARTS --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
    <div class="lg:col-span-2 bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col min-h-[400px]">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Tren Kunjungan</h3>
                <p class="text-xs text-gray-400 font-medium">Data harian seminggu terakhir</p>
            </div>
            <div class="w-10 h-10 bg-blue-50 rounded-full flex items-center justify-center text-blue-500"><i class="fas fa-chart-line"></i></div>
        </div>
        <div class="flex-1 relative"><canvas id="lineChart"></canvas></div>
    </div>

    <div class="bg-white rounded-[2rem] p-6 md:p-8 shadow-sm border border-gray-100 flex flex-col min-h-[400px]">
        <div class="flex items-center justify-between mb-8">
            <div>
                <h3 class="font-bold text-gray-800 text-lg">Analisa Layanan</h3>
                <p class="text-xs text-gray-400 font-medium">Skor berdasarkan kategori</p>
            </div>
            <div class="w-10 h-10 bg-pink-50 rounded-full flex items-center justify-center text-pink-500"><i class="fas fa-bullseye"></i></div>
        </div>
        <div class="flex-1 relative flex items-center justify-center"><canvas id="radarChart"></canvas></div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        flatpickr("#datepicker", { locale: "id", dateFormat: "Y-m-d", positionElement: document.getElementById('calendar-trigger'), position: "auto" });
        
        Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
        Chart.defaults.color = '#94a3b8';

        const userId = "{{ session('user')['email'] ?? 'guest' }}";        const storageKey = 'last_notif_id_user_' + userId;

        let lastNotifiedId = localStorage.getItem(storageKey);
        const notifDot = document.getElementById('notif-dot');
        const bellIcon = document.querySelector('#notif-bell i');

function checkNewKunjungan() {
    fetch("{{ route('admin.check-notification') }}")
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success' && data.latest_id) {
                const currentId = data.latest_id.toString();
                
                // Jika ID berbeda dengan yang terakhir disimpan di browser
                if (lastNotifiedId !== currentId) {
                    localStorage.setItem(storageKey, currentId);
                    lastNotifiedId = currentId;

                    showNotification(data.nama, data.keperluan);                    
                    // Efek Visual
                    notifDot.classList.remove('hidden');
                    bellIcon.classList.replace('text-gray-400', 'text-yellow-500');
                    bellIcon.classList.add('animate-bounce');
                }
            }
        })
        .catch(err => console.error("Notif Error:", err));
}

        function showNotification(nama, keperluan) {

            const displayNama = nama || "Tamu Baru";
            const displayKeperluan = keperluan || "Kunjungan";
    
            Swal.fire({
                title: '<div class="flex items-center gap-3"><i class="fas fa-bell animate-tada text-yellow-400"></i> <span class="text-indigo-600">Tamu Prodi Baru!</span></div>',                icon: 'success',
                html: `
                    <div class="text-left border-t border-gray-100 pt-3 mt-2">
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Nama Pengunjung</p>
                        <p class="font-bold text-gray-800 text-lg mb-3">${displayNama}</p>
                        <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest mb-1">Keperluan</p>
                        <p class="text-gray-600 text-sm italic bg-gray-50 p-2 rounded-lg border-l-4 border-indigo-500">"${displayKeperluan}"</p>
                    </div>
                `,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 30000,
                timerProgressBar: true,
                showCloseButton: true,
                background: '#ffffff',
                customClass: { popup: 'rounded-[2rem] shadow-2xl border-2 border-indigo-100' },
                didOpen: () => {
                    const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
                    audio.play().catch(e => console.log("Audio blocked by browser"));
                }
            });
        }

        // Jalankan polling setiap 60 detik
        setInterval(checkNewKunjungan, 10000);
        // Jalankan sekali saat halaman terbuka
        checkNewKunjungan();

        // --- SAMPAI SINI ---

        // Reset bell saat diklik
        document.getElementById('notif-bell').addEventListener('click', () => {
            notifDot.classList.add('hidden');
            bellIcon.classList.replace('text-yellow-500', 'text-gray-400');
            bellIcon.classList.remove('animate-bounce');
        });

        // Line Chart
        const lineCtx = document.getElementById('lineChart').getContext('2d');
        const gradient = lineCtx.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, 'rgba(51, 102, 255, 0.2)');
        gradient.addColorStop(1, 'rgba(51, 102, 255, 0)');

        new Chart(lineCtx, {
            type: 'line',
            data: {
                labels: @json($grafik->pluck('tanggal')),
                datasets: [{
                    label: 'Pengunjung',
                    data: @json($grafik->pluck('jumlah')),
                    borderColor: '#3366ff',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#fff',
                    pointBorderColor: '#3366ff',
                    pointBorderWidth: 2,
                    pointRadius: 4
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                plugins: { legend: { display: false } },
                scales: { 
                    y: { beginAtZero: true, grid: { borderDash: [2, 4], color: '#f1f5f9' } }, 
                    x: { grid: { display: false } } 
                }
            }
        });

        // Radar Chart di dashboard.blade.php
        new Chart(document.getElementById('radarChart'), {
            type: 'radar',
            data: {
                // SESUAIKAN URUTAN INI
                labels: ['Fasilitas', 'Pelayanan', 'Kebersihan', 'Respon', 'Informasi'], 
                datasets: [{
                    label: 'Skor',
                    // Pastikan mapping variabelnya benar sesuai indeks GAS kamu
                    data: [
                        {{ $avg_aspek->p1 }}, // Fasilitas
                        {{ $avg_aspek->p2 }}, // Pelayanan
                        {{ $avg_aspek->p3 }}, // Kebersihan
                        {{ $avg_aspek->p4 }}, // Respon
                        {{ $avg_aspek->p5 }}  // Informasi
                    ],
                    backgroundColor: 'rgba(255, 51, 102, 0.2)',
                    borderColor: '#ff3366',
                    borderWidth: 2,
                    pointBackgroundColor: '#ff3366',
                }]
            },
            options: { 
                responsive: true, 
                maintainAspectRatio: false, 
                scales: { 
                    r: { 
                        angleLines: { color: '#e2e8f0' }, 
                        suggestMin: 0, 
                        suggestMax: 5, // Batasi skala ke 5 agar grafik mekar
                        min: 0,        // Tambahkan min agar pasti mulai dari tengah
                        max: 5,        // Tambahkan max agar bentuk radar terlihat jelas
                        ticks: { 
                            stepSize: 1,
                            display: false 
                        },
                        pointLabels: { font: { size: 10, weight: '600' } }
                    } 
                }, 
                plugins: { legend: { display: false } } 
            }
        });
    });
</script>
@endsection