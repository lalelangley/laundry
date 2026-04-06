@extends('layouts.master')

@section('content')

@php
    $routePrefix = 'admin';
    $from        = request('from') ?? 'dashboard';
    $idTransaksi = request('id_transaksi');
    $backUrl = match ($from) {
        'transaksi' => route('transaksi.create'),
        'riwayat'   => route('riwayat.detail', ['id' => $idTransaksi]),
        default     => route('admin.dashboard'),
    };
@endphp

<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="min-h-screen bg-gray-50 pb-24">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Kelola Layanan</span>
    </div>

    <div class="px-4 md:px-8 py-6 space-y-6">

       {{-- SEARCH + SORT --}}
<div class="flex items-center gap-3">
    <div class="relative flex-1">
        <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
        <input id="searchInput" type="text" placeholder="Cari layanan..."
            class="w-full pl-12 pr-4 py-4 rounded-xl bg-white shadow-md outline-none focus:ring-2 focus:ring-yellow-400 transition-all">
    </div>

    {{-- SORT BUTTON + DROPDOWN --}}
    <div class="relative" id="sortWrapper">
            <button id="sortBtn" class="bg-white px-6 py-4 rounded-xl shadow-md hover:shadow-lg flex items-center gap-2 hover:bg-gray-50 transition-all">
                <i class="bi bi-arrow-down-up text-xl"></i>
                <span class="font-semibold hidden sm:inline" id="sortLabel">Sort</span>
            </button>

            <div id="sortDropdown" class="hidden absolute right-0 top-14 w-52 bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden z-50">
                <div class="px-4 py-3 border-b border-gray-100">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Urutkan</p>
                </div>
                <ul class="py-2">
                    <li>
                        <button data-sort="default"
                            class="sort-option w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                            <i class="bi bi-dash-circle text-gray-400 text-lg"></i>
                            <span class="font-medium">Default</span>
                            <i class="bi bi-check2 ml-auto text-yellow-500 text-lg sort-check"></i>
                        </button>
                    </li>
                    <li>
                        <button data-sort="az"
                            class="sort-option w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                            <i class="bi bi-sort-alpha-down text-blue-500 text-lg"></i>
                            <span class="font-medium">Nama A → Z</span>
                            <i class="bi bi-check2 ml-auto text-yellow-500 text-lg sort-check hidden"></i>
                        </button>
                    </li>
                    <li>
                        <button data-sort="za"
                            class="sort-option w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                            <i class="bi bi-sort-alpha-up text-purple-500 text-lg"></i>
                            <span class="font-medium">Nama Z → A</span>
                            <i class="bi bi-check2 ml-auto text-yellow-500 text-lg sort-check hidden"></i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>

        {{-- LIST LAYANAN --}}
        <div id="layananList" class="space-y-5">
            @forelse ($layananUtama as $item)
            <div class="layanan-item bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all hover:-translate-y-1 relative group"
                data-id="{{ $item->id_layanan }}"
                data-name="{{ $item->nama_layanan }}">
                <div class="p-6">
                    <div class="flex items-start justify-between mb-4 pb-4 border-b-2 border-gray-100">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-gray-800 capitalize mb-2">{{ $item->nama_layanan }}</h3>
                            @php
                                $icons = ['Cuci' => 'bi bi-droplet', 'Kering' => 'bi bi-wind', 'Setrika' => 'bi bi-iron'];
                                $raw   = $item->proses ?? '';
                                if (is_string($raw) && Str::startsWith(trim($raw), '[')) {
                                    $steps = json_decode($raw, true) ?: [];
                                } else {
                                    $parts = array_map('trim', explode(',', trim($raw, "[]\"' ")));
                                    $steps = array_filter($parts, fn($s) => $s !== '');
                                }
                            @endphp
                            <div class="flex items-center gap-2 flex-wrap">
                                @foreach($steps as $i => $step)
                                    <div class="flex items-center gap-1.5 bg-yellow-50 px-3 py-1.5 rounded-lg">
                                        <i class="{{ $icons[$step] ?? 'bi bi-gear' }} text-yellow-600 text-lg"></i>
                                        <span class="text-sm font-semibold text-gray-700">{{ $step }}</span>
                                    </div>
                                    @if($i < count($steps) - 1)
                                        <i class="bi bi-chevron-right text-gray-300"></i>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        {{-- DROPDOWN --}}
                        <div class="dropdown-area relative">
                            <button class="dropdown-btn text-gray-600 hover:text-gray-800 p-2 hover:bg-gray-100 rounded-lg transition-colors">
                                <i class="bi bi-three-dots-vertical text-xl"></i>
                            </button>
                            <ul class="dropdown-menu hidden absolute right-0 top-12 w-48 bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 z-50">
                                <li>
                                    <button onclick="confirmDuplicate('{{ route('layanan.duplicate', $item->id_layanan) }}')"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-gray-700 hover:bg-yellow-50 transition-colors">
                                        <i class="bi bi-layers text-lg text-blue-600"></i>
                                        <span class="font-medium">Duplikat</span>
                                    </button>
                                </li>
                                <li class="border-t border-gray-100">
                                    <button onclick="confirmDelete('{{ route('layanan.destroy', $item->id_layanan) }}')"
                                        class="w-full flex items-center gap-3 px-4 py-3 text-red-600 hover:bg-red-50 transition-colors">
                                        <i class="bi bi-trash text-lg"></i>
                                        <span class="font-medium">Hapus</span>
                                    </button>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- JENIS --}}
                    @if($item->jenis->count() > 0)
                        <div class="space-y-4">
                            @foreach($item->jenis as $jenis)
                            <div class="jenis-item flex items-center gap-4 p-4 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors cursor-pointer"
                                data-id-layanan="{{ $item->id_layanan }}"
                                data-id-jenis="{{ $jenis->id_jenis_layanan }}"
                                data-nama="{{ $jenis->nama_jenis }}">
                                <div class="w-20 h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-xl overflow-hidden flex-shrink-0 border-2 border-gray-200 group-hover:border-yellow-300 transition-all">
                                    @if(!empty($jenis->gambar))
                                        <img src="{{ asset('storage/' . $jenis->gambar) }}"
                                            alt="{{ $jenis->nama_jenis }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-full h-full flex items-center justify-center bg-gradient-to-br from-yellow-100 to-yellow-200\'><i class=\'bi bi-image text-3xl text-yellow-400\'></i></div>';">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center">
                                            <i class="bi bi-image text-3xl text-gray-300"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-lg text-gray-800 capitalize truncate">{{ $jenis->nama_jenis }}</p>
                                    <p class="text-green-600 font-semibold">Rp {{ number_format($jenis->harga, 0, ',', '.') }} / {{ $jenis->satuan->nama_satuan ?? '-' }}</p>
                                    <div class="flex items-center gap-1.5 text-gray-500 text-sm">
                                        <i class="bi bi-clock"></i>
                                        {{ $jenis->lama }} {{ $jenis->lama_satuan }}
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="bi bi-inbox text-4xl text-gray-300 mb-2"></i>
                            <p class="text-gray-400 text-sm">Belum ada jenis layanan</p>
                        </div>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-16">
                <i class="bi bi-gear-wide-connected text-5xl text-gray-400 mb-4"></i>
                <p class="text-xl text-gray-500 font-semibold">Tidak ada layanan</p>
                <p class="text-gray-400 text-sm mt-2">Silahkan tambahkan layanan baru</p>
            </div>
            @endforelse
        </div>

        {{-- TAMBAH --}}
        <a href="{{ route('layanan.create', ['from' => $from]) }}"
            class="flex items-center justify-center gap-2 bg-yellow-400 hover:bg-yellow-500 py-4 rounded-2xl font-bold text-black text-center shadow-lg hover:shadow-xl transition-all hover:scale-105">
            <i class="bi bi-plus-circle-fill text-xl"></i>
            Tambah Layanan
        </a>
    </div>
</div>

{{-- MODAL LAYANAN --}}
<div id="modalLayanan" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white w-full max-w-lg mx-auto rounded-2xl shadow-2xl overflow-hidden">
        <div class="bg-yellow-400 p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-black text-center"></h2>
        </div>
        <div class="p-6 space-y-5">
            <div>
                <label class="block font-bold text-gray-700 mb-2">Jumlah Kuantitas</label>
                <div class="relative">
                    <i class="bi bi-123 absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xl"></i>
                    <input id="qtyInput" type="text" inputmode="decimal"
                        class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all"
                        placeholder="Minimal 0.01">
                </div>
                <p class="text-xs text-gray-500 mt-1.5 ml-1">Minimal kuantitas: 0.01 (contoh: 1, 2.5, 10.75)</p>
            </div>
            <div>
                <label class="block font-bold text-gray-700 mb-2">Pilih Parfum</label>
                <div class="relative">
                    <i class="bi bi-flower1 absolute left-4 top-1/2 -translate-y-1/2 text-pink-400 text-xl z-10"></i>
                    <select id="parfumSelect"
                        class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all appearance-none bg-white">
                        <option value="">Pilih Parfum</option>
                        @foreach ($parfum as $p)
                            <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
            <div class="flex gap-3 pt-2">
                <button onclick="closeModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                    Batal
                </button>
                <button id="btnSave"
                    class="flex-1 bg-yellow-400 hover:bg-yellow-500 text-black py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all"
                    data-mode="{{ request('from') }}" data-id="" data-transaksi="{{ request('id_transaksi') }}">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ALERT MODAL --}}
<div id="alertModal" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[9999] hidden">
    <div id="alertCard" class="alert-modal bg-white w-full max-w-sm rounded-2xl shadow-2xl overflow-hidden">
        <div id="alertHeader" class="p-6 flex items-center justify-center">
            <div id="alertIcon" class="w-16 h-16 rounded-full flex items-center justify-center"></div>
        </div>
        <div class="px-6 pb-6 text-center">
            <h3 id="alertTitle" class="text-xl font-bold text-gray-800 mb-2"></h3>
            <p id="alertMessage" class="text-gray-600 mb-6"></p>
            <button id="alertButton" class="w-full py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95">
                OK, Mengerti
            </button>
        </div>
    </div>
</div>

{{-- MODAL DUPLIKAT --}}
<div id="modalDuplicate" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-blue-500 p-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="bi bi-layers text-3xl"></i>
                Konfirmasi Duplikat
            </h2>
        </div>
        <div class="p-6">
            <p class="text-gray-700 text-lg mb-6">Apakah Anda yakin ingin menduplikat layanan ini?</p>
            <div class="flex gap-3">
                <button onclick="closeDuplicateModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                    Batal
                </button>
                <button id="btnConfirmDuplicate"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                    Ya, Duplikat
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL HAPUS --}}
<div id="modalDelete" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
        <div class="bg-red-500 p-6">
            <h2 class="text-2xl font-bold text-white flex items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-3xl"></i>
                Konfirmasi Hapus
            </h2>
        </div>
        <div class="p-6">
            <p class="text-gray-700 text-lg mb-2">Apakah Anda yakin ingin menghapus layanan ini?</p>
            <p class="text-red-600 font-semibold mb-6">Tindakan ini tidak dapat dibatalkan!</p>
            <form id="formDelete" method="POST">
                @csrf
                @method('DELETE')
                <div class="flex gap-3">
                    <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                        Ya, Hapus
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    window.LAYANAN_DATA = {
        addJenisTransaksiUrl: "{{ route('transaksi.addJenis', ':id') }}",
        from:                 "{{ request('from') ?? 'dashboard' }}",
        idTransaksi:          "{{ request('id_transaksi') }}",
        transaksiCreateUrl:   "{{ route('transaksi.create') }}",
    };
</script>
<script src="{{ asset('js/superadmin/layanan.js') }}"></script>
@endsection