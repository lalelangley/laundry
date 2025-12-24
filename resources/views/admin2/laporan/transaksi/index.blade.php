@extends('layouts.master')
@section('title', 'Laporan Transaksi')
@section('content')

<div class="min-h-screen bg-gray-100">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl shadow flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('laporan.index') }}" class="text-3xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold">Laporan Transaksi</h1>
        </div>

        <div class="flex gap-5 text-xl">
            <i class="bi bi-funnel"></i>
            <i class="bi bi-sort-down"></i>
        </div>
    </div>

 <form method="GET" action="{{ route('laporan.transaksi.index') }}" class="px-8 mt-6 space-y-4">

    {{-- FILTER TANGGAL --}}
    <div class="flex items-center gap-4">
        <div class="flex-1 bg-yellow-400 rounded-full px-6 py-4 flex items-center gap-3 font-semibold">
            <i class="bi bi-calendar-event"></i>
            <input type="date" name="dari" value="{{ request('dari', $tglAwal) }}"
                   class="bg-transparent outline-none w-full">
        </div>

        <span class="font-bold">&gt;</span>

        <div class="flex-1 bg-yellow-400 rounded-full px-6 py-4 flex items-center gap-3 font-semibold">
            <i class="bi bi-calendar-event"></i>
            <input type="date" name="sampai" value="{{ request('sampai', $tglAkhir) }}"
                   class="bg-transparent outline-none w-full">
        </div>
    </div>

    {{-- SEARCH --}}
    <div class="bg-white rounded-full shadow flex items-center px-6 py-4 gap-4">
        <i class="bi bi-search text-xl text-gray-400"></i>

        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari nama pelanggan / no nota / no HP..."
            class="flex-1 outline-none bg-transparent font-semibold text-gray-700"
        >

        <button type="submit"
                class="bg-yellow-400 px-6 py-2 rounded-full font-bold">
            Cari
        </button>
    </div>

</form>

</form>
    {{-- SUMMARY --}}
    <div class="px-8 mt-6">
        <div class="bg-white border-2 border-yellow-400 rounded-2xl p-6 font-semibold">
            <div class="flex justify-between text-lg">
                <span>Total Omzet</span>
                <span>Rp {{ number_format($totalOmzet,0,',','.') }}</span>
            </div>
            <div class="flex justify-between text-lg mt-2">
                <span>Jumlah Transaksi</span>
                <span>{{ $jumlah }}</span>
            </div>
        </div>
    </div>

    {{-- LIST TRANSAKSI --}}
    <div class="px-8 mt-6 space-y-4 pb-10">

        @forelse ($transaksi as $t)
        <div class="bg-white rounded-2xl shadow p-6 flex gap-6">

            {{-- AVATAR --}}
        <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 
            flex items-center justify-center">

    @if($t->pelanggan && $t->pelanggan->gambar)
        <img 
            class="w-full h-full object-cover"
            src="{{ asset('storage/'.$t->pelanggan->gambar) }}"
        >
    @else
        <i class="bi bi-person text-5xl text-gray-500"></i>
    @endif

</div>

            {{-- INFO --}}
            <div class="flex-1">
                <div class="flex justify-between">
                    <h3 class="text-xl font-bold">{{ $t->nama_pelanggan }}</h3>
                    <span class="text-xl font-bold">
                        Rp {{ number_format($t->total_bayar,0,',','.') }}
                    </span>
                </div>

                <div class="mt-3 space-y-1 text-gray-700">
                    <div class="flex gap-2">
                        <i class="bi bi-hash text-red-500"></i>
                        <span>No Nota</span>
                        <span class="ml-auto font-semibold">
                            TRX/{{ $t->id_transaksi }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <i class="bi bi-calendar-check text-blue-500"></i>
                        <span>Tanggal Masuk</span>
                        <span class="ml-auto">
                            {{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <i class="bi bi-check-circle-fill text-green-500"></i>
                        <span>Estimasi Selesai</span>
                        <span class="ml-auto">
                            {{ \Carbon\Carbon::parse($t->tgl_estimasi)->format('d/m/Y H:i') }}
                        </span>
                    </div>

                    <div class="flex gap-2">
                        <i class="bi bi-percent text-yellow-500"></i>
                        <span>Diskon</span>
                        <span class="ml-auto">
                            Rp {{ number_format($t->diskon,0,',','.') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        @empty
            <div class="text-center text-gray-500 py-10">
                Tidak ada transaksi
            </div>
        @endforelse

    </div>
</div>

@endsection
@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function () {
            this.form.submit(); // 🔥 AUTO SUBMIT
        });
    });

});
</script>
@endpush

