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
    <form action="{{ route('session.store', $from) }}" method="POST" enctype="multipart/form-data"
        class="bg-white p-5 rounded-2xl shadow space-y-6">

        @csrf
        <input type="hidden" name="from" value="{{ $from }}">

        {{-- Gambar --}}
        <div>
            <label class="font-semibold block mb-1">Gambar</label>

            <div class="flex items-center gap-4">

                {{-- RECTANGLE PREVIEW --}}
                <div id="previewWrapper"
                    class="w-24 h-24 bg-gray-200 rounded-xl flex items-center justify-center overflow-hidden">

                    <i id="previewIcon" class="bi bi-image text-gray-500 text-4xl"></i>

                    <img id="previewJenis" src="" class="w-full h-full object-cover hidden">
                </div>

                {{-- BUTTON PILIH FILE --}}
                <label class="bg-yellow-400 px-6 py-3 rounded-xl text-white font-semibold cursor-pointer">
                    Pilih Gambar
                    <input type="file" name="gambar" id="inputGambarJenis" class="hidden" accept="image/*">
                </label>
            </div>
        </div>

        {{-- Nama Jenis --}}
        <div>
            <label class="font-semibold block mb-1">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis') }}"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
        </div>

        {{-- Satuan --}}
        <div>
            <label class="font-semibold block mb-1">Satuan</label>
            <div class="flex items-center gap-3">

                <select name="id_satuan" class="form-select">
                    @foreach ($satuan as $s)
                        <option 
                            value="{{ $s->id_satuan }}"
                            @if(request('new_satuan') == $s->id_satuan) selected @endif
                        >
                            {{ $s->nama_satuan }}
                        </option>
                    @endforeach
                </select>

                <input type="hidden" name="from" value="{{ request('from') }}">
                <input type="hidden" name="id_layanan" value="{{ request('id_layanan') }}">
                <input type="hidden" name="id_jenis" value="{{ request('id_jenis') }}">

                <a href="{{ route('satuan.create', [
                        'from' => 'create-jenis',
                        'id_layanan' => $from
                ]) }}" class="text-blue-500 underline">
                    Tambah
                </a>
            </div>
        </div>

        {{-- Harga --}}
        <div>
            <label class="font-semibold block mb-1">Harga</label>
            <input type="number" name="harga" value="{{ old('harga') }}"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
        </div>

        {{-- Lama + Lama Satuan --}}
        <div>
            <label class="font-semibold block mb-1">Lama Pengerjaan</label>
            <div class="flex gap-3">
                <input type="number" name="lama" value="{{ old('lama') }}"
                    class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>

                <select name="lama_satuan" class="bg-gray-100 p-4 rounded-2xl text-lg" required>
                    <option value="Hari" {{ old('lama_satuan') == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Jam" {{ old('lama_satuan') == 'Jam' ? 'selected' : '' }}>Jam</option>
                </select>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="font-semibold block mb-1">Keterangan</label>
            <textarea name="keterangan"
                class="w-full bg-gray-100 p-4 rounded-2xl text-lg">{{ old('keterangan') }}</textarea>
        </div>

        {{-- Submit --}}
        <button type="submit"
            class="w-full bg-green-700 text-white py-4 rounded-2xl text-xl font-semibold">
            Simpan
        </button>

    </form>
</div>

{{-- JS PREVIEW GAMBAR --}}
<script>
document.getElementById('inputGambarJenis')?.addEventListener('change', function (e) {
    const file = e.target.files[0];

    const preview = document.getElementById('previewJenis');
    const icon = document.getElementById('previewIcon');

    if (file) {
        preview.src = URL.createObjectURL(file);

        // TAMPILIN GAMBAR
        preview.classList.remove('hidden');

        // SEMBUNYIKAN ICON
        icon.classList.add('hidden');
    }
});
</script>


@endsection
