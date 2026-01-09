@extends('layouts.master')
@section('title', 'pengeluaran')
@section('content')

<!-- HEADER -->
<div class="w-full bg-yellow-400 p-4 flex items-center justify-between rounded-b-3xl shadow-md">
    <div class="flex items-center gap-3">
        <a href="{{ route('kasir.dashboard') }}" class="text-2xl font-bold hover:opacity-70 transition">
            ←
        </a>
        <h1 class="text-xl font-bold tracking-wide">List Pengeluaran</h1>
    </div>
    <div class="text-sm font-semibold text-black/70 mr-2 select-none">
        Sort
    </div>
</div>

{{-- WRAPPER --}}
<div class="p-5 pb-[150px] bg-gray-100 min-h-screen">

    {{-- SEARCH --}}
    <div class="bg-white rounded-3xl p-4 shadow-sm flex items-center gap-3 mb-6">
        <i class="bi bi-search text-yellow-500 text-xl"></i>
        <input type="text"
               class="w-full bg-transparent border-none focus:ring-0 text-gray-700 placeholder-gray-400"
               placeholder="Cari pengeluaran...">
    </div>

    {{-- LIST / EMPTY STATE --}}
    @if ($pengeluaran->isEmpty())

        <div class="flex flex-col items-center justify-center mt-20 opacity-80">
            <i class="bi bi-search text-[90px] text-yellow-400 drop-shadow"></i>
            <p class="text-lg font-semibold text-gray-600 mt-3">Data Tidak Ditemukan</p>
        </div>

    @else

        <div class="space-y-5">

            @foreach ($pengeluaran as $item)
                <div class="bg-white p-6 rounded-3xl shadow-md relative overflow-hidden border border-gray-100 hover:shadow-lg transition">

                    {{-- Garis Kuning Kiri --}}
                    <div class="absolute left-0 top-0 h-full w-2 bg-yellow-400 rounded-l-3xl"></div>

                    {{-- DROPDOWN MENU --}}
                    <div class="absolute right-4 top-4 z-50 dropdown-area">
                        <button class="dropdown-btn text-gray-700 text-2xl hover:text-yellow-600 transition">
                            <i class="bi bi-three-dots-vertical"></i>
                        </button>

                        <ul class="dropdown-menu hidden absolute right-0 top-10 w-40 bg-yellow-400 rounded-2xl shadow-xl overflow-hidden py-1 z-50">
                            <li>
                                <a href="{{ route('kasir.pengeluaran.edit', $item->id_pengeluaran) }}"
                                   class="flex items-center gap-2 px-4 py-3 text-black text-sm font-medium hover:bg-yellow-300">
                                    <i class="bi bi-pencil text-lg"></i> Edit
                                </a>
                            </li>

                            <li>
                                <form action="{{ route('kasir.pengeluaran.destroy', $item->id_pengeluaran) }}"
                                      method="POST"
                                      class="inline">
                                    @csrf
                                    @method('DELETE')

                                    <button type="button"
                                            onclick="confirmDelete(this, 'pengeluaran')"
                                            data-nama="{{ $item->nama_pengeluaran }}"
                                            data-harga="Rp {{ number_format($item->nominal, 0, ',', '.') }}"
                                            data-tanggal="{{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('d/m/Y') : '-' }}"
                                            class="w-full flex items-center gap-2 px-4 py-3 text-red-600 text-sm font-medium hover:bg-yellow-300 text-left">
                                        <i class="bi bi-trash text-lg"></i> Hapus
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>

                    <div class="flex justify-between items-start">

                        {{-- TEXT --}}
                        <div class="ml-4 w-full">

                            {{-- JUDUL --}}
                            <p class="font-extrabold text-lg uppercase tracking-wide text-gray-800 leading-tight">
                                {{ $item->nama_pengeluaran }}
                            </p>

                            {{-- TGL --}}
                            <p class="text-sm text-gray-500 mt-1">
                                {{ $item->tanggal_pengeluaran ? \Carbon\Carbon::parse($item->tanggal_pengeluaran)->translatedFormat('l, d/m/Y') : '-' }}
                            </p>

                            {{-- CATATAN --}}
                            <div class="bg-gray-100 p-3 rounded-xl mt-3 border border-gray-200">
                                <p class="text-sm text-gray-600 leading-relaxed">
                                    {{ $item->catatan ?? '-' }}
                                </p>
                            </div>

                            {{-- NOMINAL --}}
                            <p class="font-bold mt-4 text-lg text-gray-800">
                                Rp{{ number_format($item->nominal, 0, ',', '.') }}
                            </p>

                        </div>

                    </div>

                </div>
            @endforeach
        </div>

    @endif

</div>

{{-- BUTTON TAMBAH --}}
<div class="fixed bottom-0 left-0 w-full bg-gray-100 px-6 py-5">
    <a href="{{ route('kasir.pengeluaran.create') }}"
       class="w-full block text-center bg-yellow-400 text-black py-4 rounded-3xl text-lg font-bold shadow hover:bg-yellow-500 transition">
        Tambah Pengeluaran
    </a>
</div>

<script>
// Dropdown Toggle
document.querySelectorAll('.dropdown-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.stopPropagation();
        
        // Close all other dropdowns
        document.querySelectorAll('.dropdown-menu').forEach(menu => {
            if (menu !== this.nextElementSibling) {
                menu.classList.add('hidden');
            }
        });
        
        // Toggle current dropdown
        this.nextElementSibling.classList.toggle('hidden');
    });
});

// Close dropdown when clicking outside
document.addEventListener('click', function() {
    document.querySelectorAll('.dropdown-menu').forEach(menu => menu.classList.add('hidden'));
});
</script>

@endsection