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

    {{-- FORM EDIT LAYANAN --}}
    <form action="{{ route('layanan.update', $layanan->id_layanan) }}" method="POST">
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
            $selectedProses = old('proses', explode(',', $layanan->proses));
        @endphp
        <div class="mb-5">
            <label class="font-semibold">Proses</label>
            <div class="grid grid-cols-3 gap-3 mt-2">
                @foreach ($prosesList as $p)
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer
                                  {{ in_array($p, $selectedProses) ? 'bg-yellow-100 border-yellow-400' : '' }}">
                        <input type="checkbox" name="proses[]" value="{{ $p }}"
                               {{ in_array($p, $selectedProses) ? 'checked' : '' }}>
                        <span>{{ $p }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- LIST JENIS LAYANAN --}}
        <h3 class="mt-10 mb-3 font-bold text-lg">Jenis Layanan</h3>
        @php
            // Ambil data jenis baru dari session
            $jenisBaru = session()->get("jenis_baru_{$layanan->id_layanan}", []);
        @endphp

        @if ($layanan->jenis->count() > 0 || count($jenisBaru) > 0)
            <div class="space-y-4">
                {{-- Jenis Lama --}}
                {{-- Jenis Lama --}}
@foreach ($layanan->jenis as $jenis)
    <a href="{{ route('jenis.edit', $jenis->id_jenis_layanan) }}?from={{ $layanan->id_layanan }}"
       class="flex gap-3 p-4 bg-white border rounded-2xl shadow hover:bg-yellow-50 transition">
       
        {{-- Gambar Jenis --}}
        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100 flex-shrink-0">
            <img src="{{ asset('images/' . ($jenis->gambar ?? 'default.png')) }}"
                 class="w-full h-full object-cover">
        </div>

        {{-- Detail Jenis --}}
        <div class="flex-1">
            <p class="font-semibold text-base capitalize">{{ $jenis->nama_jenis }}</p>
            <p class="text-gray-700 text-sm">
                Rp{{ number_format($jenis->harga, 0, ',', '.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}
            </p>
            <p class="text-gray-500 text-xs flex items-center gap-1">
                <i class="bi bi-clock"></i>
                {{ $jenis->lama }} {{ $jenis->lama_satuan }}
            </p>
        </div>

        {{-- Icon Edit --}}
        <div class="flex items-center">
            <i class="bi bi-pencil-square text-gray-400"></i>
        </div>
    </a>
@endforeach


                {{-- Jenis Baru dari Session --}}
@foreach ($jenisBaru as $jb)
    <div class="flex gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl shadow">
        <div class="w-16 h-16 rounded-xl overflow-hidden bg-gray-100">
            @if(!empty($jb['gambar']))
                <img src="{{ asset('images/' . $jb['gambar']) }}" class="w-full h-full object-cover">
            @else
                <img src="{{ asset('images/default.png') }}" class="w-full h-full object-cover">
            @endif
        </div>
        <div class="flex-1">
            <p class="font-semibold text-base capitalize">
                {{ $jb['nama'] ?? '-' }} 
                <span class="text-xs text-gray-500">(baru)</span>
            </p>
            <p class="text-gray-700 text-sm">
                Rp{{ number_format($jb['harga'], 0, ',', '.') }} / {{ $jb['satuan'] ?? '-' }}
            </p>
            <p class="text-gray-500 text-xs flex items-center gap-1">
                <i class="bi bi-clock"></i>
                {{ $jb['lama'] ?? '-' }} {{ $jb['lama_satuan'] ?? '-' }}
            </p>
        </div>
    </div>
@endforeach

            </div>
        @else
            <p class="text-gray-400 text-sm">Belum ada jenis layanan.</p>
        @endif

        {{-- BUTTON TAMBAH JENIS --}}
        <a href="{{ route('session.create', ['from' => $layanan->id_layanan]) }}?mode=edit" 
   class="block mt-5 bg-yellow-400 text-white text-center py-3 rounded-2xl font-semibold shadow">
   <i class="bi bi-plus-circle"></i> Tambah Jenis
</a>



        {{-- SUBMIT UPDATE LAYANAN --}}
        <div class="flex justify-end mt-6">
            <button type="submit"
                    class="bg-yellow-500 hover:bg-yellow-600 px-6 py-2 rounded-xl font-semibold text-white">
                Update Layanan
            </button>
        </div>

    </form>
</div>

@endsection
