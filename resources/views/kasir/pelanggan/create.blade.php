@extends('layouts.master')

@section('title', 'Tambah Pelanggan')

@section('content')

<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
@php
    $backUrl = request('from') === 'transaksi'
        ? route('kasir.transaksi.pelanggan')   
        : route('kasir.pelanggan.index');    
@endphp

<a href="{{ $backUrl }}" class="text-black text-3xl font-bold">
    <i class="bi bi-arrow-left"></i>
</a>

    <span class="text-2xl font-bold">Tambah Pelanggan</span>
</div>


{{-- CONTENT --}}
<div class="px-5 py-6">

   <form action="{{ route('kasir.pelanggan.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
    @csrf

    <input type="hidden" name="from" value="{{ request('from') }}">
        
        {{-- FOTO --}}
        <div class="flex flex-col items-center">
            <div class="w-28 h-28 bg-gray-200 rounded-full overflow-hidden shadow flex items-center justify-center">
                <img id="previewImg" class="hidden w-full h-full object-cover" />
                <i id="iconDefault" class="bi bi-person text-5xl text-gray-500"></i>
            </div>

            <label class="mt-3 bg-yellow-500 text-black px-5 py-2 rounded-full font-semibold cursor-pointer shadow-md hover:bg-yellow-600 transition">
                Pilih Foto
                <input id="fotoInput" type="file" name="gambar" class="hidden" accept="image/*">
            </label>
        </div>

        {{-- FORM CARD --}}
        <div class="bg-white rounded-2xl shadow-md p-5 space-y-5 border border-yellow-300">

            {{-- Nama --}}
            <div>
                <label class="font-semibold text-gray-700">Nama Pelanggan</label>
                <input type="text" name="nama_pelanggan"
                    class="mt-2 w-full bg-gray-100 px-4 py-3 rounded-xl focus:ring-2 focus:ring-yellow-400 outline-none"
                    placeholder="Nama pelanggan...">
            </div>

            {{-- No HP --}}
            <div>
                <label class="font-semibold text-gray-700">No Handphone</label>
                <input type="text" name="no_hp"
                    class="mt-2 w-full bg-gray-100 px-4 py-3 rounded-xl outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="08xxxxxxxxxx">
            </div>

            {{-- Email --}}
            <div>
                <label class="font-semibold text-gray-700">Email</label>
                <input type="email" name="email"
                    class="mt-2 w-full bg-gray-100 px-4 py-3 rounded-xl outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="Email pelanggan...">
            </div>

            {{-- Gender --}}
            <div>
                <label class="font-semibold text-gray-700">Gender</label>
                <div class="flex items-center gap-8 mt-2">
                    <label class="flex items-center gap-2">
                    <input type="radio" name="jk" value="L" class="accent-yellow-500" checked>
                    <span>Pria</span>
                </label>

                <label class="flex items-center gap-2">
                    <input type="radio" name="jk" value="P" class="accent-yellow-500">
                    <span>Wanita</span>
                </label>
                </div>
            </div>

            {{-- ALAMAT --}}
            <div>
                <label class="font-semibold text-gray-700">Alamat</label>
                <textarea name="alamat" rows="3"
                    class="mt-2 w-full bg-gray-100 px-4 py-3 rounded-xl outline-none focus:ring-2 focus:ring-yellow-400"
                    placeholder="Alamat pelanggan..."></textarea>
            </div>

        </div>
        @if ($errors->any())
    <div class="bg-red-300 text-red-900 p-3 rounded-xl mb-4">
        {{ $errors->first() }}
    </div>
@endif

        {{-- BUTTON --}}
        <button type="submit"
        
            class="w-full bg-yellow-500 text-black py-4 rounded-full text-xl font-semibold shadow-lg hover:bg-yellow-600 transition">
            Simpan
        </button>

    </form>

</div>

{{-- SCRIPT PREVIEW FOTO --}}
<script>
    document.getElementById('fotoInput').addEventListener('change', function () {
        const file = this.files[0];
        const preview = document.getElementById('previewImg');
        const icon = document.getElementById('iconDefault');

        if (file) {
            const reader = new FileReader();
            reader.onload = function (e) {
                preview.src = e.target.result;
                preview.classList.remove('hidden');
                icon.classList.add('hidden');
            }
            reader.readAsDataURL(file);
        }
    });
</script>

@endsection
