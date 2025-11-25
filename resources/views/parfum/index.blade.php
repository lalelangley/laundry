@extends('layouts.master')

@section('content')

<div class="w-full bg-yellow-400 p-4 flex items-center">
    <div onclick="toggleSidebar()" class="text-2xl mr-3 cursor-pointer">☰</div>
    <h1 class="text-xl font-bold">Kelola Parfum</h1>
</div>

<div class="p-4 bg-gray-100 min-h-screen">

    @foreach($parfums as $p)
    <div class="bg-white p-4 mb-3 flex items-center rounded-xl shadow">
        <span class="text-3xl mr-3">🧴</span>
        <p class="font-semibold flex-1">{{ $p->nama_parfum }}</p>

        <a href="{{ route('parfum.edit', $p->id_parfum) }}" class="text-blue-500 text-xl mr-3">
            <i class="bi bi-pencil-square"></i>
        </a>

        <form action="{{ route('parfum.destroy',$p->id_parfum) }}" method="POST">
            @csrf
            @method('DELETE')
            <button class="text-red-500 text-xl">
                <i class="bi bi-trash"></i>
            </button>
        </form>
    </div>
    @endforeach

    <a href="{{ route('parfum.create') }}">
        <button class="w-full bg-yellow-400 py-3 rounded-full mt-6 font-bold text-white">
            Tambah Parfum
        </button>
    </a>

</div>

@endsection
