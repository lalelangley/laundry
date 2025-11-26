@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.create') }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Tambah Jenis Layanan</span>
</div>

<div class="px-5 pb-32 pt-6">

    {{-- Flash Message --}}
    @if(session('success'))
        <div class="bg-green-500 text-white p-4 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    {{-- FORM TAMBAH JENIS --}}
    <form action="{{ route('jenis_layanan.store') }}" method="POST" enctype="multipart/form-data">
        @csrf

        {{-- GAMBAR --}}
        <label class="font-bold text-lg">Gambar</label>
        <div class="flex items-center gap-4 mt-2 mb-6">
            
            <div class="w-24 h-24 bg-gray-200 rounded-xl flex items-center justify-center overflow-hidden">
                <img id="preview-gambar" class="hidden w-full h-full object-cover">
                <svg id="placeholder-gambar" width="45" height="45" fill="#bbb" viewBox="0 0 24 24">
                    <path d="M12 5a3 3 0 1 1-3 3 3 3 0 0 1 3-3Zm0-2a5 5 0 1 0 5 5A5 5 0 0 0 12 3Zm7 16v-1a7 7 0 0 0-14 0v1h14Zm2 2H3v-3a9 9 0 0 1 18 0Z"/>
                </svg>
            </div>

            <label class="bg-yellow-400 px-6 py-3 rounded-xl text-white font-bold cursor-pointer">
                Pilih Gambar
                <input type="file" name="gambar" id="gambar" class="hidden" accept="image/*">
            </label>
        </div>

        {{-- NAMA JENIS --}}
        <label class="font-bold text-lg">Nama Jenis Layanan</label>
        <input type="text" name="nama_jenis" class="w-full bg-gray-200 p-4 rounded-xl mt-1 mb-6 outline-none" required>

        {{-- SATUAN --}}
        <label class="font-bold text-lg">Satuan</label>
        <select name="satuan" class="w-full bg-gray-200 p-4 rounded-xl mt-1 mb-6" required>
            <option value="Kg">Kg</option>
            <option value="Pcs">Pcs</option>
        </select>

        {{-- HARGA --}}
        <label class="font-bold text-lg">Harga</label>
        <input type="number" name="harga" class="w-full bg-gray-200 p-4 rounded-xl mt-1 mb-6 outline-none" required>

        {{-- LAMA PENGERJAAN --}}
        <label class="font-bold text-lg">Lama Pengerjaan</label>
        <div class="flex gap-3 mt-1 mb-6">
            <input type="number" name="lama" class="flex-1 bg-gray-200 p-4 rounded-xl outline-none" required>

            <select name="lama_satuan" class="bg-gray-200 p-4 rounded-xl outline-none">
                <option value="Jam">Jam</option>
                <option value="Hari">Hari</option>
            </select>
        </div>

        {{-- KETERANGAN --}}
        <label class="font-bold text-lg">Keterangan</label>
        <textarea name="keterangan" class="w-full bg-gray-200 p-4 rounded-xl h-28 mt-1 mb-8 outline-none"></textarea>

        {{-- BUTTON SIMPAN --}}
        <div class="fixed bottom-0 left-0 w-full px-5 pb-5 bg-transparent">
            <button type="submit" class="w-full bg-green-600 py-4 rounded-2xl text-white text-xl font-bold shadow">
                Simpan
            </button>
        </div>

    </form>

</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('gambar');
    const preview = document.getElementById('preview-gambar');
    const placeholder = document.getElementById('placeholder-gambar');

    input.addEventListener('change', function() {
        const file = this.files[0];

        if (file) {
            preview.src = URL.createObjectURL(file);
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
        } else {
            preview.classList.add('hidden');
            placeholder.classList.remove('hidden');
        }
    });
});
</script>
@endsection
