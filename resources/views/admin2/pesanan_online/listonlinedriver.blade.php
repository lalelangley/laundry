{{-- FE-DOC: Template frontend untuk resources/views/admin2/pesanan_online/listonlinedriver.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('content')
{{-- HEADER --}}
{{-- FE-DOC: Header halaman dipakai untuk judul modul, navigasi balik, dan kadang tombol export cepat. --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-3xl flex items-center gap-3 shadow-lg">
    <a href="{{ route('admin2.pesanan.online.detail', $pesanan->id_transaksi) }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <div>
        <span class="text-2xl font-bold">Pilih Driver {{ $isPickup ? 'Pickup' : 'Delivery' }}</span>
        <p class="text-sm text-gray-800">ORDER #{{ $pesanan->id_transaksi }}</p>
    </div>
</div>

{{-- INFO PESANAN --}}
<div class="px-5 mt-5">
    <div class="bg-white rounded-2xl px-5 py-4 shadow">
        <h3 class="font-bold text-lg mb-3 flex items-center gap-2">
            <i class="bi bi-info-circle text-blue-600"></i>
            Informasi Pesanan
        </h3>
        
        <div class="space-y-3 text-sm">
            <div class="flex items-start gap-3">
                <i class="bi bi-person-fill text-gray-500 text-xl"></i>
                <div>
                    <p class="text-gray-500 text-xs">Nama Pelanggan</p>
                    <p class="font-semibold text-base">{{ $pesanan->nama_pelanggan }}</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <i class="bi bi-telephone-fill text-green-600 text-xl"></i>
                <div>
                    <p class="text-gray-500 text-xs">No. Telepon</p>
                    <p class="font-semibold text-base">{{ $pesanan->no_hp }}</p>
                </div>
            </div>
            
            <div class="flex items-start gap-3">
                <i class="bi bi-geo-alt-fill text-red-500 text-xl"></i>
                <div>
                    <p class="text-gray-500 text-xs">Alamat {{ $isPickup ? 'Pickup' : 'Pengiriman' }}</p>
                    <p class="font-semibold text-base">{{ $pesanan->pelanggan->alamat ?? '-' }}</p>
                </div>
            </div>

            {{-- INFO JENIS DELIVERY --}}
            <div class="flex items-start gap-3">
                <i class="bi bi-truck text-yellow-600 text-xl"></i>
                <div>
                    <p class="text-gray-500 text-xs">Jenis Layanan</p>
                    <p class="font-semibold text-base">
                        @if($isPickup)
                            <span class="bg-orange-100 text-orange-600 px-3 py-1 rounded-full text-sm">
                                <i class="bi bi-arrow-down-circle"></i> Pickup (Jemput Cucian)
                            </span>
                        @else
                            <span class="bg-blue-100 text-blue-600 px-3 py-1 rounded-full text-sm">
                                <i class="bi bi-arrow-up-circle"></i> Delivery (Antar Cucian)
                            </span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SEARCH + TITLE --}}
<div class="px-5 mt-5">
    <div class="bg-white rounded-2xl px-4 py-3 flex items-center shadow justify-between">
        <div class="flex items-center gap-3">
            <i class="bi bi-person-lines-fill text-yellow-500 text-2xl"></i>
            <span class="text-lg font-bold">Daftar Driver Tersedia</span>
        </div>
        <span class="bg-yellow-400 px-3 py-1 rounded-full text-sm font-bold">
            {{ $drivers->count() }}
        </span>
    </div>
</div>

{{-- LIST DRIVER --}}
<div class="px-5 mt-5 space-y-4 mb-24">
    @if($drivers->count() > 0)
        @foreach($drivers as $driver)
            <div class="bg-white rounded-2xl px-4 py-4 shadow cursor-pointer hover:shadow-lg transition-all"
                 onclick="openCatatanModal({{ $driver->id_driver }}, '{{ $driver->nama_driver }}')">
                
                <div class="flex gap-3 items-center">
                    {{-- AVATAR --}}
                    <div class="w-16 h-16 bg-yellow-400 rounded-xl flex items-center justify-center flex-shrink-0">
                        <i class="bi bi-person-fill text-white text-3xl"></i>
                    </div>
                    
                    {{-- DETAIL --}}
                    <div class="flex-1">
                        <div class="text-xl font-semibold">{{ $driver->nama_driver }}</div>
                        <div class="flex items-center text-gray-600 text-base">
                            <i class="bi bi-telephone me-2"></i>{{ $driver->no_telp ?? $driver->no_hp ?? '-' }}
                        </div>
                        @if($driver->alamat)
                        <div class="flex items-center text-gray-600 text-sm">
                            <i class="bi bi-geo-alt me-2"></i>{{ $driver->alamat }}
                        </div>
                        @endif
                    </div>

                    {{-- ARROW ICON --}}
                    <div class="text-yellow-500 text-2xl">
                        <i class="bi bi-chevron-right"></i>
                    </div>
                </div>
            </div>
        @endforeach
    @else
        {{-- EMPTY STATE --}}
        <div class="bg-white rounded-2xl px-5 py-12 text-center shadow">
            <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="bi bi-person-x text-5xl text-gray-400"></i>
            </div>
            <p class="text-xl text-gray-600 font-bold mb-2">Tidak ada driver tersedia</p>
            <p class="text-gray-400 text-sm">Silakan tambahkan driver terlebih dahulu</p>
        </div>
    @endif
</div>

{{-- MODAL CATATAN --}}
<div id="catatanModal" class="fixed inset-0 bg-black/50 z-50 hidden flex items-end sm:items-center justify-center">
    <div class="bg-white w-full sm:max-w-lg rounded-t-3xl sm:rounded-2xl p-6 transform transition-all">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-2xl font-bold flex items-center gap-2">
                <i class="bi bi-chat-left-text text-yellow-500"></i>
                Catatan untuk Driver
            </h3>
            <button onclick="closeCatatanModal()" class="text-gray-400 hover:text-gray-600 text-2xl">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <div class="mb-4 bg-yellow-50 p-3 rounded-xl">
            <p class="text-sm text-gray-600">Driver yang dipilih:</p>
            <p class="font-bold text-lg" id="selectedDriverName"></p>
        </div>

        <div class="mb-4 bg-blue-50 p-3 rounded-xl">
            <p class="text-sm text-gray-600">Jenis Layanan:</p>
            <p class="font-bold text-lg">
                @if($isPickup)
                    <i class="bi bi-arrow-down-circle text-orange-600"></i> Pickup (Jemput Cucian)
                @else
                    <i class="bi bi-arrow-up-circle text-blue-600"></i> Delivery (Antar Cucian)
                @endif
            </p>
        </div>

        {{-- ✅ FORM DENGAN ROUTE DINAMIS BERDASARKAN JENIS DELIVERY --}}
        <form id="assignDriverForm" 
              action="{{ $isPickup ? route('admin2.pesanan.online.assign-driver-pickup', $pesanan->id_transaksi) : route('admin2.pesanan.online.assign-driver-antar', $pesanan->id_transaksi) }}" 
              method="POST"
              onsubmit="return confirmAssign(event)">
            @csrf
            <input type="hidden" name="id_driver" id="selectedDriverId">
            
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">
                    Catatan (Opsional)
                </label>
                <textarea name="catatan_driver" 
                          rows="3"
                          class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-yellow-500 focus:border-transparent resize-none"
                          placeholder="Tambahkan catatan untuk driver jika ada..."></textarea>
                <p class="text-xs text-gray-500 mt-2">
                    <i class="bi bi-info-circle"></i> Contoh: "Tolong telpon dulu sebelum berangkat"
                </p>
            </div>

            <div class="flex gap-3">
                <button type="button" 
                        onclick="closeCatatanModal()"
                        class="flex-1 bg-gray-200 py-3 rounded-xl text-lg font-semibold hover:bg-gray-300 transition-all">
                    Batal
                </button>
                <button type="submit" 
                        class="flex-1 bg-yellow-400 py-3 rounded-xl text-lg font-semibold shadow hover:bg-yellow-500 transition-all flex items-center justify-center gap-2">
                    <i class="bi bi-check-circle-fill"></i>
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

{{-- JAVASCRIPT --}}
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
function openCatatanModal(driverId, driverName) {
    document.getElementById('selectedDriverId').value = driverId;
    document.getElementById('selectedDriverName').textContent = driverName;
    document.getElementById('catatanModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeCatatanModal() {
    document.getElementById('catatanModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    // Reset form
    document.getElementById('assignDriverForm').reset();
}

// ✅ TAMBAHKAN KONFIRMASI SEBELUM SUBMIT
function confirmAssign(event) {
    const driverName = document.getElementById('selectedDriverName').textContent;
    const jenisLayanan = '{{ $isPickup ? "Pickup (Jemput Cucian)" : "Delivery (Antar Cucian)" }}';
    const confirmation = confirm(`Yakin pilih driver ${driverName} untuk ${jenisLayanan}?`);
    
    if (!confirmation) {
        event.preventDefault();
        return false;
    }
    
    // Show loading
    const submitBtn = event.target.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Memproses...';
    
    return true;
}

// Close modal when clicking outside
document.getElementById('catatanModal')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeCatatanModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCatatanModal();
    }
});
</script>

@endsection