@extends('layouts.master')

@section('title', 'Laporan Pelanggan')

@section('content')
<div class="min-h-screen bg-gray-100 pb-24">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-5 py-4 rounded-b-3xl flex items-center justify-between sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <a href="{{ route('laporan.index') }}" class="text-2xl font-bold">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h1 class="text-lg font-bold">Laporan Pelanggan</h1>
        </div>

        <div class="flex items-center gap-4 text-sm font-semibold">
            <span>Export</span>
            <span>Sort</span>
        </div>
    </div>

    {{-- FILTER --}}
    <div class="px-5 mt-5 flex items-center gap-3">
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3">
            <p class="text-xs">Tanggal Awal</p>
            <p class="font-bold">
                {{ now()->subMonth()->format('d/m/Y') }}
            </p>
        </div>
        <span class="font-bold">&gt;</span>
        <div class="flex-1 bg-yellow-400 rounded-full px-4 py-3">
            <p class="text-xs">Tanggal Akhir</p>
            <p class="font-bold">
                {{ now()->format('d/m/Y') }}
            </p>
        </div>
    </div>

    {{-- TOP PELANGGAN --}}
    @php $top = $data->first(); @endphp
    @if($top)
    <div class="mx-5 mt-5 bg-gray-100 border-2 border-yellow-400 rounded-xl p-4 flex justify-between">
        <div>
            <p class="font-semibold">Top Pelanggan :</p>
            <p class="font-bold">{{ $top->nama_pelanggan }}</p>
            <p class="font-bold">
                Rp {{ number_format($top->total_belanja,0,',','.') }}
            </p>
        </div>
        <div class="text-right">
            <p class="font-semibold">Jumlah Transaksi</p>
            <p class="text-2xl font-bold">{{ $top->total_transaksi }}</p>
        </div>
    </div>
    @endif

    {{-- LIST PELANGGAN --}}
    <div class="mt-6">
        @foreach($data as $item)
            <div class="bg-white flex items-center justify-between px-5 py-4 border-b">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-lg overflow-hidden bg-yellow-400 flex items-center justify-center">
                        @if($item->gambar ?? false)
                            <img src="{{ asset('storage/'.$item->gambar) }}" class="w-full h-full object-cover">
                        @else
                            <i class="bi bi-person text-white text-xl"></i>
                        @endif
                    </div>
                    <div>
                        <p class="font-bold">{{ $item->nama_pelanggan }}</p>
                        <p class="font-semibold">
                            Rp {{ number_format($item->total_belanja,0,',','.') }}
                        </p>
                    </div>
                </div>

                <div class="text-xl font-bold">
                    {{ $item->total_transaksi }}
                </div>
            </div>
        @endforeach
    </div>

</div>
@endsection
