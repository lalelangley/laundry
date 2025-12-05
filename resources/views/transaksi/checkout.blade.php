@extends('layouts.master')

@section('title', 'Checkout')

@section('content')

{{-- HEADER --}}
<div class="bg-yellow-400 px-5 py-5 rounded-b-[32px] flex items-center gap-3 shadow-lg">
    <a href="{{ route('transaksi.create') }}" class="text-black text-3xl font-bold">
        <i class="bi bi-arrow-left"></i>
    </a>
    <span class="text-2xl font-bold">Checkout</span>
</div>

<div class="p-4 space-y-6 pb-40">

    {{-- CARD PELANGGAN --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center gap-4">
        <div class="w-16 h-16 rounded-full overflow-hidden bg-gray-200 flex items-center justify-center">
            <img src="{{ asset('images/default-user.png') }}" class="w-full h-full object-cover">
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
            <div class="w-20 h-20 bg-white rounded-2xl flex justify-center items-center border">
                <img src="{{ asset('images/teddy.png') }}" class="w-full h-full object-contain">
            </div>

            <div class="flex-1">
                <p class="font-bold text-lg">{{ $d['nama_layanan'] }}</p>
                <p class="text-sm text-gray-600">
                    Rp{{ number_format($d['harga'],0,',','.') }} / {{ $d['satuan'] }}
                </p>

                <p class="font-semibold mt-2">
                    SubTotal : Rp{{ number_format($d['harga'] * $d['qty'],0,',','.') }}
                </p>

                <p class="text-xs text-gray-500 mt-1">Qty: {{ $d['qty'] }} {{ $d['satuan'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- KETERANGAN TRANSAKSI --}}
    {{-- KETERANGAN TRANSAKSI --}}
    <div class="bg-white rounded-2xl p-5 shadow-xl">

        <p class="font-semibold text-base mb-2 flex items-center gap-2">
            <i class="bi bi-chat-left-text-fill text-yellow-500 text-xl"></i>
            Keterangan Transaksi
        </p>

        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl
                    text-gray-800 whitespace-pre-line text-[16px] leading-normal">
            {{ $keterangan ?: 'Tidak ada keterangan.' }}
        </div>
        {{-- INPUT KETERANGAN (HIDDEN) --}}
    <input type="hidden" id="keteranganTransaksi" value="{{ $keterangan }}">


</div>
    {{-- TANGGAL MASUK --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <p class="font-medium flex items-center gap-2">
            <i class="bi bi-calendar-event text-red-500"></i>
            Tanggal Masuk :
        </p>

        <input type="datetime-local" id="tgl_masuk"
            class="p-2 rounded-xl border bg-gray-100 text-sm w-44">
    </div>

    {{-- ESTIMASI SELESAI --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <p class="font-medium flex items-center gap-2">
            <i class="bi bi-check-circle-fill text-red-500"></i>
            Estimasi Selesai :
        </p>

        <input type="datetime-local" id="tgl_estimasi"
            class="p-2 rounded-xl border bg-gray-100 text-sm w-44">
    </div>

    {{-- LANGSUNG BAYAR --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <input type="hidden" id="hiddenLangsungBayar" value="1">

        <button id="toggleBayar"
            class="px-5 py-2 rounded-2xl font-bold text-black bg-yellow-400 shadow">
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
            <input id="inputDiskon" type="number"
                class="flex-1 p-3 rounded-2xl border bg-gray-100"
                placeholder="Diskon / Rupiah">

            <div class="flex flex-col rounded-2xl overflow-hidden">
                <button id="btnRupiah" class="px-4 py-2 bg-yellow-400 font-bold">Rupiah Rp</button>
                <button id="btnPersen" class="px-4 py-2 bg-white border font-bold text-sm">Persen %</button>
            </div>
        </div>
    </div>
</div> {{-- END container utama --}}
 {{-- penutup detail order --}}

{{-- INFO DISKON --}}
<div id="infoDiskon"
    class="px-5 py-3 text-sm text-red-600 bg-white rounded-xl shadow fixed bottom-[88px] left-0 w-full"
    style="display: none;">
</div>

{{-- FOOTER --}}
<div class="fixed bottom-0 left-0 w-full bg-yellow-400 px-5 py-5 
            flex justify-between items-center shadow-xl z-50">

    <div>
        <p class="text-sm">Total Harga</p>
        <p id="totalHargaFooter" class="text-2xl font-bold">
            Rp {{ number_format($totalHarga,0,',','.') }}
        </p>
    </div>

    <button id="btnBayar" class="bg-green-600 hover:bg-green-700 text-white px-7 py-3 rounded-2xl text-lg shadow font-bold">
        Bayar
    </button>
</div>

<!-- POPUP KONFIRMASI -->
<div id="popupBayar" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-50 hidden">

    <div id="popupBoxBayar"
        class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 relative animate__animated animate__fadeInUp">

        <!-- HEADER -->
        <div class="bg-yellow-400 text-center py-3 rounded-2xl mb-4 relative">
            <span class="font-bold text-lg">Konfirmasi Pembelian !</span>
            <button id="closePopup" class="absolute right-3 top-3 text-xl font-bold text-white">✕</button>
        </div>

        <p class="font-semibold text-lg">Nama Pelanggan : <span id="popupNama"></span></p>
        <p class="font-semibold text-lg mb-3">Total Harga : <span id="popupTotal"></span></p>

        <!-- INPUT JUMLAH BAYAR -->
        <div class="bg-gray-100 rounded-2xl p-4 flex items-center gap-3 mb-2">
            <i class="bi bi-cash-coin text-3xl text-yellow-500"></i>
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Jumlah Bayar</p>
                <input id="inputBayar" type="number"
                    class="w-full bg-transparent font-bold text-xl outline-none"
                    value="">
            </div>
        </div>

        <p class="text-xs text-gray-500">(Jika ingin DP dulu, masukkan jumlah DP)</p>

        <!-- BUTTON SIMPAN -->
        <button id="btnSimpanPembayaran"
            class="mt-6 w-full py-3 bg-green-600 text-white font-bold rounded-2xl text-lg shadow">
            Simpan
        </button>
    </div>
</div>
<!-- POPUP BELUM BAYAR -->
<div id="popupBelumBayar"
    class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">

    <div class="bg-white rounded-3xl w-full max-w-md shadow-xl p-7 relative">

        <!-- HEADER -->
        <div class="bg-yellow-400 text-center py-3 rounded-2xl mb-4 relative">
            <span class="font-bold text-lg">Konfirmasi Pembelian !</span>
            <button id="closePopupBelum" class="absolute right-3 top-3 text-xl font-bold text-white">✕</button>
        </div>

        <p class="font-semibold text-lg">
            Nama Pelanggan : <span id="bbNama"></span>
        </p>

        <p class="font-semibold text-lg">
            Total Harga : <span id="bbTotal"></span>
        </p>
        <p class="font-semibold text-lg">
            Diskon : <span id="bbDiskon"></span>
        </p>
        <p class="font-semibold text-lg mb-3">
            Status : <span class="text-red-500 font-bold">Belum Dibayar</span>
        </p>

        <div class="bg-gray-100 rounded-2xl p-4 flex items-center gap-3 mb-4">
            <i class="bi bi-cash text-3xl text-yellow-500"></i>
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Jumlah Pembayaran</p>
                <p id="bbBayar" class="font-bold text-xl text-black"></p>
            </div>
        </div>

        <button id="btnBelumBayarSimpan"
            class="w-full py-3 bg-green-600 text-white text-lg font-bold rounded-2xl">
            Simpan
        </button>

    </div>
</div>


<!-- POPUP SUCCESS -->
<div id="popupSuccess"
    class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">


    <div class="bg-white rounded-3xl w-full max-w-md shadow-xl p-7 text-center animate__animated animate__zoomIn">
        
        <!-- ICON -->
        <div class="w-36 h-36 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-check2 text-white text-7xl"></i>
        </div>
        <p class="font-semibold text-lg">
            Nama : <span id="succNama"></span>
        </p>
        <p class="font-semibold text-lg mb-3">
            No HP : <span id="succHp"></span>
        </p>

        <h1 class="text-2xl font-bold mb-1">Transaksi Berhasil Disimpan !!</h1>
        <p class="font-semibold text-lg mt-4">
            Total Harga : <span id="succTotal"></span>
        </p>
        <p class="font-semibold text-lg mb-6">
            Jumlah Bayar : <span id="succBayar"></span>
        </p>
        <p class="font-semibold text-lg">
            Diskon : <span id="succDiskon"></span>
        </p>
        <!-- 3 BUTTON ICON -->
        <div class="flex justify-center gap-8 mb-6">
            <div class="flex flex-col items-center cursor-pointer" id="btnSelesai">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-check text-3xl text-white"></i>
                </div>
                <span class="font-semibold mt-1">Selesai</span>
            </div>

            <div class="flex flex-col items-center cursor-pointer" id="btnCetak">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-printer text-3xl text-white"></i>
                </div>
                <span class="font-semibold mt-1">Cetak</span>
            </div>

            <div class="flex flex-col items-center cursor-pointer" id="btnBagikan">
                <div class="w-16 h-16 bg-yellow-400 rounded-full flex items-center justify-center">
                    <i class="bi bi-share text-3xl text-white"></i>
                </div>
                <span class="font-semibold mt-1">Bagikan</span>
            </div>
        </div>

        <!-- BUTTON BUAT TRANSAKSI BARU -->
        <button id="btnTransaksiBaru"
            class="w-full py-3 bg-green-600 text-white text-lg font-bold rounded-2xl">
            Buat Transaksi Baru
        </button>
    </div>
</div>

@endsection
@section('scripts')
<script>
    // GLOBAL supaya bisa diakses semua function
const btnRupiah = document.getElementById('btnRupiah');
const btnPersen = document.getElementById('btnPersen');
const infoDiskon = document.getElementById('infoDiskon');
const inputDiskon = document.getElementById('inputDiskon');
let mode = "rupiah";

document.addEventListener("DOMContentLoaded", () => {
const totalAwal = {{ $totalHarga }};

const hitungDiskon = () => {
    let value = parseFloat(inputDiskon.value) || 0;
    let potongan = 0;

    if (mode === "persen") {
        if (value > 100) value = 100;
        potongan = totalAwal * (value / 100);
    } else {
        potongan = value;
    }

    if (potongan < 0) potongan = 0;
    if (potongan > totalAwal) potongan = totalAwal;

    let totalAkhir = totalAwal - potongan;

    infoDiskon.style.display = "block";
    infoDiskon.innerHTML = `
        <span class="font-semibold">Diskon :</span>
        Rp${potongan.toLocaleString('id-ID')}<br>
        <span class="text-xs text-gray-600">
            ( Rp${totalAwal.toLocaleString('id-ID')} - Rp${potongan.toLocaleString('id-ID')} )
        </span>
    `;

    document.getElementById("totalHargaFooter").innerHTML = `Rp ${totalAkhir.toLocaleString('id-ID')}`;
};

btnRupiah.addEventListener('click', () => {
    mode = "rupiah";
    btnRupiah.classList.add('bg-yellow-400');
    btnPersen.classList.remove('bg-yellow-400');
    inputDiskon.placeholder = "Diskon / Rupiah";
    hitungDiskon();
});

btnPersen.addEventListener('click', () => {
    mode = "persen";
    btnPersen.classList.add('bg-yellow-400');
    btnRupiah.classList.remove('bg-yellow-400');
    inputDiskon.placeholder = "Diskon / Persen %";
    hitungDiskon();
});

inputDiskon.addEventListener('input', hitungDiskon);

}); // DOMContentLoaded END


/* ======================= TOGGLE LANGSUNG BAYAR ======================= */
document.getElementById('toggleBayar').addEventListener('click', function () {
    let status = document.getElementById('statusBayar');
    let hidden = document.getElementById('hiddenLangsungBayar');

    if (status.textContent === "Aktif") {
        status.textContent = "Tidak";
        hidden.value = 0; // tidak langsung bayar
        this.textContent = "✖ Tidak Bayar";
        this.classList.remove("bg-yellow-400");
        this.classList.add("bg-red-400");
    } else {
        status.textContent = "Aktif";
        hidden.value = 1; // langsung bayar
        this.textContent = "✔ Langsung Bayar";
        this.classList.add("bg-yellow-400");
        this.classList.remove("bg-red-400");
    }
});

/* ======================= POPUP BAYAR ======================= */
const popupBayar = document.getElementById("popupBayar");
const closePopup = document.getElementById("closePopup");
const tombolBayar = document.getElementById("btnBayar");
const popupNama = document.getElementById("popupNama");
const popupTotal = document.getElementById("popupTotal");
const inputBayar = document.getElementById("inputBayar");

tombolBayar.addEventListener("click", () => {
    popupNama.textContent = "{{ $pelanggan['nama_pelanggan'] }}";

    let totalText = document.getElementById("totalHargaFooter").textContent;
    let total = parseInt(totalText.replace(/[^0-9]/g, "")) || 0;

    popupTotal.textContent = "Rp " + total.toLocaleString('id-ID');

    let langsung = document.getElementById("hiddenLangsungBayar").value;

    if (langsung == "1") {
        inputBayar.value = total;
        inputBayar.setAttribute("readonly", true);
    } else {
        inputBayar.value = "";
        inputBayar.removeAttribute("readonly");
    }

    popupBayar.classList.remove("hidden");
});


// ===== POPUP BELUM BAYAR =====
const popupBelumBayar = document.getElementById("popupBelumBayar");

document.getElementById("closePopupBelum").addEventListener("click", () => {
    popupBelumBayar.classList.add("hidden");
});

document.getElementById("btnBelumBayarSimpan").addEventListener("click", () => {
    popupBelumBayar.classList.add("hidden");
    window.location.reload(); // kembali ke awal transaksi
});



closePopup.addEventListener("click", () => popupBayar.classList.add("hidden"));


/* ======================= POPUP SUCCESS ======================= */
const popupSuccess = document.getElementById("popupSuccess");
const succTotal = document.getElementById("succTotal");
const succBayar = document.getElementById("succBayar");

// ===== SIMPAN PEMBAYARAN =====
document.getElementById("btnSimpanPembayaran").addEventListener("click", async () => {
    try {
        let total = parseInt(document.getElementById("popupTotal").textContent.replace(/[^0-9]/g, ""));
        let bayar = parseInt(inputBayar.value || 0);
        let diskon = parseFloat(inputDiskon.value) || 0;
        let tipe_diskon = btnPersen.classList.contains("bg-yellow-400") ? "percent" : "nominal";
        let keterangan = document.getElementById("keteranganTransaksi").value || null;
        let id_metode_bayar = document.getElementById("selectMetodeBayar").value || null;
        let tgl_masuk = (document.querySelectorAll("input[type='datetime-local']")[0] || {}).value || null;
        let tgl_estimasi = (document.querySelectorAll("input[type='datetime-local']")[1] || {}).value || null;
        let potonganDiskon = tipe_diskon === "percent" ? (diskon / 100) * total : diskon;
        if (potonganDiskon > total) potonganDiskon = total;

        const res = await fetch("{{ route('transaksi.bayar') }}", {
            method: "POST",
            credentials: 'include',
            headers: {
                "Accept": "application/json",
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
            jumlah_bayar: bayar,
            langsung_bayar: document.getElementById("hiddenLangsungBayar").value,
            total_harga: total,
            diskon: diskon,
            tipe_diskon: tipe_diskon,
            keterangan: keterangan,
            id_metode_bayar: id_metode_bayar,
            tgl_masuk: tgl_masuk,
            tgl_estimasi: tgl_estimasi,
        })
        });

        if (!res.ok) {
            let text = await res.text();
            alert("Gagal menyimpan: " + text);
            return;
        }

        const data = await res.json();
        popupBayar.classList.add("hidden");

        // Gunakan popupSuccess untuk semua status
        succTotal.textContent = "Rp " + parseInt(data.total).toLocaleString("id-ID");
        succDiskon.textContent = "Rp " + parseInt(data.diskon ?? 0).toLocaleString("id-ID");
        succNama.textContent = data.nama;
        succHp.textContent = data.hp;

        if (parseInt(data.status_bayar) === 1) {
            succBayar.textContent = "Rp " + parseInt(data.bayar).toLocaleString("id-ID");
        } else {
            succBayar.textContent = "Rp 0";

            // Tambahkan label status "Belum Dibayar" di popup success
            let existingLabel = document.getElementById("labelBelumBayar");
            if (!existingLabel) {
                let label = document.createElement("p");
                label.id = "labelBelumBayar";
                label.textContent = "Status: Belum Dibayar";
                label.classList.add("text-red-500", "font-bold", "mt-2");
                popupSuccess.querySelector("div.bg-white").appendChild(label);
            }
        }

        popupSuccess.classList.remove("hidden");

    } catch (err) {
        console.error(err);
        alert("Terjadi kesalahan saat menyimpan.");
    }
});

/* ======================= SUCCESS BUTTONS ======================= */
document.getElementById("btnSelesai").addEventListener("click", () => {
    window.location.href = "{{ route('admin.dashboard') }}";
});


document.getElementById("btnTransaksiBaru").addEventListener("click", () => {
    window.location.reload();
});

document.getElementById("btnCetak").addEventListener("click", () => {
    window.print();
});

document.getElementById("btnBagikan").addEventListener("click", async () => {
    const total = succTotal.textContent;
    const bayar = succBayar.textContent;

    const shareText = `Transaksi Berhasil!\nTotal: ${total}\nBayar: ${bayar}`;

    if (navigator.share) {
        await navigator.share({ text: shareText });
    } else {
        alert("Fitur Share tidak didukung.");
    }
});
</script>
@endsection