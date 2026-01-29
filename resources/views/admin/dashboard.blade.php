@extends('layouts.master')

@section('title', 'Dashboard Admin')

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
    <a href="{{ route('profile.admin.edit') }}"
    class="bg-gray-300 w-12 h-12 flex items-center justify-center rounded-full text-2xl text-black shadow
            hover:bg-gray-400 transition">
        <i class="bi bi-person-fill"></i>
    </a>

    <!-- Logout (bulat) -->
    <form action="{{ route('logout') }}" method="POST">
        @csrf
        <button type="submit" 
            class="bg-red-500 w-12 h-12 flex items-center justify-center rounded-full text-white text-2xl shadow hover:bg-red-600 transition">
            <i class="bi bi-box-arrow-right"></i>
        </button>
    </form>

    <!-- Role -->
    <div class="bg-[#ffcc00] px-5 py-3 rounded-xl text-black text-sm font-bold shadow">
        Super Admin
    </div>

</div>

<!-- Statistik Cards - IMPROVED -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mx-4 mt-6">
    <!-- Transaksi Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-red-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-red-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-basket-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalTransaksi }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Transaksi</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-red-500 to-red-300 rounded-full"></div>
    </div>

    <!-- Kasir Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-green-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-green-500 to-green-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-people-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalKasir }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Aktif</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Kasir</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-green-500 to-green-300 rounded-full"></div>
    </div>

    <!-- Pelanggan Card -->
    <div class="group bg-white rounded-2xl p-6 shadow-lg hover:shadow-xl transition-all duration-300 border-2 border-gray-100 hover:border-blue-300">
        <div class="flex items-center justify-between mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-blue-600 rounded-2xl flex items-center justify-center shadow-md group-hover:scale-110 transition-transform">
                <i class="bi bi-person-vcard-fill text-white text-2xl"></i>
            </div>
            <div class="text-right">
                <div class="text-3xl font-bold text-gray-900">{{ $totalPelanggan }}</div>
                <div class="text-xs text-gray-500 font-medium uppercase tracking-wide">Total</div>
            </div>
        </div>
        <div class="text-base font-semibold text-gray-700">Pelanggan</div>
        <div class="mt-2 h-1 bg-gradient-to-r from-blue-500 to-blue-300 rounded-full"></div>
    </div>
</div>

{{-- DATA TABLES - IMPROVED & FIXED --}}
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

            <a href="{{ route('transaksi.create') }}"
               class="bg-gradient-to-r from-yellow-400 to-amber-500 hover:from-yellow-500 hover:to-amber-600 transition-all px-6 py-3 rounded-2xl text-gray-900 font-bold shadow-lg hover:shadow-xl hover:scale-105 transform inline-flex items-center gap-2">
                <i class="bi bi-plus-circle-fill text-lg"></i>
                <span>Transaksi Baru</span>
            </a>
        </div>
    </div>

    {{-- TABLE - COMPLETE DATA --}}
    <div class="p-8">
        <div class="overflow-x-auto rounded-2xl border-2 border-gray-200">
            <table id="orderTable" class="w-full text-sm">
                <thead>
                    <tr class="bg-gradient-to-r from-gray-100 to-gray-50 text-gray-700 text-xs uppercase tracking-wider border-b-2 border-gray-200">
                        <th class="py-4 px-4 text-left font-bold">No</th>
                        <th class="py-4 px-4 text-left font-bold">ID Transaksi</th>
                        <th class="py-4 px-4 text-left font-bold">Pelanggan</th>
                        <th class="py-4 px-4 text-left font-bold">Kasir</th>
                        <th class="py-4 px-4 text-left font-bold">Jenis</th>
                        <th class="py-4 px-4 text-left font-bold">Metode Bayar</th>
                        <th class="py-4 px-4 text-left font-bold">Status Bayar</th>
                        <th class="py-4 px-4 text-left font-bold">Status Transaksi</th>
                        <th class="py-4 px-4 text-right font-bold">Total Harga</th>
                        <th class="py-4 px-4 text-right font-bold">Diskon</th>
                        <th class="py-4 px-4 text-right font-bold">DP</th>
                        <th class="py-4 px-4 text-right font-bold">Total Bayar</th>
                        <th class="py-4 px-4 text-left font-bold">Tgl Transaksi</th>
                        <th class="py-4 px-4 text-left font-bold">Tgl Estimasi</th>
                        <th class="py-4 px-4 text-left font-bold">Tgl Lunas</th>
                        <th class="py-4 px-4 text-left font-bold">Keterangan</th>
                        <th class="py-4 px-4 text-center font-bold">Bukti</th>
                        <th class="py-4 px-4 text-center font-bold">Action</th>
                    </tr>
                </thead>

                <tbody class="text-gray-700">
                    @forelse ($orders as $i => $o)
                    <tr class="border-b border-gray-100 hover:bg-yellow-50/80 transition-colors">
                        <td class="py-4 px-4 text-gray-600 font-medium">{{ $i+1 }}</td>

                        <td class="py-4 px-4">
                            <span class="font-bold text-gray-900">{{ $o->id_transaksi }}</span>
                            
                            {{-- Indikator untuk transaksi online yang butuh pickup/antar --}}
                            @if($o->jenis_transaksi == 'online')
                                @php
                                    $deliveries = DB::table('delivery')->where('id_transaksi', $o->id_transaksi)->get();
                                    $hasPickupPending = $deliveries->where('jenis', 'pickup')->where('id_driver', null)->first();
                                    $hasAntarPending = $deliveries->where('jenis', 'antar')->where('id_driver', null)->first();
                                @endphp
                                
                                @if($hasPickupPending || $hasAntarPending)
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @if($hasPickupPending)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-orange-100 text-orange-800">
                                                <i class="bi bi-exclamation-circle-fill mr-1"></i> Pickup
                                            </span>
                                        @endif
                                        @if($hasAntarPending)
                                            <span class="inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">
                                                <i class="bi bi-exclamation-circle-fill mr-1"></i> Antar
                                            </span>
                                        @endif
                                    </div>
                                @endif
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            <div class="min-w-[120px]">
                                <div class="font-semibold text-gray-900">{{ $o->nama_pelanggan ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $o->no_hp ?? '-' }}</div>
                                @if($o->id_pelanggan)
                                    <div class="text-xs text-gray-400">ID: {{ $o->id_pelanggan }}</div>
                                @endif
                            </div>
                        </td>

                        <td class="py-4 px-4">
                            @if($o->id_kasir)
                                @php
                                    try {
                                        // Try to find kasir from akun_kasir or kasir table
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

                        <td class="py-4 px-4">
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

                        <td class="py-4 px-4">
                            @if($o->id_metode_bayar)
                                @php
                                    $metodeBayar = DB::table('metode_bayar')->where('id_metode_bayar', $o->id_metode_bayar)->first();
                                @endphp
                                <div class="text-xs min-w-[100px]">
                                    <div class="font-semibold text-gray-900">{{ $metodeBayar->nama_metode ?? '-' }}</div>
                                    <div class="text-gray-500">ID: {{ $o->id_metode_bayar }}</div>
                                </div>
                            @else
                                <span class="text-xs text-gray-400">Belum diisi</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            @if($o->status_bayar)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold whitespace-nowrap
                                    {{ $o->status_bayar == 'lunas' ? 'bg-green-100 text-green-800' : ($o->status_bayar == 'DP' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                    {{ $o->status_bayar == 'belum_lunas' ? 'Belum Lunas' : ucfirst($o->status_bayar) }}
                                </span>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
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

                        <td class="py-4 px-4 text-right">
                            <div class="font-bold text-gray-900 whitespace-nowrap">
                                Rp {{ number_format($o->total_harga ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($o->diskon > 0)
                                <div class="font-semibold text-red-600 whitespace-nowrap">
                                    Rp {{ number_format($o->diskon, 0, ',', '.') }}
                                </div>
                                @if($o->tipe_diskon)
                                    <div class="text-xs text-gray-500">
                                        {{ ucfirst($o->tipe_diskon) }}
                                    </div>
                                @endif
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            @if($o->dp > 0)
                                <div class="font-semibold text-blue-600 whitespace-nowrap">
                                    Rp {{ number_format($o->dp, 0, ',', '.') }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-right">
                            <div class="font-bold text-green-600 whitespace-nowrap">
                                Rp {{ number_format($o->total_bayar ?? 0, 0, ',', '.') }}
                            </div>
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_transaksi ? \Carbon\Carbon::parse($o->tgl_transaksi)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_estimasi ? \Carbon\Carbon::parse($o->tgl_estimasi)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-4 text-gray-600 whitespace-nowrap">
                            {{ $o->tgl_lunas ? \Carbon\Carbon::parse($o->tgl_lunas)->format('d/m/Y') : '-' }}
                        </td>

                        <td class="py-4 px-4">
                            @if($o->keterangan)
                                <div class="max-w-[150px] text-xs text-gray-700 truncate" title="{{ $o->keterangan }}">
                                    {{ $o->keterangan }}
                                </div>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4 text-center">
                            @if($o->foto_bukti)
                                <button onclick="showBuktiImage('{{ asset('storage/'.$o->foto_bukti) }}')" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-100 text-green-800 hover:bg-green-200 transition">
                                    <i class="bi bi-image"></i>
                                    <span>Lihat</span>
                                </button>
                            @else
                                <span class="text-xs text-gray-400">-</span>
                            @endif
                        </td>

                        <td class="py-4 px-4">
                            <div class="flex items-center justify-center gap-2 flex-nowrap min-w-[180px]">
                                {{-- Button Detail --}}
                                <a href="{{ route('riwayat.detail', ['id' => $o->id_transaksi, 'from' => 'dashboard']) }}"
                                   class="inline-flex items-center gap-1 bg-gradient-to-r from-yellow-400 to-amber-500 px-3 py-2 rounded-xl text-gray-900 text-xs font-bold hover:from-yellow-500 hover:to-amber-600 transition-all hover:shadow-lg hover:scale-105 whitespace-nowrap">
                                    <i class="bi bi-eye-fill"></i>
                                    <span>Detail</span>
                                </a>

                                {{-- Button Info Delivery untuk transaksi online --}}
                                @if($o->jenis_transaksi == 'online')
                                    <button onclick="showDeliveryInfo('{{ $o->id_transaksi }}')"
                                           class="inline-flex items-center gap-1 bg-gradient-to-r from-blue-400 to-blue-500 px-3 py-2 rounded-xl text-white text-xs font-bold hover:from-blue-500 hover:to-blue-600 transition-all hover:shadow-lg hover:scale-105 whitespace-nowrap">
                                        <i class="bi bi-truck"></i>
                                        <span>Delivery</span>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="18" class="py-10 text-center">
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

{{-- POPUP DELIVERY INFO --}}
<div id="deliveryModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-3xl w-full max-h-[90vh] overflow-hidden">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 px-6 py-5 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="bi bi-truck text-white text-2xl"></i>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-white">Info Pickup & Delivery</h3>
                    <p class="text-sm text-blue-100">Transaksi #<span id="modalTransaksiId"></span></p>
                </div>
            </div>
            <button onclick="closeDeliveryModal()" class="text-white hover:bg-white/20 rounded-xl p-2 transition">
                <i class="bi bi-x-lg text-2xl"></i>
            </button>
        </div>

        {{-- Content --}}
        <div class="p-6 overflow-y-auto max-h-[calc(90vh-100px)]">
            <div id="deliveryContent" class="space-y-4">
                {{-- Content will be loaded here --}}
            </div>
        </div>
    </div>
</div>

{{-- POPUP BUKTI IMAGE --}}
<div id="buktiModal" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center p-4" onclick="closeBuktiModal()">
    <div class="relative max-w-4xl w-full" onclick="event.stopPropagation()">
        <button onclick="closeBuktiModal()" class="absolute -top-10 right-0 text-white hover:text-gray-300 transition">
            <i class="bi bi-x-lg text-3xl"></i>
        </button>
        <img id="buktiImage" src="" alt="Bukti Pembayaran" class="w-full h-auto rounded-2xl shadow-2xl">
    </div>
</div>

@endsection

@push('scripts')
<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

<script>
    $(document).ready(function() {
        let table = $('#orderTable').DataTable({
            pageLength: 25, // Tampilkan lebih banyak data per halaman
            lengthMenu: [10, 25, 50, 100], // Opsi lebih banyak
            ordering: true,
            searching: true,
            destroy: true,
            order: [[1, 'desc']], // Sort by ID Transaksi descending (yang terbaru duluan)
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi",
                infoEmpty: "Menampilkan 0 sampai 0 dari 0 transaksi",
                infoFiltered: "(difilter dari _MAX_ total transaksi)",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Selanjutnya",
                    previous: "Sebelumnya"
                },
                emptyTable: "Tidak ada data transaksi"
            },

            initComplete: function () {
                // SEARCH BOX
                $('div.dataTables_filter input')
                    .addClass("border-2 border-gray-300 rounded-xl px-4 py-3 ml-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");

                // SELECT LENGTH
                $('div.dataTables_length select')
                    .addClass("border-2 border-gray-300 rounded-xl px-4 py-2.5 mr-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition");

                // PAGINATION
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
        
        // Log total data untuk debugging
        console.log('Total transaksi dimuat:', table.data().length);
    });

    // Function to show delivery info
    function showDeliveryInfo(transaksiId) {
        document.getElementById('modalTransaksiId').textContent = transaksiId;
        document.getElementById('deliveryModal').classList.remove('hidden');
        
        // Fetch delivery data
        fetch(`/api/delivery-info/${transaksiId}`)
            .then(response => response.json())
            .then(data => {
                let content = '';
                
                if (data.deliveries && data.deliveries.length > 0) {
                    data.deliveries.forEach((delivery, index) => {
                        const jenisIcon = delivery.jenis === 'pickup' ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up';
                        const jenisColor = delivery.jenis === 'pickup' ? 'from-orange-400 to-orange-500' : 'from-purple-400 to-purple-500';
                        const jenisText = delivery.jenis === 'pickup' ? 'PICKUP' : 'ANTAR';
                        
                        // Status config
                        const statusConfig = {
                            'pending': { bg: 'bg-yellow-100', text: 'text-yellow-800', label: 'Pending', icon: 'bi-clock' },
                            'accepted': { bg: 'bg-blue-100', text: 'text-blue-800', label: 'Diterima', icon: 'bi-check-circle' },
                            'on_the_way_to_customer': { bg: 'bg-cyan-100', text: 'text-cyan-800', label: 'Dalam Perjalanan', icon: 'bi-truck' },
                            'arrived_at_customer': { bg: 'bg-indigo-100', text: 'text-indigo-800', label: 'Sampai di Pelanggan', icon: 'bi-geo-alt' },
                            'on_the_way_to_laundry': { bg: 'bg-purple-100', text: 'text-purple-800', label: 'Ke Laundry', icon: 'bi-arrow-left-right' },
                            'arrived_at_laundry': { bg: 'bg-green-100', text: 'text-green-800', label: 'Sampai di Laundry', icon: 'bi-house-check' },
                            'delivered': { bg: 'bg-green-100', text: 'text-green-800', label: 'Terkirim', icon: 'bi-check-circle-fill' },
                        };
                        
                        const status = statusConfig[delivery.status] || { bg: 'bg-gray-100', text: 'text-gray-800', label: delivery.status || 'Unknown', icon: 'bi-question-circle' };
                        
                        const driverInfo = delivery.id_driver 
                            ? `<div class="flex items-center gap-2 text-sm text-gray-700">
                                <i class="bi bi-person-badge"></i>
                                <span>Driver ID: ${delivery.id_driver}</span>
                               </div>`
                            : `<div class="flex items-center gap-2 text-sm text-red-600">
                                <i class="bi bi-exclamation-triangle-fill"></i>
                                <span class="font-semibold">Belum Ada Driver</span>
                               </div>`;
                        
                        content += `
                            <div class="bg-gradient-to-r ${jenisColor} rounded-2xl p-1 shadow-lg">
                                <div class="bg-white rounded-xl p-5">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-gradient-to-r ${jenisColor} rounded-xl flex items-center justify-center">
                                                <i class="bi ${jenisIcon} text-white text-xl"></i>
                                            </div>
                                            <div>
                                                <div class="font-bold text-lg text-gray-900">${jenisText}</div>
                                                <div class="text-xs text-gray-500">ID: ${delivery.id_delivery}</div>
                                            </div>
                                        </div>
                                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full ${status.bg} ${status.text} text-xs font-semibold">
                                            <i class="bi ${status.icon}"></i>
                                            ${status.label}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-3">
                                        <div class="flex items-start gap-2 text-sm">
                                            <i class="bi bi-geo-alt-fill text-gray-400 mt-0.5"></i>
                                            <div>
                                                <div class="text-gray-500 text-xs">Alamat Tujuan</div>
                                                <div class="font-semibold text-gray-900">${delivery.alamat_tujuan || '-'}</div>
                                            </div>
                                        </div>
                                        
                                        ${driverInfo}
                                        
                                        ${delivery.waktu ? `
                                            <div class="flex items-center gap-2 text-sm text-gray-700">
                                                <i class="bi bi-clock-fill text-gray-400"></i>
                                                <span>${new Date(delivery.waktu).toLocaleString('id-ID')}</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                } else {
                    content = `
                        <div class="text-center py-10">
                            <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
                            <p class="text-gray-500 font-semibold">Belum ada data pickup/delivery</p>
                        </div>
                    `;
                }
                
                document.getElementById('deliveryContent').innerHTML = content;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('deliveryContent').innerHTML = `
                    <div class="text-center py-10">
                        <i class="bi bi-exclamation-triangle text-5xl text-red-400 mb-3"></i>
                        <p class="text-red-600 font-semibold">Gagal memuat data delivery</p>
                    </div>
                `;
            });
    }

    // Function to close modal
    function closeDeliveryModal() {
        document.getElementById('deliveryModal').classList.add('hidden');
    }

    // Close modal when clicking outside
    document.getElementById('deliveryModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeliveryModal();
        }
    });

    // Function to show bukti image
    function showBuktiImage(imageUrl) {
        document.getElementById('buktiImage').src = imageUrl;
        document.getElementById('buktiModal').classList.remove('hidden');
    }

    // Function to close bukti modal
    function closeBuktiModal() {
        document.getElementById('buktiModal').classList.add('hidden');
    }

    // Close bukti modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeBuktiModal();
            closeDeliveryModal();
        }
    });
</script>
@endpush