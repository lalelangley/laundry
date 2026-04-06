// public/js/admin/layanan.js

// ════════════════════════════════════════
// INJECT CSS ANIMATIONS
// ════════════════════════════════════════
(function injectStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }
        .shake { animation: shake 0.5s; }

        @keyframes fadeIn {
            from { opacity: 0; transform: scale(0.9); }
            to   { opacity: 1; transform: scale(1); }
        }
        .alert-modal { animation: fadeIn 0.3s ease; }
    `;
    document.head.appendChild(style);
})();

// ════════════════════════════════════════
// INIT
// ════════════════════════════════════════
document.addEventListener("DOMContentLoaded", () => {
    const modal               = document.getElementById("modalLayanan");
    const modalTitle          = document.getElementById("modalTitle");
    const qtyInput            = document.getElementById("qtyInput");
    const parfumSelect        = document.getElementById("parfumSelect");
    const btnSave             = document.getElementById("btnSave");
    const modalDuplicate      = document.getElementById("modalDuplicate");
    const btnConfirmDuplicate = document.getElementById("btnConfirmDuplicate");
    const modalDelete         = document.getElementById("modalDelete");
    const formDelete          = document.getElementById("formDelete");

    const { addJenisTransaksiUrl, from, idTransaksi, transaksiCreateUrl } = window.LAYANAN_DATA;

    // ════════════════════════════════════════
    // CUSTOM ALERT
    // ════════════════════════════════════════
    function showAlert(type, title, message) {
        const alertModal   = document.getElementById('alertModal');
        const alertHeader  = document.getElementById('alertHeader');
        const alertIcon    = document.getElementById('alertIcon');
        const alertTitle   = document.getElementById('alertTitle');
        const alertMessage = document.getElementById('alertMessage');
        const alertButton  = document.getElementById('alertButton');

        alertIcon.className   = 'w-16 h-16 rounded-full flex items-center justify-center';
        alertButton.className = 'w-full py-3 rounded-xl font-bold shadow-md hover:shadow-lg transition-all active:scale-95';

        const config = {
            warning: {
                header: 'p-6 flex items-center justify-center bg-gradient-to-br from-yellow-50 to-orange-50',
                icon:   ['bg-gradient-to-br', 'from-yellow-400', 'to-orange-500', 'shadow-lg'],
                html:   '<i class="bi bi-exclamation-triangle-fill text-white text-3xl"></i>',
                btn:    ['bg-gradient-to-r', 'from-yellow-400', 'to-orange-500', 'text-white'],
            },
            error: {
                header: 'p-6 flex items-center justify-center bg-gradient-to-br from-red-50 to-pink-50',
                icon:   ['bg-gradient-to-br', 'from-red-500', 'to-pink-600', 'shadow-lg'],
                html:   '<i class="bi bi-x-circle-fill text-white text-3xl"></i>',
                btn:    ['bg-gradient-to-r', 'from-red-500', 'to-pink-600', 'text-white'],
            },
            info: {
                header: 'p-6 flex items-center justify-center bg-gradient-to-br from-blue-50 to-cyan-50',
                icon:   ['bg-gradient-to-br', 'from-blue-500', 'to-cyan-600', 'shadow-lg'],
                html:   '<i class="bi bi-info-circle-fill text-white text-3xl"></i>',
                btn:    ['bg-gradient-to-r', 'from-blue-500', 'to-cyan-600', 'text-white'],
            },
        };

        const c = config[type] ?? config.info;
        alertHeader.className = c.header;
        alertIcon.classList.add(...c.icon);
        alertIcon.innerHTML      = c.html;
        alertTitle.textContent   = title;
        alertMessage.textContent = message;
        alertButton.classList.add(...c.btn);
        alertModal.classList.remove('hidden');

        alertButton.onclick = () => {
            alertModal.classList.add('hidden');
            if (type === 'warning' || type === 'error') setTimeout(() => qtyInput.focus(), 100);
        };
        alertModal.onclick = (e) => {
            if (e.target === alertModal) alertModal.classList.add('hidden');
        };
    }

    // ════════════════════════════════════════
    // VALIDASI QTY INPUT
    // ════════════════════════════════════════
    qtyInput.addEventListener('input', function (e) {
        let value = e.target.value.replace(/[^\d.,]/g, '').replace(',', '.');
        const parts = value.split('.');
        if (parts.length > 2) value = parts[0] + '.' + parts.slice(1).join('');
        if (value.length > 1 && value[0] === '0' && value[1] !== '.') value = value.replace(/^0+/, '');
        if (parts.length === 2 && parts[1].length > 2) value = parts[0] + '.' + parts[1].substring(0, 2);
        e.target.value = value;
    });

    qtyInput.addEventListener('blur', function (e) {
        const value = parseFloat(e.target.value);
        if (isNaN(value) || value < 0.01) {
            e.target.value = '';
            e.target.classList.add('border-red-500');
        } else {
            e.target.classList.remove('border-red-500');
        }
    });

    qtyInput.addEventListener('paste', function (e) {
        e.preventDefault();
        const paste   = (e.clipboardData || window.clipboardData).getData('text');
        const cleaned = paste.replace(/[^\d.,]/g, '').replace(',', '.');
        const number  = parseFloat(cleaned);
        if (!isNaN(number) && number >= 0.01) e.target.value = number.toString();
    });

    // ════════════════════════════════════════
    // MODAL UTAMA
    // ════════════════════════════════════════
    function openModal(name, id, mode = "transaksi", riwayatId = null) {
        modal.classList.remove("hidden");
        modalTitle.innerText       = name;
        btnSave.dataset.id         = id;
        btnSave.dataset.mode       = mode;
        btnSave.dataset.riwayat    = riwayatId ?? "";
        qtyInput.value             = "";
        qtyInput.classList.remove('border-red-500');
        parfumSelect.value         = "";
        btnSave.disabled           = false;
        btnSave.style.pointerEvents = 'auto';
        setTimeout(() => qtyInput.focus(), 100);
    }

    window.closeModal = function () {
        modal.classList.add("hidden");
    };
    modal.addEventListener("click", e => {
        if (e.target === modal) window.closeModal();
    });

    // ════════════════════════════════════════
    // DUPLIKAT
    // ════════════════════════════════════════
    window.confirmDuplicate = function (url) {
        modalDuplicate.classList.remove("hidden");
        btnConfirmDuplicate.onclick = () => { window.location.href = url; };
    };
    window.closeDuplicateModal = function () {
        modalDuplicate.classList.add("hidden");
    };
    modalDuplicate.addEventListener("click", e => {
        if (e.target === modalDuplicate) window.closeDuplicateModal();
    });

    // ════════════════════════════════════════
    // HAPUS
    // ════════════════════════════════════════
    window.confirmDelete = function (url) {
        modalDelete.classList.remove("hidden");
        formDelete.action = url;
    };
    window.closeDeleteModal = function () {
        modalDelete.classList.add("hidden");
    };
    modalDelete.addEventListener("click", e => {
        if (e.target === modalDelete) window.closeDeleteModal();
    });

    // ════════════════════════════════════════
    // KLIK LAYANAN UTAMA
    // ════════════════════════════════════════
    document.querySelectorAll('.layanan-item').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.closest('.jenis-item') ||
                e.target.closest('.dropdown-area') ||
                e.target.closest('button')) return;

            const idLayanan = card.dataset.id;
            if (from !== "transaksi" && from !== "riwayat") {
                window.location.href = `/admin/layanan/${idLayanan}/edit`;
            }
        });
    });

    // ════════════════════════════════════════
    // KLIK JENIS LAYANAN
    // ════════════════════════════════════════
    document.querySelectorAll('.jenis-item').forEach(item => {
        item.addEventListener('click', e => {
            e.stopPropagation();
            const idLayanan = item.dataset.idLayanan;
            const idJenis   = item.dataset.idJenis;
            const namaJenis = item.dataset.nama;

            if (from === "transaksi" || from === "riwayat") {
                openModal(namaJenis, idJenis, from, idTransaksi || null);
            } else {
                window.location.href = `/admin/layanan/${idLayanan}/edit`;
            }
        });
    });

    // ════════════════════════════════════════
    // DROPDOWN
    // ════════════════════════════════════════
    document.querySelectorAll(".dropdown-area").forEach(area => {
        area.addEventListener("click", e => e.stopPropagation());
    });
    document.querySelectorAll(".dropdown-btn").forEach(btn => {
        btn.addEventListener("click", function (e) {
            e.stopPropagation();
            const menu = this.nextElementSibling;
            document.querySelectorAll(".dropdown-menu").forEach(m => {
                if (m !== menu) m.classList.add("hidden");
            });
            menu.classList.toggle("hidden");
        });
    });
    document.addEventListener("click", () => {
        document.querySelectorAll(".dropdown-menu").forEach(m => m.classList.add("hidden"));
    });

    // ════════════════════════════════════════
    // TOMBOL SIMPAN
    // ════════════════════════════════════════
    if (btnSave) {
        btnSave.addEventListener("click", function (e) {
            e.preventDefault();

            const qtyValue = qtyInput.value.trim();

            if (!qtyValue) {
                qtyInput.classList.add('border-red-500', 'shake');
                setTimeout(() => qtyInput.classList.remove('shake'), 500);
                showAlert('warning', 'Oops! Kuantitas Belum Diisi', 'Mohon isi jumlah kuantitas terlebih dahulu. Minimal 0.01');
                return;
            }

            const qty = parseFloat(qtyValue);

            if (isNaN(qty)) {
                qtyInput.classList.add('border-red-500', 'shake');
                setTimeout(() => qtyInput.classList.remove('shake'), 500);
                showAlert('error', 'Format Tidak Valid!', 'Gunakan format angka yang benar. Contoh: 1, 2.5, atau 10.75');
                return;
            }

            if (qty < 0.01) {
                qtyInput.classList.add('border-red-500', 'shake');
                setTimeout(() => qtyInput.classList.remove('shake'), 500);
                showAlert('warning', 'Kuantitas Terlalu Kecil!', 'Jumlah minimal adalah 0.01. Silakan masukkan nilai yang lebih besar.');
                return;
            }

            qtyInput.classList.remove('border-red-500');

            const parfum    = parfumSelect.value || null;
            const idJenis   = btnSave.dataset.id;
            const mode      = btnSave.dataset.mode;
            const idRiwayat = btnSave.dataset.riwayat;

            if (mode === "riwayat" && !idRiwayat) {
                showAlert('error', 'ID Transaksi Tidak Ditemukan', 'Terjadi kesalahan sistem. Silakan coba lagi.');
                return;
            }

            btnSave.disabled     = true;
            btnSave.textContent  = "Menyimpan...";

            const url = addJenisTransaksiUrl.replace(':id', idJenis);

            fetch(url, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                    "Content-Type": "application/json",
                    "Accept":       "application/json",
                },
                body: JSON.stringify({ qty, parfum }),
            })
            .then(res => res.text().then(text => {
                if (!res.ok) throw new Error(`HTTP ${res.status}: ${text}`);
                try { return JSON.parse(text); }
                catch (e) { throw new Error('Response bukan JSON: ' + text); }
            }))
            .then(data => {
                if (data.success) {
                    const redirectUrl = mode === "transaksi"
                        ? transaksiCreateUrl
                        : `/admin/riwayat/${idRiwayat}/edit`;
                    window.location.href = redirectUrl;
                } else {
                    throw new Error(data.message || 'Gagal menyimpan');
                }
            })
            .catch(err => {
                showAlert('error', 'Gagal Menyimpan!', 'Terjadi kesalahan: ' + err.message);
                btnSave.disabled    = false;
                btnSave.textContent = "Simpan";
            });
        });

        btnSave.disabled = false;
    }

    console.log('✅ layanan.js loaded');
});

// ── SORT ──────────────────────────────────────────────
const sortBtn      = document.getElementById('sortBtn');
const sortDropdown = document.getElementById('sortDropdown');
const sortLabel    = document.getElementById('sortLabel');
const sortOptions  = document.querySelectorAll('.sort-option');
let currentSort    = 'default';

// Toggle dropdown
sortBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    sortDropdown.classList.toggle('hidden');
});

// Tutup kalau klik di luar
document.addEventListener('click', () => {
    sortDropdown.classList.add('hidden');
});

sortOptions.forEach(btn => {
    btn.addEventListener('click', () => {
        currentSort = btn.dataset.sort;

        // Update label
        const labels = { default: 'Sort', az: 'A → Z', za: 'Z → A' };
        sortLabel.textContent = labels[currentSort];

        // Update centang
        sortOptions.forEach(b => b.querySelector('.sort-check').classList.add('hidden'));
        btn.querySelector('.sort-check').classList.remove('hidden');

        sortDropdown.classList.add('hidden');
        applySortAndSearch();
    });
});

// ── SEARCH ────────────────────────────────────────────
document.getElementById('searchInput').addEventListener('input', applySortAndSearch);

function applySortAndSearch() {
    const keyword = document.getElementById('searchInput').value.toLowerCase().trim();
    const list    = document.getElementById('layananList');
    const items   = [...list.querySelectorAll('.layanan-item')];

    // Filter
    items.forEach(item => {
        const name = item.dataset.name.toLowerCase();
        item.style.display = name.includes(keyword) ? '' : 'none';
    });

    // Sort
    const visible = items.filter(i => i.style.display !== 'none');
    visible.sort((a, b) => {
        const nameA = a.dataset.name.toLowerCase();
        const nameB = b.dataset.name.toLowerCase();
        if (currentSort === 'az') return nameA.localeCompare(nameB, 'id');
        if (currentSort === 'za') return nameB.localeCompare(nameA, 'id');
        return 0; // default: urutan asli
    });

    visible.forEach(item => list.appendChild(item));
}