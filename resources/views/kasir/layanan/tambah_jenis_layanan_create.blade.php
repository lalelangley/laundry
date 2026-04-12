{{-- FE-DOC: Template frontend untuk resources/views/kasir/layanan/tambah_jenis_layanan_create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Tambah Jenis Layanan')

@section('content')

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('kasir.layanan.create') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-bold">Tambah Jenis Layanan</span>
</div>

<div class="px-5 mt-6">

    {{-- ERROR VALIDATION --}}
    @if ($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5">
            <ul class="ml-4 list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM TAMBAH JENIS LAYANAN KASIR --}}
    <form action="{{ route('kasir.layanan.jenis.session.store', $from) }}" method="POST" enctype="multipart/form-data"
        class="bg-white p-6 rounded-2xl shadow-lg space-y-5">

        @csrf
        <input type="hidden" name="from" value="{{ $from }}">

        {{-- Gambar --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-2">Gambar Jenis Layanan</label>

            <div id="imagePreview" class="mb-3 hidden">
                <img id="previewJenis" src="" alt="Preview"
                    class="w-full max-w-xs h-48 object-cover rounded-xl border-2 border-gray-200">
            </div>

            <div class="relative">
                <input type="file" name="gambar" id="inputGambarJenis" accept="image/*" class="hidden">
                <label for="inputGambarJenis"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-gray-100 hover:bg-gray-200 border-2 border-gray-300 rounded-xl cursor-pointer transition">
                    <i id="previewIcon" class="bi bi-image text-xl"></i>
                    <span class="font-semibold">Pilih Gambar</span>
                </label>
                <span id="fileName" class="ml-3 text-sm text-gray-600"></span>
            </div>
            <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, JPEG, GIF, WEBP (Max: 2MB)</p>
        </div>

        {{-- Nama Jenis --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Nama Jenis Layanan</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis') }}"
                class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                placeholder="Contoh: Cuci Kering" required>
        </div>

        {{-- Satuan --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Satuan</label>
            <div class="flex items-center gap-3">
                <select name="id_satuan"
                    class="flex-1 p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                    required>
                    <option value="">-- Pilih Satuan --</option>
                    @foreach ($satuan as $s)
                        <option value="{{ $s->id_satuan }}" {{ old('id_satuan', request('new_satuan')) == $s->id_satuan ? 'selected' : '' }}>
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('kasir.satuan.create', ['from' => 'create-jenis', 'id_layanan' => $from]) }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-xl text-sm font-semibold transition whitespace-nowrap">
                    <i class="bi bi-plus-circle"></i> Tambah
                </a>
            </div>
        </div>

        {{-- Harga --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Harga</label>
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="number" name="harga" value="{{ old('harga') }}"
                    class="w-full p-3 pl-12 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                    placeholder="0" min="0" required>
            </div>
        </div>

        {{-- Lama + Lama Satuan --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Lama Pengerjaan</label>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <input type="number" name="lama" value="{{ old('lama') }}"
                        class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                        placeholder="0" min="0" required>
                </div>
                <div>
                    <select name="lama_satuan"
                        class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                        required>
                        <option value="">-- Pilih --</option>
                        <option value="Jam" {{ old('lama_satuan') == 'Jam' ? 'selected' : '' }}>Jam</option>
                        <option value="Hari" {{ old('lama_satuan') == 'Hari' ? 'selected' : '' }}>Hari</option>
                        <option value="Minggu" {{ old('lama_satuan') == 'Minggu' ? 'selected' : '' }}>Minggu</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="font-semibold text-gray-700 block mb-1">Keterangan (Opsional)</label>
            <textarea name="keterangan" rows="3"
                class="w-full p-3 border-2 border-gray-300 rounded-xl focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 outline-none"
                placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Submit --}}
        <div class="flex justify-between items-center pt-4">
            <a href="{{ route('kasir.layanan.create') }}"
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

{{-- JS PREVIEW GAMBAR --}}
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
document.getElementById('inputGambarJenis')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('previewJenis');
    const previewWrap = document.getElementById('imagePreview');
    const fileName = document.getElementById('fileName');

    if (file) {
        preview.src = URL.createObjectURL(file);
        previewWrap.classList.remove('hidden');
        fileName.textContent = file.name;
    } else {
        previewWrap.classList.add('hidden');
        fileName.textContent = '';
    }
});
</script>

@endsection
