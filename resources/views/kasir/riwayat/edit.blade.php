{{-- FE-DOC: Template frontend untuk resources/views/kasir/riwayat/edit.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur Blade, Tailwind, CSS, dan JavaScript tanpa mengubah behavior. --}}
@extends('layouts.master')

@section('title', 'Edit Riwayat Transaksi')

@section('content')

<meta name="csrf-token" content="{{ csrf_token() }}">

@php
    $riwayat = $detail->first()?->transaksi;
    $from = request('from');

    if ($from === 'pesanan_online') {
        $backUrl = route('kasir.pesanan.online.index');
    } elseif ($riwayat) {
        $backUrl = route('kasir.riwayat.detail', $riwayat->id_transaksi);
    } else {
        $backUrl = url()->previous();
    }
@endphp


<div class="min-h-screen bg-gray-50 pb-32">

    {{-- HEADER --}}
    <div class="bg-yellow-400 px-6 py-5 rounded-b-3xl flex items-center gap-4 shadow-lg sticky top-0 z-10">
        <a href="{{ $backUrl }}" class="text-black text-3xl font-bold hover:scale-110 transition-transform">
            <i class="bi bi-arrow-left"></i>
        </a>
        <span class="text-2xl font-bold">Edit Transaksi</span>
    </div>

    {{-- CARD PELANGGAN --}}
    <div class="mx-6 mt-6">
        <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center gap-4">
            {{-- Foto Pelanggan --}}
            <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center flex-shrink-0 shadow-md">
                @if(!empty($pelanggan->gambar))
                    <img src="{{ asset('images/' . $pelanggan->gambar) }}"
                         alt="{{ $pelanggan->nama_pelanggan }}"
                         class="w-full h-full object-cover"
                         onerror="this.onerror=null; this.src='{{ asset('images/default-user.png') }}';">
                @else
                    <i class="bi bi-person-fill text-4xl text-gray-400"></i>
                @endif
            </div>
            
            {{-- Info Pelanggan --}}
            <div class="flex-1">
                <p class="text-xl font-bold text-gray-800 leading-tight">
                    {{ $pelanggan->nama_pelanggan ?? 'Pelanggan Umum' }}
                </p>
                <p class="text-sm text-gray-500 flex items-center gap-1 mt-1">
                    <i class="bi bi-phone-fill text-yellow-500"></i>
                    {{ $pelanggan->no_hp ?? '-' }}
                </p>
            </div>
        </div>
    </div>

    {{-- HEADER DETAIL ORDER --}}
    <div class="mx-6 mt-6">
        <div class="bg-white rounded-3xl shadow-xl p-5">
            <div class="flex items-center justify-between mb-5">
                <div class="flex items-center gap-3">
                    <i class="bi bi-basket-fill text-4xl text-red-500"></i>
                    <span class="text-xl font-semibold text-gray-800">Detail Order</span>
                </div>

                @if($riwayat)
                    <a href="{{ route('kasir.riwayat.addlayanan', $riwayat->id_transaksi) }}"
                       class="bg-yellow-400 hover:bg-yellow-500 px-5 py-3 rounded-2xl font-bold text-black shadow-md hover:shadow-lg transition-all">
                        Tambah Layanan
                    </a>
                @endif
            </div>

            {{-- LIST LAYANAN --}}
            @if($riwayat)
                <form action="{{ route('kasir.riwayat.update', $riwayat->id_transaksi) }}" method="POST" id="formUpdate">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4" id="layananList">
                        @php
                            $isNewItem = request('highlight') === 'new';
                        @endphp
                        
                        @foreach ($detail->sortByDesc('id_detail_transaksi') as $index => $d)
                        @php
                            $hargaItem = (float) ($d->harga > 0 ? $d->harga : ($d->jenis->harga ?? 0));
                        @endphp
                        <div class="group bg-gray-100 rounded-3xl p-5 shadow hover:shadow-xl transition-all layanan-item {{ $isNewItem && $index === 0 ? 'ring-4 ring-green-500 animate-pulse' : '' }}"
                            data-id="{{ $d->id_detail_transaksi }}"
                            data-nama="{{ $d->jenis->nama_jenis ?? 'Layanan' }}"
                            data-qty="{{ $d->qty }}"
                            data-parfum="{{ $d->id_parfum ?? '' }}"
                            data-harga="{{ $hargaItem }}"
                            data-satuan="{{ $d->jenis->satuan->nama_satuan ?? 'Pcs' }}">

                            <div class="flex gap-4">
                                {{-- Gambar Jenis Layanan --}}
                                <div class="w-20 h-20 rounded-2xl overflow-hidden bg-white border-2 border-gray-200 flex items-center justify-center flex-shrink-0 shadow-sm group-hover:border-yellow-400 transition-colors">
                                    @if(!empty($d->jenis->gambar))
                                        <img src="{{ asset('storage/' . $d->jenis->gambar) }}"
                                            alt="{{ $d->jenis->nama_jenis }}"
                                            class="w-full h-full object-cover"
                                            onerror="this.onerror=null; this.src='{{ asset('images/default.png') }}';">
                                    @else
                                        <i class="bi bi-image text-3xl text-gray-300"></i>
                                    @endif
                                </div>

                                {{-- Detail --}}
                                <div class="flex-1 cursor-pointer" onclick="openModalLayanan(event, this.closest('.layanan-item'))">
                                    {{-- Nama Layanan Utama --}}
                                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">
                                        {{ $d->jenis->layanan->nama_layanan ?? '-' }}
                                    </p>
                                    
                                    {{-- Nama Jenis Layanan --}}
                                    <p class="font-bold text-lg text-gray-800 leading-tight">
                                        {{ $d->jenis->nama_jenis ?? '-' }}
                                    </p>
                                    
                                    <p class="text-sm text-gray-600 mt-1">
                                        Rp{{ number_format($hargaItem, 0, ',', '.') }} / 
                                        {{ $d->jenis->satuan->nama_satuan ?? 'Pcs' }}
                                    </p>
                                    
                                    {{-- ✅ Parfum Display dengan class khusus --}}
                                    <p class="parfum-display text-sm text-gray-600 flex items-center gap-1 mt-1 {{ !$d->id_parfum ? 'hidden' : '' }}">
                                        <i class="bi bi-bag-heart-fill text-red-500"></i>
                                        <span class="parfum-name">{{ $d->parfum->nama_parfum ?? 'Tanpa parfum' }}</span>
                                    </p>

                                    <p class="font-semibold text-green-600 mt-2 subtotal">
                                        SubTotal: Rp{{ number_format($d->qty * $hargaItem, 0, ',', '.') }}
                                    </p>

                                    <input type="hidden" name="detail[{{ $d->id_detail_transaksi }}][qty]" value="{{ $d->qty }}" class="qty-input">
                                    <input type="hidden" name="detail[{{ $d->id_detail_transaksi }}][id_parfum]" value="{{ $d->id_parfum ?? '' }}" class="parfum-input">
                                </div>

                                {{-- QTY & Actions --}}
                                <div class="flex flex-col items-end justify-between">
                                    <div class="text-center">
                                        <p class="text-sm font-semibold text-gray-700">Qty</p>
                                        <p class="text-lg font-bold text-gray-800 qty-display">
                                            {{ $d->qty }} {{ $d->jenis->satuan->nama_satuan ?? 'Pcs' }}
                                        </p>
                                    </div>

                                    {{-- Actions (muncul saat hover) --}}
                                    <div class="flex gap-2 opacity-0 group-hover:opacity-100 transition-opacity">
                                        {{-- Edit Button --}}
                                        <button type="button" 
                                                onclick="openModalLayanan(event, this.closest('.layanan-item'))"
                                                class="p-2 bg-yellow-400 hover:bg-yellow-500 rounded-lg transition-colors">
                                            <i class="bi bi-pencil-fill text-white"></i>
                                        </button>
                                        
                                        {{-- Delete Button --}}
                                        <button type="button" 
                                                onclick="confirmDelete(event, '{{ $d->id_detail_transaksi }}', '{{ $d->jenis->nama_jenis ?? "Layanan" }}')"
                                                class="p-2 bg-red-500 hover:bg-red-600 rounded-lg transition-colors">
                                            <i class="bi bi-trash-fill text-white"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- BOTTOM BAR --}}
    <div class="fixed bottom-0 left-0 right-0 bg-yellow-400 px-6 py-5 flex justify-between items-center shadow-2xl z-50 desktop-docked-bar lg:bottom-4 lg:rounded-[28px]">
        <div>
            <p class="text-sm text-gray-700">Total Harga</p>
            <p class="text-2xl font-bold text-gray-900" id="totalHarga">
                Rp{{ number_format($detail->sum(fn($d) => $d->qty * ($d->harga > 0 ? $d->harga : ($d->jenis->harga ?? 0))),0,',','.') }}
            </p>
        </div>

        <button type="submit" form="formUpdate" 
                class="bg-green-600 hover:bg-green-700 text-white px-7 py-3 rounded-2xl text-lg shadow-lg hover:shadow-xl font-bold transition-all hover:scale-105">
            <i class="bi bi-check-circle-fill"></i>
            Simpan Perubahan
        </button>
    </div>
</div>

{{-- MODAL EDIT LAYANAN --}}
<div id="modalLayanan"
     class="fixed inset-0 bg-black/60 hidden items-center justify-center z-[999] px-4">
    <div class="bg-white rounded-3xl w-full max-w-lg shadow-2xl overflow-hidden animate__animated animate__fadeInUp">
        {{-- Header Modal --}}
        <div class="bg-yellow-400 p-6">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-900"></h2>
        </div>

        {{-- Body Modal --}}
        <div class="p-6 space-y-5">
            {{-- Qty Input --}}
            <div>
                <label class="block font-bold text-gray-700 mb-2">
                    <i class="bi bi-123 text-yellow-500"></i>
                    Jumlah Qty
                </label>
                <div class="relative">
                    <i class="bi bi-calculator absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400 text-xl"></i>
                    <input id="qtyInput" 
                           type="number" 
                           step="0.01"
                           class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all text-lg font-semibold"
                           placeholder="Masukkan qty...">
                </div>
            </div>

            {{-- Parfum Select --}}
            <div>
                <label class="block font-bold text-gray-700 mb-2">
                    <i class="bi bi-bag-heart-fill text-red-500"></i>
                    Pilih Parfum
                </label>
                <div class="relative">
                    <i class="bi bi-flower1 absolute left-4 top-1/2 transform -translate-y-1/2 text-pink-400 text-xl z-10"></i>
                    <select id="parfumSelect" 
                            class="w-full pl-12 pr-4 py-4 rounded-xl border-2 border-gray-200 outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-200 transition-all appearance-none bg-white text-lg">
                        <option value="">Pilih Parfum (Opsional)</option>
                        @foreach($parfum as $p)
                            <option value="{{ $p->id_parfum }}">{{ $p->nama_parfum }}</option>
                        @endforeach
                    </select>
                    <i class="bi bi-chevron-down absolute right-4 top-1/2 transform -translate-y-1/2 text-gray-400 pointer-events-none"></i>
                </div>
            </div>
        </div>

        {{-- Footer Modal --}}
        <div class="p-6 bg-gray-50 flex gap-3">
            <button onclick="closeModal()" 
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                Batal
            </button>
            <button id="btnSave" 
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                <i class="bi bi-check-circle-fill"></i>
                Simpan
            </button>
        </div>
    </div>
</div>

{{-- MODAL KONFIRMASI HAPUS --}}
<div id="modalDelete" class="fixed inset-0 bg-black/70 hidden items-center justify-center z-[9999] px-4">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-2xl overflow-hidden animate__animated animate__zoomIn">
        {{-- Header --}}
        <div class="bg-gradient-to-r from-red-500 to-red-600 p-6">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-4xl text-red-500"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-white">Hapus Layanan?</h2>
                    <p class="text-red-100 text-sm mt-1">Tindakan ini tidak dapat dibatalkan</p>
                </div>
            </div>
        </div>

        {{-- Body --}}
        <div class="p-6">
            <p class="text-gray-700 text-lg mb-2">
                Anda akan menghapus layanan:
            </p>
            <div class="bg-red-50 border-2 border-red-200 rounded-xl p-4 mb-6">
                <p id="deleteItemName" class="font-bold text-red-700 text-xl"></p>
            </div>
            <p class="text-gray-600 text-sm">
                <i class="bi bi-info-circle text-blue-500"></i>
                Data yang sudah dihapus tidak dapat dikembalikan.
            </p>
        </div>

        {{-- Footer --}}
        <div class="p-6 bg-gray-50 flex gap-3">
            <button onclick="closeDeleteModal()"
                    class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 py-3 rounded-xl font-bold transition-all">
                <i class="bi bi-x-circle"></i>
                Batal
            </button>
            <button id="btnConfirmDelete"
                    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all">
                <i class="bi bi-trash-fill"></i>
                Ya, Hapus
            </button>
        </div>
    </div>
</div>

@endsection

@section('scripts')
{{-- FE-DOC: Blok JavaScript untuk interaksi halaman ini. --}}
<script>
let currentCard = null;
let deleteDetailId = null;

const modalLayanan = document.getElementById('modalLayanan');
const modalTitle = document.getElementById('modalTitle');
const qtyInput = document.getElementById('qtyInput');
const parfumSelect = document.getElementById('parfumSelect');
const btnSave = document.getElementById('btnSave');
const totalHarga = document.getElementById('totalHarga');

const modalDelete = document.getElementById('modalDelete');
const deleteItemName = document.getElementById('deleteItemName');
const btnConfirmDelete = document.getElementById('btnConfirmDelete');

function openModalLayanan(event, card) {
    event.stopPropagation();
    currentCard = card;
    modalLayanan.classList.remove('hidden');
    modalLayanan.classList.add('flex');
    modalTitle.innerText = card.dataset.nama;
    qtyInput.value = card.dataset.qty;
    
    // ✅ FIX: Set parfum value dengan benar
    const parfumValue = card.dataset.parfum;
    console.log('Parfum ID:', parfumValue);
    
    if (parfumValue && parfumValue !== '' && parfumValue !== 'null') {
        parfumSelect.value = parfumValue;
    } else {
        parfumSelect.value = '';
    }
    
    qtyInput.focus();
}

function closeModal() {
    modalLayanan.classList.add('hidden');
    modalLayanan.classList.remove('flex');
    currentCard = null;
}

// ✅ Confirm Delete with Beautiful Modal
function confirmDelete(event, detailId, itemName) {
    event.stopPropagation();
    deleteDetailId = detailId;
    deleteItemName.innerText = itemName;
    modalDelete.classList.remove('hidden');
    modalDelete.classList.add('flex');
}

function closeDeleteModal() {
    modalDelete.classList.add('hidden');
    modalDelete.classList.remove('flex');
    deleteDetailId = null;
}

// ✅ Execute Delete
btnConfirmDelete.onclick = function() {
    if (!deleteDetailId) return;

    // Disable button
    btnConfirmDelete.disabled = true;
    btnConfirmDelete.innerHTML = '<i class="bi bi-hourglass-split animate-spin"></i> Menghapus...';

    // Send delete request
    fetch(`/kasir/riwayat/detail/${deleteDetailId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            // Remove card from UI
            const card = document.querySelector(`[data-id="${deleteDetailId}"]`);
            if (card) {
                card.style.transition = 'all 0.3s ease';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                
                setTimeout(() => {
                    card.remove();
                    updateTotal();
                    
                    // Check if no more items
                    if (document.querySelectorAll('.layanan-item').length === 0) {
                        document.getElementById('layananList').innerHTML = `
                            <div class="text-center py-16">
                                <i class="bi bi-inbox text-5xl text-gray-400 mb-4"></i>
                                <p class="text-xl text-gray-500 font-semibold">Tidak ada layanan</p>
                                <p class="text-gray-400 text-sm mt-2">Silahkan tambahkan layanan baru</p>
                            </div>
                        `;
                        totalHarga.innerText = 'Rp0';
                    }
                }, 300);
            }
            
            closeDeleteModal();
            showNotification('Layanan berhasil dihapus', 'success');
        } else {
            alert(data.message || 'Gagal menghapus layanan');
        }
    })
    .catch(err => {
        console.error('Error:', err);
        alert('Terjadi kesalahan saat menghapus layanan');
    })
    .finally(() => {
        btnConfirmDelete.disabled = false;
        btnConfirmDelete.innerHTML = '<i class="bi bi-trash-fill"></i> Ya, Hapus';
    });
};

// Show Notification
function showNotification(message, type = 'success') {
    const notification = document.createElement('div');
    notification.className = `fixed top-20 right-6 z-[9999] px-6 py-4 rounded-2xl shadow-2xl transform translate-x-full transition-all duration-300 ${
        type === 'success' ? 'bg-green-500' : 'bg-red-500'
    } text-white font-bold`;
    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <i class="bi bi-${type === 'success' ? 'check-circle-fill' : 'x-circle-fill'} text-2xl"></i>
            <span>${message}</span>
        </div>
    `;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateX(0)';
    }, 10);
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Close modal when clicking outside
modalLayanan.addEventListener('click', function(e) {
    if (e.target === modalLayanan) closeModal();
});

modalDelete.addEventListener('click', function(e) {
    if (e.target === modalDelete) closeDeleteModal();
});

// Close modal with ESC key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!modalLayanan.classList.contains('hidden')) closeModal();
        if (!modalDelete.classList.contains('hidden')) closeDeleteModal();
    }
});

btnSave.onclick = () => {
    const qty = parseFloat(qtyInput.value);
    const parfum = parfumSelect.value;
    const harga = parseFloat(currentCard.dataset.harga);

    // Validasi
    if (!qty || qty <= 0) {
        alert('Qty harus diisi dan lebih dari 0!');
        qtyInput.focus();
        return;
    }

    console.log('Saving - Qty:', qty, 'Parfum:', parfum);

    // Update data di card
    currentCard.dataset.qty = qty;
    currentCard.dataset.parfum = parfum;
    
    // Update hidden inputs
    currentCard.querySelector('.qty-input').value = qty;
    currentCard.querySelector('.parfum-input').value = parfum || '';
    
    // Update display qty
    currentCard.querySelector('.qty-display').innerText =
        qty + ' ' + currentCard.dataset.satuan;
    
    // Update subtotal
    currentCard.querySelector('.subtotal').innerText =
        'SubTotal: Rp' + (qty * harga).toLocaleString('id-ID');

    // ✅ UPDATE DISPLAY PARFUM
    const parfumDisplay = currentCard.querySelector('.parfum-display');
    if (parfumDisplay) {
        if (parfum && parfum !== '' && parfum !== 'null') {
            const selectedOption = parfumSelect.options[parfumSelect.selectedIndex];
            const parfumName = selectedOption ? selectedOption.text : 'Tanpa parfum';
            parfumDisplay.querySelector('.parfum-name').innerText = parfumName;
            parfumDisplay.classList.remove('hidden');
        } else {
            parfumDisplay.classList.add('hidden');
        }
    }

    updateTotal();
    closeModal();
    showNotification('Data berhasil diperbarui', 'success');
};

function updateTotal(){
    let total = 0;
    document.querySelectorAll('.layanan-item').forEach(c => {
        total += parseFloat(c.dataset.qty) * parseFloat(c.dataset.harga);
    });
    totalHarga.innerText = 'Rp' + total.toLocaleString('id-ID');
}

// Auto-scroll ke item baru jika ada highlight
@if(request('highlight') === 'new')
document.addEventListener('DOMContentLoaded', function() {
    const firstItem = document.querySelector('.layanan-item');
    if (firstItem) {
        firstItem.scrollIntoView({ behavior: 'smooth', block: 'center' });
        
        // Remove highlight after 3 seconds
        setTimeout(() => {
            firstItem.classList.remove('ring-4', 'ring-green-500', 'animate-pulse');
        }, 3000);
    }
});
@endif
</script>
@endsection
