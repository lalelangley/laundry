@extends('layouts.master')

@section('content')
@php
    $layanan_id = 0; // default untuk layanan baru
    $jenisBaru = session()->get("jenis_baru_{$layanan_id}", []);
    $jenisLama = \App\Models\JenisLayanan::selectRaw('MIN(id_jenis_layanan) as id_jenis_layanan, nama_jenis, harga, id_satuan, lama, lama_satuan, keterangan')
    ->groupBy('nama_jenis', 'harga', 'id_satuan', 'lama', 'lama_satuan', 'keterangan')
    ->orderBy('nama_jenis')
    ->get();

@endphp

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center gap-3 shadow">
    <a href="{{ route('layanan.index') }}" class="text-black text-3xl font-bold">←</a>
    <span class="text-xl font-bold">Tambah Layanan</span>
</div>

<div class="px-5 mt-6">

    {{-- ALERT jika belum ada jenis --}}
    @if($errors->has('jenis_kosong'))
        <div class="bg-red-500 text-white p-3 rounded-xl mb-5">
            {{ $errors->first('jenis_kosong') }}
        </div>
    @endif

    {{-- FLASH SUCCESS --}}
    @if(session('success'))
        <div class="bg-green-500 text-white p-3 rounded-xl mb-5">
            {{ session('success') }}
        </div>
    @endif


    {{-- JENIS DARI SESSION --}}
    @if(count($jenisBaru) > 0)
        <h3 class="mt-6 mb-3 font-bold text-lg">Jenis Layanan (Baru)</h3>

        <div class="space-y-4">
            @foreach ($jenisBaru as $jb)
                @php
                    $satuanNama = '-';
                    if(!empty($jb['id_satuan'])){
                        $satuan = \App\Models\Satuan::find($jb['id_satuan']);
                        if($satuan) $satuanNama = $satuan->nama_satuan;
                    }
                @endphp

                <div class="flex gap-3 p-4 bg-yellow-50 border border-yellow-200 rounded-2xl shadow">
                    <div class="flex-1">
                        <p class="font-semibold capitalize">{{ $jb['nama_jenis'] ?? '-' }}</p>
                        <p class="text-gray-700 text-sm">
                            Rp{{ number_format($jb['harga'] ?? 0) }} / {{ $satuanNama }}
                        </p>
                        <p class="text-gray-500 text-xs">
                            {{ $jb['lama'] ?? '-' }} {{ $jb['lama_satuan'] ?? '-' }}
                        </p>

                        @if(!empty($jb['keterangan']))
                            <p class="text-gray-600 text-xs mt-1">{{ $jb['keterangan'] }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- BUTTON TAMBAH JENIS BARU --}}
    <a href="{{ route('kasir.session.create', $layanan_id) }}" 
    class="block mt-6 mb-6 bg-yellow-400 text-white text-center py-3 rounded-2xl font-semibold shadow">
        <i class="bi bi-plus-circle"></i> Tambah Jenis Layanan Baru
    </a>

    {{-- ======================== --}}
    {{-- FORM CREATE LAYANAN      --}}
    {{-- ======================== --}}
    <form action="{{ route('kasir.layanan.store') }}" method="POST" class="bg-white p-5 rounded-2xl shadow">
        @csrf

        {{-- JENIS LAYANAN LAMA --}}
        @if(count($jenisLama) > 0)
            <h3 class="mb-3 font-bold text-lg">Riwayat Jenis Layanan</h3>
            <div class="space-y-2 mb-6">
                @foreach ($jenisLama as $jl)
                    <label class="flex items-center p-3 border rounded-xl cursor-pointer hover:bg-gray-50">
                        <input type="checkbox" name="jenis_lama[]" value="{{ $jl->id_jenis_layanan }}"
                            class="mr-3"
                            {{ in_array($jl->id_jenis_layanan, old('jenis_lama', [])) ? 'checked' : '' }}>
                        <div>
                            <p class="font-semibold">{{ $jl->nama_jenis }}</p>
                            <p class="text-sm text-gray-600">
                                Rp{{ number_format($jl->harga,0,',','.') }} /
                                {{ $jl->satuan->nama_satuan ?? '-' }}
                            </p>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif

        {{-- Nama Layanan --}}
        <div class="mb-5">
            <label class="font-semibold">Nama Layanan</label>
            <input type="text" name="nama_layanan"
                   value="{{ old('nama_layanan') }}"
                   class="w-full p-3 border rounded-xl mt-1"
                   required>
        </div>

        {{-- Proses --}}
        @php
            $prosesList = ['Cuci', 'Kering', 'Setrika'];
        @endphp

        <div class="mb-5">
            <label class="font-semibold">Proses</label>
            <div class="grid grid-cols-3 gap-3 mt-2">
                @foreach ($prosesList as $p)
                    <label class="flex items-center gap-2 p-3 border rounded-xl cursor-pointer hover:bg-gray-100">
                        <input type="checkbox" name="proses[]" value="{{ $p }}">
                        <span>{{ $p }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        {{-- SUBMIT --}}
        <div class="flex justify-end mt-6">
            <button type="submit" 
                    class="bg-green-600 hover:bg-green-700 px-6 py-2 rounded-xl font-semibold text-white">
                Simpan Layanan
            </button>
        </div>

    </form>
</div>
@endsection

