@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('admin2.layanan.create') }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Tambah Jenis Layanan</span>
</div>

<div class="px-5 mt-6">

    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-xl mb-5">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5">
            <ul class="list-disc ml-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin2.layanan.jenis.store', ['id_layanan' => $id_layanan ?? 0]) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="bg-white p-6 rounded-2xl shadow-lg">
        @csrf

        {{-- Upload Gambar --}}
        <div class="mb-5">
            <label class="font-semibold text-gray-700 block mb-2">Gambar Jenis Layanan</label>
            
            {{-- Preview Container --}}
            <div id="imagePreview" class="mb-3 hidden">
                <img id="previewImg" src="" alt="Preview" class="w-full max-w-xs h-48 object-cover rounded-xl border-2 border-gray-200">
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
                    <span class="font-semibold">Pilih Gambar</span>
                </label>
                <span id="fileName" class="ml-3 text-sm text-gray-600"></span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, JPEG, GIF, WEBP (Max: 2MB)</p>
        </div>

        {{-- Nama Jenis --}}
        <div class="mb-5">
            <label class="font-semibold text-gray-700">Nama Jenis Layanan</label>
            <input type="text" name="nama_jenis"
                   value="{{ old('nama_jenis') }}"
                   class="w-full p-3 border-2 border-gray-300 rounded-xl mt-1 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                   placeholder="Contoh: Cuci Kering"
                   required>
        </div>

        {{-- Satuan --}}
        <div class="mb-5">
            <label class="font-semibold text-gray-700">Satuan</label>
            <select name="id_satuan"
                    class="w-full p-3 border-2 border-gray-300 rounded-xl mt-1 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                    required>
                <option value="">-- Pilih Satuan --</option>
                @foreach($satuan as $s)
                    <option value="{{ $s->id_satuan }}" {{ old('id_satuan') == $s->id_satuan ? 'selected' : '' }}>
                        {{ $s->nama_satuan }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Harga --}}
        <div class="mb-5">
            <label class="font-semibold text-gray-700">Harga</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="number" name="harga"
                       value="{{ old('harga') }}"
                       class="w-full p-3 pl-12 border-2 border-gray-300 rounded-xl mt-1 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                       placeholder="0"
                       min="0"
                       required>
            </div>
        </div>

        {{-- Lama Pengerjaan --}}
        <div class="grid grid-cols-2 gap-4 mb-5">
            <div>
                <label class="font-semibold text-gray-700">Lama Pengerjaan</label>
                <input type="number" name="lama"
                       value="{{ old('lama') }}"
                       class="w-full p-3 border-2 border-gray-300 rounded-xl mt-1 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                       placeholder="0"
                       min="0"
                       required>
            </div>
            <div>
                <label class="font-semibold text-gray-700">Satuan Waktu</label>
                <select name="lama_satuan"
                        class="w-full p-3 border-2 border-gray-300 rounded-xl mt-1 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                        required>
                    <option value="">-- Pilih --</option>
                    <option value="Jam" {{ old('lama_satuan') == 'Jam' ? 'selected' : '' }}>Jam</option>
                    <option value="Hari" {{ old('lama_satuan') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Minggu" {{ old('lama_satuan') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                </select>
            </div>
        </div>

        {{-- Keterangan --}}
        <div class="mb-5">
            <label class="font-semibold text-gray-700">Keterangan (Opsional)</label>
            <textarea name="keterangan"
                      rows="3"
                      class="w-full p-3 border-2 border-gray-300 rounded-xl mt-1 focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                      placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Buttons --}}
        <div class="flex justify-between items-center mt-6">
            <a href="{{ route('admin2.layanan.create') }}"
               class="px-6 py-3 rounded-xl bg-white border-2 border-gray-300 hover:bg-gray-50 text-gray-700 font-semibold transition inline-flex items-center gap-2">
                <i class="bi bi-x-circle"></i>
                <span>Batal</span>
            </a>
            <button type="submit"
                    class="px-8 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-bold transition inline-flex items-center gap-2 shadow-lg">
                <i class="bi bi-check-circle-fill"></i>
                <span>Simpan Jenis</span>
            </button>
        </div>
    </form>
</div>

<script>
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    const previewImg = document.getElementById('previewImg');
    const fileName = document.getElementById('fileName');
    
    if (file) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            preview.classList.remove('hidden');
        }
        
        reader.readAsDataURL(file);
        fileName.textContent = file.name;
    } else {
        preview.classList.add('hidden');
        fileName.textContent = '';
    }
}
</script>

@endsection