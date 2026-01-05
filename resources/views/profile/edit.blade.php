@extends('layouts.master')

@section('title', 'Edit Profile Kasir')

@section('content')
<div class="min-h-screen bg-gray-50">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('kasir.dashboard') }}" class="text-white text-3xl font-bold">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-white">Edit Profile Kasir</span>
    </div>

    <div class="px-6 md:px-12 py-10">
        <div class="bg-white rounded-2xl shadow-xl max-w-4xl mx-auto overflow-hidden">

            {{-- HEADER CARD --}}
            <div class="px-8 py-8 border-b bg-yellow-50 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl overflow-hidden border-2 border-yellow-400">
                        @if($kasir->gambar)
                            <img src="{{ asset('storage/'.$kasir->gambar) }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-yellow-400">
                                <i class="bi bi-person-fill text-white text-2xl"></i>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="font-bold text-lg">{{ $kasir->nama_kasir }}</h3>
                        <p class="text-sm text-gray-600">{{ $kasir->no_hp }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-xs text-yellow-600 font-semibold">ROLE</p>
                    <p class="font-bold text-yellow-700">Kasir</p>
                </div>
            </div>

            {{-- BODY --}}
            <div class="p-8">

                @if(session('success'))
                    <div class="mb-6 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                        <i class="bi bi-check-circle-fill mr-2"></i>
                        {{ session('success') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                        <ul class="list-disc ml-5">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('profile.kasir.update') }}" enctype="multipart/form-data">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <label class="text-sm font-semibold">Nama Kasir</label>
                            <input type="text" name="nama_kasir"
                                value="{{ old('nama_kasir', $kasir->nama_kasir) }}"
                                class="w-full mt-2 rounded-xl border-gray-300 focus:ring-yellow-400"
                                required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">No HP</label>
                            <input type="text" name="no_hp"
                                value="{{ old('no_hp', $kasir->no_hp) }}"
                                class="w-full mt-2 rounded-xl border-gray-300 focus:ring-yellow-400"
                                required>
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Password Baru</label>
                            <input type="password" name="password"
                                class="w-full mt-2 rounded-xl border-gray-300"
                                placeholder="Kosongkan jika tidak diubah">
                        </div>

                        <div>
                            <label class="text-sm font-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                class="w-full mt-2 rounded-xl border-gray-300">
                        </div>

                        <div class="md:col-span-2">
                            <label class="text-sm font-semibold">Foto Profil</label>
                            <input type="file" name="gambar"
                                class="w-full mt-2 rounded-xl border-gray-300">
                        </div>
                    </div>

                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ route('kasir.dashboard') }}"
                            class="px-6 py-3 rounded-xl border font-semibold">
                            Batal
                        </a>

                        <button type="submit"
                            class="px-8 py-3 rounded-xl bg-yellow-400 hover:bg-yellow-500 font-bold shadow">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
