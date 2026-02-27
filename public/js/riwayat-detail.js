/* ================================================
   resources/js/riwayat-detail.js
   JavaScript untuk halaman detail transaksi riwayat
   ================================================ */

// ==============================
// VARIABEL GLOBAL
// Diisi dari blade via window object
// ==============================
// window.DETAIL_DATA = { subtotal, diskon, totalTagihan, dp, sisaBayar,
//                        statusBayar, statusTransaksi, bolehDP, harusPelunasan,
//                        idTransaksi, csrfToken, routeBayar }

let dpTerbayar        = window.DETAIL_DATA.dp;
let sisaBayar         = window.DETAIL_DATA.sisaBayar;
let currentStatusBayar = window.DETAIL_DATA.statusBayar;
const totalTagihan    = window.DETAIL_DATA.totalTagihan;
const bolehDP         = window.DETAIL_DATA.bolehDP;
const harusPelunasan  = window.DETAIL_DATA.harusPelunasan;
const routeBayar      = window.DETAIL_DATA.routeBayar;
const csrfToken       = window.DETAIL_DATA.csrfToken;
const idTransaksi     = window.DETAIL_DATA.idTransaksi;

// ==============================
// HELPER: FORMAT RUPIAH
// ==============================
function formatRupiah(angka) {
    return angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// ==============================
// INPUT FORMAT RUPIAH
// ==============================
document.getElementById('jumlahBayarDisplay').addEventListener('input', function () {
    let value = this.value.replace(/\./g, '').replace(/[^0-9]/g, '');
    if (value) {
        this.value = formatRupiah(value);
        document.getElementById('jumlahBayar').value = value;
    } else {
        this.value = '';
        document.getElementById('jumlahBayar').value = '0';
    }
});

// ==============================
// MODAL BAYAR - BUKA
// ==============================
function openModalBayar() {
    const infoModePembayaran = document.getElementById('infoModePembayaran');
    const modalTitle         = document.getElementById('modalTitle');
    const labelNominal       = document.getElementById('labelNominal');
    const infoPembayaran     = document.getElementById('infoPembayaran');

    if (harusPelunasan) {
        modalTitle.textContent   = 'Pelunasan Pembayaran';
        labelNominal.textContent = 'Nominal Pelunasan (Wajib Lunas)';

        infoModePembayaran.className = 'p-4 rounded-xl border-2 bg-orange-50 border-orange-400';
        infoModePembayaran.innerHTML = `
            <div class="flex items-start gap-3">
                <i class="bi bi-exclamation-triangle-fill text-orange-600 text-xl mt-1"></i>
                <div>
                    <p class="font-bold text-orange-800">Pelunasan Wajib</p>
                    <p class="text-sm text-orange-700 mt-1">Pesanan sudah siap diambil. Pembayaran harus lunas sesuai total tagihan.</p>
                </div>
            </div>`;

        infoPembayaran.innerHTML = `
            <i class="bi bi-info-circle-fill text-orange-500"></i>
            Masukkan nominal sesuai sisa bayar untuk melunasi transaksi`;

        document.getElementById('jumlahBayarDisplay').value = formatRupiah(sisaBayar);
        document.getElementById('jumlahBayar').value        = sisaBayar;

    } else if (bolehDP) {
        modalTitle.textContent   = 'Pembayaran (DP/Lunas)';
        labelNominal.textContent = 'Masukkan Nominal Pembayaran';

        infoModePembayaran.className = 'p-4 rounded-xl border-2 bg-blue-50 border-blue-400';
        infoModePembayaran.innerHTML = `
            <div class="flex items-start gap-3">
                <i class="bi bi-info-circle-fill text-blue-600 text-xl mt-1"></i>
                <div>
                    <p class="font-bold text-blue-800">DP atau Lunas</p>
                    <p class="text-sm text-blue-700 mt-1">Anda dapat membayar DP atau langsung melunasi. Kosongkan untuk "Belum Bayar".</p>
                </div>
            </div>`;

        infoPembayaran.innerHTML = `
            <i class="bi bi-info-circle-fill text-yellow-500"></i>
            Masukkan nominal DP atau lunas. Kosongkan untuk status "Belum Bayar"`;
    }

    document.getElementById('modalBayar').classList.remove('hidden');
}

// ==============================
// MODAL BAYAR - TUTUP
// ==============================
function closeModalBayar() {
    document.getElementById('modalBayar').classList.add('hidden');
}

// ==============================
// FORM BAYAR - SUBMIT
// ==============================
document.getElementById('formBayar').addEventListener('submit', function (e) {
    e.preventDefault();

    const jumlahBayar = parseInt(document.getElementById('jumlahBayar').value) || 0;

    if (jumlahBayar < 0) {
        Swal.fire({
            icon: 'error',
            title: 'Nominal Tidak Valid',
            text: 'Nominal pembayaran tidak boleh negatif!',
            confirmButtonColor: '#ef4444',
            customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-6 py-3 font-bold' }
        });
        return;
    }

    if (jumlahBayar > sisaBayar) {
        Swal.fire({
            icon: 'warning',
            title: 'Nominal Melebihi Tagihan',
            html: `<div class="text-gray-600">Sisa bayar: <strong>Rp ${formatRupiah(sisaBayar)}</strong><br>Nominal yang Anda masukkan melebihi sisa tagihan!</div>`,
            confirmButtonColor: '#f59e0b',
            customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-6 py-3 font-bold' }
        });
        return;
    }

    if (harusPelunasan && jumlahBayar < sisaBayar) {
        Swal.fire({
            icon: 'error',
            title: 'Pelunasan Wajib',
            html: `
                <div class="text-gray-600">
                    <p class="mb-2">Pesanan sudah <strong class="text-orange-600">siap diambil</strong>.</p>
                    <p>Pembayaran harus <strong class="text-orange-600">lunas</strong> sesuai total tagihan:</p>
                    <p class="text-xl font-bold text-orange-600 mt-3">Rp ${formatRupiah(sisaBayar)}</p>
                </div>`,
            confirmButtonColor: '#f59e0b',
            customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-6 py-3 font-bold' }
        });
        return;
    }

    if (jumlahBayar === 0 && bolehDP) {
        Swal.fire({
            title: 'Konfirmasi',
            html: '<div class="text-gray-600">Anda tidak memasukkan pembayaran.<br>Status pembayaran akan tetap <strong class="text-red-600">BELUM BAYAR</strong>.<br><br>Lanjutkan?</div>',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Lanjutkan',
            cancelButtonText: 'Batal',
            reverseButtons: true,
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) prosesSubmitPembayaran(jumlahBayar);
        });
        return;
    }

    prosesSubmitPembayaran(jumlahBayar);
});

// ==============================
// PROSES SUBMIT PEMBAYARAN (AJAX)
// ==============================
function prosesSubmitPembayaran(jumlahBayar) {
    Swal.fire({
        title: 'Memproses Pembayaran...',
        html: 'Mohon tunggu sebentar',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading()
    });

    const formData = new FormData(document.getElementById('formBayar'));

    fetch(routeBayar, {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return response.json();
    })
    .then(data => {
        Swal.close();

        if (data.success) {
            dpTerbayar       = data.dp_terbayar  || (dpTerbayar + jumlahBayar);
            sisaBayar        = data.sisa_bayar    || (totalTagihan - dpTerbayar);
            currentStatusBayar = data.status_bayar || currentStatusBayar;

            updatePaymentUI();
            closeModalBayar();

            if (jumlahBayar === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Transaksi Disimpan',
                    html: `
                        <div class="text-left space-y-2 mt-4">
                            <div class="flex justify-between p-4 bg-gray-50 rounded-lg border border-gray-200">
                                <span class="text-gray-600 font-semibold">Status Pembayaran:</span>
                                <span class="font-bold text-red-600 uppercase">Belum Bayar</span>
                            </div>
                            <div class="flex justify-between p-3 bg-red-50 rounded-lg">
                                <span class="text-gray-600">Sisa Bayar:</span>
                                <span class="font-bold text-red-700">Rp ${formatRupiah(sisaBayar)}</span>
                            </div>
                        </div>`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#6b7280',
                    customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-6 py-3 font-bold' }
                });
            } else {
                Swal.fire({
                    icon: 'success',
                    title: 'Pembayaran Berhasil!',
                    html: `
                        <div class="text-left space-y-2 mt-4">
                            <div class="flex justify-between p-3 bg-yellow-50 rounded-lg">
                                <span class="text-gray-600">Jumlah Bayar:</span>
                                <span class="font-bold">Rp ${formatRupiah(jumlahBayar)}</span>
                            </div>
                            <div class="flex justify-between p-3 bg-orange-50 rounded-lg">
                                <span class="text-gray-600">Total DP:</span>
                                <span class="font-bold text-orange-700">Rp ${formatRupiah(dpTerbayar)}</span>
                            </div>
                            <div class="flex justify-between p-3 bg-orange-50 rounded-lg">
                                <span class="text-gray-600">Sisa Bayar:</span>
                                <span class="font-bold text-orange-700">Rp ${formatRupiah(sisaBayar)}</span>
                            </div>
                            <div class="flex justify-between p-4 bg-orange-50 rounded-lg border-2 ${sisaBayar <= 0 ? 'border-orange-500' : 'border-orange-300'}">
                                <span class="text-gray-600 font-semibold">Status:</span>
                                <span class="font-bold ${sisaBayar <= 0 ? 'text-orange-600' : 'text-yellow-600'} uppercase">${currentStatusBayar}</span>
                            </div>
                        </div>`,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#f97316',
                    customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-6 py-3 font-bold' }
                }).then(() => {
                    if (sisaBayar <= 0) location.reload();
                });
            }
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Pembayaran Gagal',
                text: data.message || 'Terjadi kesalahan saat memproses pembayaran',
                confirmButtonColor: '#ef4444',
                customClass: { popup: 'rounded-2xl', confirmButton: 'rounded-xl px-6 py-3 font-bold' }
            });
        }
    })
    .catch(error => {
        Swal.close();
        console.error('Error:', error);
        Swal.fire({
            icon: 'error',
            title: 'Terjadi Kesalahan',
            html: `
                <div class="text-gray-600">
                    <p>Gagal menghubungi server.</p>
                    <p class="text-sm mt-2 text-red-600">${error.message}</p>
                    <p class="text-sm mt-2">Data mungkin sudah tersimpan. Silakan refresh halaman untuk memastikan.</p>
                </div>`,
            confirmButtonText: 'Refresh Halaman',
            showCancelButton: true,
            cancelButtonText: 'Tutup',
            confirmButtonColor: '#f59e0b',
            cancelButtonColor: '#6b7280',
            customClass: {
                popup: 'rounded-2xl',
                confirmButton: 'rounded-xl px-6 py-3 font-bold',
                cancelButton: 'rounded-xl px-6 py-3 font-bold'
            }
        }).then((result) => {
            if (result.isConfirmed) location.reload();
        });
    });
}

// ==============================
// UPDATE UI SETELAH PEMBAYARAN
// ==============================
function updatePaymentUI() {
    const dpDisplay    = document.getElementById('dpDisplay');
    const dpAmount     = document.getElementById('dpAmount');
    const modalDpDisplay = document.getElementById('modalDpDisplay');
    const modalDpAmount  = document.getElementById('modalDpAmount');

    if (dpTerbayar > 0) {
        dpDisplay.classList.remove('hidden');
        modalDpDisplay.classList.remove('hidden');
        dpAmount.textContent      = 'Rp ' + formatRupiah(dpTerbayar);
        modalDpAmount.textContent = 'Rp ' + formatRupiah(dpTerbayar);
    }

    document.getElementById('sisaBayarDisplay').textContent  = 'Rp ' + formatRupiah(sisaBayar);
    document.getElementById('jumlahBayarDisplay').value      = formatRupiah(sisaBayar);
    document.getElementById('jumlahBayar').value             = sisaBayar;

    const statusEl = document.getElementById('statusBayarDisplay');
    statusEl.className = 'inline-block px-5 py-3 rounded-xl capitalize font-bold w-full text-center';

    if (currentStatusBayar === 'lunas') {
        statusEl.classList.add('bg-orange-100', 'text-orange-700', 'border', 'border-orange-200');
        statusEl.textContent = 'Lunas';
        document.getElementById('btnBayarSekarang').classList.add('hidden');
    } else if (currentStatusBayar === 'DP') {
        statusEl.classList.add('bg-yellow-100', 'text-yellow-700', 'border', 'border-yellow-200');
        statusEl.textContent = 'DP';
    } else {
        statusEl.classList.add('bg-red-100', 'text-red-700', 'border', 'border-red-200');
        statusEl.textContent = 'Belum Bayar';
    }
}

// ==============================
// KONFIRMASI BATAL
// ==============================
function confirmBatal() {
    Swal.fire({
        title: 'Batalkan Transaksi?',
        html: `<div class="text-gray-600">Transaksi <strong>TRX/${idTransaksi}</strong> akan dibatalkan.<br>Tindakan ini tidak dapat diurungkan.</div>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-x-lg"></i> Ya, Batalkan!',
        cancelButtonText: 'Tidak',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Membatalkan...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
            document.getElementById('formBatal').submit();
        }
    });
}

// ==============================
// KONFIRMASI HAPUS
// ==============================
function confirmHapus() {
    Swal.fire({
        title: 'Hapus Transaksi?',
        html: `<div class="text-gray-600">Transaksi <strong>TRX/${idTransaksi}</strong> akan dihapus permanen.<br><span class="text-red-600 font-semibold">Data tidak dapat dikembalikan!</span></div>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="bi bi-trash-fill"></i> Ya, Hapus!',
        cancelButtonText: 'Batal',
        reverseButtons: true,
        customClass: {
            popup: 'rounded-2xl',
            confirmButton: 'rounded-xl px-6 py-3 font-bold shadow-lg',
            cancelButton: 'rounded-xl px-6 py-3 font-bold'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Menghapus...',
                html: 'Mohon tunggu sebentar',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => Swal.showLoading()
            });
            document.getElementById('formHapus').submit();
        }
    });
}