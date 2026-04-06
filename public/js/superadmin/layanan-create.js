document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formLayanan');

    if (form) {
        form.addEventListener('submit', function (e) {
            const namaLayanan = form.querySelector('input[name="nama_layanan"]');
            const prosesChecked = form.querySelectorAll('input[name="proses[]"]:checked');

            // Validasi nama layanan
            if (!namaLayanan.value.trim()) {
                e.preventDefault();
                alert('Nama layanan tidak boleh kosong.');
                namaLayanan.focus();
                return;
            }

            // Validasi minimal 1 proses dipilih
            if (prosesChecked.length === 0) {
                e.preventDefault();
                alert('Pilih minimal 1 proses layanan.');
                return;
            }
        });
    }

    // Highlight checkbox jenis lama saat di-klik
    const checkboxLabels = document.querySelectorAll('input[name="jenis_lama[]"]');
    checkboxLabels.forEach(function (cb) {
        cb.addEventListener('change', function () {
            const label = cb.closest('label');
            if (cb.checked) {
                label.classList.add('bg-yellow-50', 'border-yellow-400');
            } else {
                label.classList.remove('bg-yellow-50', 'border-yellow-400');
            }
        });

        // Sync state awal (untuk old() yang sudah checked)
        if (cb.checked) {
            const label = cb.closest('label');
            label.classList.add('bg-yellow-50', 'border-yellow-400');
        }
    });
});