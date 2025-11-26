@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.index') }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Edit Layanan</span>
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

    {{-- FORM EDIT --}}
    <form action="{{ route('layanan.update', $layanan->id_layanan) }}"
          method="POST"
          class="bg-white p-5 rounded-2xl shadow">

        @csrf
        @method('PUT')

        {{-- NAMA LAYANAN --}}
        <div class="mb-5">
            <label class="font-semibold">Nama Layanan</label>
            <input type="text" name="nama_layanan"
                   value="{{ old('nama_layanan', $layanan->nama_layanan) }}"
                   class="w-full p-3 border rounded-xl mt-1"
                   required>
        </div>

        {{-- PROSES --}}
        @php
            $prosesList = ['Cuci', 'Kering', 'Setrika'];
            $selectedProses = explode(',', $layanan->proses);
        @endphp

        <div class="mb-5">
            <label class="font-semibold">Proses</label>

            <div class="grid grid-cols-3 gap-3 mt-2">
                @foreach ($prosesList as $p)
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer
                                  {{ in_array($p, $selectedProses) ? 'bg-yellow-100 border-yellow-400' : '' }}">
                        <input type="checkbox" name="proses[]"
                               value="{{ $p }}"
                               {{ in_array($p, $selectedProses) ? 'checked' : '' }}>
                        <span>{{ $p }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end mt-6">
            <button class="bg-yellow-500 hover:bg-yellow-600 px-6 py-2 rounded-xl font-semibold">
                Update Layanan
            </button>
        </div>
    </form>


    {{-- ======================
         LIST JENIS LAYANAN
       ====================== --}}
    <h3 class="mt-10 mb-3 font-bold text-lg">Jenis Layanan</h3>

    @if ($layanan->jenis->count() > 0)
        <div class="space-y-4">

            @foreach ($layanan->jenis as $jenis)
                <a href="{{ route('jenis.edit', $jenis->id_jenis_layanan) }}"
                   class="flex gap-3 p-4 bg-white border rounded-2xl shadow">

                    {{-- GAMBAR --}}
                    <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100">
                        <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                             class="w-full h-full object-cover">
                    </div>

                    {{-- DATA --}}
                    <div class="flex-1">
                        <p class="font-semibold text-base capitalize">{{ $jenis->nama_jenis }}</p>
                        <p class="text-gray-700 text-sm">
                            Rp{{ number_format($jenis->harga, 0, ',', '.') }} / {{ $jenis->satuan }}
                        </p>
                        <p class="text-gray-500 text-xs flex items-center gap-1">
                            <i class="bi bi-clock"></i>
                            {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                        </p>
                    </div>

                </a>
            @endforeach

        </div>
    @else
        <p class="text-gray-400 text-sm">Belum ada jenis layanan.</p>
    @endif

    {{-- BUTTON TAMBAH JENIS --}}
    <a href="{{ route('layanan.jenis.create') }}"
       class="block mt-5 bg-yellow-400 text-white text-center py-3 rounded-2xl font-semibold shadow">
        <i class="bi bi-plus-circle"></i> Tambah Jenis
    </a>

</div>

@endsection
