@extends('layouts.master')

@section('title', 'Checkout')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('kasir.transaksi.create') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Checkout</span>
</div>

<div class="p-4 space-y-6 pb-40">

    {{-- CARD PELANGGAN --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center gap-4">
        <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
            @if(!empty($pelanggan['foto']))
                <img src="{{ asset('images/' . $pelanggan['foto']) }}" 
                     alt="{{ $pelanggan['nama_pelanggan'] }}"
                     class="w-full h-full object-cover"
                     onerror="this.onerror=null; this.src='{{ asset('images/default-user.png') }}';">
            @else
                <i class="bi bi-person-fill text-4xl text-gray-400"></i>
            @endif
        </div>
        <div>
            <p class="text-xl font-bold leading-tight">{{ $pelanggan['nama_pelanggan'] }}</p>
            <p class="text-sm text-gray-500 flex items-center gap-1">
                <i class="bi bi-phone-fill text-yellow-500"></i>
                {{ $pelanggan['no_hp'] }}
            </p>
        </div>
    </div>

    {{-- DETAIL ORDER --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl">
        <div class="flex items-center gap-3 mb-3">
            <i class="bi bi-basket-fill text-3xl text-red-500"></i>
            <span class="text-xl font-semibold">Detail Order</span>
        </div>
        @foreach ($detail as $d)
        <div class="bg-gray-100 rounded-2xl p-4 flex gap-4 mb-4">
            <div class="w-20 h-20 bg-white rounded-2xl flex justify-center items-center border overflow-hidden">
                @php
                    $imagePath = isset($d['gambar']) && !empty($d['gambar']) 
                        ? 'storage/' . $d['gambar'] 
                        : 'images/default.png';
                @endphp
                
                <img src="{{ asset($imagePath) }}" 
                     alt="{{ $d['nama_layanan'] ?? 'Layanan' }}"
                     class="w-full h-full object-cover"
                     onerror="this.onerror=null; this.src='{{ asset('images/default.png') }}';">
            </div>
            
            <div class="flex-1">
                <p class="font-bold text-lg leading-tight">
                    {{ $d['nama_layanan'] }}{{ isset($d['jenis']) ? ' ('.$d['jenis'].')' : '' }}
                </p>
                <p class="text-sm text-gray-600">
                    Rp{{ number_format($d['harga'],0,',','.') }} / {{ $d['satuan'] ?? '' }}
                </p>
                <p class="font-semibold mt-2">
                    SubTotal: Rp{{ number_format($d['harga'] * $d['qty'],0,',','.') }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Qty: {{ $d['qty'] }} {{ $d['satuan'] ?? '' }}
                </p>
            </div>
        </div>
        @endforeach
    </div>

        {{-- KETERANGAN --}}
    <div class="bg-white rounded-2xl p-5 shadow-xl">
        <p class="font-semibold text-base mb-3 flex items-center gap-2">
            <i class="bi bi-chat-left-text-fill text-yellow-500 text-xl"></i>
            Keterangan Transaksi
        </p>
        @if($keterangan)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl px-4 py-3">
                <p class="text-gray-800 text-sm leading-relaxed whitespace-pre-line">{{ $keterangan }}</p>
            </div>
        @else
            <div class="flex items-center gap-2 text-gray-400 bg-gray-50 border border-dashed border-gray-200 rounded-xl px-4 py-3">
                <i class="bi bi-dash-circle text-base"></i>
                <p class="text-sm italic">Tidak ada keterangan.</p>
            </div>
        @endif
        <input type="hidden" id="keteranganTransaksi" value="{{ $keterangan }}">
    </div>

    {{-- TANGGAL ESTIMASI --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl" id="containerEstimasi">
        <p class="font-medium flex items-center gap-2 mb-3">
            <i class="bi bi-calendar-check-fill text-green-600 text-xl"></i>
            <span class="font-bold">Estimasi Selesai</span>
            <span class="text-red-500 font-bold">*</span>
        </p>
        <div class="flex items-center gap-3">
            <input type="datetime-local" id="tgl_estimasi" 
                class="flex-1 p-3 rounded-xl border-2 border-gray-200 focus:border-green-500 focus:ring-2 focus:ring-green-200 transition-all outline-none">
            <button type="button" id="btnSetEstimasi" 
                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-xl font-bold shadow-md transition-all whitespace-nowrap">
                <i class="bi bi-check-circle-fill"></i>
                Set Estimasi
            </button>
        </div>
        <p class="text-xs text-gray-500 mt-2">
            <i class="bi bi-info-circle-fill text-blue-500"></i>
            Wajib diisi untuk melanjutkan transaksi
        </p>
    </div>

    {{-- LANGSUNG BAYAR --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <input type="hidden" id="hiddenLangsungBayar" value="1">
        <button id="toggleBayar" class="px-5 py-2 rounded-2xl font-bold text-black bg-yellow-400 shadow">
            ✔ Langsung Bayar
        </button>
        <span class="text-sm text-gray-700" id="statusBayar">Aktif</span>
    </div>

    {{-- METODE BAYAR & DISKON --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl space-y-3">
        <select name="id_metode_bayar" id="selectMetodeBayar" 
                class="w-full p-3 rounded-2xl border bg-gray-100 text-gray-700">
            @foreach ($metode_bayar as $m)
                <option value="{{ $m->id_metode_bayar }}">{{ $m->nama_metode_bayar }}</option>
            @endforeach
        </select>

        <div class="flex gap-2 mt-3">
            <input id="inputDiskon" type="text" inputmode="decimal"
                   class="flex-1 p-3 rounded-2xl border bg-gray-100" 
                   placeholder="Diskon nominal">
        </div>
        <p class="text-xs text-gray-500">
            <i class="bi bi-info-circle-fill text-blue-500"></i>
            Diskon diinput dalam nominal rupiah saja.
        </p>
    </div>
</div>

<div id="infoDiskon" class="px-5 py-3 text-sm text-red-600 bg-white rounded-xl shadow fixed bottom-[88px] left-0 w-full hidden"></div>

{{-- FOOTER --}}
<div class="fixed bottom-0 left-0 w-full bg-yellow-400 px-5 py-5 flex justify-between items-center shadow-xl z-50">
    <div>
        <p class="text-sm">Total Harga</p>
        <p id="totalHargaFooter" class="text-2xl font-bold">Rp {{ number_format($totalHarga,0,',','.') }}</p>
    </div>
    <button id="btnBayar" class="bg-green-600 hover:bg-green-700 text-white px-7 py-3 rounded-2xl text-lg shadow font-bold">
        Bayar
    </button>
</div>

<!-- POPUP ALERT CUSTOM -->
<div id="customAlert" class="fixed inset-0 bg-black/60 flex items-center justify-center px-4 z-[9999] hidden">
    <div id="customAlertBox" class="bg-white rounded-3xl w-full max-w-sm shadow-2xl p-6">
        <div class="flex flex-col items-center">
            <div id="alertIconWrap" class="w-20 h-20 bg-red-100 rounded-full flex items-center justify-center mb-4 transition">
                <i id="alertIcon" class="bi bi-exclamation-triangle-fill text-red-500 text-4xl"></i>
            </div>
            <h3 id="alertTitle" class="text-xl font-bold text-gray-800 mb-2">Perhatian!</h3>
            <p id="alertMessage" class="text-center text-gray-600 mb-6 leading-relaxed"></p>
            <button id="btnCloseAlert"
                    class="w-full py-3 bg-red-500 hover:bg-red-600 text-white font-bold rounded-2xl transition">
                Tutup
            </button>
        </div>
    </div>
</div>

<!-- POPUP BAYAR -->
<div id="popupBayar" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-50 hidden">
    <div id="popupBoxBayar" class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 relative">
        <div class="bg-yellow-400 text-center py-3 rounded-2xl mb-4 relative">
            <span class="font-bold text-lg">Konfirmasi Pembelian!</span>
            <button id="closePopup" class="absolute right-3 top-3 text-xl font-bold text-white">✕</button>
        </div>
        <p class="font-semibold text-lg">Nama Pelanggan: <span id="popupNama"></span></p>
        <p class="font-semibold text-lg mb-3">Total Harga: <span id="popupTotal"></span></p>

        <div class="bg-gray-100 rounded-2xl p-4 flex items-center gap-3 mb-2">
            <i class="bi bi-cash-coin text-3xl text-yellow-500"></i>
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Jumlah Bayar / DP</p>
                <input id="popupInputBayar" name="dp" type="number" 
                       class="w-full bg-transparent font-bold text-xl outline-none"
                       placeholder="Kosongkan jika belum bayar">
            </div>
        </div>
        <p id="infoDpMessage" class="text-xs text-gray-500 hidden">
            <i class="bi bi-info-circle-fill text-blue-500"></i>
            Kosongkan jika belum bayar, atau isi dengan jumlah DP
        </p>

        <button id="btnSimpanPembayaran" 
                class="mt-6 w-full py-3 bg-green-600 text-white font-bold rounded-2xl text-lg shadow">
            Simpan
        </button>
    </div>
</div>

<!-- POPUP SUCCESS -->
<div id="popupSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-xl p-7 text-center">
        <div class="w-36 h-36 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-check2 text-white text-7xl"></i>
        </div>
        <p class="font-semibold text-lg">Nama: <span id="succNama"></span></p>
        <p class="font-semibold text-lg">No HP: <span id="succHp"></span></p>
        <h1 class="text-2xl font-bold mb-1">Transaksi Berhasil Disimpan!!</h1>
        <p class="font-semibold text-lg mt-4">Total Harga: <span id="succTotal"></span></p>
        <p class="font-semibold text-lg mb-6">Jumlah Bayar: <span id="succBayar"></span></p>
        <p class="font-semibold text-lg">Diskon: <span id="succDiskon"></span></p>
        <input type="hidden" id="shareTransactionId">
        <input type="hidden" id="shareCustomerEmail">

        <div class="flex justify-center gap-8 mb-6">
            <div class="flex flex-col items-center cursor-pointer" id="btnSelesai">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-check text-3xl text-white"></i>
                </div>
                <span class="font-semibold mt-1">Selesai</span>
            </div>
            <div class="flex flex-col items-center cursor-pointer" id="btnBagikan">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-share text-3xl text-white"></i>
                </div>
                <span class="font-semibold mt-1">Bagikan</span>
            </div>
        </div>

        <a href="{{ route('kasir.transaksi.pelanggan') }}" 
           class="block w-full py-3 bg-green-600 text-white text-lg font-bold rounded-2xl text-center">
            Buat Transaksi Baru
        </a>
    </div>
</div>

<!-- POPUP BAGIKAN -->
<div id="popupBagikan" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[1000] hidden">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-xl p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-xl font-bold text-gray-900">Bagikan Hasil Pembayaran</h3>
            <button type="button" id="closeSharePopup" class="text-2xl font-bold text-gray-500 hover:text-gray-700">×</button>
        </div>

        <div class="grid grid-cols-2 gap-3 mb-5">
            <button type="button" id="shareEmailOption"
                class="share-channel-btn bg-yellow-400 text-black py-3 rounded-2xl font-bold border-2 border-yellow-400">
                <i class="bi bi-envelope-fill mr-2"></i>Email
            </button>
            <button type="button" id="shareTelegramOption"
                class="share-channel-btn bg-white text-gray-700 py-3 rounded-2xl font-bold border-2 border-gray-200">
                <i class="bi bi-telegram mr-2"></i>Telegram
            </button>
        </div>

        <div class="mb-3">
            <label id="shareRecipientLabel" class="block font-semibold text-gray-700 mb-2">Email Tujuan</label>
            <input type="text" id="shareRecipientInput"
                class="w-full p-3 rounded-2xl border bg-gray-100 text-gray-700"
                placeholder="Masukkan email tujuan">
            <p id="shareRecipientHint" class="text-xs text-gray-500 mt-2">
                Email pelanggan akan diisi otomatis jika tersedia.
            </p>
        </div>

        <button type="button" id="btnKirimBagikan"
            class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded-2xl font-bold shadow">
            Kirim Sekarang
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {

    // ===== CUSTOM ALERT FUNCTION =====
    const customAlert = document.getElementById('customAlert');
    const customAlertBox = document.getElementById('customAlertBox');
    const alertMessage = document.getElementById('alertMessage');
    const alertTitle = document.getElementById('alertTitle');
    const alertIconWrap = document.getElementById('alertIconWrap');
    const alertIcon = document.getElementById('alertIcon');
    const btnCloseAlert = document.getElementById('btnCloseAlert');

    const showAlert = (message, type = 'error') => {
        const isSuccess = type === 'success';

        customAlertBox.className = `bg-white rounded-3xl w-full max-w-sm shadow-2xl p-6 animate__animated ${isSuccess ? 'animate__fadeInUp' : 'animate__shakeX'}`;
        alertIconWrap.className = `w-20 h-20 rounded-full flex items-center justify-center mb-4 transition ${isSuccess ? 'bg-green-100' : 'bg-red-100'}`;
        alertIcon.className = `bi text-4xl ${isSuccess ? 'bi-check-circle-fill text-green-500' : 'bi-exclamation-triangle-fill text-red-500'}`;
        alertTitle.textContent = isSuccess ? 'Berhasil!' : 'Perhatian!';
        btnCloseAlert.className = `w-full py-3 text-white font-bold rounded-2xl transition ${isSuccess ? 'bg-green-500 hover:bg-green-600' : 'bg-red-500 hover:bg-red-600'}`;

        alertMessage.textContent = message;
        customAlert.classList.remove('hidden');
        
        btnCloseAlert.onclick = () => customAlert.classList.add('hidden');
        
        customAlert.onclick = (e) => {
            if (e.target === customAlert) {
                customAlert.classList.add('hidden');
            }
        };
    };

    // ===== GLOBAL VARIABLES =====
    const inputDiskon = document.getElementById('inputDiskon');
    const infoDiskon = document.getElementById('infoDiskon');
    const totalHargaFooter = document.getElementById('totalHargaFooter');
    const totalAwal = {{ $totalHarga }};
    let estimasiValid = false;

    // ===== VALIDASI INPUT DISKON =====
    // Hanya izinkan angka, titik, dan koma
    inputDiskon.addEventListener('input', function(e) {
        let value = e.target.value;
        
        // Izinkan angka, titik, dan koma
        value = value.replace(/[^0-9.,]/g, '');
        
        // Ganti koma dengan titik untuk konsistensi
        value = value.replace(',', '.');
        
        // Hanya izinkan satu titik desimal
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        e.target.value = value;
        hitungDiskon();
    });

    // ===== ESTIMASI SELESAI =====
    const tglEstimasi = document.getElementById('tgl_estimasi');
    const btnSetEstimasi = document.getElementById('btnSetEstimasi');
    const containerEstimasi = document.getElementById('containerEstimasi');

    const defaultDate = new Date();
    defaultDate.setDate(defaultDate.getDate() + 3);
    tglEstimasi.value = defaultDate.toISOString().slice(0, 16);

    btnSetEstimasi.addEventListener('click', () => {
        if (!tglEstimasi.value) {
            showAlert('Silakan pilih tanggal estimasi selesai terlebih dahulu!');
            tglEstimasi.focus();
            return;
        }

        const selectedDate = new Date(tglEstimasi.value);
        const now = new Date();

        if (selectedDate <= now) {
            showAlert('Tanggal estimasi harus lebih dari waktu sekarang!');
            tglEstimasi.focus();
            return;
        }

        if (estimasiValid) {
            estimasiValid = false;
            containerEstimasi.classList.remove('border-2', 'border-green-500', 'bg-green-50');
            btnSetEstimasi.innerHTML = '<i class="bi bi-check-circle-fill"></i> Set Estimasi';
            btnSetEstimasi.classList.remove('bg-blue-600', 'hover:bg-blue-700');
            btnSetEstimasi.classList.add('bg-green-600', 'hover:bg-green-700');
            tglEstimasi.disabled = false;
            tglEstimasi.focus();
            
            const successMsg = document.getElementById('successMsgEstimasi');
            if (successMsg) successMsg.remove();
            
        } else {
            estimasiValid = true;
            
            containerEstimasi.classList.add('border-2', 'border-green-500', 'bg-green-50');
            btnSetEstimasi.innerHTML = '<i class="bi bi-pencil-fill"></i> Edit Estimasi';
            btnSetEstimasi.classList.remove('bg-green-600', 'hover:bg-green-700');
            btnSetEstimasi.classList.add('bg-blue-600', 'hover:bg-blue-700');
            tglEstimasi.disabled = true;

            const successMsg = document.createElement('div');
            successMsg.id = 'successMsgEstimasi';
            successMsg.className = 'mt-2 text-sm text-green-600 font-semibold flex items-center gap-2';
            successMsg.innerHTML = '<i class="bi bi-check-circle-fill"></i> Estimasi selesai berhasil disimpan!';
            containerEstimasi.appendChild(successMsg);
        }
    });

   // ===== HITUNG DISKON =====
const hitungDiskon = () => {
    let value = parseFloat(inputDiskon.value.replace(',', '.')) || 0;
    let potongan = value;
    
    potongan = Math.min(Math.max(potongan, 0), totalAwal);
    let totalAkhir = totalAwal - potongan;

    if (value > 0) {
        infoDiskon.style.display = "block";
        const displayValue = `Rp${value.toLocaleString('id-ID')}`;
        
        infoDiskon.innerHTML = `
            <span class="font-semibold">Diskon (${displayValue}):</span> Rp${potongan.toLocaleString('id-ID')}<br>
            <span class="text-xs text-gray-600">(Rp${totalAwal.toLocaleString('id-ID')} - Rp${potongan.toLocaleString('id-ID')})</span>
        `;
    } else {
        infoDiskon.style.display = "none";
    }

    totalHargaFooter.textContent = `Rp ${totalAkhir.toLocaleString('id-ID')}`;
    return totalAkhir;
};

    inputDiskon.addEventListener('input', hitungDiskon);

    // ===== TOGGLE LANGSUNG BAYAR =====
    const toggleBayar = document.getElementById('toggleBayar');
    const statusBayar = document.getElementById('statusBayar');
    const hiddenBayar = document.getElementById('hiddenLangsungBayar');

    toggleBayar.addEventListener('click', () => {
        if (hiddenBayar.value === "1") {
            hiddenBayar.value = "0";
            statusBayar.textContent = "Tidak";
            toggleBayar.textContent = "✖ Belum Bayar";
            toggleBayar.classList.replace("bg-yellow-400", "bg-red-400");
        } else {
            hiddenBayar.value = "1";
            statusBayar.textContent = "Aktif";
            toggleBayar.textContent = "✔ Langsung Bayar";
            toggleBayar.classList.replace("bg-red-400", "bg-yellow-400");
        }
        updatePopupBayar();
    });

    // ===== POPUP BAYAR =====
    const popupBayar = document.getElementById("popupBayar");
    const closePopup = document.getElementById("closePopup");
    const tombolBayar = document.getElementById("btnBayar");
    const popupNama = document.getElementById("popupNama");
    const popupTotal = document.getElementById("popupTotal");
    const inputBayar = document.getElementById("popupInputBayar");

    const updatePopupBayar = () => {
        const totalAkhir = hitungDiskon();
        popupTotal.textContent = "Rp " + totalAkhir.toLocaleString('id-ID');

        if (hiddenBayar.value === "1") {
            inputBayar.value = totalAkhir;
            inputBayar.readOnly = true;
        } else {
            inputBayar.value = "";
            inputBayar.readOnly = false;
            inputBayar.placeholder = "Kosongkan jika belum bayar";
        }
    };

    tombolBayar.addEventListener("click", () => {
        if (!estimasiValid) {
            showAlert('Silakan set tanggal estimasi selesai terlebih dahulu!');
            tglEstimasi.focus();
            containerEstimasi.scrollIntoView({ behavior: 'smooth', block: 'center' });
            containerEstimasi.classList.add('animate__animated', 'animate__shakeX');
            setTimeout(() => {
                containerEstimasi.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
            return;
        }

        popupNama.textContent = "{{ $pelanggan['nama_pelanggan'] }}";
        updatePopupBayar();
        popupBayar.classList.remove("hidden");
    });

    closePopup.addEventListener("click", () => popupBayar.classList.add("hidden"));
    inputDiskon.addEventListener('input', updatePopupBayar);

    // ===== SUCCESS POPUP ELEMENTS =====
    const popupSuccess = document.getElementById("popupSuccess");
    const succTotal = document.getElementById("succTotal");
    const succBayar = document.getElementById("succBayar");
    const succDiskon = document.getElementById("succDiskon");
    const succNama = document.getElementById("succNama");
    const succHp = document.getElementById("succHp");
    const shareTransactionId = document.getElementById("shareTransactionId");
    const shareCustomerEmail = document.getElementById("shareCustomerEmail");
    const popupBagikan = document.getElementById("popupBagikan");
    const closeSharePopup = document.getElementById("closeSharePopup");
    const shareRecipientInput = document.getElementById("shareRecipientInput");
    const shareRecipientLabel = document.getElementById("shareRecipientLabel");
    const shareRecipientHint = document.getElementById("shareRecipientHint");
    const shareEmailOption = document.getElementById("shareEmailOption");
    const shareTelegramOption = document.getElementById("shareTelegramOption");
    let selectedShareChannel = "email";

    const updateShareChannelUI = () => {
        if (selectedShareChannel === "email") {
            shareEmailOption.className = "share-channel-btn bg-yellow-400 text-black py-3 rounded-2xl font-bold border-2 border-yellow-400";
            shareTelegramOption.className = "share-channel-btn bg-white text-gray-700 py-3 rounded-2xl font-bold border-2 border-gray-200";
            shareRecipientLabel.textContent = "Email Tujuan";
            shareRecipientInput.placeholder = "Masukkan email tujuan";
            shareRecipientInput.value = shareCustomerEmail.value || "";
            shareRecipientHint.textContent = "Email pelanggan akan diisi otomatis jika tersedia.";
        } else {
            shareEmailOption.className = "share-channel-btn bg-white text-gray-700 py-3 rounded-2xl font-bold border-2 border-gray-200";
            shareTelegramOption.className = "share-channel-btn bg-yellow-400 text-black py-3 rounded-2xl font-bold border-2 border-yellow-400";
            shareRecipientLabel.textContent = "Chat ID Telegram";
            shareRecipientInput.placeholder = "Masukkan chat ID Telegram";
            shareRecipientInput.value = "";
            shareRecipientHint.textContent = "Gunakan chat ID numerik Telegram, bukan username. Untuk chat pribadi, kirim pesan dulu ke bot lalu ambil chat.id dari getUpdates.";
        }
    };

    // ===== SIMPAN PEMBAYARAN =====
    document.getElementById("btnSimpanPembayaran").addEventListener("click", async () => {
        try {
            const totalAkhir = hitungDiskon();
            const bayar = parseFloat(inputBayar.value) || 0;
            const diskonValue = parseFloat(inputDiskon.value.replace(',', '.')) || 0;
            const keterangan = document.getElementById("keteranganTransaksi").value || null;
            const id_metode_bayar = document.getElementById("selectMetodeBayar").value || null;
            const tgl_estimasi_value = document.getElementById("tgl_estimasi").value || null;
            const langsung = parseInt(hiddenBayar.value);

            console.log("📤 SENDING DATA:", {
                dp: bayar,
                langsung_bayar: langsung,
                diskon: diskonValue,
                keterangan: keterangan,
                id_metode_bayar: id_metode_bayar,
                tgl_estimasi: tgl_estimasi_value,
                totalAkhir: totalAkhir
            });

            // ✅ VALIDASI
            if (langsung === 1) {
                if (bayar === 0) {
                    showAlert("Silakan masukkan jumlah pembayaran!");
                    return;
                }
                if (bayar < totalAkhir) {
                    showAlert("Langsung Bayar harus lunas penuh! Nonaktifkan jika ingin DP.");
                    return;
                }
            }

            if (langsung === 0) {
                if (bayar > 0 && bayar >= totalAkhir) {
                    showAlert("Jika bayar penuh, silakan aktifkan Langsung Bayar!");
                    return;
                }
            }

            // ✅ KIRIM DATA KE SERVER
            console.log("🌐 Fetching to:", "{{ route('kasir.transaksi.bayar') }}");
            
            const res = await fetch("{{ route('kasir.transaksi.bayar') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    dp: bayar,
                    langsung_bayar: langsung,
                    diskon: diskonValue,
                    keterangan: keterangan,
                    id_metode_bayar: id_metode_bayar,
                    tgl_estimasi: tgl_estimasi_value
                })
            });

            console.log("📡 RESPONSE STATUS:", res.status, res.statusText);
            console.log("📡 RESPONSE HEADERS:", res.headers);

            if (!res.ok) {
                const errorText = await res.text();
                console.error("❌ ERROR RESPONSE TEXT:", errorText);
                
                try {
                    const errorData = JSON.parse(errorText);
                    showAlert(errorData.message || "Gagal menyimpan transaksi!");
                } catch (e) {
                    showAlert(`Server error (${res.status}): ${errorText.substring(0, 100)}`);
                }
                return;
            }

            const data = await res.json();
            console.log("✅ SUCCESS RESPONSE:", data);
            
            popupBayar.classList.add("hidden");

            // UPDATE POPUP SUCCESS
            shareTransactionId.value = data.id_transaksi ?? "";
            shareCustomerEmail.value = data.email ?? "";
            succTotal.textContent = "Rp " + parseInt(data.total).toLocaleString("id-ID");
            succDiskon.textContent = "Rp " + parseInt(data.diskon ?? 0).toLocaleString("id-ID");
            succNama.textContent = data.nama;
            succHp.textContent = data.hp;
            succBayar.textContent = "Rp " + parseInt(data.total_bayar ?? 0).toLocaleString("id-ID");

            document.getElementById("labelStatusBayar")?.remove();

            const label = document.createElement("p");
            label.id = "labelStatusBayar";
            label.classList.add("font-bold", "mt-2");
            
            if (data.status_bayar === "lunas") {
                label.classList.add("text-green-500");
                label.textContent = "Status: LUNAS";
            } else if (data.status_bayar === "DP") {
                label.classList.add("text-orange-500");
                label.textContent = "Status: DP";
            } else {
                label.classList.add("text-red-500");
                label.textContent = "Status: BELUM LUNAS";
            }
            
            popupSuccess.querySelector(".bg-white")?.appendChild(label);
            popupSuccess.classList.remove("hidden");

        } catch (err) {
            console.error('❌ FULL ERROR:', err);
            console.error('❌ ERROR NAME:', err.name);
            console.error('❌ ERROR MESSAGE:', err.message);
            console.error('❌ ERROR STACK:', err.stack);
            
            showAlert(`Terjadi kesalahan: ${err.message}\n\n${err.name}`);
        }
    });

    // ===== BUTTON SUCCESS =====
    document.getElementById("btnSelesai").addEventListener("click", () => {
        window.location.href = "{{ route('kasir.dashboard') }}";
    });

    shareEmailOption.addEventListener("click", () => {
        selectedShareChannel = "email";
        updateShareChannelUI();
    });

    shareTelegramOption.addEventListener("click", () => {
        selectedShareChannel = "telegram";
        updateShareChannelUI();
    });

    closeSharePopup.addEventListener("click", () => {
        popupBagikan.classList.add("hidden");
    });

    document.getElementById("btnBagikan").addEventListener("click", () => {
        if (!shareTransactionId.value) {
            showAlert("ID transaksi belum tersedia untuk dibagikan.");
            return;
        }

        selectedShareChannel = "email";
        updateShareChannelUI();
        popupBagikan.classList.remove("hidden");
    });

    document.getElementById("btnKirimBagikan").addEventListener("click", async () => {
        try {
            const recipient = shareRecipientInput.value.trim();

            if (selectedShareChannel === "email" && recipient === "") {
                showAlert("Silakan isi email tujuan terlebih dahulu.");
                return;
            }

            const response = await fetch("{{ route('kasir.transaksi.share') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    id_transaksi: shareTransactionId.value,
                    channel: selectedShareChannel,
                    recipient: recipient
                })
            });

            const rawResult = await response.text();
            let result = {};

            try {
                result = rawResult ? JSON.parse(rawResult) : {};
            } catch (error) {
                console.error("Share response parse error:", error, rawResult);
                showAlert("Server mengembalikan respons yang tidak valid saat membagikan transaksi.");
                return;
            }

            if (!response.ok || !result.success) {
                showAlert(result.message || "Gagal membagikan hasil pembayaran.");
                return;
            }

            popupBagikan.classList.add("hidden");
            showAlert(result.message || "Ringkasan pembayaran berhasil dikirim.", "success");
        } catch (error) {
            console.error("Share error:", error);
            showAlert("Terjadi kesalahan saat membagikan hasil pembayaran.");
        }
    });

});
</script>

{{-- ✅ Tambahkan Animate.css untuk animasi shake --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

@endsection
