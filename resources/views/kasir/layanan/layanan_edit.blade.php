@extends('layouts.master')

@section('content')
@php
    $from = request()->route('layanan');
@endphp

{{-- HEADER --}}
<div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-3 shadow w-full">
    <a href="{{ route('kasir.layanan.index') }}"
    class="text-black text-2xl font-bold leading-none hover:scale-110 transition-transform">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-xl font-semibold">Edit Layanan</span>
</div>

<div class="px-6 mt-8 w-full max-w-full">

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5 text-base">
            <ul class="ml-5 list-disc space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('kasir.layanan.update', $layanan->id_layanan) }}" method="POST">
    @csrf
    @method('PUT')

        {{-- NAMA LAYANAN --}}
        <label class="block font-semibold text-lg mb-2">Nama Layanan</label>
        <input type="text" 
               name="nama_layanan"
               value="{{ old('nama_layanan', $layanan->nama_layanan) }}"
               class="w-full border border-gray-300 rounded-xl px-4 py-3 text-base 
                      focus:ring-2 focus:ring-yellow-400 mb-6"
               required>

        {{-- PROSES --}}
        @php
            $prosesList = ['Cuci', 'Kering', 'Setrika'];
            $selectedProses = old('proses', explode(',', $layanan->proses));
        @endphp

        <label class="block font-semibold text-lg mb-3">Proses</label>

        <div class="grid grid-cols-3 gap-4 w-full">
            @foreach ($prosesList as $p)
                <label class="flex items-center gap-2 px-4 py-3 border rounded-xl cursor-pointer text-base
                               {{ in_array($p, $selectedProses) ? 'bg-yellow-100 border-yellow-500' : '' }}">
                    <input type="checkbox" 
                           name="proses[]" 
                           value="{{ $p }}"
                           class="scale-110"
                           {{ in_array($p, $selectedProses) ? 'checked' : '' }}>
                    {{ $p }}
                </label>
            @endforeach
        </div>


        {{-- JENIS LAYANAN --}}
        <h3 class="mt-10 mb-4 font-semibold text-xl">Jenis Layanan</h3>

        @php 
            $jenisBaru = session()->get("jenis_baru_{$layanan->id_layanan}", []); 
        @endphp

        <div class="space-y-4">

            {{-- JENIS LAMA --}}
            @foreach ($layanan->jenis as $jenis)
                <a href="{{ route('kasir.layanan.jenis.edit', $jenis->id_jenis_layanan) }}?from={{ $layanan->id_layanan }}"
                   class="flex gap-4 p-4 bg-white border rounded-2xl shadow hover:bg-gray-50 transition w-full">

                    <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden">
                        <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                             class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1">
                        <p class="font-semibold text-lg">{{ $jenis->nama_jenis }}</p>

                        <p class="text-gray-700 text-base">
                            Rp{{ number_format($jenis->harga, 0, ',', '.') }} /
                            {{ $jenis->satuan->nama_satuan ?? '-' }}
                        </p>

                        <p class="text-gray-500 text-sm flex items-center gap-1">
                            <i class="bi bi-clock text-base"></i>
                            {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                        </p>
                    </div>

                    <div class="flex items-center">
                        <i class="bi bi-pencil-square text-xl text-gray-400"></i>
                    </div>
                </a>
            @endforeach


            {{-- JENIS BARU (SESSION) --}}
            @foreach ($jenisBaru as $jb)
                <div class="flex gap-4 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl shadow">

                    <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden">
                        <img src="{{ asset('images/' . ($jb['gambar'] ?? 'default.png')) }}"
                             class="w-full h-full object-cover">
                    </div>

                    <div class="flex-1">
                        <p class="font-semibold text-lg">
                            {{ $jb['nama_jenis'] }}
                            <span class="text-xs text-gray-500">(baru)</span>
                        </p>

                        <p class="text-gray-700 text-base">
                            Rp{{ number_format($jb['harga'] ?? 0, 0, ',', '.') }} /
                            {{ $jb['satuan'] ?? '-' }}
                        </p>

                        <p class="text-gray-500 text-sm flex items-center gap-1">
                            <i class="bi bi-clock text-base"></i>
                            {{ $jb['lama'] ?? '-' }} {{ $jb['lama_satuan'] ?? '' }}
                        </p>
                    </div>
                </div>
            @endforeach

        </div>

        {{-- BUTTON TAMBAH --}}
       <a href="{{ route('kasir.layanan.jenis.tambah', $layanan->id_layanan) }}"
        class="block mt-6 bg-yellow-400 hover:bg-yellow-500 transition text-white 
                text-center py-3 rounded-xl font-semibold text-lg">
            <i class="bi bi-plus-circle text-lg"></i> Tambah Jenis
        </a>

        {{-- SUBMIT --}}
        <div class="flex justify-end mt-8">
            <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 transition px-8 py-3 rounded-xl 
                           font-semibold text-white text-lg shadow">
                Update Layanan
            </button>
        </div>

    </form>

</div>

@endsection


@section('scripts')
<script>
document.getElementById('inputGambarJenis')?.addEventListener('change', function (e) {
    const preview = document.getElementById('previewJenis');
    const file = e.target.files[0];

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.classList.remove('hidden');
    }
});
</script>
@endsection
