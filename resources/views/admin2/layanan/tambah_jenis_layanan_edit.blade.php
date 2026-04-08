{{-- FE-DOC: Template frontend untuk resources/views/admin2/layanan/tambah_jenis_layanan_edit.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('admin2.layanan.edit', $jenis->id_layanan ?? 0) }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-bold">Edit Jenis Layanan</span>
</div>

<div class="px-5 mt-6">

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-xl mb-5">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5">
            <ul class="list-disc ml-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM EDIT --}}
    <form action="{{ route('admin2.layanan.jenis.update', $jenis->id_jenis_layanan) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-6 rounded-2xl shadow-lg space-y-5">
        @csrf
        @method('PUT')

        {{-- GAMBAR --}}
        {{-- GAMBAR --}}
<div>
    <label class="font-semibold text-gray-700 block mb-2">Gambar Jenis Layanan</label>
    
    {{-- Debug Info (hapus setelah testing) --}}
    @if(!empty($jenis->gambar))
        <div class="mb-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs">
            <p><strong>Path DB:</strong> {{ $jenis->gambar }}</p>
            <p><strong>Full Path:</strong> {{ asset('storage/' . $jenis->gambar) }}</p>
            <p><strong>File Exists:</strong> {{ file_exists(public_path('storage/' . $jenis->gambar)) ? 'Yes ✅' : 'No ❌' }}</p>
        </div>
    @endif
    
    {{-- Preview Container --}}
    <div id="imagePreview" class="mb-3">
        @if(!empty($jenis->gambar))
            <img id="previewImg" 
                 src="{{ asset('storage/' . $jenis->gambar) }}?t={{ time() }}" 
                 alt="Preview" 
                 class="w-full max-w-xs h-48 object-cover rounded-xl border-2 border-gray-200"
                 onerror="console.error('Image failed to load:', this.src); this.parentElement.innerHTML = '<div class=\'w-full max-w-xs h-48 bg-gray-100 rounded-xl flex items-center justify-center\'><i class=\'bi bi-exclamation-triangle text-4xl text-red-500\'></i><span class=\'ml-2\'>Gambar tidak ditemukan</span></div>';">
        @else
            <div class="w-full max-w-xs h-48 bg-gray-100 rounded-xl flex items-center justify-center">
                <i class="bi bi-image text-4xl text-gray-300"></i>
            </div>
        @endif
    </div>

    {{-- Upload Button --}}
    <div class="relative">
        <input type="file" 
               name="gambar" 
               id="gambar"
               accept="image/*"
               class="hidden"
               onchange="previewImage(event)">
        <label for="gambar" 
               class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 rounded-xl cursor-pointer transition">
            <i class="bi bi-image text-xl"></i>
            <span class="font-semibold">{{ !empty($jenis->gambar) ? 'Ganti Gambar' : 'Pilih Gambar' }}</span>
        </label>
        <span id="fileName" class="ml-3 text-sm text-gray-600"></span>
    </div>
    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, JPEG, GIF, WEBP (Max: 2MB)</p>
    
    @if(!empty($jenis->gambar))
        <p class="text-xs text-blue-600 mt-1">
            <i class="bi bi-info-circle"></i> Gambar saat ini: {{ basename($jenis->gambar) }}
        </p>
    @endif
</div>

        {{-- NAMA JENIS --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Nama Jenis Layanan</label>
            <input type="text" 
                   name="nama_jenis" 
                   value="{{ old('nama_jenis', $jenis->nama_jenis) }}"
                   class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                   placeholder="Contoh: Cuci Kering"
                   required>
        </div>

        {{-- SATUAN --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Satuan</label>
            <div class="flex items-center gap-3">
                <select name="id_satuan" 
                        class="flex-1 p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                        required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($satuan as $s)
                        <option value="{{ $s->id_satuan }}"
                            {{ old('id_satuan', $jenis->id_satuan) == $s->id_satuan ? 'selected' : '' }}>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>

                {{-- Button Tambah Satuan --}}
                <a href="{{ route('satuan.create', [
                        'from' => 'edit-jenis',
                        'id_layanan' => $jenis->id_layanan,
                        'id_jenis' => $jenis->id_jenis_layanan
                    ]) }}"
                   class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-xl text-sm font-semibold transition whitespace-nowrap">
                    <i class="bi bi-plus-circle"></i> Tambah
                </a>
            </div>
        </div>

        {{-- HARGA --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Harga</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="number" 
                       name="harga" 
                       value="{{ old('harga', $jenis->harga) }}"
                       class="w-full p-3 pl-12 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                       placeholder="0"
                       min="0"
                       required>
            </div>
        </div>

        {{-- LAMA PENGERJAAN --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Lama Pengerjaan</label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <input type="number" 
                           name="lama" 
                           value="{{ old('lama', $jenis->lama) }}"
                           class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                           placeholder="0"
                           min="0"
                           required>
                </div>
                <div>
                    <select name="lama_satuan"
                            class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                            required>
                        <option value="">-- Pilih --</option>
                        <option value="Jam" {{ old('lama_satuan', $jenis->lama_satuan) == 'Jam' ? 'selected' : '' }}>Jam</option>
                        <option value="Hari" {{ old('lama_satuan', $jenis->lama_satuan) == 'Hari' ? 'selected' : '' }}>Hari</option>
                        <option value="Minggu" {{ old('lama_satuan', $jenis->lama_satuan) == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- KETERANGAN --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Keterangan (Opsional)</label>
            <textarea name="keterangan"
                      rows="3"
                      class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                      placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan', $jenis->keterangan) }}</textarea>
        </div>

        {{-- BUTTONS --}}
        <div class="flex justify-between items-center pt-4">
            <a href="{{ route('admin2.layanan.edit', $jenis->id_layanan) }}"
               class="px-6 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                <i class="bi bi-x-circle"></i>
                <span>Batal</span>
            </a>
            <button type="submit"
                    class="px-8 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold transition inline-flex items-center gap-2 shadow-lg">
                <i class="bi bi-check-circle-fill"></i>
                <span>Update Jenis</span>
            </button>
        </div>
    </form>
</div>

{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const fileName = document.getElementById('fileName');

    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file terlalu besar! Maksimal 2MB');
            event.target.value = '';
            return;
        }

        // Validasi tipe file
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung! Gunakan JPG, PNG, GIF, atau WEBP');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        reader.readAsDataURL(file);
        fileName.textContent = file.name;
    } else {
        // Jika cancel/kosong, tetap tampilkan gambar lama jika ada
        @if(!empty($jenis->gambar))
            previewImg.src = "{{ asset('storage/' . $jenis->gambar) }}";
            preview.classList.remove('hidden');
        @else
            preview.classList.add('hidden');
        @endif
        fileName.textContent = '';
    }
}

// Auto-load gambar saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    
    @if(!empty($jenis->gambar))
        // Pastikan gambar ditampilkan saat load
        previewImg.src = "{{ asset('storage/' . $jenis->gambar) }}";
        preview.classList.remove('hidden');
        
        // Tambahkan cache buster untuk memaksa reload gambar baru
        previewImg.src = previewImg.src + '?t=' + new Date().getTime();
    @endif
});
</script>

@endsection