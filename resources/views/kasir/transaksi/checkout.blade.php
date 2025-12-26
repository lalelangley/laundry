@extends('layouts.master')

@section('title', 'Checkout Kasir')

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
                <p class="font-bold text-lg leading-tight">
                    {{ $d['nama_layanan'] }}{{ isset($d['jenis']) ? ' '.$d['jenis'] : '' }}
                </p>
                <p class="text-sm text-gray-600">
                    Rp{{ number_format($d['harga'],0,',','.') }} / {{ $d['satuan'] ?? '' }}
                </p>
                <p class="font-semibold mt-2">
                    SubTotal : Rp{{ number_format($d['harga'] * $d['qty'],0,',','.') }}
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
        <p class="font-semibold text-base mb-2 flex items-center gap-2">
            <i class="bi bi-chat-left-text-fill text-yellow-500 text-xl"></i>
            Keterangan Transaksi
        </p>
        <div class="px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl text-gray-800 whitespace-pre-line text-[16px] leading-normal">
            {{ $keterangan ?: 'Tidak ada keterangan.' }}
        </div>
        <input type="hidden" id="keteranganTransaksi" value="{{ $keterangan }}">
    </div>

    {{-- TANGGAL MASUK & ESTIMASI --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <p class="font-medium flex items-center gap-2"><i class="bi bi-calendar-event text-red-500"></i>Tanggal Masuk :</p>
        <input type="datetime-local" id="tgl_masuk" class="p-2 rounded-xl border bg-gray-100 text-sm w-44">
    </div>

    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <p class="font-medium flex items-center gap-2"><i class="bi bi-check-circle-fill text-red-500"></i>Estimasi Selesai :</p>
        <input type="datetime-local" id="tgl_estimasi" class="p-2 rounded-xl border bg-gray-100 text-sm w-44">
    </div>

    {{-- LANGSUNG BAYAR --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl flex items-center justify-between">
        <input type="hidden" id="hiddenLangsungBayar" value="1">
        <button id="toggleBayar" class="px-5 py-2 rounded-2xl font-bold text-black bg-yellow-400 shadow">✔ Langsung Bayar</button>
        <span class="text-sm text-gray-700" id="statusBayar">Aktif</span>
    </div>

    {{-- METODE BAYAR & DISKON --}}
    <div class="bg-white rounded-3xl p-5 shadow-xl space-y-3">
        <select name="id_metode_bayar" id="selectMetodeBayar" class="w-full p-3 rounded-2xl border bg-gray-100 text-gray-700">
            @foreach ($metode_bayar as $m)
                <option value="{{ $m->id_metode_bayar }}">{{ $m->nama_metode_bayar }}</option>
            @endforeach
        </select>

        <div class="flex gap-2 mt-3">
            <input id="inputDiskon" type="number" class="flex-1 p-3 rounded-2xl border bg-gray-100" placeholder="Diskon / Rupiah">
            <div class="flex flex-col rounded-2xl overflow-hidden">
                <button id="btnRupiah" class="px-4 py-2 bg-yellow-400 font-bold">Rupiah Rp</button>
                <button id="btnPersen" class="px-4 py-2 bg-white border font-bold text-sm">Persen %</button>
            </div>
        </div>
    </div>
</div>

<div id="infoDiskon" class="px-5 py-3 text-sm text-red-600 bg-white rounded-xl shadow fixed bottom-[88px] left-0 w-full hidden"></div>

{{-- FOOTER --}}
<div class="fixed bottom-0 left-0 w-full bg-yellow-400 px-5 py-5 flex justify-between items-center shadow-xl z-50">
    <div>
        <p class="text-sm">Total Harga</p>
        <p id="totalHargaFooter" class="text-2xl font-bold">Rp {{ array_sum(array_map(fn($d)=>$d['harga']*$d['qty'],$detail)) }}</p>
    </div>
    <button id="btnBayar" class="bg-green-600 hover:bg-green-700 text-white px-7 py-3 rounded-2xl text-lg shadow font-bold">Bayar</button>
</div>

{{-- POPUP BAYAR --}}
<div id="popupBayar" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-50 hidden">
    <div id="popupBoxBayar" class="bg-white rounded-3xl w-full max-w-md shadow-2xl p-5 relative animate__animated animate__fadeInUp">
        <div class="bg-yellow-400 text-center py-3 rounded-2xl mb-4 relative">
            <span class="font-bold text-lg">Konfirmasi Pembelian !</span>
            <button id="closePopup" class="absolute right-3 top-3 text-xl font-bold text-white">✕</button>
        </div>
        <p class="font-semibold text-lg">Nama Pelanggan : <span id="popupNama"></span></p>
        <p class="font-semibold text-lg mb-3">Total Harga : <span id="popupTotal"></span></p>

        <div class="bg-gray-100 rounded-2xl p-4 flex items-center gap-3 mb-2">
            <i class="bi bi-cash-coin text-3xl text-yellow-500"></i>
            <div class="flex-1">
                <p class="text-gray-500 text-sm">Jumlah Bayar</p>
                <input id="popupInputBayar" name="dp" type="number" class="w-full bg-transparent font-bold text-xl outline-none">
            </div>
        </div>
        <p class="text-xs text-gray-500">(Jika ingin DP dulu, masukkan jumlah DP)</p>

        <button id="btnSimpanPembayaran" class="mt-6 w-full py-3 bg-green-600 text-white font-bold rounded-2xl text-lg shadow">
            Simpan
        </button>
    </div>
</div>

{{-- POPUP SUCCESS --}}
<div id="popupSuccess" class="fixed inset-0 bg-black/50 flex items-center justify-center px-4 z-[999] hidden">
    <div class="bg-white rounded-3xl w-full max-w-md shadow-xl p-7 text-center animate__animated animate__zoomIn">
        <div class="w-36 h-36 bg-yellow-400 rounded-full flex items-center justify-center mx-auto mb-6">
            <i class="bi bi-check2 text-white text-7xl"></i>
        </div>
        <p class="font-semibold text-lg">Nama : <span id="succNama"></span></p>
        <p class="font-semibold text-lg">No HP : <span id="succHp"></span></p>
        <h1 class="text-2xl font-bold mb-1">Transaksi Berhasil Disimpan !!</h1>
        <p class="font-semibold text-lg mt-4">Total Harga : <span id="succTotal"></span></p>
        <p class="font-semibold text-lg mb-6">Jumlah Bayar : <span id="succBayar"></span></p>
        <p class="font-semibold text-lg">Diskon : <span id="succDiskon"></span></p>

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

        <a href="{{ route('kasir.transaksi.create') }}" class="block w-full py-3 bg-green-600 text-white text-lg font-bold rounded-2xl text-center">
            Buat Transaksi Baru
        </a>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const btnRupiah = document.getElementById('btnRupiah');
    const btnPersen = document.getElementById('btnPersen');
    const inputDiskon = document.getElementById('inputDiskon');
    const infoDiskon = document.getElementById('infoDiskon');
    const totalHargaFooter = document.getElementById('totalHargaFooter');
    const totalAwal = {{ $totalHarga }};
    let mode = "rupiah";

    const hitungDiskon = () => {
        let value = parseFloat(inputDiskon.value) || 0;
        let potongan = mode==="persen" ? totalAwal * Math.min(value,100)/100 : value;
        potongan = Math.min(Math.max(potongan,0), totalAwal);
        let totalAkhir = totalAwal - potongan;
        infoDiskon.style.display="block";
        infoDiskon.innerHTML=`<span class="font-semibold">Diskon :</span> Rp${potongan.toLocaleString('id-ID')}<br><span class="text-xs text-gray-600">(Rp${totalAwal.toLocaleString('id-ID')} - Rp${potongan.toLocaleString('id-ID')})</span>`;
        totalHargaFooter.textContent=`Rp ${totalAkhir.toLocaleString('id-ID')}`;
        return totalAkhir;
    };

    btnRupiah.addEventListener('click',()=>{
        mode="rupiah";
        btnRupiah.classList.add('bg-yellow-400');
        btnPersen.classList.remove('bg-yellow-400');
        inputDiskon.placeholder="Diskon / Rupiah";
        hitungDiskon();
    });

    btnPersen.addEventListener('click',()=>{
        mode="persen";
        btnPersen.classList.add('bg-yellow-400');
        btnRupiah.classList.remove('bg-yellow-400');
        inputDiskon.placeholder="Diskon / Persen %";
        hitungDiskon();
    });

    inputDiskon.addEventListener('input', hitungDiskon);

    const toggleBayar = document.getElementById('toggleBayar');
    const statusBayar = document.getElementById('statusBayar');
    const hiddenBayar = document.getElementById('hiddenLangsungBayar');

    toggleBayar.addEventListener('click',()=>{
        if(hiddenBayar.value==="1"){
            hiddenBayar.value="0";
            statusBayar.textContent="Tidak";
            toggleBayar.textContent="✖ Tidak Bayar";
            toggleBayar.classList.replace("bg-yellow-400","bg-red-400");
        }else{
            hiddenBayar.value="1";
            statusBayar.textContent="Aktif";
            toggleBayar.textContent="✔ Langsung Bayar";
            toggleBayar.classList.replace("bg-red-400","bg-yellow-400");
        }
    });

    const popupBayar=document.getElementById("popupBayar");
    const closePopup=document.getElementById("closePopup");
    const tombolBayar=document.getElementById("btnBayar");
    const popupNama=document.getElementById("popupNama");
    const popupTotal=document.getElementById("popupTotal");
    const inputBayar=document.getElementById("popupInputBayar");

    const updatePopupBayar=()=>{
        const totalAkhir=hitungDiskon();
        popupTotal.textContent="Rp "+totalAkhir.toLocaleString('id-ID');
        if(hiddenBayar.value==="1"){inputBayar.value=totalAkhir;inputBayar.readOnly=true;}
        else{inputBayar.value="";inputBayar.readOnly=false;}
    };

    tombolBayar.addEventListener("click",()=>{
        popupNama.textContent="{{ $pelanggan['nama_pelanggan'] }}";
        updatePopupBayar();
        popupBayar.classList.remove("hidden");
    });

    closePopup.addEventListener("click",()=>popupBayar.classList.add("hidden"));
    inputDiskon.addEventListener('input',updatePopupBayar);

    const popupSuccess=document.getElementById("popupSuccess");
    const succTotal=document.getElementById("succTotal");
    const succBayar=document.getElementById("succBayar");
    const succDiskon=document.getElementById("succDiskon");
    const succNama=document.getElementById("succNama");
    const succHp=document.getElementById("succHp");

    const formatDateTime=dt=>dt?dt.replace('T',' ')+':00':null;

    document.getElementById("btnSimpanPembayaran").addEventListener("click",async()=>{
        try{
            const total=hitungDiskon();
            const bayar=parseFloat(inputBayar.value)||0;
            const diskon=parseFloat(inputDiskon.value)||0;
            const tipe_diskon=btnPersen.classList.contains("bg-yellow-400")?"percent":"nominal";
            const keterangan=document.getElementById("keteranganTransaksi").value||null;
            const id_metode_bayar=document.getElementById("selectMetodeBayar").value||null;
            const tgl_masuk=formatDateTime(document.getElementById("tgl_masuk").value);
            const tgl_estimasi=formatDateTime(document.getElementById("tgl_estimasi").value);
            const langsung=parseInt(hiddenBayar.value);

            const res=await fetch("{{ route('kasir.transaksi.bayar') }}",{
                method:"POST",
                headers:{"Content-Type":"application/json","X-CSRF-TOKEN":"{{ csrf_token() }}"},
                body:JSON.stringify({jumlah_bayar:bayar,dp:bayar,langsung_bayar:langsung,total_harga:total+diskon,diskon:diskon,tipe_diskon:tipe_diskon,keterangan:keterangan,id_metode_bayar:id_metode_bayar,tgl_masuk:tgl_masuk,tgl_estimasi:tgl_estimasi})
            });
            if(!res.ok){alert("Gagal menyimpan");return;}
            const data=await res.json();
            popupBayar.classList.add("hidden");

            succTotal.textContent="Rp "+parseInt(data.total).toLocaleString("id-ID");
            succDiskon.textContent="Rp "+parseInt(data.diskon??0).toLocaleString("id-ID");
            succNama.textContent=data.nama;
            succHp.textContent=data.hp;
            succBayar.textContent="Rp "+parseInt(data.bayar??0).toLocaleString("id-ID");

            const oldLabel=document.getElementById("labelStatusBayar");
            if(oldLabel) oldLabel.remove();
            if(data.status_bayar&&data.status_bayar!="lunas"){
                let label=document.createElement("p");
                label.id="labelStatusBayar";
                label.classList.add(data.status_bayar.toLowerCase()=="dp"?"text-orange-500":"text-red-500","font-bold","mt-2");
                label.textContent="Status: "+data.status_bayar.toUpperCase();
                popupSuccess.querySelector(".bg-white")?.appendChild(label);
            }

            popupSuccess.classList.remove("hidden");

        }catch(err){console.error(err);alert("Terjadi kesalahan.");}
    });

    document.getElementById("btnSelesai").addEventListener("click",()=>window.location.href="{{ route('kasir.dashboard') }}");
    document.getElementById("btnCetak").addEventListener("click",()=>window.print());
    document.getElementById("btnBagikan").addEventListener("click",async()=>{
        const shareText=`Transaksi Berhasil!\nTotal: ${succTotal.textContent}\nBayar: ${succBayar.textContent}`;
        if(navigator.share) await navigator.share({text:shareText});
        else alert("Fitur share tidak tersedia");
    });

});
</script>
@endsection
