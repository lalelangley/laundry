// FE-DOC: Script frontend untuk public/js/kasir/dashboard.js. Komentar dipakai untuk menandai file ini sebagai bagian dari interaksi UI dan helper JavaScript project.

// dashboard.js
// Semua logic dashboard: DataTable, notifikasi, reminder popup, modal

// ════════════════════════════════════════
// INJECT CSS ANIMATIONS (replaces dashboard.css)
// ════════════════════════════════════════
// FE-DOC: IIFE dipakai untuk membungkus logic agar variabel helper tidak bocor ke scope global.
(function injectStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes slide-in {
            from { transform: translateX(400px); opacity: 0; }
            to   { transform: translateX(0);     opacity: 1; }
        }
        @keyframes fade-in {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes scale-in {
            from { opacity: 0; transform: scale(0.9) translateY(-20px); }
            to   { opacity: 1; transform: scale(1)   translateY(0);     }
        }
        .animate-slide-in { animation: slide-in  0.3s ease-out; }
        .animate-fade-in  { animation: fade-in   0.3s ease-out; }
        .animate-scale-in { animation: scale-in  0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
    `;
    document.head.appendChild(style);
})();

// ════════════════════════════════════════
// INIT — entry point
// ════════════════════════════════════════
$(document).ready(function () {
    initDataTable();
    initReminderPopup();
    initEventListeners();
});

// ════════════════════════════════════════
// DATATABLE
// ════════════════════════════════════════
let orderTable;

// FE-DOC: Function berikut mengelola bagian interaksi UI atau helper data sesuai nama tanggung jawabnya.
function initDataTable() {
    orderTable = $('#orderTable').DataTable({
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],
        ordering:   true,
        searching:  true,
        destroy:    true,
        order:      [[1, 'desc']],
        language: {
            search:       'Cari:',
            lengthMenu:   'Tampilkan _MENU_ data',
            info:         'Menampilkan _START_ sampai _END_ dari _TOTAL_ transaksi',
            infoEmpty:    'Menampilkan 0 sampai 0 dari 0 transaksi',
            infoFiltered: '(difilter dari _MAX_ total transaksi)',
            paginate: {
                first: 'Pertama', last: 'Terakhir',
                next: 'Selanjutnya', previous: 'Sebelumnya',
            },
            emptyTable: 'Tidak ada data transaksi',
        },
        initComplete: function () {
            $('div.dataTables_filter input').addClass(
                'border-2 border-gray-300 rounded-xl px-4 py-3 ml-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition'
            );
            $('div.dataTables_length select').addClass(
                'border-2 border-gray-300 rounded-xl px-4 py-2.5 mr-2 focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition'
            );
            $('.dataTables_filter').append(`
                <button onclick="resetTableFilter()"
                        class="ml-3 px-4 py-2.5 bg-gradient-to-r from-gray-400 to-gray-500 hover:from-gray-500 hover:to-gray-600 text-white rounded-xl font-semibold transition-all hover:shadow-lg">
                    <i class="bi bi-arrow-clockwise mr-1"></i> Reset Filter
                </button>
            `);
            styleTablePagination();
        },
        drawCallback: function () {
            styleTablePagination();
        },
    });
}

function styleTablePagination() {
    setTimeout(() => {
        $('.dataTables_paginate a').addClass(
            'px-4 py-2.5 rounded-xl border-2 border-gray-200 bg-white hover:bg-yellow-50 hover:border-yellow-400 transition text-sm font-semibold mx-1'
        );
        $('.dataTables_paginate .current').addClass(
            'bg-gradient-to-r from-yellow-400 to-amber-500 text-gray-900 border-yellow-500 font-bold shadow-md'
        );
    }, 100);
}

// ════════════════════════════════════════
// NOTIFICATION PANEL
// ════════════════════════════════════════
function toggleNotifications() {
    document.getElementById('notificationPanel').classList.toggle('-translate-y-full');
}

function handleNotification(type) {
    toggleNotifications();

    $.fn.dataTable.ext.search = [];
    orderTable.columns().search('');

    const filters = {
        terlambat:   (data) => {
            if (!data[4].includes('Online')) return false;
            if (!['Antrian','Proses','Selesai Dicuci'].some(s => data[7].includes(s))) return false;
            return isBeforeToday(data[13]);
        },
        pickup:      (data, row) => data[4].includes('Online') && $(row).find('td:eq(1)').html()?.includes('Pickup'),
        antar:       (data, row) => data[4].includes('Online') && $(row).find('td:eq(1)').html()?.includes('Antar'),
        masuk:       (data) => data[4].includes('Online') && data[7].includes('Antrian'),
        belum_lunas: (data) => data[4].includes('Online') && data[6].includes('Belum Lunas'),
        deadline:    (data) => {
            if (!data[4].includes('Online')) return false;
            if (!['Antrian','Proses','Selesai Dicuci'].some(s => data[7].includes(s))) return false;
            return isToday(data[13]);
        },
        lunas:       (data) => data[4].includes('Online') && data[6].includes('Lunas'),
        siap_ambil:  (data) => data[4].includes('Online') && data[7].includes('Siap Ambil'),
    };

    const fn = filters[type];
    if (fn) {
        $.fn.dataTable.ext.search.push((settings, data, dataIndex) =>
            fn(data, orderTable.row(dataIndex).node())
        );
    }

    const toastColor = {
        terlambat: 'red', pickup: 'orange', antar: 'orange',
        masuk: 'yellow', belum_lunas: 'yellow', deadline: 'yellow',
        lunas: 'green', siap_ambil: 'green',
    };

    const msg = window.DASHBOARD_DATA.toastMessages[type];
    if (msg) showToast(msg, toastColor[type] ?? 'yellow');

    orderTable.draw();
    $('html, body').animate({ scrollTop: $('#orderTable').offset().top - 100 }, 500);
}

function resetTableFilter() {
    $.fn.dataTable.ext.search = [];
    orderTable.columns().search('');
    orderTable.search('');
    orderTable.draw();
    showToast('Filter direset — menampilkan ' + orderTable.rows().count() + ' transaksi', 'green');
}

// ════════════════════════════════════════
// REMINDER POPUP
// ════════════════════════════════════════
function initReminderPopup() {
    const { userId, notifications } = window.DASHBOARD_DATA;
    const storageKey = `reminderDismissed_${userId}`;
    const today      = new Date().toDateString();
    const dismissed  = localStorage.getItem(storageKey);

    if (dismissed) {
        if (dismissed !== today) localStorage.removeItem(storageKey);
        else return;
    }

    const hasNotif = Object.values(notifications).some(v => v > 0);
    if (hasNotif) setTimeout(showReminderPopup, 1000);
}

function showReminderPopup() {
    const popup = document.getElementById('reminderPopup');
    if (popup) {
        popup.classList.remove('hidden');
        playNotificationSound();
    }
}

function closeReminderPopup() {
    const popup = document.getElementById('reminderPopup');
    if (!popup) return;
    popup.style.opacity = '0';
    setTimeout(() => { popup.classList.add('hidden'); popup.style.opacity = '1'; }, 300);
}

function handleReminderAction() {
    const checkbox   = document.getElementById('dontShowAgain');
    const storageKey = `reminderDismissed_${window.DASHBOARD_DATA.userId}`;
    if (checkbox?.checked) localStorage.setItem(storageKey, new Date().toDateString());
    closeReminderPopup();
}

function playNotificationSound() {
    const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbq2EcBj+a2/LDciUFLIHO8tiJNwgZaLvt559NEAxQp+PwtmMcBjiR1/LMeSwFJHfH8N2QQAoUXrTp66hVFApGn+DyvmwhBSuAyvLTgjMGHm7A7+OZSA8PVqzn77BdGAg+ltzy0H8pBSh+zPDckT0KE2S36+mlThAPTKXh8L1pIAUrgM3z1YU1Bx1tv+/nm0sOD1Om4/C4ZRsGN5DY8tCBKwUle8rx34pGCRNjuuzrpE4RDkuq4/K+byEELYPO89WGNgcfcMPx6qBJDg5TqeXyt2McBTmQ1/PMfS0GJ37M8+CQPwsRZL3u66VTEw1Jqt/yvnAkBSyBzvTWhzYHH3HE8eqhSQ4OUqnl8rZlHQU5kdfy0oExBSiAyvLdkD0LElyz7OumUxMMSbDh8rxuIAQugM/01YY2Bx5xxPHqoUkODlSp5fK3YxwGOJLX8tKBMwQnf8rx3ZA9CxJctOzrplQTDEmy4fK8cCAFLoHO89WGNgceXb/w6qFJDg9Tp+Pyt2QcBjiS1/LSgTMEJ4DK8t2QPAsTW7Xs66ZUFA1JtuLyu2wgBSuB0PPUhzYGHl/A8OmhSQ4PUqfl8rJiHAU4k9byy4AzBSZ9y/LdjkALE12z7OumUxQMSrfh8rpuIQUsgc/z04c2Bx1ov/Dqn0kOD1Op5fK1YxwGN5PX8sl/MwUmfsrx3Y8+CxNdu+zrpVMUDUm14fK6biEFLIHP89OHNgcdX8Hw6Z9KDQ9Tp+Xys2McBjeR1/LJfzMFJn7K8d2OPwsUW7vs66ZUEw1KteLyumwgBSyB0PPUhjYHHmC/8OmgSQ0PUqnm8rJhHAU4ktjyzH8zBSd+yvLckD4LFVuy7OumVRQNSrLi8rlsIAUsgs/z1IY2Bx5gwPDon0kOEFGp5vKxYRwFOJLY8syAMwUnfsrx3I88DBVas+zrplQUDUqy4vK5biEFLYLO89SHNgceX8Hx559JDhBRqObysmAbBTiR2PLMgDMEJ37K8d2PPQsVW7Lr66ZVEg1JsuHyt2whBS2Cz/PUhjYHHl/B8OefSQ4QUanm8rFgHAU4kdfy0n8zBCd+y/HdjkAMFFuy7OulUxQOSrLh8rdsIQUtg87z04c2Bx1fwfDnn0sOD1Go5vKwYRwEOJHX8sZ/MwQnf8rx3I9ADBNZ7OulUxQOSrLh8rdsIQU=');
    audio.volume = 0.3;
    audio.play().catch(() => {});
}

// ════════════════════════════════════════
// DELIVERY MODAL
// ════════════════════════════════════════
function showDeliveryInfo(transaksiId) {
    document.getElementById('modalTransaksiId').textContent = transaksiId;
    document.getElementById('deliveryModal').classList.remove('hidden');

    fetch(`/api/delivery-info/${transaksiId}`)
        .then(r => r.json())
        .then(data => {
            document.getElementById('deliveryContent').innerHTML = buildDeliveryHTML(data);
        })
        .catch(() => {
            document.getElementById('deliveryContent').innerHTML = `
                <div class="text-center py-10">
                    <i class="bi bi-exclamation-triangle text-5xl text-red-400 mb-3"></i>
                    <p class="text-red-600 font-semibold">Gagal memuat data delivery</p>
                </div>`;
        });
}

function buildDeliveryHTML(data) {
    if (!data.deliveries?.length) return `
        <div class="text-center py-10">
            <i class="bi bi-inbox text-5xl text-gray-300 mb-3"></i>
            <p class="text-gray-500 font-semibold">Belum ada data pickup/delivery</p>
        </div>`;

    const statusMap = {
        pending:                { bg:'bg-yellow-100', text:'text-yellow-800', label:'Pending',            icon:'bi-clock' },
        accepted:               { bg:'bg-green-100',  text:'text-green-800',  label:'Diterima',           icon:'bi-check-circle' },
        on_the_way_to_pickup:   { bg:'bg-orange-100', text:'text-orange-800', label:'Menuju Pickup',      icon:'bi-truck' },
        picked_up:              { bg:'bg-orange-100', text:'text-orange-800', label:'Sudah Pickup',       icon:'bi-check' },
        on_the_way_to_deliver:  { bg:'bg-orange-100', text:'text-orange-800', label:'Menuju Antar',       icon:'bi-truck' },
        on_the_way_to_customer: { bg:'bg-orange-100', text:'text-orange-800', label:'Menuju Pelanggan',   icon:'bi-truck' },
        arrived_at_customer:    { bg:'bg-yellow-100', text:'text-yellow-800', label:'Sampai Pelanggan',   icon:'bi-geo-alt' },
        on_the_way_to_laundry:  { bg:'bg-orange-100', text:'text-orange-800', label:'Menuju Laundry',     icon:'bi-arrow-left-right' },
        arrived_at_laundry:     { bg:'bg-green-100',  text:'text-green-800',  label:'Sampai Laundry',     icon:'bi-house-check' },
        delivered:              { bg:'bg-green-100',  text:'text-green-800',  label:'Terkirim',           icon:'bi-check-circle-fill' },
        failed:                 { bg:'bg-red-100',    text:'text-red-800',    label:'Gagal',              icon:'bi-x-circle' },
    };

    return data.deliveries.map(d => {
        const isPickup = d.jenis === 'pickup';
        const s = statusMap[d.status] ?? { bg:'bg-gray-100', text:'text-gray-800', label: d.status || 'Unknown', icon:'bi-question-circle' };
        const driverHtml = d.id_driver
            ? `<div class="flex items-center gap-2 text-sm text-gray-700"><i class="bi bi-person-badge"></i><span>Driver ID: ${d.id_driver}</span></div>`
            : `<div class="flex items-center gap-2 text-sm text-red-600"><i class="bi bi-exclamation-triangle-fill"></i><span class="font-semibold">Belum Ada Driver</span></div>`;
        const waktuHtml = d.waktu
            ? `<div class="flex items-center gap-2 text-sm text-gray-700"><i class="bi bi-clock-fill text-gray-400"></i><span>${new Date(d.waktu).toLocaleString('id-ID')}</span></div>`
            : '';
        return `
            <div class="bg-gradient-to-r from-orange-400 to-orange-500 rounded-2xl p-1 shadow-lg">
                <div class="bg-white rounded-xl p-5">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-gradient-to-r from-orange-400 to-orange-500 rounded-xl flex items-center justify-center">
                                <i class="bi ${isPickup ? 'bi-box-arrow-in-down' : 'bi-box-arrow-up'} text-white text-xl"></i>
                            </div>
                            <div>
                                <div class="font-bold text-lg text-gray-900">${isPickup ? 'PICKUP' : 'ANTAR'}</div>
                                <div class="text-xs text-gray-500">ID: ${d.id_delivery}</div>
                            </div>
                        </div>
                        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full ${s.bg} ${s.text} text-xs font-semibold">
                            <i class="bi ${s.icon}"></i> ${s.label}
                        </span>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-start gap-2 text-sm">
                            <i class="bi bi-geo-alt-fill text-gray-400 mt-0.5"></i>
                            <div><div class="text-gray-500 text-xs">Alamat Tujuan</div>
                            <div class="font-semibold text-gray-900">${d.alamat_tujuan || '-'}</div></div>
                        </div>
                        ${driverHtml}${waktuHtml}
                    </div>
                </div>
            </div>`;
    }).join('');
}

function closeDeliveryModal() {
    document.getElementById('deliveryModal').classList.add('hidden');
}

// ════════════════════════════════════════
// BUKTI MODAL
// ════════════════════════════════════════
function showBuktiImage(url) {
    document.getElementById('buktiImage').src = url;
    document.getElementById('buktiModal').classList.remove('hidden');
}

function closeBuktiModal() {
    document.getElementById('buktiModal').classList.add('hidden');
}

// ════════════════════════════════════════
// TOAST
// ════════════════════════════════════════
function showToast(message, color) {
    const colors = { red:'bg-red-500', orange:'bg-orange-500', yellow:'bg-yellow-500', green:'bg-green-500' };
    const icons  = { red:'bi-exclamation-triangle-fill', orange:'bi-truck', yellow:'bi-info-circle-fill', green:'bi-check-circle-fill' };
    const toast  = $(`
        <div class="fixed bottom-4 right-4 ${colors[color]??colors.yellow} text-white px-6 py-4 rounded-2xl shadow-2xl z-50 flex items-center gap-3 animate-slide-in max-w-md">
            <i class="bi ${icons[color]??icons.yellow} text-2xl flex-shrink-0"></i>
            <div class="flex-1">
                <div class="font-bold text-sm mb-1">Filter Diterapkan</div>
                <span class="text-sm">${message}</span>
            </div>
            <button onclick="$(this).parent().remove()" class="ml-2 hover:bg-white/20 rounded-lg p-1 flex-shrink-0">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>`);
    $('body').append(toast);
    setTimeout(() => toast.fadeOut(300, function(){ $(this).remove(); }), 5000);
}

// ════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════
function parseTableDate(str) {
    if (!str || str === '-') return null;
    const p = str.split('/');
    return p.length === 3 ? new Date(p[2], p[1]-1, p[0]) : null;
}
function isBeforeToday(str) {
    const d = parseTableDate(str); if (!d) return false;
    const t = new Date(); t.setHours(0,0,0,0); return d < t;
}
function isToday(str) {
    const d = parseTableDate(str); if (!d) return false;
    const t = new Date(); t.setHours(0,0,0,0); d.setHours(0,0,0,0);
    return d.getTime() === t.getTime();
}

// ════════════════════════════════════════
// EVENT LISTENERS
// ════════════════════════════════════════
function initEventListeners() {
    document.getElementById('deliveryModal')?.addEventListener('click', function(e) {
        if (e.target === this) closeDeliveryModal();
    });
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') { closeBuktiModal(); closeDeliveryModal(); closeReminderPopup(); }
    });
    document.addEventListener('click', e => {
        const panel   = document.getElementById('notificationPanel');
        const bellBtn = e.target.closest('button[onclick="toggleNotifications()"]');
        if (!panel.classList.contains('-translate-y-full') && !panel.contains(e.target) && !bellBtn) {
            toggleNotifications();
        }
    });
}
