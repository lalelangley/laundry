@extends('layouts.master')

@section('title', 'Laporan Transaksi')

@section('content')
<div class="min-h-screen bg-gray-100">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-6 rounded-b-3xl shadow flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin2.laporan.index') }}" class="text-3xl font-bold hover:scale-110 transition-transform">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-2xl font-bold">Laporan Transaksi</h1>
        </div>

        <div class="flex gap-5 text-xl">
            <i class="bi bi-funnel"></i>
            <i class="bi bi-sort-down"></i>
        </div>
    </div>

    {{-- FORM FILTER --}}
    <form method="GET" action="{{ route('admin2.laporan.transaksi.index') }}" class="px-8 mt-6 space-y-4">

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
                    class="bg-yellow-400 hover:bg-yellow-500 px-6 py-2 rounded-full font-bold transition-all">
                Cari
            </button>
        </div>

    </form>

    {{-- SUMMARY --}}
    <div class="px-8 mt-6">
        <div class="bg-white border-2 border-yellow-400 rounded-2xl p-6 font-semibold">
            <div class="flex justify-between text-lg">
                <span>Total Omzet</span>
                <span class="text-orange-600 font-bold">Rp {{ number_format($totalOmzet,0,',','.') }}</span>
            </div>
            <div class="flex justify-between text-lg mt-2">
                <span>Jumlah Transaksi</span>
                <span class="text-yellow-600 font-bold">{{ $jumlah }}</span>
            </div>
        </div>
    </div>

    {{-- LIST TRANSAKSI --}}
    <div class="px-8 mt-6 space-y-4 pb-10">

        @forelse ($transaksi as $t)
        <div class="bg-white rounded-2xl shadow-sm hover:shadow-md transition-all p-6 flex gap-6 border border-gray-200">

            {{-- AVATAR --}}
            <div class="w-16 h-16 rounded-full overflow-hidden bg-gradient-to-br from-yellow-400 to-orange-500 flex-shrink-0 flex items-center justify-center shadow-md">
                @if($t->pelanggan && !empty($t->pelanggan->gambar))
                    @php
                        $gambar = $t->pelanggan->gambar;
                        $paths = [
                            $gambar,
                            'pelanggan/' . $gambar,
                            'gambar_pelanggan/' . $gambar,
                            'images/pelanggan/' . $gambar,
                            ltrim($gambar, '/'),
                            str_replace('public/', '', $gambar),
                        ];
                        
                        $imageSrc = '';
                        foreach ($paths as $testPath) {
                            $fullPath = public_path('storage/' . $testPath);
                            if (file_exists($fullPath) && is_file($fullPath)) {
                                $imageSrc = asset('storage/' . $testPath);
                                break;
                            }
                        }
                    @endphp

                    @if($imageSrc)
                        <img 
                            class="w-full h-full object-cover"
                            src="{{ $imageSrc }}"
                            alt="{{ $t->nama_pelanggan }}"
                            loading="lazy"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        >
                    @endif
                    <i class="bi bi-person text-3xl text-white {{ $imageSrc ? 'hidden' : '' }}"></i>
                @else
                    <i class="bi bi-person text-3xl text-white"></i>
                @endif
            </div>

            {{-- INFO --}}
            <div class="flex-1">
                <div class="flex justify-between items-start">
                    <div>
                        <h3 class="text-xl font-bold text-gray-800">{{ $t->nama_pelanggan }}</h3>
                        
                        <div class="flex gap-2 mt-2">
                            @if($t->jenis_transaksi === 'online')
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-yellow-100 text-yellow-700 text-xs font-semibold rounded-full">
                                    <i class="bi bi-globe"></i>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-3 py-1 bg-orange-100 text-orange-700 text-xs font-semibold rounded-full">
                                    <i class="bi bi-shop"></i>
                                    Offline
                                </span>
                            @endif

                            {{-- Status Transaksi --}}
                            @php
                                $statusColors = [
                                    'antrian' => 'bg-gray-100 text-gray-700',
                                    'proses' => 'bg-yellow-100 text-yellow-700',
                                    'siap_di_ambil' => 'bg-orange-100 text-orange-700',
                                    'siap_di_antar' => 'bg-orange-100 text-orange-700',
                                    'selesai' => 'bg-green-100 text-green-700',
                                    'batal' => 'bg-red-100 text-red-700',
                                ];
                                $statusColor = $statusColors[$t->status_transaksi] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            <span class="inline-flex items-center gap-1 px-3 py-1 {{ $statusColor }} text-xs font-semibold rounded-full capitalize">
                                {{ str_replace('_', ' ', $t->status_transaksi) }}
                            </span>

                            {{-- Status Pembayaran --}}
                            @php
                                $bayarColors = [
                                    'lunas' => 'bg-orange-100 text-orange-700',
                                    'DP' => 'bg-yellow-100 text-yellow-700',
                                    'belum_lunas' => 'bg-red-100 text-red-700',
                                ];
                                $bayarColor = $bayarColors[$t->status_bayar] ?? 'bg-gray-100 text-gray-700';
                            @endphp
                            @if($t->status_bayar)
                                <span class="inline-flex items-center gap-1 px-3 py-1 {{ $bayarColor }} text-xs font-semibold rounded-full capitalize">
                                    {{ $t->status_bayar === 'belum_lunas' ? 'Belum Lunas' : $t->status_bayar }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="text-2xl font-bold text-orange-600">
                            Rp {{ number_format($t->total_bayar ?? 0, 0, ',', '.') }}
                        </span>
                        @if($t->total_harga && $t->total_harga != $t->total_bayar)
                            <div class="text-sm text-gray-500 line-through mt-1">
                                Rp {{ number_format($t->total_harga, 0, ',', '.') }}
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-hash text-yellow-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">No Nota</div>
                            <div class="font-semibold text-gray-800">TRX/{{ $t->id_transaksi }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-calendar-check text-yellow-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Tanggal Masuk</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($t->tgl_transaksi)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    @if($t->tgl_estimasi)
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-clock-history text-orange-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Estimasi Selesai</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($t->tgl_estimasi)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->tgl_lunas)
                    <div class="flex items-center gap-2 p-3 bg-gray-50 rounded-lg">
                        <i class="bi bi-check-circle-fill text-orange-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Tanggal Lunas</div>
                            <div class="font-semibold text-gray-800">
                                {{ \Carbon\Carbon::parse($t->tgl_lunas)->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->diskon && $t->diskon > 0)
                    <div class="flex items-center gap-2 p-3 bg-red-50 rounded-lg">
                        <i class="bi bi-percent text-red-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Diskon</div>
                            <div class="font-semibold text-red-600">
                                @if($t->tipe_diskon === 'percent')
                                    {{ $t->diskon }}% (Rp {{ number_format($t->total_harga * $t->diskon / 100, 0, ',', '.') }})
                                @else
                                    Rp {{ number_format($t->diskon, 0, ',', '.') }}
                                @endif
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->dp && $t->dp > 0)
                    <div class="flex items-center gap-2 p-3 bg-orange-50 rounded-lg">
                        <i class="bi bi-wallet2 text-orange-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">DP Terbayar</div>
                            <div class="font-semibold text-orange-600">
                                Rp {{ number_format($t->dp, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($t->keterangan)
                    <div class="flex items-center gap-2 p-3 bg-yellow-50 rounded-lg col-span-2">
                        <i class="bi bi-chat-left-text text-yellow-500"></i>
                        <div class="flex-1">
                            <div class="text-gray-500 text-xs">Keterangan</div>
                            <div class="font-semibold text-gray-800">{{ $t->keterangan }}</div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @empty
            <div class="text-center text-gray-500 py-20 bg-white rounded-2xl">
                <i class="bi bi-inbox text-6xl mb-3 block text-gray-300"></i>
                <p class="font-semibold text-lg">Tidak ada transaksi</p>
                <p class="text-sm mt-1">Coba ubah filter atau rentang tanggal</p>
            </div>
        @endforelse

    </div>

    {{-- PAGINATION --}}
    @if($transaksi->hasPages())
        <div class="px-8 pb-10">
            <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-600">
                        Menampilkan {{ $transaksi->firstItem() ?? 0 }} - {{ $transaksi->lastItem() ?? 0 }} dari {{ $transaksi->total() }} transaksi
                    </div>
                    
                    <div class="flex gap-2">
                        {{-- Previous Button --}}
                        @if ($transaksi->onFirstPage())
                            <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                <i class="bi bi-chevron-left"></i> Prev
                            </span>
                        @else
                            <a href="{{ $transaksi->previousPageUrl() }}" 
                               class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded-lg font-semibold transition-all">
                                <i class="bi bi-chevron-left"></i> Prev
                            </a>
                        @endif

                        {{-- Page Numbers --}}
                        @foreach ($transaksi->getUrlRange(1, $transaksi->lastPage()) as $page => $url)
                            @if ($page == $transaksi->currentPage())
                                <span class="px-4 py-2 bg-orange-500 text-white rounded-lg font-semibold">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" 
                                   class="px-4 py-2 bg-gray-100 hover:bg-yellow-400 text-gray-700 hover:text-gray-900 rounded-lg font-semibold transition-all">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach

                        {{-- Next Button --}}
                        @if ($transaksi->hasMorePages())
                            <a href="{{ $transaksi->nextPageUrl() }}" 
                               class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 rounded-lg font-semibold transition-all">
                                Next <i class="bi bi-chevron-right"></i>
                            </a>
                        @else
                            <span class="px-4 py-2 bg-gray-100 text-gray-400 rounded-lg font-semibold cursor-not-allowed">
                                Next <i class="bi bi-chevron-right"></i>
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto submit saat tanggal berubah
    document.querySelectorAll('input[type="date"]').forEach(input => {
        input.addEventListener('change', function () {
            this.form.submit();
        });
    });
});
</script>
@endpush