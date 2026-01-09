@extends('layouts.master')

@section('title', 'Edit Profile')

@section('content')
<div class="min-h-screen bg-gray-50">

     {{-- HEADER --}}
   <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl flex items-center gap-4 shadow-lg">
        <a href="{{ route('admin2.dashboard') }}" class="text-white text-3xl font-bold hover:opacity-80 transition">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold text-white">Edit Profile</span>
    </div>

    <div class="px-6 md:px-12 py-10">
        <div class="bg-white rounded-2xl shadow-xl max-w-4xl mx-auto overflow-hidden">

            {{-- HEADER CARD --}}
            <div class="px-8 py-8 border-b bg-yellow-50 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-yellow-400 rounded-2xl flex items-center justify-center">
                        <i class="bi bi-person-gear text-white text-2xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Edit Profil Saya</h3>
                        <p class="text-sm text-gray-600">{{ $admin->email }}</p>
                    </div>
                </div>

                <div class="text-right">
                    <p class="text-xs text-yellow-600 font-semibold">ROLE</p>
                    <p class="font-bold text-yellow-700">
                        {{ $admin->role_id == 1 ? 'Super Admin' : 'Admin' }}
                    </p>
                </div>
            </div>

            {{-- BODY --}}
            <div class="p-8">

                {{-- SUCCESS --}}
                @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-200 text-green-800 px-4 py-3 rounded-xl">
                    <i class="bi bi-check-circle-fill mr-2"></i>
                    {{ session('success') }}
                </div>
                @endif

                {{-- ERROR --}}
                @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                    <ul class="list-disc ml-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <form method="POST" action="{{ route('profile.admin.update') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        {{-- NAMA --}}
                        <div>
                            <label class="text-sm font-semibold">Nama</label>
                            <input type="text" name="nama"
                                   value="{{ old('nama', $admin->nama) }}"
                                   class="w-full mt-2 rounded-xl border-gray-300 focus:ring-yellow-400 focus:border-yellow-400"
                                   required>
                        </div>

                        {{-- EMAIL --}}
                        <div>
                            <label class="text-sm font-semibold">Email</label>
                            <input type="email" name="email"
                                   value="{{ old('email', $admin->email) }}"
                                   class="w-full mt-2 rounded-xl border-gray-300 focus:ring-yellow-400 focus:border-yellow-400"
                                   required>
                        </div>

                        {{-- PASSWORD --}}
                        <div>
                            <label class="text-sm font-semibold">Password Baru</label>
                            <input type="password" name="password"
                                   class="w-full mt-2 rounded-xl border-gray-300 focus:ring-yellow-400 focus:border-yellow-400"
                                   placeholder="Kosongkan jika tidak diubah">
                        </div>

                        {{-- CONFIRM --}}
                        <div>
                            <label class="text-sm font-semibold">Konfirmasi Password</label>
                            <input type="password" name="password_confirmation"
                                   class="w-full mt-2 rounded-xl border-gray-300 focus:ring-yellow-400 focus:border-yellow-400">
                        </div>
                    </div>

                    {{-- BUTTON --}}
                    <div class="flex justify-end gap-4 mt-8">
                        <a href="{{ url()->previous() }}"
                           class="px-6 py-3 rounded-xl border border-gray-300 font-semibold">
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
