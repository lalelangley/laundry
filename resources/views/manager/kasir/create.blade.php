{{-- FE-DOC: Template frontend untuk resources/views/manager/kasir/create.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Tambah Manajemen Pengguna')

@section('content')
<div class="min-h-screen bg-slate-50">
    <div class="bg-yellow-400 px-5 py-5 shadow-lg sm:px-8 sm:py-6">
        <div class="mx-auto flex max-w-6xl items-center gap-4">
            <a href="{{ route('manager.index') }}" class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-white/70 text-2xl font-bold text-gray-900 transition hover:bg-white">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.24em] text-gray-700">User Manager</p>
                <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">Tambah Kasir</h1>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-8 sm:py-10">
        <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_320px]">
            <section class="overflow-hidden rounded-[28px] bg-white shadow-[0_24px_60px_-32px_rgba(15,23,42,0.35)]">
                <div class="border-b border-slate-200 bg-gradient-to-r from-amber-50 via-yellow-50 to-white px-6 py-7 sm:px-10">
                    <div class="flex items-start gap-4">
                        <div class="flex h-16 w-16 items-center justify-center rounded-3xl bg-gray-900 text-2xl text-yellow-400 shadow-lg">
                            <i class="bi bi-person-plus-fill"></i>
                        </div>
                        <div>
                            <h2 class="text-xl font-bold text-slate-900">Informasi Kasir Baru</h2>
                            <p class="mt-1 text-sm text-slate-600">Lengkapi identitas akun kasir sebelum disimpan ke sistem.</p>
                        </div>
                    </div>
                </div>

                <div class="px-6 py-7 sm:px-10 sm:py-10">
                    @if ($errors->any())
                        <div class="mb-8 rounded-2xl border border-red-200 bg-red-50 px-5 py-4">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-exclamation-triangle-fill mt-0.5 text-lg text-red-500"></i>
                                <div>
                                    <p class="font-semibold text-red-700">Data belum bisa disimpan.</p>
                                    <ul class="mt-2 list-disc space-y-1 pl-5 text-sm text-red-600">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="mb-8 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-700">
                            <div class="flex items-center gap-3">
                                <i class="bi bi-check-circle-fill text-lg"></i>
                                <span>{{ session('success') }}</span>
                            </div>
                        </div>
                    @endif

                    <form action="{{ route('manager.kasir.store') }}" method="POST" class="space-y-8">
                        @csrf

                        <div class="grid gap-6 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    Nama Lengkap <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-lg {{ $errors->has('nama_kasir') ? 'text-red-400' : 'text-slate-400' }}">
                                        <i class="bi bi-person"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="nama_kasir"
                                        value="{{ old('nama_kasir') }}"
                                        class="w-full rounded-2xl border-2 bg-white py-4 pl-12 pr-4 text-slate-900 outline-none transition {{ $errors->has('nama_kasir') ? 'border-red-300 bg-red-50 focus:border-red-400 focus:ring-4 focus:ring-red-100' : 'border-slate-200 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                        placeholder="Contoh: Budi Santoso"
                                        required
                                    >
                                </div>
                                @error('nama_kasir')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    Email <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-lg {{ $errors->has('email') ? 'text-red-400' : 'text-slate-400' }}">
                                        <i class="bi bi-envelope-fill"></i>
                                    </span>
                                    <input
                                        type="email"
                                        name="email"
                                        value="{{ old('email') }}"
                                        class="w-full rounded-2xl border-2 bg-white py-4 pl-12 pr-4 text-slate-900 outline-none transition {{ $errors->has('email') ? 'border-red-300 bg-red-50 focus:border-red-400 focus:ring-4 focus:ring-red-100' : 'border-slate-200 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                        placeholder="Contoh: kasir@kasmini.com"
                                        required
                                    >
                                </div>
                                @error('email')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    No. Telepon <span class="text-xs font-normal text-slate-400">(Opsional)</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-lg {{ $errors->has('no_hp') ? 'text-red-400' : 'text-slate-400' }}">
                                        <i class="bi bi-telephone-fill"></i>
                                    </span>
                                    <input
                                        type="text"
                                        name="no_hp"
                                        value="{{ old('no_hp') }}"
                                        class="w-full rounded-2xl border-2 bg-white py-4 pl-12 pr-4 text-slate-900 outline-none transition {{ $errors->has('no_hp') ? 'border-red-300 bg-red-50 focus:border-red-400 focus:ring-4 focus:ring-red-100' : 'border-slate-200 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                        placeholder="Contoh: 08xxxxxxxxxx"
                                    >
                                </div>
                                @error('no_hp')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="md:col-span-2">
                                <label class="mb-2 block text-sm font-semibold text-slate-700">
                                    Password <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-lg {{ $errors->has('password') ? 'text-red-400' : 'text-slate-400' }}">
                                        <i class="bi bi-lock-fill"></i>
                                    </span>
                                    <input
                                        type="password"
                                        name="password"
                                        id="passwordInput"
                                        class="w-full rounded-2xl border-2 bg-white py-4 pl-12 pr-14 text-slate-900 outline-none transition {{ $errors->has('password') ? 'border-red-300 bg-red-50 focus:border-red-400 focus:ring-4 focus:ring-red-100' : 'border-slate-200 focus:border-yellow-400 focus:ring-4 focus:ring-yellow-100' }}"
                                        placeholder="Minimal 6 karakter"
                                        required
                                    >
                                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 transition hover:text-slate-700">
                                        <i class="bi bi-eye-fill text-lg" id="eyeIcon"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="rounded-3xl border border-slate-200 bg-slate-50 px-5 py-5">
                            <div class="flex items-start gap-3">
                                <i class="bi bi-shield-lock-fill mt-0.5 text-lg text-slate-500"></i>
                                <div class="text-sm text-slate-600">
                                    <p class="font-semibold text-slate-800">Catatan keamanan</p>
                                    <p class="mt-1">Gunakan kombinasi huruf, angka, dan simbol agar password lebih kuat. Password minimal <strong>6 karakter</strong>.</p>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-col-reverse gap-3 border-t border-slate-200 pt-8 sm:flex-row sm:items-center sm:justify-between">
                            <a href="{{ route('manager.index') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl border-2 border-slate-200 bg-white px-6 py-3 font-semibold text-slate-700 transition hover:bg-slate-50">
                                <i class="bi bi-x-circle"></i>
                                <span>Batal</span>
                            </a>
                            <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-gray-900 px-7 py-3 font-bold text-white shadow-lg shadow-slate-300 transition hover:bg-slate-800">
                                <i class="bi bi-check-circle-fill"></i>
                                <span>Simpan Kasir</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>

            <aside class="space-y-5">
                <div class="rounded-[28px] border border-blue-100 bg-gradient-to-br from-blue-50 to-cyan-50 px-6 py-6 shadow-sm">
                    <div class="flex items-start gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-600 text-xl text-white">
                            <i class="bi bi-info-circle-fill"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Informasi Penting</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-700">Pastikan nama, email, dan nomor HP belum dipakai akun lain sebelum menambahkan kasir baru.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[28px] border border-amber-100 bg-white px-6 py-6 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-amber-600">Checklist</p>
                    <ul class="mt-4 space-y-3 text-sm text-slate-700">
                        <li class="flex items-start gap-3">
                            <i class="bi bi-check2-circle text-emerald-500"></i>
                            <span>Email akan dipakai untuk login kasir.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="bi bi-check2-circle text-emerald-500"></i>
                            <span>No telepon bersifat opsional, tapi membantu saat verifikasi akun.</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <i class="bi bi-check2-circle text-emerald-500"></i>
                            <span>Hak akses kasir bisa diatur lagi dari halaman manager.</span>
                        </li>
                    </ul>
                </div>
            </aside>
        </div>
    </div>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('passwordInput');
    const icon = document.getElementById('eyeIcon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye-fill', 'bi-eye-slash-fill');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash-fill', 'bi-eye-fill');
    }
}
</script>
@endsection
