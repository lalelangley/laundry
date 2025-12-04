@extends('layouts.master')

@section('content')
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.index') }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Tambah Layanan</span>
</div>

<div class="px-5 pb-32 pt-6">

    {{-- Flash Message --}}
    @if(session('success'))
        <div id="flash-message" class="mb-4 p-4 bg-green-200 text-green-800 rounded-xl shadow transition-opacity duration-500">
            {{ session('success') }}
        </div>
    @endif

    {{-- ERROR VALIDASI --}}
    @if ($errors->any())
        <div class="bg-red-200 text-red-800 p-4 rounded-xl mb-4 shadow">
            <ul class="list-disc ml-4">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form id="form-layanan" action="{{ route('layanan.store') }}" method="POST">
        @csrf

        {{-- Nama Layanan --}}
        <label class="font-bold text-lg">Nama Layanan</label>
        <input type="text" name="nama_layanan" id="nama-layanan"
               class="w-full bg-gray-200 p-4 rounded-xl mt-1 mb-6" placeholder="Masukkan nama layanan">

        {{-- Proses --}}
        <label class="font-bold text-lg">Proses</label>
        <div class="flex gap-3 mt-3 mb-8">
            @foreach (['Cuci', 'Kering', 'Setrika'] as $p)
                <label class="flex items-center gap-2 border-2 border-yellow-400 rounded-full px-6 py-2 bg-white hover:bg-yellow-100 transition">
                    <input type="checkbox" name="proses[]" value="{{ $p }}" class="w-5 h-5 accent-yellow-500">
                    <span class="text-lg font-medium">{{ $p }}</span>
                </label>
            @endforeach
        </div>

        {{-- Tombol Tambah Jenis Layanan --}}
        <div class="flex items-center justify-between mb-3">
            <label class="font-bold text-lg">Jenis Layanan Baru</label>
            <a href="{{ route('jenis_layanan.create') }}"
               class="bg-yellow-400 hover:bg-yellow-500 px-5 py-2 rounded-full font-semibold text-white text-sm shadow inline-block">
               + Tambah Jenis Layanan
            </a>
        </div>

        {{-- LIST JENIS LAYANAN SESSION --}}
        <div id="list-jenis" class="mb-32 space-y-3">
            @if(session('jenis_baru'))
                @foreach(session('jenis_baru') as $i => $jenis)
                    <div class="jenis-item flex gap-3 items-center p-4 rounded-xl bg-white shadow">

                        {{-- Thumbnail --}}
                        <div class="w-20 h-20 bg-gray-200 rounded-xl overflow-hidden flex-shrink-0 flex items-center justify-center text-gray-400">
                            <img src="{{ $jenis['gambar'] ?? '#' }}" alt="{{ $jenis['nama'] }}" class="w-full h-full object-cover">
                        </div>

                        {{-- Detail --}}
                        <div class="flex-1 space-y-2">
                            <input type="text" class="font-bold text-lg nama border border-gray-300 rounded p-2 w-full"
                                   value="{{ $jenis['nama'] }}" readonly>

                            <div class="flex gap-2">
                                <input type="number" class="harga flex-1 border border-gray-300 rounded p-2"
                                       value="{{ $jenis['harga'] }}" readonly>

                                <select class="satuan border border-gray-300 rounded p-2" disabled>
                                    <option value="Kg" {{ $jenis['satuan'] == 'Kg' ? 'selected' : '' }}>Kg</option>
                                    <option value="Pcs" {{ $jenis['satuan'] == 'Pcs' ? 'selected' : '' }}>Pcs</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Hidden input dalam bentuk ARRAY (FIX) --}}
                    <input type="hidden" name="jenis_baru[{{ $i }}][nama]" value="{{ $jenis['nama'] }}">
                    <input type="hidden" name="jenis_baru[{{ $i }}][harga]" value="{{ $jenis['harga'] }}">
                    <input type="hidden" name="jenis_baru[{{ $i }}][satuan]" value="{{ $jenis['satuan'] }}">
                    <input type="hidden" name="jenis_baru[{{ $i }}][gambar]" value="{{ $jenis['gambar'] }}">

                @endforeach
            @endif
        </div>

        {{-- Tombol Submit --}}
        <div class="fixed bottom-0 left-0 w-full px-5 pb-5 bg-transparent z-10">
            <button type="submit"
                    class="w-full bg-green-600 py-4 rounded-2xl text-white text-xl font-bold shadow">
                Simpan
            </button>
        </div>

    </form>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Flash message fade out
    const flash = document.getElementById('flash-message');
    if(flash){
        setTimeout(()=> {
            flash.classList.add('opacity-0');
            setTimeout(()=> flash.remove(), 500);
        }, 3000);
    }

    // Validasi sebelum submit
    const form = document.getElementById('form-layanan');
    form.addEventListener('submit', function(e){
        const inputNama = document.getElementById('nama-layanan');
        if(inputNama.value.trim() === ''){
            e.preventDefault();
            alert('Nama layanan harus diisi!');
            inputNama.focus();
        }
    });
});
</script>
@endsection
