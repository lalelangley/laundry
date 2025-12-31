@extends('layouts.master')
@section('content')
<div class="min-h-screen bg-gray-50 pb-10">
    {{-- ========================================
         HEADER SECTION
    ======================================== --}}
    <div class="bg-yellow-400 px-8 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ route('pesanan.online.index') }}" 
           class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div class="flex-1">
            <span class="text-2xl font-bold">Detail Pesanan</span>
        </div>
    </div>

    {{-- ========================================
         CONTENT SECTION
    ======================================== --}}
    <div class="container mx-auto px-4 py-6">
        <div class="bg-white rounded-xl shadow-md overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-yellow-400">
                        <tr>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 uppercase tracking-wider">
                                ID Transaksi
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 uppercase tracking-wider">
                                Pelanggan
                            </th>
                            <th class="px-6 py-4 text-left text-sm font-bold text-gray-800 uppercase tracking-wider">
                                Total
                            </th>
                            <th class="px-6 py-4 text-center text-sm font-bold text-gray-800 uppercase tracking-wider">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($pesanan as $t)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $t->id_transaksi }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                {{ $t->pelanggan->nama ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                Rp {{ number_format($t->total_harga ?? 0, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('riwayat.detail', $t->id_transaksi) }}"
                                   class="inline-flex items-center px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-gray-900 font-semibold rounded-lg shadow transition-all duration-200 hover:shadow-lg hover:scale-105">
                                    <i class="bi bi-pencil-square mr-2"></i>
                                    Isi Harga
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center justify-center">
                                    <i class="bi bi-inbox text-6xl text-gray-300 mb-3"></i>
                                    <p class="text-gray-500 text-lg font-medium">Tidak ada transaksi</p>
                                    <p class="text-gray-400 text-sm mt-1">Belum ada data pesanan yang tersedia</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection