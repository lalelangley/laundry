@extends('layouts.master')

@section('title', 'Laporan Pengeluaran')

@section('content')
<div class="min-h-screen bg-gray-100 pb-28">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-5 rounded-b-3xl shadow flex items-center justify-between sticky top-0 z-10">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin2.laporan.index') }}" class="text-3xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-xl font-bold">Laporan Pengeluaran</h1>
        </div>

        <a href="#" class="flex items-center gap-1 font-semibold">
            <i class="bi bi-file-earmark-arrow-down text-xl"></i>
            Export
        </a>
    </div>

    {{-- FILTER --}}
    <form method="GET" class="px-6 mt-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3 flex items-center gap-2 font-semibold">
                <i class="bi bi-calendar-event"></i>
                <input type="date" name="dari"
                       value="{{ request('dari') }}"
                       class="bg-transparent outline-none w-full">
            </div>

            <span class="font-bold">&gt;</span>

            <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3 flex items-center gap-2 font-semibold">
                <i class="bi bi-calendar-event"></i>
                <input type="date" name="sampai"
                       value="{{ request('sampai') }}"
                       class="bg-transparent outline-none w-full">
            </div>
        </div>
    </form>

    {{-- TABLE --}}
    <div class="px-6 mt-6">
        <div class="bg-yellow-400 rounded-xl overflow-hidden shadow">
            <div class="grid grid-cols-3 text-center font-bold text-white py-3 border-b border-yellow-500">
                <div><i class="bi bi-calendar-event"></i></div>
                <div>Nama Pengeluaran</div>
                <div>Nominal</div>
            </div>

            @forelse ($pengeluaran as $p)
                <div class="grid grid-cols-3 bg-white text-center py-4 border-b">
                    <div class="text-gray-700">
                        {{ \Carbon\Carbon::parse($p->tanggal_pengeluaran)->format('d/m/Y') }}
                    </div>
                    <div class="font-semibold text-gray-800">
                        {{ $p->nama_pengeluaran }}
                    </div>
                    <div class="font-bold">
                        Rp {{ number_format($p->nominal,0,',','.') }}
                    </div>
                </div>
            @empty
                <div class="bg-white text-center py-10 text-gray-400">
                    Tidak ada data pengeluaran
                </div>
            @endforelse
        </div>
    </div>

    {{-- TOTAL --}}
    <div class="fixed bottom-0 left-0 right-0 bg-yellow-400 px-6 py-5 flex justify-between items-center font-bold text-lg shadow-2xl">
        <span>Total Pengeluaran :</span>
        <span>
            Rp {{ number_format($pengeluaran->sum('nominal'),0,',','.') }}
        </span>
    </div>

</div>
@endsection

@section('scripts')
<script>
    // auto submit pas ganti tanggal (biar interaktif)
    document.querySelectorAll('input[type="date"]').forEach(el => {
        el.addEventListener('change', () => {
            el.form.submit();
        });
    });
</script>
@endsection
