{{-- FE-DOC: Template frontend untuk resources/views/admin2/layanan/layanan_edit.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Edit Layanan')

@section('content')
@php
    $from = request()->route('layanan');
@endphp

{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-6 py-4 rounded-b-2xl flex items-center gap-3 shadow w-full">
    <a href="{{ route('admin2.layanan.index') }}"
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

    {{-- SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-xl mb-5 text-base">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('admin2.layanan.update', $layanan->id_layanan) }}" method="POST">
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
            $rawProses = $layanan->proses ?? '';
            if (is_string($rawProses) && \Illuminate\Support\Str::startsWith(trim($rawProses), '[')) {
                $selectedProses = json_decode($rawProses, true) ?: [];
            } else {
                $parts = explode(',', trim($rawProses, "[]\"' "));
                $selectedProses = array_filter(array_map('trim', $parts), fn($s) => !empty($s));
            }
            if (old('proses')) {
                $selectedProses = old('proses');
            }
        @endphp

        <label class="block font-semibold text-lg mb-3">Proses</label>

        <div class="grid grid-cols-3 gap-4 w-full mb-8">
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
                {{-- FIX: Ganti route ke admin2.layanan.jenis.edit --}}
                <a href="{{ route('admin2.layanan.jenis.edit', $jenis->id_jenis_layanan) }}?from={{ $layanan->id_layanan }}"
                   class="flex gap-4 p-4 bg-white border-2 border-gray-200 rounded-2xl shadow hover:bg-gray-50 hover:border-yellow-400 transition w-full">

                     {{-- ✅ FIXED IMAGE SECTION --}}
                        <div class="w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl overflow-hidden flex-shrink-0 border-2 border-gray-200 group-hover:border-yellow-300 transition-all">
                            @if(!empty($jenis->gambar))
                                <img src="{{ asset('storage/' . $jenis->gambar) }}"
                                     alt="{{ $jenis->nama_jenis }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-100 to-yellow-200\'><i class=\'bi bi-image text-3xl text-yellow-400\'></i></div>';">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="bi bi-image text-3xl text-gray-300"></i>
                                </div>
                            @endif
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
                        <i class="bi bi-pencil-square text-xl text-yellow-500"></i>
                    </div>
                </a>
            @endforeach


            {{-- JENIS BARU (SESSION) --}}
            @if(count($jenisBaru) > 0)
                @foreach ($jenisBaru as $index => $jb)
                    <div class="flex gap-4 p-4 bg-yellow-50 border-2 border-yellow-200 rounded-2xl shadow">

                        <div class="w-16 h-16 bg-gray-100 rounded-xl overflow-hidden flex-shrink-0">
                             @if(!empty($jb['gambar']))
                                    <img src="{{ asset('storage/' . $jb['gambar']) }}"
                                         alt="{{ $jb['nama_jenis'] ?? 'Jenis' }}"
                                         class="w-full h-full object-cover"
                                         onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center\'><i class=\'bi bi-image text-3xl text-yellow-400\'></i></div>';">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="bi bi-image text-3xl text-yellow-400"></i>
                                    </div>
                                @endif
                        </div>

                        <div class="flex-1">
                            <p class="font-semibold text-lg">
                                {{ $jb['nama_jenis'] ?? '-' }}
                                <span class="text-xs text-yellow-700 font-normal">(baru - belum disimpan)</span>
                            </p>

                            <p class="text-gray-700 text-base">
                                Rp{{ number_format($jb['harga'] ?? 0, 0, ',', '.') }} /
                                @php
                                    $satuanName = '-';
                                    if(!empty($jb['id_satuan'])){
                                        $s = \App\Models\Satuan::find($jb['id_satuan']);
                                        if($s) $satuanName = $s->nama_satuan;
                                    }
                                @endphp
                                {{ $satuanName }}
                            </p>

                            <p class="text-gray-500 text-sm flex items-center gap-1">
                                <i class="bi bi-clock text-base"></i>
                                {{ $jb['lama'] ?? '-' }} {{ $jb['lama_satuan'] ?? '' }}
                            </p>
                        </div>

                        <div class="flex items-center">
                            <span class="text-xs bg-yellow-500 text-white px-2 py-1 rounded">BARU</span>
                        </div>
                    </div>
                @endforeach
            @endif

        </div>

        {{-- BUTTON TAMBAH --}}
       <a href="{{ route('admin2.layanan.jenis.create', $layanan->id_layanan) }}"
        class="block mt-6 bg-yellow-400 hover:bg-yellow-500 transition text-black 
                text-center py-3 rounded-xl font-semibold text-lg inline-flex items-center justify-center gap-2">
            <i class="bi bi-plus-circle text-xl"></i> 
            <span>Tambah Jenis Layanan</span>
        </a>

        {{-- SUBMIT --}}
        <div class="flex justify-end mt-8">
            <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 transition px-8 py-3 rounded-xl 
                           font-semibold text-white text-lg shadow inline-flex items-center gap-2">
                <i class="bi bi-check-circle-fill"></i>
                <span>Update Layanan</span>
            </button>
        </div>

    </form>

</div>

@endsection


@section('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
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
