
function printNota() {
    window.print();
}

/**
 * Tutup tab / kembali ke halaman sebelumnya.
 */
function closeNota() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.close();
    }
}

/**
 * Auto-trigger print dialog saat halaman selesai dimuat.
 * Diberi delay 300ms agar rendering selesai dulu.
 */
window.addEventListener('load', function () {
    setTimeout(function () {
        window.print();
    }, 300);
});