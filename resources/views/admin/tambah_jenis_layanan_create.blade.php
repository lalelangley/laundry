{{-- UNTUK create-jenis-layanan.blade.php --}}
@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.create') }}" class="text-black text-3xl font-bold">←</a>
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

    {{-- FORM TAMBAH --}}
    <form action="{{ route('layanan.jenis.store', $id_layanan)}}" method="POST" enctype="multipart/form-data"
        class="bg-white p-5 rounded-2xl shadow space-y-6">

        @csrf
        <input type="hidden" name="from" value="{{ $from }}">

        {{-- Gambar --}}
        <div>
            <label class="font-semibold block mb-2">Gambar Jenis Layanan</label>

            <div class="flex items-start gap-4">

                {{-- PREVIEW BOX --}}
                <div id="previewWrapper"
                    class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center overflow-hidden border-2 border-gray-300 shadow-sm">

                    <i id="previewIcon" class="bi bi-image text-gray-400 text-4xl"></i>

                    <img id="previewJenis" src="" class="w-full h-full object-cover hidden">
                </div>

                {{-- BUTTON UPLOAD --}}
                <div class="flex-1">
                    <label class="bg-yellow-400 hover:bg-yellow-500 px-6 py-3 rounded-xl text-black font-semibold cursor-pointer inline-flex items-center gap-2 transition-all shadow-md hover:shadow-lg">
                        <i class="bi bi-camera-fill text-xl"></i>
                        Pilih Gambar
                        <input type="file" name="gambar" id="inputGambarJenis" class="hidden" accept="image/*">
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, maksimal 2MB</p>
                </div>
            </div>
        </div>

        {{-- Nama Jenis --}}
        <div>
            <label class="font-semibold block mb-2">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis') }}"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none transition-all" 
                placeholder="Contoh: Cuci Kering" required>
        </div>

        {{-- Satuan --}}
        <div>
            <label class="font-semibold block mb-2">Satuan</label>
            <div class="flex items-center gap-3">

                <select name="id_satuan" class="flex-1 bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none">
                    @foreach ($satuan as $s)
                        <option 
                            value="{{ $s->id_satuan }}"
                            @if(request('new_satuan') == $s->id_satuan) selected @endif
                        >
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>

                <a href="{{ route('satuan.create', [
                        'from' => 'create-jenis',
                        'id_layanan' => $from
                ]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-xl font-semibold transition-all whitespace-nowrap">
                    + Tambah
                </a>
            </div>
        </div>

        {{-- Harga --}}
        <div>
            <label class="font-semibold block mb-2">Harga</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="number" name="harga" value="{{ old('harga') }}"
                    class="w-full bg-gray-100 pl-12 pr-4 py-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none" 
                    placeholder="0" required>
            </div>
        </div>

        {{-- Lama + Lama Satuan --}}
        <div>
            <label class="font-semibold block mb-2">Lama Pengerjaan</label>
            <div class="flex gap-3">
                <input type="number" name="lama" value="{{ old('lama') }}"
                    class="flex-1 bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none" 
                    placeholder="0" required>

                <select name="lama_satuan" class="bg-gray-100 px-6 py-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none" required>
                    <option value="Hari" {{ old('lama_satuan') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Jam" {{ old('lama_satuan') == 'Jam' ? 'selected' : '' }}>Jam</option>
                </select>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="font-semibold block mb-2">Keterangan (Opsional)</label>
            <textarea name="keterangan" rows="4"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none resize-none" 
                placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-2xl text-xl font-semibold transition-all shadow-lg hover:shadow-xl">
            <i class="bi bi-check-circle-fill mr-2"></i>
            Simpan Jenis Layanan
        </button>

    </form>
</div>

{{-- JS PREVIEW GAMBAR --}}
<script>
document.getElementById('inputGambarJenis')?.addEventListener('change', function (e) {
    const file = e.target.files[0];
    const preview = document.getElementById('previewJenis');
    const icon = document.getElementById('previewIcon');
    const wrapper = document.getElementById('previewWrapper');

    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            this.value = '';
            return;
        }

        // Validasi tipe file
        if (!file.type.match('image.*')) {
            alert('File harus berupa gambar!');
            this.value = '';
            return;
        }

        // Tampilkan preview
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            icon.classList.add('hidden');
            wrapper.classList.remove('from-gray-100', 'to-gray-200');
            wrapper.classList.add('from-yellow-50', 'to-yellow-100');
        };
        reader.readAsDataURL(file);
    }
});
</script>

@endsection

{{-- ====================================== --}}
{{-- UNTUK edit-jenis-layanan.blade.php --}}
{{-- ====================================== --}}

@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    @php
        $idLayanan = isset($jenis) ? $jenis->id_layanan : ($id_layanan ?? request()->route('id_layanan') ?? request('from'));
    @endphp
    <a href="{{ route('layanan.edit', ['id' => $idLayanan]) }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">{{ isset($jenis) ? 'Ubah' : 'Tambah' }} Jenis Layanan</span>
</div>

<div class="px-5 mt-6">
    <form action="{{ isset($jenis) ? route('layanan.jenis.update', $jenis->id_jenis_layanan) : route('layanan.jenis.store', $idLayanan) }}" method="POST" enctype="multipart/form-data"
          class="bg-white p-5 rounded-2xl shadow space-y-6">
        @csrf
        @if(isset($jenis))
            @method('PUT')
        @endif

        <input type="hidden" name="from" value="{{ $idLayanan }}">

        {{-- Gambar --}}
        <div>
            <label class="font-semibold block mb-2">Gambar Jenis Layanan</label>

            <div class="flex items-start gap-4">

                {{-- Preview Box --}}
                <div class="w-24 h-24 bg-gradient-to-br from-gray-100 to-gray-200 rounded-xl flex items-center justify-center shadow-sm overflow-hidden border-2 border-gray-300">
                    @if (isset($jenis) && $jenis->gambar)
                        <img id="preview" src="{{ asset('storage/' . $jenis->gambar) }}"
                             class="w-full h-full object-cover rounded-xl"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<i class=\'bi bi-image text-gray-400 text-4xl\'></i>';">
                    @else
                        <img id="preview" src="" class="w-full h-full object-cover rounded-xl hidden">
                        <i id="previewIcon" class="bi bi-image text-gray-400 text-4xl"></i>
                    @endif
                </div>

                {{-- Upload Button --}}
                <div class="flex-1">
                    <label class="bg-yellow-400 hover:bg-yellow-500 px-6 py-3 rounded-xl text-black font-semibold cursor-pointer inline-flex items-center gap-2 transition-all shadow-md hover:shadow-lg">
                        <i class="bi bi-camera-fill text-xl"></i>
                        {{ isset($jenis) && $jenis->gambar ? 'Ganti Gambar' : 'Pilih Gambar' }}
                        <input type="file" name="gambar" class="hidden" accept="image/*" onchange="loadPreview(event)">
                    </label>
                    <p class="text-xs text-gray-500 mt-2">Format: JPG, PNG, maksimal 2MB</p>
                    @if(isset($jenis) && $jenis->gambar)
                        <p class="text-xs text-green-600 mt-1">
                            <i class="bi bi-check-circle-fill"></i> Gambar saat ini: {{ basename($jenis->gambar) }}
                        </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Nama Jenis --}}
        <div>
            <label class="font-semibold block mb-2">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis', $jenis->nama_jenis ?? '') }}"
                   class="w-full bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none" 
                   placeholder="Contoh: Cuci Kering" required>
        </div>

        {{-- Satuan & Tambah --}}
        <div>
            <label class="font-semibold block mb-2">Satuan</label>
            <div class="flex items-center gap-3">
                <select name="id_satuan" class="flex-1 bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none">
                @foreach($satuan as $s)
                    <option value="{{ $s->id_satuan }}"
                        @if(old('id_satuan', request('new_satuan', $jenis->id_satuan ?? '')) == $s->id_satuan) selected @endif>
                        {{ $s->nama_satuan }}
                    </option>
                @endforeach
                </select>

                {{-- LINK TAMBAH SATUAN --}}
                <a href="{{ route('satuan.create', [
                    'from' => isset($jenis) ? 'edit-jenis' : 'create-jenis',
                    'id_layanan' => $idLayanan,
                    'id_jenis' => $jenis->id_jenis_layanan ?? null
                ]) }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-3 rounded-xl font-semibold transition-all whitespace-nowrap">
                    + Tambah
                </a>
            </div>
        </div>

        {{-- Harga --}}
        <div>
            <label class="font-semibold block mb-2">Harga</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 font-semibold">Rp</span>
                <input type="number" name="harga" value="{{ old('harga', $jenis->harga ?? '') }}"
                       class="w-full bg-gray-100 pl-12 pr-4 py-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none" 
                       placeholder="0" required>
            </div>
        </div>

        {{-- Lama Pengerjaan --}}
        <div>
            <label class="font-semibold block mb-2">Lama Pengerjaan</label>
            <div class="flex items-center gap-3">
                <input type="number" name="lama" value="{{ old('lama', $jenis->lama ?? '') }}"
                       class="flex-1 bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none" 
                       placeholder="0" required>
                <select name="lama_satuan" class="bg-gray-100 px-6 py-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none">
                    <option value="Hari" {{ old('lama_satuan', $jenis->lama_satuan ?? 'Hari') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Jam" {{ old('lama_satuan', $jenis->lama_satuan ?? '') == 'Jam' ? 'selected' : '' }}>Jam</option>
                </select>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="font-semibold block mb-2">Keterangan (Opsional)</label>
            <textarea name="keterangan" rows="4"
                      class="w-full bg-gray-100 p-4 rounded-2xl text-lg focus:ring-2 focus:ring-yellow-400 outline-none resize-none" 
                      placeholder="Tambahkan keterangan jika diperlukan">{{ old('keterangan', $jenis->keterangan ?? '') }}</textarea>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-2xl text-xl font-semibold transition-all shadow-lg hover:shadow-xl">
            <i class="bi bi-check-circle-fill mr-2"></i>
            {{ isset($jenis) ? 'Perbarui' : 'Simpan' }} Jenis Layanan
        </button>
    </form>
</div>

{{-- Preview Script --}}
<script>
function loadPreview(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('preview');
    const icon = document.getElementById('previewIcon');

    if (file) {
        // Validasi ukuran file (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('Ukuran file maksimal 2MB!');
            event.target.value = '';
            return;
        }

        // Validasi tipe file
        if (!file.type.match('image.*')) {
            alert('File harus berupa gambar!');
            event.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('hidden');
            if (icon) icon.classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }
}
</script>

@endsection