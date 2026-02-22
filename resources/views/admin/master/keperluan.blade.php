@extends('layouts.admin')

@section('content')

{{-- Script Pendukung --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- === HEADER SECTION === --}}
<div class="mb-8" x-data="{ addModalOpen: false }">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            {{-- Breadcrumb Modern --}}
            <nav class="flex items-center gap-2 mb-3">
                <a href="{{ route('admin.dashboard') }}" class="text-xs font-semibold text-gray-400 hover:text-indigo-600 transition-colors">Dashboard</a>
                <i class="fas fa-chevron-right text-[10px] text-gray-300"></i>
                <span class="text-xs font-semibold text-indigo-600 bg-indigo-50 px-2 py-0.5 rounded-md">Keperluan</span>
            </nav>
            
            {{-- Title --}}
            <h1 class="text-4xl font-extrabold text-gray-900 tracking-tight">
                Master <span class="bg-clip-text text-transparent bg-gradient-to-r from-blue-600 to-indigo-600">Keperluan</span>
            </h1>
            <p class="text-gray-500 font-medium mt-2 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"></span>
                Kelola daftar tujuan kunjungan tamu secara dinamis.
            </p>
        </div>

        {{-- Action Buttons --}}
        <div class="flex items-center gap-4">
            <div class="hidden md:flex bg-white px-6 py-3 rounded-2xl shadow-sm border border-gray-100 items-center gap-4">
                <div class="flex flex-col">
                    <span class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Data</span>
                    <span class="text-2xl font-black text-gray-800">{{ count($keperluan) }}</span>
                </div>
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white flex items-center justify-center shadow-md shadow-blue-200">
                    <i class="fas fa-list-ul"></i>
                </div>
            </div>
            
            <button @click="addModalOpen = true" 
                class="flex items-center gap-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 rounded-2xl font-bold shadow-xl shadow-indigo-100 hover:shadow-indigo-200 transition-all duration-300 transform hover:-translate-y-1 active:scale-95">
                <div class="bg-white/20 p-1.5 rounded-lg">
                    <i class="fas fa-plus text-xs"></i>
                </div>
                <span>Tambah Keperluan</span>
            </button>
        </div>
    </div>

    {{-- === MODAL TAMBAH (Style Match) === --}}
    <div x-show="addModalOpen" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-cloak 
         class="fixed inset-0 z-[120] flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div @click.away="addModalOpen = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden border border-white/20">
            <div class="bg-gradient-to-r from-blue-600 to-indigo-700 p-8 text-white relative">
                <div class="relative z-10">
                    <h3 class="text-2xl font-black">Tambah Data</h3>
                    <p class="text-blue-100 text-xs font-medium opacity-80">Masukkan kategori keperluan kunjungan baru</p>
                </div>
                <i class="fas fa-folder-plus absolute right-8 top-1/2 -translate-y-1/2 text-5xl text-white/10"></i>
            </div>
            
            <form action="{{ route('admin.keperluan.store') }}" method="POST" class="p-8">
                @csrf
                <div class="space-y-4">
                    <div>
                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Keterangan Keperluan</label>
                        <textarea name="keterangan" rows="4" required 
                            placeholder="Contoh: Koordinasi Kurikulum atau Penyerahan Berkas"
                            class="w-full px-5 py-4 border-none rounded-2xl focus:ring-4 focus:ring-indigo-500/10 text-sm bg-gray-50 focus:bg-white transition font-medium text-gray-700 shadow-inner"></textarea>
                    </div>
                </div>
                
                <div class="mt-8 flex gap-3">
                    <button type="button" @click="addModalOpen = false" 
                        class="flex-1 py-4 text-sm font-bold text-gray-500 bg-gray-50 rounded-2xl hover:bg-gray-100 transition">Batal</button>
                    <button type="submit" 
                        class="flex-[2] py-4 text-sm font-bold text-white bg-indigo-600 rounded-2xl shadow-lg shadow-indigo-100 hover:bg-indigo-700 transition">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- === CONTENT TABLE SECTION === --}}
<div class="bg-white rounded-[2rem] shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50/50 border-b border-gray-100 text-[10px] uppercase tracking-[0.15em] text-gray-400 font-black">
                    <th class="px-8 py-6 w-24 text-center">No</th>
                    <th class="px-8 py-6">Keterangan Keperluan</th>
                    <th class="px-8 py-6 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @forelse($keperluan as $k)
                <tr class="group hover:bg-gray-50/80 transition duration-150" x-data="{ editModalOpen: false, showModalOpen: false }">
                    <td class="px-8 py-5 text-center">
                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-white border border-gray-100 shadow-sm text-xs font-black text-indigo-600 group-hover:scale-110 transition-transform">
                            {{ sprintf('%02d', $loop->iteration) }}
                        </span>
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex items-center gap-4">
                            <div class="w-2.5 h-2.5 rounded-full bg-indigo-500 shadow-[0_0_10px_rgba(79,70,229,0.4)]"></div>
                            <span class="text-sm font-bold text-gray-700 tracking-tight">{{ $k->keterangan }}</span>
                        </div>
                    </td>
                    <td class="px-8 py-5">
                        <div class="flex justify-center items-center gap-3">
                            {{-- Action Buttons Style Match --}}
                            <button @click="showModalOpen = true" 
                                class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm">
                                <i class="fas fa-eye text-[10px]"></i>
                            </button>

                            <button @click="editModalOpen = true" 
                                class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm">
                                <i class="fas fa-edit text-[10px]"></i>
                            </button>

                            <button type="button" onclick="confirmDelete('{{ $k->id }}', '{{ $k->keterangan }}')"
                                class="w-9 h-9 rounded-xl bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-all duration-300 flex items-center justify-center shadow-sm">
                                <i class="fas fa-trash text-[10px]"></i>
                            </button>

                            <form id="delete-form-{{ $k->id }}" action="{{ route('admin.keperluan.destroy', $k->id) }}" method="POST" class="hidden">
                                @csrf @method('DELETE')
                            </form>
                        </div>

                        {{-- MODAL DETAIL (Style Match) --}}
                        <div x-show="showModalOpen" x-cloak class="fixed inset-0 z-[130] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                            <div @click.away="showModalOpen = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-sm overflow-hidden transform transition-all">
                                <div class="bg-gradient-to-br from-blue-600 to-indigo-700 p-10 text-center relative">
                                    <div class="w-20 h-20 bg-white/20 backdrop-blur-md rounded-3xl flex items-center justify-center mx-auto mb-4 rotate-6 border border-white/30 shadow-xl">
                                        <i class="fas fa-info-circle text-3xl text-white"></i>
                                    </div>
                                    <h3 class="font-black text-white text-xl tracking-tight">Detail Info</h3>
                                </div>
                                <div class="p-10 text-center bg-white">
                                    <p class="text-[10px] font-black text-gray-400 uppercase tracking-[0.2em] mb-4">Keterangan Lengkap</p>
                                    <p class="text-gray-700 leading-relaxed font-bold text-lg italic">"{{ $k->keterangan }}"</p>
                                </div>
                                <div class="p-6 bg-gray-50 flex justify-center">
                                    <button @click="showModalOpen = false" class="w-full py-4 bg-white border border-gray-200 text-gray-600 rounded-2xl font-black text-xs hover:bg-gray-100 transition shadow-sm uppercase tracking-widest">Tutup</button>
                                </div>
                            </div>
                        </div>

                        {{-- MODAL EDIT (Style Match) --}}
                        <div x-show="editModalOpen" x-cloak class="fixed inset-0 z-[130] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
                            <div @click.away="editModalOpen = false" class="bg-white rounded-[2.5rem] shadow-2xl w-full max-w-md overflow-hidden">
                                <div class="bg-amber-500 p-8 text-white relative">
                                    <h3 class="text-2xl font-black flex items-center gap-3">
                                        <i class="fas fa-edit"></i> Edit Data
                                    </h3>
                                    <i class="fas fa-pen-nib absolute right-8 top-1/2 -translate-y-1/2 text-5xl text-white/20"></i>
                                </div>
                                <form action="{{ route('admin.keperluan.update', $k->id) }}" method="POST" class="p-8">
                                    @csrf @method('PUT')
                                    <div class="space-y-4">
                                        <label class="block text-[10px] font-black text-gray-400 uppercase tracking-widest mb-2 ml-1">Update Keterangan</label>
                                        <textarea name="keterangan" rows="4" required
                                            class="w-full px-5 py-4 border-none rounded-2xl focus:ring-4 focus:ring-amber-500/10 text-sm bg-gray-50 focus:bg-white transition font-medium text-gray-700 shadow-inner">{{ $k->keterangan }}</textarea>
                                    </div>
                                    <div class="mt-8 flex gap-3">
                                        <button type="button" @click="editModalOpen = false" class="flex-1 py-4 text-sm font-bold text-gray-500 bg-gray-50 rounded-2xl">Batal</button>
                                        <button type="submit" class="flex-[2] py-4 text-sm font-bold text-white bg-amber-500 rounded-2xl shadow-lg shadow-amber-100 hover:bg-amber-600 transition">Update Data</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="px-6 py-20 text-center">
                        <div class="flex flex-col items-center">
                            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-folder-open text-gray-200 text-3xl"></i>
                            </div>
                            <p class="text-gray-400 font-bold uppercase text-[10px] tracking-widest">Data Kosong</p>
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
            title: 'Hapus Keperluan?',
            html: `<p class="text-sm text-gray-500">Menghapus <b>"${text}"</b> dapat mempengaruhi histori data kunjungan yang menggunakan kategori ini.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Ya, Hapus Data',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-[2.5rem] p-8',
                confirmButton: 'rounded-2xl px-6 py-3 font-bold',
                cancelButton: 'rounded-2xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Menghapus...',
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
</style>

@endsection