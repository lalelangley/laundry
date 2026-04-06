@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="w-full bg-yellow-400 p-4 flex items-center">
    <a href="{{ route('satuan.index') }}" class="text-2xl mr-3">←</a>
    <h1 class="text-xl font-bold">Edit Satuan</h1>
</div>

<div class="p-4 bg-white min-h-screen">

    <form action="{{ route('satuan.update', $satuan->id_satuan) }}" method="POST">
        @csrf
        @method('PUT')

        <label class="block font-bold mb-2 text-lg">Nama Satuan</label>

        <input type="text" name="nama_satuan"
            value="{{ $satuan->nama_satuan }}"
            class="w-full p-4 rounded-2xl bg-gray-200 text-lg outline-none">

        {{-- ERROR MESSAGE --}}
        @error('nama_satuan')
            <p class="text-red-500 text-sm mt-2">{{ $message }}</p>
        @enderror

        <!-- BUTTON -->
        <button type="submit"
            class="w-full bg-green-600 py-4 rounded-full mt-10 font-bold text-white text-xl">
            Update
        </button>

    </form>

</div>

@endsection
