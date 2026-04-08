{{-- FE-DOC: Template frontend untuk resources/views/kasir/layanan/edit_jenis_layanan.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Edit Jenis Layanan')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow sticky top-0 z-10">
    @php
        $idLayanan = isset($jenis) ? $jenis->id_layanan : ($id_layanan ?? request()->route('id_layanan') ?? request('from'));
    @endphp
    <a href="{{ route('kasir.layanan.edit', ['id' => $idLayanan]) }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-bold">{{ isset($jenis) ? 'Ubah' : 'Tambah' }} Jenis Layanan</span>
</div>

<div class="px-5 mt-6 pb-8">
    
    {{-- ERROR MESSAGES --}}
    @if ($errors->any())
        <div class="bg-red-500 text-white p-4 rounded-xl mb-5 shadow-md">
            <div class="flex items-center gap-2 mb-2">
                <i class="bi bi-exclamation-triangle-fill text-xl"></i>
                <span class="font-bold">Terdapat kesalahan:</span>
            </div>
            <ul class="ml-6 list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUCCESS MESSAGE --}}
    @if (session('success'))
        <div class="bg-green-500 text-white p-4 rounded-xl mb-5 shadow-md flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-xl"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <form action="{{ isset($jenis) ? route('kasir.layanan.jenis.update', $jenis->id_jenis_layanan) : route('kasir.layanan.jenis.store', $idLayanan) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-6 rounded-2xl shadow-lg space-y-6">
        
        @csrf
        @if(isset($jenis))
            @method('PUT')
        @endif

        <input type="hidden" name="from" value="{{ $idLayanan }}">

        {{-- GAMBAR SECTION --}}
        <div>
            <label class="font-bold text-gray-700 block mb-3">
                <i class="bi bi-image text-yellow-500 mr-1"></i>
                Gambar Jenis Layanan
            </label>

            <div class="flex items-start gap-4">

                {{-- PREVIEW BOX --}}
                <div id="previewBox" class="w-28 h-28 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center shadow-md overflow-hidden border-2 border-gray-300 flex-shrink-0">
                    @if (isset($jenis) && $jenis->gambar)
                        <img id="preview" 
                             src="{{ asset('storage/' . $jenis->gambar) }}"
                             alt="Preview"
                             class="w-full h-full object-cover"
                             onerror="this.onerror=null; this.style.display='none'; document.getElementById('previewIcon').style.display='flex';">
                        <i id="previewIcon" class="bi bi-image text-gray-400 text-4xl" style="display: none;"></i>
                    @else
                        <img id="preview" 
                             src="" 
                             alt="Preview"
                             class="w-full h-full object-cover hidden">
                        <i id="previewIcon" class="bi bi-image text-gray-400 text-4xl"></i>
                    @endif
                </div>

                {{-- UPLOAD BUTTON & INFO --}}
                <div class="flex-1">
                    <label class="bg-yellow-400 hover:bg-yellow-500 px-6 py-3 rounded-xl text-black font-bold cursor-pointer inline-flex items-center gap-2 transition-all shadow-md hover:shadow-lg active:scale-95">
                        <i class="bi bi-camera-fill text-xl"></i>
                        {{ isset($jenis) && $jenis->gambar ? 'Ganti Gambar' : 'Pilih Gambar' }}
                        <input type="file" 
                               name="gambar" 
                               id="inputGambar"
                               class="hidden" 
                               accept="image/jpeg,image/png,image/jpg,image/gif,image/webp"
                               onchange="loadPreview(event)">
                    </label>
                    
                    <div class="mt-3 space-y-1">
                        <p class="text-xs text-gray-500">
                            <i class="bi bi-info-circle"></i>
                            Format: JPG, PNG, GIF, WEBP (Maksimal 2MB)
                        </p>
                        @if(isset($jenis) && $jenis->gambar)
                            <p class="text-xs text-green-600 font-semibold flex items-center gap-1">
                                <i class="bi bi-check-circle-fill"></i>
                                Gambar saat ini: {{ basename($jenis->gambar) }}
                            </p>
                        @endif
                        <p id="selectedFileName" class="text-xs text-blue-600 font-semibold hidden">
                            <i class="bi bi-file-image"></i>
                            File baru: <span id="fileName"></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- NAMA JENIS --}}
        <div>
            <label class="font-bold text-gray-700 block mb-2">
                <i class="bi bi-tag text-yellow-500 mr-1"></i>
                Nama Jenis Layanan
            </label>
            <input type="text" 
                   name="nama_jenis" 
                   value="{{ old('nama_jenis', $jenis->nama_jenis ?? '') }}"
                   class="w-full bg-gray-50 border-2 border-gray-200 p-4 rounded-xl text-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all" 
                   placeholder="Contoh: Cuci Kering Lipat"
                   required>
        </div>

        {{-- SATUAN --}}
        <div>
            <label class="font-bold text-gray-700 block mb-2">
                <i class="bi bi-box text-yellow-500 mr-1"></i>
                Satuan
            </label>
            <div class="flex items-center gap-3">
                <select name="id_satuan" 
                        class="flex-1 bg-gray-50 border-2 border-gray-200 p-4 rounded-xl text-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all"
                        required>
                    @foreach($satuan as $s)
                        <option value="{{ $s->id_satuan }}"
                            @if(old('id_satuan', request('new_satuan', $jenis->id_satuan ?? '')) == $s->id_satuan) selected @endif>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('kasir.satuan.create', [
                    'from' => isset($jenis) ? 'edit-jenis' : 'create-jenis',
                    'id_layanan' => $idLayanan,
                    'id_jenis' => $jenis->id_jenis_layanan ?? null
                ]) }}" 
                   class="bg-blue-500 hover:bg-blue-600 text-white px-5 py-4 rounded-xl font-bold transition-all shadow-md hover:shadow-lg whitespace-nowrap active:scale-95">
                    <i class="bi bi-plus-circle-fill mr-1"></i>
                    Tambah
                </a>
            </div>
        </div>

        {{-- HARGA --}}
        <div>
            <label class="font-bold text-gray-700 block mb-2">
                <i class="bi bi-cash-coin text-yellow-500 mr-1"></i>
                Harga
            </label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-bold text-lg">Rp</span>
                <input type="number" 
                       name="harga" 
                       value="{{ old('harga', $jenis->harga ?? '') }}"
                       class="w-full bg-gray-50 border-2 border-gray-200 pl-14 pr-4 py-4 rounded-xl text-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all" 
                       placeholder="0"
                       min="0"
                       required>
            </div>
        </div>

        {{-- LAMA PENGERJAAN --}}
        <div>
            <label class="font-bold text-gray-700 block mb-2">
                <i class="bi bi-clock text-yellow-500 mr-1"></i>
                Lama Pengerjaan
            </label>
            <div class="flex items-center gap-3">
                <input type="number" 
                       name="lama" 
                       value="{{ old('lama', $jenis->lama ?? '') }}"
                       class="flex-1 bg-gray-50 border-2 border-gray-200 p-4 rounded-xl text-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all" 
                       placeholder="0"
                       min="0"
                       required>
                
                <select name="lama_satuan" 
                        class="bg-gray-50 border-2 border-gray-200 px-6 py-4 rounded-xl text-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all"
                        required>
                    <option value="Hari" {{ old('lama_satuan', $jenis->lama_satuan ?? 'Hari') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Jam" {{ old('lama_satuan', $jenis->lama_satuan ?? '') == 'Jam' ? 'selected' : '' }}>Jam</option>
                </select>
            </div>
        </div>

        {{-- KETERANGAN --}}
        <div>
            <label class="font-bold text-gray-700 block mb-2">
                <i class="bi bi-journal-text text-yellow-500 mr-1"></i>
                Keterangan (Opsional)
            </label>
            <textarea name="keterangan"
                      rows="4"
                      class="w-full bg-gray-50 border-2 border-gray-200 p-4 rounded-xl text-lg focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none transition-all resize-none"
                      placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan', $jenis->keterangan ?? '') }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="flex gap-3 pt-4">
            <a href="{{ route('kasir.layanan.edit', ['id' => $idLayanan]) }}"
               class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-4 rounded-xl text-lg font-bold text-center transition-all shadow-md hover:shadow-lg active:scale-95">
                <i class="bi bi-x-circle mr-1"></i>
                Batal
            </a>
            
            <button type="submit"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-4 rounded-xl text-lg font-bold transition-all shadow-lg hover:shadow-xl active:scale-95">
                <i class="bi bi-check-circle-fill mr-1"></i>
                {{ isset($jenis) ? 'Perbarui' : 'Simpan' }} Jenis
            </button>
        </div>
    </form>
</div>

{{-- PREVIEW SCRIPT --}}
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
function loadPreview(event) {
    const file = event.target.files[0];
    
    if (!file) return;
    
    const preview = document.getElementById('preview');
    const icon = document.getElementById('previewIcon');
    const previewBox = document.getElementById('previewBox');
    const selectedFileName = document.getElementById('selectedFileName');
    const fileName = document.getElementById('fileName');
    
    // Validasi ukuran file (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        alert('❌ Ukuran file maksimal 2MB!\n\nFile Anda: ' + (file.size / 1024 / 1024).toFixed(2) + ' MB');
        event.target.value = '';
        return;
    }
    
    // Validasi tipe file
    const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!validTypes.includes(file.type)) {
        alert('❌ Format file tidak valid!\n\nHanya menerima: JPG, PNG, GIF, WEBP');
        event.target.value = '';
        return;
    }
    
    // Tampilkan nama file yang dipilih
    fileName.textContent = file.name;
    selectedFileName.classList.remove('hidden');
    
    // Load preview
    const reader = new FileReader();
    
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.classList.remove('hidden');
        preview.style.display = 'block';
        
        if (icon) {
            icon.style.display = 'none';
        }
        
        // Ubah gradient box jadi putih
        previewBox.classList.remove('from-gray-100', 'to-gray-200');
        previewBox.classList.add('bg-white');
        
        // Animasi smooth
        preview.style.opacity = '0';
        setTimeout(() => {
            preview.style.transition = 'opacity 0.3s ease-in-out';
            preview.style.opacity = '1';
        }, 10);
    };
    
    reader.onerror = function() {
        alert('❌ Gagal membaca file!\n\nSilakan coba lagi.');
        event.target.value = '';
    };
    
    reader.readAsDataURL(file);
}

// Auto-hide success message after 5 seconds
window.addEventListener('DOMContentLoaded', function() {
    const successMsg = document.querySelector('.bg-green-500');
    if (successMsg) {
        setTimeout(() => {
            successMsg.style.transition = 'opacity 0.5s ease-out';
            successMsg.style.opacity = '0';
            setTimeout(() => successMsg.remove(), 500);
        }, 5000);
    }
});
</script>

@endsection
