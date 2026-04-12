{{-- FE-DOC: Template frontend untuk resources/views/admin2/laporan/bayar/index.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')
@section('title', 'Laporan Metode Bayar')
@section('content')
<div class="min-h-screen bg-gray-100 pb-16">
{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
    <div class="sticky top-0 z-20 bg-yellow-400 shadow-md">
        <div class="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-5 sm:px-6 lg:flex-row lg:items-center lg:justify-between lg:px-8">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin2.laporan.index') }}" class="text-2xl font-bold text-gray-900 transition hover:opacity-80">
                    <i class="bi bi-arrow-left"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-gray-900 lg:text-2xl">Laporan Metode Bayar</h1>
                    <p class="text-sm text-gray-700">Tampilan laporan diseragamkan untuk pembacaan desktop.</p>
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin2.laporan.bayar.export', ['dari' => $tglAwal, 'sampai' => $tglAkhir, 'format' => 'pdf']) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50">
                    <i class="bi bi-file-earmark-pdf text-red-500"></i>
                    PDF
                </a>
                <a href="{{ route('admin2.laporan.bayar.export', ['dari' => $tglAwal, 'sampai' => $tglAkhir]) }}"
                   class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-sm transition hover:bg-gray-50">
                    <i class="bi bi-file-earmark-excel text-green-600"></i>
                    Excel
                </a>
            </div>
        </div>
    </div>

    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
        {{-- FILTER TANGGAL --}}
        {{-- FE-DOC: Dua input tanggal biasanya menjadi filter utama untuk semua data laporan per periode. --}}
        <form method="GET" class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5 lg:p-5">
            <div class="grid grid-cols-1 gap-4 xl:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_220px_56px] xl:items-end">
                <label class="block">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-gray-500">Tanggal Awal</span>
                    <div class="flex items-center gap-3 rounded-2xl bg-yellow-100 px-4 py-3">
                        <i class="bi bi-calendar-event text-yellow-700"></i>
                        <input type="date" name="dari" value="{{ $tglAwal }}" class="w-full bg-transparent font-semibold text-gray-800 outline-none">
                    </div>
                </label>
                <label class="block">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-gray-500">Tanggal Akhir</span>
                    <div class="flex items-center gap-3 rounded-2xl bg-yellow-100 px-4 py-3">
                        <i class="bi bi-calendar-event text-yellow-700"></i>
                        <input type="date" name="sampai" value="{{ $tglAkhir }}" class="w-full bg-transparent font-semibold text-gray-800 outline-none">
                    </div>
                </label>
                <label class="relative block">
                    <span class="mb-2 block text-xs font-bold uppercase tracking-[0.2em] text-gray-500">Urutkan</span>
                    <select name="sort" onchange="this.form.submit()" class="w-full appearance-none rounded-2xl border border-gray-200 bg-gray-50 px-4 py-3 pr-11 text-sm font-semibold text-gray-700 outline-none transition focus:border-yellow-400">
                        <option value="penggunaan_tertinggi" {{ request('sort', 'penggunaan_tertinggi') === 'penggunaan_tertinggi' ? 'selected' : '' }}>Penggunaan Tertinggi</option>
                        <option value="penggunaan_terendah" {{ request('sort') === 'penggunaan_terendah' ? 'selected' : '' }}>Penggunaan Terendah</option>
                        <option value="nama_az" {{ request('sort') === 'nama_az' ? 'selected' : '' }}>Nama A-Z</option>
                        <option value="nama_za" {{ request('sort') === 'nama_za' ? 'selected' : '' }}>Nama Z-A</option>
                    </select>
                    <i class="bi bi-chevron-down pointer-events-none absolute right-4 top-[calc(50%+12px)] -translate-y-1/2 text-gray-500 text-sm"></i>
                </label>
                <a href="{{ route('admin2.laporan.bayar.index') }}" class="inline-flex h-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-600 transition hover:bg-gray-200" title="Reset Filter">
                    <i class="bi bi-arrow-clockwise"></i>
                </a>
            </div>
        </form>

        {{-- SUMMARY CARD --}}
        {{-- FE-DOC: Summary card dipakai untuk menonjolkan angka utama yang paling cepat dibaca user. --}}
        <div class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-3">
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-sm font-semibold text-gray-500">Total Penggunaan</p>
                <p class="mt-2 text-3xl font-bold text-yellow-500">{{ $totalPenggunaan }}</p>
            </div>
            <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                <p class="text-sm font-semibold text-gray-500">Jumlah Metode</p>
                <p class="mt-2 text-3xl font-bold text-gray-800">{{ $data->count() }}</p>
            </div>
            <div class="rounded-3xl bg-gradient-to-br from-yellow-400 to-orange-400 p-5 text-gray-900 shadow-sm">
                <p class="text-sm font-semibold">Periode Aktif</p>
                <p class="mt-2 text-lg font-bold">{{ \Carbon\Carbon::parse($tglAwal)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tglAkhir)->format('d M Y') }}</p>
            </div>
        </div>

        {{-- LIST --}}
        <div class="mt-6 overflow-hidden rounded-3xl bg-white shadow-sm ring-1 ring-black/5">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-900">Ringkasan Metode Bayar</h2>
                    <p class="text-sm text-gray-500">Struktur daftar dibuat sama untuk layar desktop dan mobile.</p>
                </div>
            </div>

            <div class="lg:hidden">
                @forelse ($data as $item)
                <div class="border-b border-gray-100 px-5 py-4 last:border-b-0">
                    <div class="flex items-center justify-between gap-4">
                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-gray-400">Metode</p>
                            <p class="mt-1 text-lg font-bold text-gray-900 break-words">{{ $item->nama_metode_bayar }}</p>
                        </div>
                        <div class="rounded-2xl bg-yellow-50 px-4 py-2 text-right">
                            <p class="text-xs font-bold uppercase tracking-[0.2em] text-yellow-700">Penggunaan</p>
                            <p class="text-xl font-bold text-yellow-600">{{ $item->total_penggunaan }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <div class="px-5 py-12 text-center text-gray-500">
                    Tidak ada data
                </div>
                @endforelse
            </div>

            <div class="hidden lg:block">
                <table class="min-w-full divide-y divide-gray-100">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.2em] text-gray-500">No</th>
                            <th class="px-5 py-4 text-left text-xs font-bold uppercase tracking-[0.2em] text-gray-500">Metode Bayar</th>
                            <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-[0.2em] text-gray-500">Total Penggunaan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($data as $index => $item)
                        <tr class="transition hover:bg-yellow-50/60">
                            <td class="px-5 py-4 text-sm font-semibold text-gray-500">{{ ($data->firstItem() ?? 1) + $index }}</td>
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="h-10 w-1 rounded-full bg-yellow-400"></span>
                                    <span class="text-base font-bold text-gray-900">{{ $item->nama_metode_bayar }}</span>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-right text-lg font-bold text-yellow-600">{{ $item->total_penggunaan }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-5 py-12 text-center text-gray-500">Tidak ada data</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if(method_exists($data, 'links'))
        <div class="mt-6">
            {{ $data->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.addEventListener('change', () => el.form.submit());
    });
</script>
@endsection
