@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="w-full bg-yellow-400 p-4 flex items-center">
    <a href="{{ route('parfum.index') }}" class="text-2xl mr-3">←</a>
    <h1 class="text-xl font-bold">Tambah Parfum</h1>
</div>

{{-- CONTENT --}}
<div class="p-4 bg-white min-h-screen">

    <form action="{{ route('parfum.store') }}" method="POST">
        @csrf

        <label class="block font-bold mb-2 text-lg">Nama Parfum</label>

        <input type="text" name="nama_parfum"
            class="w-full p-4 rounded-2xl bg-gray-200 text-lg outline-none"
            placeholder="">

        {{-- BUTTON --}}
        <button type="submit"
            class="w-full bg-green-600 py-4 rounded-full mt-10 font-bold text-white text-xl">
            Simpan
        </button>
    </form>

</div>

@endsection
