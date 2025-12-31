@extends('layouts.master')

@section('content')

{{-- HEADER --}}
<div class="w-full bg-yellow-400 p-4 flex items-center">
    <a href="{{ route('kasir.satuan.index') }}" class="text-2xl mr-3">←</a>
    <h1 class="text-xl font-bold">Edit Satuan</h1>
</div>

<div class="p-4 bg-white min-h-screen">

    <form action="{{ route('kasir.satuan.update', $satuan->id_satuan) }}" method="POST">
        @csrf
        @method('PUT')

        <label class="block font-bold mb-2 text-lg">Nama Satuan</label>

        <input type="text" name="nama_satuan"
            value="{{ $satuan->nama_satuan }}"
            class="w-full p-4 rounded-2xl bg-gray-200 text-lg outline-none">

        <!-- BUTTON -->
        <button type="submit"
            class="w-full bg-green-600 py-4 rounded-full mt-10 font-bold text-white text-xl">
            Update
        </button>

    </form>

</div>

@endsection
