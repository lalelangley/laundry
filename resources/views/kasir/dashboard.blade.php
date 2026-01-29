@extends('layouts.master')

@section('title', 'Dashboard Kasir')

@section('content')

<!-- HEADER -->
<div class="bg-[#ffcc00] p-4 rounded-b-3xl shadow-lg">
    <div class="flex items-center justify-between">

        <!-- Toggle Sidebar -->
        <div onclick="toggleSidebar()" class="text-3xl font-bold cursor-pointer">≡</div>

        <!-- Logo -->
        <img src="{{ asset('images/dashboard_logo.png') }}" class="w-40" alt="logo">

        <!-- Versi -->
        <div class="text-sm font-semibold text-black">
            Versi -
        </div>
    </div>

    <!-- Laporan Hari Ini -->
    <div class="mt-4 text-[14px] font-semibold flex justify-between">
        <div>
            <div class="flex items-center gap-2">
                <i class="bi bi-cart-fill text-lg"></i>
                Laporan Hari ini
            </div>
            <div>Total Omzet : Rp.{{ number_format($totalOmzet,0,',','.') }}</div>
        </div>
    </div>
</div>


<!-- USER BAR -->
<div class="mx-4 mt-4 flex items-center justify-end gap-3">

    <!-- Edit Profile -->
    <a href="{{ route('profile.kasir.edit') }}"
    class="bg-gray-300 w-12 h-12 flex items-center justify-center rounded-full text-2xl text-black shadow
            hover:bg-gray-400 transition">
        <i class="bi bi-person-fill"></i>
    </a>

    <!-- Logout -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit"
            class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow hover:bg-red-600 transition">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>

    <!-- Role -->
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
        Kasir
    </div>

</div>

<!-- STATISTIK CARDS -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">

    <!-- MASUK -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-blue-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-box-arrow-in-down text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $masuk ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Masuk</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-blue-500 to-blue-300 rounded-full"></div>
    </div>

    <!-- HARUS SELESAI -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-green-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-check-circle-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $harusSelesai ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Harus Selesai</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

    <!-- TERLAMBAT -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow">
                <i class="bi bi-clock-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $terlambat ?? 0 }}</div>
                <div class="text-xs text-gray-500 uppercase">Order</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Terlambat</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-red-500 to-red-300 rounded-full"></div>
    </div>

</div>

{{-- DATA TABLE KASIR --}}
<div class="mx-4 mt-8 mb-8 bg-white rounded-3xl shadow-xl border-2 border-gray-100">

    
   {{-- HEADER SECTION --}}
    <div class="px-8 py-6 border-b-2 border-gray-100 bg-gradient-to-r from-gray-50 to-white rounded-t-3xl">
        <div class="flex justify-between items-center flex-wrap gap-4">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 bg-gradient-to-br from-yellow-400 to-amber-500 rounded-2xl flex items-center justify-center shadow-lg">
                    <i class="bi bi-clock-history text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-gray-900">Riwayat Transaksi</h2>
                    <p class="text-sm text-gray-500">Menampilkan {{ $orders->count() }} dari {{ $orders->count() }} transaksi</p>
                </div>
            </div>

            <a href="{{ route('kasir.transaksi.create') }}"
               class="bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 transition-all px-6 py-3 rounded-2xl text-gray-900 font-bold shadow-lg hover:shadow-xl hover:scale-105 transform inline-flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
    </div>

    {{-- TABLE --}}
    <div class="p-6">
        <div class="overflow-x-auto rounded-2xl border">
            <table id="kasirTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-100 text-gray-700 text-xs uppercase">
                        <th class="px-4 py-3 text-left">No</th>
                        <th class="px-4 py-3 text-left">No Order</th>
                        <th class="px-4 py-3 text-left">Pelanggan</th>
                        <th class="px-4 py-3 text-left">Kasir</th>
                        <th class="px-4 py-3 text-center">Jenis</th>
                        <th class="px-4 py-3 text-center">Status Bayar</th>
                        <th class="px-4 py-3 text-center">Status Transaksi</th>
                        <th class="px-4 py-3 text-right">Total Bayar</th>
                        <th class="px-4 py-3 text-center">Tgl Transaksi</th>
                        <th class="px-4 py-3 text-center">Deadline</th>
                        <th class="px-4 py-3 text-center">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($orders as $i => $o)
                    <tr class="border-b hover:bg-yellow-50 transition">
                        <td class="px-4 py-3">{{ $i+1 }}</td>

                        <td class="px-4 py-3">
                            <span class="font-bold text-gray-900">{{ $o->id_transaksi }}</span>
                        </td>

                        <td class="px-4 py-3">
                            <div>
                                <div class="font-semibold text-gray-900">{{ $o->nama_pelanggan ?? '-' }}</div>
                                @if($o->no_hp)
                                    <div class="text-xs text-gray-500">{{ $o->no_hp }}</div>
                                @endif
                            </div>
                        </td>

                        <td class="px-4 py-3">
                            @if($o->id_kasir)
                                @php
                                    try {
                                        $kasir = DB::table('akun_kasir')->where('id_kasir', $o->id_kasir)->first();
                                        if (!$kasir) {
                                            $kasir = DB::table('kasir')->where('id_kasir', $o->id_kasir)->first();
                                        }
                                    } catch (\Exception $e) {
                                        $kasir = null;
                                    }
                                @endphp
                                <div class="text-xs">
                                    @if($kasir)
                                        <div class="font-semibold text-gray-900">{{ $kasir->nama_kasir ?? $kasir->username ?? $kasir->name ?? 'Kasir' }}</div>
                                    @else
                                        <div class="font-semibold text-gray-900">Kasir</div>
                                    @endif
                                    <div class="text-gray-500">ID: {{ $o->id_kasir }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($o->jenis_transaksi == 'online')
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <i class="bi bi-globe mr-1"></i> Online
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <i class="bi bi-shop mr-1"></i> Offline
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @if($o->status_bayar)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                    {{ $o->status_bayar == 'lunas' ? 'bg-green-100 text-green-800' : ($o->status_bayar == 'DP' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $o->status_bayar == 'belum_lunas' ? 'Belum Lunas' : ucfirst($o->status_bayar) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            @php
                                $statusConfig = [
                                    'antrian' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'label' => 'Antrian'],
                                    'proses' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'label' => 'Proses'],
                                    'selesai_dicuci' => ['bg' => 'bg-cyan-100', 'text' => 'text-cyan-800', 'label' => 'Selesai Dicuci'],
                                    'siap_di_ambil' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-800', 'label' => 'Siap Ambil'],
                                    'siap_di_antar' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'label' => 'Siap Antar'],
                                    'pick_up' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-800', 'label' => 'Pick Up'],
                                    'selesai' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'label' => 'Selesai'],
                                    'batal' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'label' => 'Batal'],
                                ];
                                
                                $status = $statusConfig[$o->status_transaksi] ?? ['bg' => 'bg-gray-100', 'text' => 'text-gray-800', 'label' => ucfirst(str_replace('_', ' ', $o->status_transaksi))];
                            @endphp
                            
                            <span class="inline-flex items-center px-3 py-1.5 text-xs font-semibold rounded-full {{ $status['bg'] }} {{ $status['text'] }} whitespace-nowrap">
                                {{ $status['label'] }}
                            </span>
                        </td>

                        <td class="px-4 py-3 text-right">
                            <div class="font-bold text-green-600 whitespace-nowrap">
                                Rp {{ number_format($o->total_bayar ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="px-4 py-3 text-center text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}
                        </td>

                        {{-- ✅ DEADLINE DENGAN STYLING --}}
                        <td class="px-4 py-3 text-center">
                            @if(isset($o->deadline_status))
                                @if($o->deadline_status === 'terlambat')
                                    <div class="flex items-center justify-center gap-1">
                                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-red-100 text-red-700 inline-flex items-center gap-1 whitespace-nowrap">
                                            <i class="bi bi-exclamation-triangle-fill"></i>
                                            {{ $o->deadline }}
                                        </span>
                                    </div>
                                @elseif($o->deadline_status === 'mendesak')
                                    <div class="flex items-center justify-center gap-1">
                                        <span class="px-3 py-1 rounded-xl text-xs font-bold bg-orange-100 text-orange-700 inline-flex items-center gap-1 whitespace-nowrap">
                                            <i class="bi bi-clock-fill"></i>
                                            {{ $o->deadline }}
                                        </span>
                                    </div>
                                @elseif($o->deadline_status === 'normal')
                                    <span class="px-3 py-1 rounded-xl text-xs font-bold bg-green-100 text-green-700 whitespace-nowrap">
                                        {{ $o->deadline }}
                                    </span>
                                @else
                                    <span class="text-gray-400 text-xs">Tidak ada deadline</span>
                                @endif
                            @else
                                <span class="text-gray-400 text-xs">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('kasir.riwayat.detail', $o->id_transaksi) }}"
                               class="bg-gradient-to-r from-yellow-400 to-amber-500 px-4 py-2 rounded-xl text-gray-900 font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg inline-flex items-center gap-2 justify-center whitespace-nowrap">
                                <i class="bi bi-eye-fill"></i>
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="11" class="py-10 text-center">
                            <div class="text-gray-400">
                                <i class="bi bi-inbox text-5xl mb-3 block"></i>
                                <p class="font-semibold">Belum ada transaksi</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    let table = $('#kasirTable').DataTable({
        pageLength: 25,
        lengthChange: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Semua"]],
        ordering: true,
        searching: true,
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
            infoEmpty: "Tidak ada data",
            infoFiltered: "(difilter dari _MAX_ total transaksi)",
            zeroRecords: "Tidak ada transaksi yang cocok",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        // ✅ Urutkan berdasarkan No Order (kolom 1) descending - transaksi terbaru duluan
        order: [[1, 'desc']],
        
        initComplete: function () {
            // Style search box
            $('div.dataTables_filter input')
                .addClass("border-2 border-gray-300 rounded-xl px-4 py-3 ml-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");

            // Style length select
            $('div.dataTables_length select')
                .addClass("border-2 border-gray-300 rounded-xl px-4 py-2.5 mr-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");

            // Style pagination
            setTimeout(() => {
                $('.dataTables_paginate a')
                    .addClass("px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-yellow-50 hover:border-yellow-400 transition text-sm font-semibold mx-1");

                $('.dataTables_paginate .current')
                    .addClass("bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-900 border-yellow-500 font-bold shadow-md");
            }, 100);
        },
        
        drawCallback: function() {
            // Re-apply styling after page change
            $('.dataTables_paginate a')
                .addClass("px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-yellow-50 hover:border-yellow-400 transition text-sm font-semibold mx-1");

            $('.dataTables_paginate .current')
                .addClass("bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-900 border-yellow-500 font-bold shadow-md");
        }
    });
    
    // Log untuk debugging
    console.log('Total transaksi dimuat:', table.data().length);
});
</script>
@endpush