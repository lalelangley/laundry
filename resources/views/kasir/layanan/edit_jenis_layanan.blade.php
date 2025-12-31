@extends('layouts.master')

@section('content')

{{-- HEADER --}}
{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('kasir.layanan.edit', ['id' => request('from')]) }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Ubah Jenis Layanan</span>
</div>


<div class="px-5 mt-6">
    <form action="{{ route('layanan.jenis.update', $jenis->id_jenis_layanan) }}" method="POST" enctype="multipart/form-data"
          class="bg-white p-5 rounded-2xl shadow space-y-5">
        @csrf
        @method('PUT')

        <input type="hidden" name="from" value="{{ request('from') }}">

        {{-- Gambar --}}
        <div>
            <label class="font-semibold block mb-1">Gambar</label>

            <div class="flex items-center gap-4">

                {{-- Kotak Preview --}}
                <div class="w-20 h-20 bg-gray-200 rounded-xl flex items-center justify-center shadow overflow-hidden">
                    @if ($jenis->gambar)
                        <img id="preview" src="{{ asset('storage/' . $jenis->gambar) }}"
                             class="w-full h-full object-cover rounded-xl">
                    @else
                        <img id="preview" src="{{ asset('img/noimage.png') }}"
                             class="w-full h-full object-cover rounded-xl">
                    @endif
                </div>

                {{-- Tombol Upload --}}
                <label class="bg-yellow-400 px-6 py-3 rounded-xl text-white font-semibold cursor-pointer flex items-center gap-2">
                    <i class="bi bi-camera-fill text-2xl"></i>
                    Pilih Gambar
                    <input type="file" name="gambar" class="hidden" accept="image/*" onchange="loadPreview(event)">
                </label>
            </div>
        </div>

        {{-- Nama Jenis --}}
        <div>
            <label class="font-semibold block mb-1">Nama Jenis</label>
            <input type="text" name="nama_jenis" value="{{ old('nama_jenis', $jenis->nama_jenis) }}"
                   class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
        </div>

       {{-- Satuan & Tambah --}}
        <div>
            <label class="font-semibold block mb-1">Satuan</label>
            <div class="flex items-center gap-3">
                <select name="id_satuan" class="w-full bg-gray-100 p-4 rounded-2xl text-lg">
                @foreach($satuan as $s)
                    <option value="{{ $s->id_satuan }}"
                        @if(old('id_satuan', request('new_satuan', $jenis->id_satuan)) == $s->id_satuan) selected @endif>
                        {{ $s->nama_satuan }}
                    </option>
                @endforeach
                </select>

                {{-- LINK T MBAH SATUAN --}}
                <a href="{{ route('satuan.create', [
                    'from' => 'edit-jenis',
                    'id_layanan' => $jenis->id_layanan,
                    'id_jenis' => $jenis->id_jenis_layanan   {{-- PASTIKAN ini benar --}}
                ]) }}" class="text-blue-500 underline">Tambah</a>
        </div>

        {{-- Harga --}}
        <div>
            <label class="font-semibold block mb-1">Harga</label>
            <input type="number" name="harga" value="{{ old('harga', $jenis->harga) }}"
                   class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
        </div>

        {{-- Lama Pengerjaan --}}
        <div>
            <label class="font-semibold block mb-1">Lama Pengerjaan</label>
            <div class="flex items-center gap-3">
                <input type="number" name="lama" value="{{ old('lama', $jenis->lama) }}"
                       class="w-full bg-gray-100 p-4 rounded-2xl text-lg" required>
                <select name="lama_satuan" class="bg-gray-100 p-4 rounded-2xl text-lg">
                    <option value="Hari" {{ old('lama_satuan', $jenis->lama_satuan) == 'Hari' ? 'selected' : '' }}>Hari</option>
                    <option value="Jam" {{ old('lama_satuan', $jenis->lama_satuan) == 'Jam' ? 'selected' : '' }}>Jam</option>
                </select>
            </div>
        </div>

        {{-- Keterangan --}}
        <div>
            <label class="font-semibold block mb-1">Keterangan</label>
            <textarea name="keterangan"
                      class="w-full bg-gray-100 p-4 rounded-2xl text-lg">{{ old('keterangan', $jenis->keterangan) }}</textarea>
        </div>

        {{-- Submit --}}
        <button type="submit"
                class="w-full bg-green-700 text-white py-4 rounded-2xl text-xl font-semibold mt-4">
            Ubah Jenis
        </button>
    </form>
</div>

{{-- Preview Script --}}
<script>
    function loadPreview(event) {
        let output = document.getElementById('preview');
        output.src = URL.createObjectURL(event.target.files[0]);
    }
</script>

@endsection
