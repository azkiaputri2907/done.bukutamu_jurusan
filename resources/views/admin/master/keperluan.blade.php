@extends('layouts.admin')

@section('content')

{{-- Script Pendukung --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Header Section --}}
<div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6" x-data="{ addModalOpen: false }">
    <div>
        <h2 class="text-2xl font-extrabold text-gray-800 tracking-tight">Master Keperluan</h2>
        <p class="text-sm text-gray-500 font-medium">Manajemen daftar pilihan keperluan kunjungan tamu secara dinamis.</p>
    </div>

    {{-- Tombol Tambah --}}
    <button @click="addModalOpen = true" 
        class="group flex items-center gap-2 bg-gradient-to-r from-[#3366ff] to-[#a044ff] text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-blue-200 transition transform hover:scale-105 hover:shadow-xl">
        <div class="bg-white/20 p-1 rounded-md group-hover:rotate-90 transition duration-300">
            <i class="fas fa-plus text-xs"></i>
        </div>
        <span>Tambah Keperluan</span>
    </button>

    {{-- MODAL TAMBAH --}}
    <div x-show="addModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak 
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.away="addModalOpen = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="bg-gray-50 p-4 border-b flex justify-between items-center">
                <h3 class="font-bold text-gray-800 flex items-center gap-2">
                    <i class="fas fa-plus-circle text-blue-500"></i> Tambah Data Baru
                </h3>
                <button @click="addModalOpen = false" class="text-gray-400 hover:text-red-500 transition">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form action="{{ route('admin.keperluan.store') }}" method="POST">
                @csrf
                <div class="p-6">
                    <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Keterangan Keperluan</label>
                    <textarea name="keterangan" rows="3" required placeholder="Contoh: Koordinasi Kurikulum atau Penyerahan Berkas"
                        class="w-full border-gray-200 rounded-xl focus:ring-[#a044ff] focus:border-[#a044ff] text-sm bg-gray-50 focus:bg-white transition"></textarea>
                </div>
                <div class="bg-gray-50 p-4 flex justify-end gap-2">
                    <button type="button" @click="addModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500 hover:text-gray-700 transition">Batal</button>
                    <button type="submit" class="px-5 py-2 text-sm font-bold text-white bg-gradient-to-r from-[#3366ff] to-[#a044ff] rounded-lg shadow-md hover:opacity-90 transition">Simpan Keperluan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Content Card --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-xs uppercase tracking-wider text-gray-500 font-bold">
                    <th class="px-6 py-5 w-20 text-center">No</th>
                    <th class="px-6 py-5">Keterangan Keperluan</th>
                    <th class="px-6 py-5 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($keperluan as $k)
                <tr class="hover:bg-blue-50/30 transition duration-150" x-data="{ editModalOpen: false, showModalOpen: false }">
                    <td class="px-6 py-4 text-center">
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-lg bg-gray-100 text-xs font-bold text-gray-500">
                            {{ $loop->iteration }}
                        </span>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                            <span class="text-sm font-semibold text-gray-700">{{ $k->keterangan }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4">
                        <div class="flex justify-center items-center gap-3">
                            {{-- Tombol Lihat --}}
                            <button @click="showModalOpen = true" title="Lihat Detail"
                                class="group w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm">
                                <i class="fas fa-eye text-xs group-hover:scale-110"></i>
                            </button>

                            {{-- Tombol Edit --}}
                            <button @click="editModalOpen = true" title="Edit Data"
                                class="group w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm">
                                <i class="fas fa-edit text-xs group-hover:scale-110"></i>
                            </button>

                            {{-- Tombol Hapus --}}
                            <button type="button" onclick="confirmDelete('{{ $k->id }}', '{{ $k->keterangan }}')" title="Hapus Data"
                                class="group w-9 h-9 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm">
                                <i class="fas fa-trash text-xs group-hover:scale-110"></i>
                            </button>

                            {{-- Form Hapus (Hidden) --}}
                            @if(isset($k->id))
                                <form id="delete-form-{{ $k->id }}" action="{{ route('admin.keperluan.destroy', $k->id) }}" method="POST" class="hidden">
                                    @csrf @method('DELETE')
                                </form>
                            @endif
                        </div>

                        {{-- MODAL DETAIL (Lihat) --}}
                        <div x-show="showModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                            <div @click.away="showModalOpen = false" class="bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden transform transition-all">
                                <div class="bg-gradient-to-r from-blue-600 to-indigo-600 p-6 text-center">
                                    <div class="w-16 h-16 bg-white/20 rounded-full flex items-center justify-center mx-auto mb-3">
                                        <i class="fas fa-info-circle text-2xl text-white"></i>
                                    </div>
                                    <h3 class="font-bold text-white text-lg">Detail Keperluan</h3>
                                </div>
                                <div class="p-8 text-center">
                                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-4">Keterangan:</p>
                                    <p class="text-gray-700 leading-relaxed font-semibold text-lg italic">"{{ $k->keterangan }}"</p>
                                </div>
                                <div class="bg-gray-50 px-6 py-4 flex justify-center">
                                    <button @click="showModalOpen = false" class="w-full py-3 bg-white border border-gray-200 text-gray-600 rounded-xl font-bold text-sm hover:bg-gray-50 transition shadow-sm">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDIT --}}
                        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                            <div @click.away="editModalOpen = false" class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
                                <div class="bg-amber-50 p-4 border-b border-amber-100 flex justify-between items-center">
                                    <h3 class="font-bold text-amber-800 flex items-center gap-2">
                                        <i class="fas fa-edit"></i> Edit Data Keperluan
                                    </h3>
                                    <button @click="editModalOpen = false" class="text-amber-400 hover:text-red-500 transition">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <form action="{{ route('admin.keperluan.update', $k->id) }}" method="POST">
                                    @csrf @method('PUT')
                                    <div class="p-6">
                                        <label class="block text-xs font-bold text-gray-400 uppercase mb-2 ml-1">Update Keterangan</label>
                                        <textarea name="keterangan" rows="3" required
                                            class="w-full border-gray-200 rounded-xl focus:ring-amber-400 focus:border-amber-400 text-sm bg-gray-50 focus:bg-white transition">{{ $k->keterangan }}</textarea>
                                    </div>
                                    <div class="bg-gray-50 p-4 flex justify-end gap-2">
                                        <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-sm font-bold text-gray-500">Batal</button>
                                        <button type="submit" class="px-6 py-2 text-sm font-bold text-white bg-amber-500 rounded-lg shadow-md hover:bg-amber-600 transition">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-folder-open text-gray-200 text-5xl mb-3"></i>
                            <p class="text-gray-400 font-medium">Belum ada data keperluan yang tersimpan.</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
    function confirmDelete(id, text) {
        Swal.fire({
            title: 'Hapus Data?',
            html: `Apakah Anda yakin ingin menghapus keperluan <br><b>"${text}"</b>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: '<i class="fas fa-trash mr-2"></i>Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[1.5rem]',
                confirmButton: 'rounded-xl px-5 py-2.5',
                cancelButton: 'rounded-xl px-5 py-2.5'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Beri feedback visual loading sebelum submit
                Swal.fire({
                    title: 'Memproses...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });
                document.getElementById('delete-form-' + id).submit();
            }
        })
    }
</script>

<style>
    [x-cloak] { display: none !important; }
    
    /* Smooth Scroll & Hover Effects */
    .table-container {
        scrollbar-width: thin;
        scrollbar-color: #e5e7eb transparent;
    }
</style>

@endsection