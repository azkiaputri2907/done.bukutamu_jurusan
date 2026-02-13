@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gradient-to-r from-pink-400 via-purple-400 to-blue-500 py-8 px-4 sm:px-6 lg:px-8 flex items-center justify-center font-sans">
    
    <div class="w-full max-w-4xl space-y-6">
        
        <div class="flex flex-col md:flex-row md:items-center justify-between w-full gap-4"> 
            <div class="text-left"> 
                <h1 class="text-3xl md:text-4xl font-extrabold text-white drop-shadow-md">
                    Isi Buku Tamu
                </h1>
                <nav class="flex text-white/90 mt-2 text-sm font-medium tracking-wide drop-shadow-sm" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li class="inline-flex items-center">
                            <a href="{{ route('guest.landing') }}" class="inline-flex items-center hover:text-white hover:underline transition-all">
                                <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a2 2 0 012-2h2a2 2 0 012 2v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path></svg>
                                Home
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-white/60" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg>
                                <span class="ml-1 md:ml-2 text-white font-bold">Form Buku Tamu</span>
                            </div>
                        </li>
                    </ol>
                </nav>
            </div>
        </div>

        <div class="bg-white/95 backdrop-blur-xl rounded-3xl shadow-2xl overflow-hidden border border-white/40 relative">
            
            <div class="h-1.5 bg-gradient-to-r from-pink-500 via-purple-500 to-blue-500 w-full"></div>

            <div class="p-6 md:p-10">
                
                <div class="flex items-center mb-6 text-gray-800">
                    <div class="bg-blue-100 p-2 rounded-lg mr-3 text-blue-600">
                        <i class="fas fa-edit text-xl"></i>
                    </div>
                    <h3 class="text-xl font-bold">Formulir Data Pengunjung</h3>
                </div>

                <form action="{{ route('guest.store') }}" method="POST" class="space-y-6">
                    @csrf
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8">
                        
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nomor Kunjungan (Auto)</label>
                            <input type="text" value="C0-{{ date('Ymd') }}-XXX" readonly 
                                class="w-full px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-500 font-mono font-bold cursor-not-allowed focus:outline-none">
                            <p class="text-xs text-blue-500 mt-1 italic">*Simpan nomor kunjungan ini</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Waktu Kunjungan</label>
                            <div class="relative">
                                <input type="text" value="{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMMM YYYY') }}" readonly 
                                    class="w-full pl-10 px-4 py-3 rounded-xl bg-gray-100 border border-gray-200 text-gray-600 cursor-default focus:outline-none font-medium">
                                <div class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-500">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 border-t border-gray-100 my-2"></div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">NIM / NIP / NIK <span class="text-pink-500">*</span></label>
                            <div class="flex flex-col sm:flex-row gap-3">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-4 text-blue-500">
                                        <i class="fas fa-id-card text-lg"></i>
                                    </div>
                                    <input type="text" name="identitas_no" id="identitas_no" required placeholder="Masukkan nomor identitas..." 
                                        class="w-full pl-12 px-4 py-3 rounded-xl border border-gray-300 bg-white text-gray-800 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition-all shadow-sm">
                                </div>
                                <button type="button" id="btnCek" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-xl font-bold shadow-md active:scale-95 transition-all flex items-center justify-center gap-2 whitespace-nowrap">
                                    <i class="fas fa-search"></i>
                                    <span>CEK DATA</span>
                                </button>
                            </div>
                            <p class="text-xs text-gray-500 mt-2 ml-1">Gunakan tombol <strong>CEK DATA</strong> untuk memuat profil otomatis.</p>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap <span class="text-pink-500">*</span></label>
                            <input name="nama_lengkap" id="nama_lengkap" type="text" required placeholder="Isi nama lengkap..." 
                                class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-gray-800 focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition-all shadow-sm">
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Program Studi / Instansi Asal <span class="text-pink-500">*</span></label>
                            <div class="relative">
                                <select name="asal_instansi" id="asal_instansi" required
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition-all appearance-none cursor-pointer shadow-sm">
                                    <option value="" selected disabled>-- Pilih Program Studi --</option>
                                    <option value="D3 Teknik Listrik">D3 Teknik Listrik</option>
                                    <option value="D3 Teknik Elektronika">D3 Teknik Elektronika</option>
                                    <option value="D3 Teknik Informatika">D3 Teknik Informatika</option>
                                    <option value="D4 Teknologi Rekayasa Otomasi">D4 Teknologi Rekayasa Otomasi</option>
                                    <option value="D4 Sistem Informasi Kota Cerdas">D4 Sistem Informasi Kota Cerdas</option>
                                    <option value="D4 Teknologi Rekayasa Pembangkit Energi">D4 Teknologi Rekayasa Pembangkit Energi</option>
                                    <option value="Lainnya / Instansi Luar">Lainnya / Instansi Luar</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Keperluan Kunjungan <span class="text-pink-500">*</span></label>
                            <div class="relative">
                                <select name="keperluan" id="keperluan" required onchange="toggleKeperluanLainnya()"
                                    class="w-full px-4 py-3 rounded-xl border border-gray-300 bg-white text-gray-700 font-medium focus:ring-2 focus:ring-purple-400 focus:border-purple-400 outline-none transition-all appearance-none cursor-pointer shadow-sm">
                                    <option value="" selected disabled>-- Pilih Keperluan --</option>
                                    @foreach($keperluan_master as $k)
                                        <option value="{{ $k->keterangan }}">{{ $k->keterangan }}</option>
                                    @endforeach
                                    <option value="Lainnya">Lainnya (Sebutkan...)</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                        </div>

                        <div class="md:col-span-2 hidden transition-all duration-300 ease-in-out" id="input_keperluan_lainnya">
                            <label class="block text-sm font-bold text-blue-600 mb-2">Detail Keperluan Lainnya</label>
                            <textarea name="keperluan_lainnya" id="keperluan_lainnya" rows="3" placeholder="Jelaskan keperluan Anda secara detail..." 
                                class="w-full px-4 py-3 rounded-xl border border-blue-200 bg-blue-50 text-gray-800 focus:ring-2 focus:ring-blue-400 focus:border-blue-400 outline-none transition-all resize-none shadow-inner"></textarea>
                        </div>

                    </div>

                    <div class="pt-6 flex flex-col-reverse sm:flex-row gap-4 border-t border-gray-100 mt-8">
                        <a href="{{ route('guest.landing') }}" class="w-full sm:w-auto px-8 py-3.5 rounded-xl border border-gray-300 text-gray-600 font-bold hover:bg-gray-50 active:bg-gray-100 transition-all flex justify-center items-center gap-2 text-center no-underline">
                            Batal
                        </a>
                        <button type="submit" class="w-full sm:flex-1 px-8 py-3.5 rounded-xl bg-gradient-to-r from-pink-500 to-blue-500 hover:from-pink-400 hover:to-blue-400 text-white font-bold shadow-lg hover:shadow-xl hover:-translate-y-1 active:translate-y-0 transition-all duration-300 flex justify-center items-center gap-2">
                            <span>SIMPAN & LANJUT SURVEY</span>
                            <i class="fas fa-chevron-right"></i>
                        </button>
                    </div>

                </form>
            </div>
        </div>
        
        <p class="text-center text-white/70 text-xs font-medium tracking-wider drop-shadow-sm">
            &copy; {{ date('Y') }} Digital Guestbook System &bull; Teknik Elektro
        </p>
    </div>
</div>

<div id="customModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" onclick="closeModal()"></div>
    <div class="relative bg-white rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 transform transition-all scale-95 opacity-0 duration-300" id="modalContent">
        <div class="text-center">
            <div id="modalIcon" class="mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4"></div>
            <h3 class="text-xl font-bold text-gray-900 mb-2" id="modalTitle">Judul</h3>
            <p class="text-gray-500 mb-6" id="modalMessage">Pesan.</p>
            <button onclick="closeModal()" class="w-full py-3 px-6 bg-gradient-to-r from-purple-600 to-blue-600 text-white font-bold rounded-xl hover:shadow-lg active:scale-95 transition-all">
                Mengerti
            </button>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. MODAL LOGIC ---
        const modal = document.getElementById('customModal');
        const modalContent = document.getElementById('modalContent');

        window.showModal = function(title, message, type = 'success') {
            const iconBox = document.getElementById('modalIcon');
            document.getElementById('modalTitle').innerText = title;
            document.getElementById('modalMessage').innerText = message;

            if (type === 'success') {
                iconBox.className = "mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4 bg-green-100 text-green-600";
                iconBox.innerHTML = '<i class="fas fa-check text-3xl"></i>';
            } else if (type === 'info') {
                iconBox.className = "mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4 bg-blue-100 text-blue-600";
                iconBox.innerHTML = '<i class="fas fa-info text-3xl"></i>';
            } else {
                iconBox.className = "mx-auto flex items-center justify-center h-16 w-16 rounded-full mb-4 bg-amber-100 text-amber-600";
                iconBox.innerHTML = '<i class="fas fa-exclamation text-3xl"></i>';
            }

            modal.classList.remove('hidden');
            setTimeout(() => {
                modalContent.classList.remove('scale-95', 'opacity-0');
                modalContent.classList.add('scale-100', 'opacity-100');
            }, 10);
        };

        window.closeModal = function() {
            modalContent.classList.remove('scale-100', 'opacity-100');
            modalContent.classList.add('scale-95', 'opacity-0');
            setTimeout(() => modal.classList.add('hidden'), 300);
        };

        // --- 2. AUTO DETECT PRODI SAAT MENGETIK ---
        const inputIdentitas = document.getElementById('identitas_no');
        const selectInstansi = document.getElementById('asal_instansi');

        inputIdentitas.addEventListener('input', function(e) {
            let value = this.value.trim().toUpperCase();

            if (value.startsWith('C01')) {
                selectInstansi.value = 'D3 Teknik Listrik';
            } else if (value.startsWith('C02')) {
                selectInstansi.value = 'D3 Teknik Elektronika';
            } else if (value.startsWith('C03')) {
                selectInstansi.value = 'D3 Teknik Informatika';
            } else if (value.startsWith('C04')) {
                selectInstansi.value = 'D4 Teknologi Rekayasa Otomasi';
            } else if (value.startsWith('C05')) {
                selectInstansi.value = 'D4 Sistem Informasi Kota Cerdas';
            } else if (value.startsWith('C06')) {
                selectInstansi.value = 'D4 Teknologi Rekayasa Pembangkit Energi';
            } else if (/^\d/.test(value)) { 
                // Jika diawali angka (0-9)
                selectInstansi.value = 'Lainnya / Instansi Luar';
            }
            // Jika dihapus/kosong, biarkan apa adanya atau user bisa ganti manual
        });

        // --- 3. CEK DATA LOGIC (FETCH API) ---
        document.getElementById('btnCek').addEventListener('click', function() {
            let noId = inputIdentitas.value.trim();
            if(noId === "") {
                showModal('Peringatan', 'Harap masukkan nomor identitas (NIM/NIP) terlebih dahulu!', 'warning');
                return;
            }

            let btn = this;
            let originalContent = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            btn.disabled = true;

            fetch(`{{ route('guest.check') }}?no_id=${noId}`)
                .then(response => response.json())
                .then(data => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;

                    // PERBAIKAN: Cek apakah data benar-benar ada dan tidak undefined
                    if(data && data.nama_lengkap) {
                        document.getElementById('nama_lengkap').value = data.nama_lengkap;
                        selectInstansi.value = data.asal_instansi;
                        showModal('Berhasil!', `Data ditemukan. Halo, ${data.nama_lengkap}.`, 'success');
                    } else {
                        // Jika data tidak ditemukan, kosongkan nama (jangan tulis undefined)
                        // dan JANGAN ubah pilihan dropdown prodi yang sudah terdeteksi otomatis
                        document.getElementById('nama_lengkap').value = '';
                        document.getElementById('nama_lengkap').focus();
                        showModal('Pengunjung Baru', 'Identitas belum ada di sistem. Silakan isi nama secara manual.', 'info');
                    }
                })
                .catch(err => {
                    btn.innerHTML = originalContent;
                    btn.disabled = false;
                    showModal('Error', 'Terjadi kesalahan saat menghubungi server.', 'warning');
                });
        });

    });

    // --- 4. TOGGLE KEPERLUAN LAINNYA ---
    // Dipindah ke luar DOMContentLoaded karena dipanggil langsung lewat HTML onchange=""
    function toggleKeperluanLainnya() {
        const selectKeperluan = document.getElementById('keperluan');
        const divLainnya = document.getElementById('input_keperluan_lainnya');
        const txtLainnya = document.getElementById('keperluan_lainnya');

        if (selectKeperluan.value === 'Lainnya') {
            divLainnya.classList.remove('hidden');
            divLainnya.classList.add('block');
            txtLainnya.setAttribute('required', 'required');
            txtLainnya.focus();
        } else {
            divLainnya.classList.add('hidden');
            divLainnya.classList.remove('block');
            txtLainnya.removeAttribute('required');
            txtLainnya.value = '';
        }
    }
</script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
@endsection