function printNota() {
    const notaEl = document.querySelector('.nota-wrapper');
    const notaHTML = notaEl.outerHTML;

    // Buat overlay
    const overlay = document.createElement('div');
    overlay.id = 'print-overlay';
    overlay.style.cssText = `
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
    `;

    // Buat popup box
    const box = document.createElement('div');
    box.style.cssText = `
        background: #fff;
        border-radius: 10px;
        padding: 16px;
        max-height: 90vh;
        overflow-y: auto;
        position: relative;
        box-shadow: 0 8px 32px rgba(0,0,0,0.3);
    `;

    // Tombol di dalam popup
    box.innerHTML = `
        <div style="display:flex; gap:6px; margin-bottom:12px; justify-content:center;">
            <button onclick="cetakDariPopup()" style="
                background:#f59e0b; color:#1a1a1a; border:none;
                padding:8px 16px; border-radius:6px; font-weight:bold;
                cursor:pointer; font-size:12px;
            ">🖨️ Print / Simpan PDF</button>
            <button onclick="tutupPopup()" style="
                background:#6b7280; color:#fff; border:none;
                padding:8px 16px; border-radius:6px; font-weight:bold;
                cursor:pointer; font-size:12px;
            ">✕ Tutup</button>
        </div>
        ${notaHTML}
    `;

    overlay.appendChild(box);
    document.body.appendChild(overlay);

    // Tutup kalau klik di luar box
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) tutupPopup();
    });
}

function cetakDariPopup() {
    const notaEl = document.querySelector('#print-overlay .nota-wrapper');
    const notaHTML = notaEl.outerHTML;

    const popup = window.open('', '_blank', 'width=400,height=700');
    popup.document.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <title>Cetak Nota</title>
            <link rel="stylesheet" href="${window.location.origin}/css/cetak-nota.css">
        </head>
        <body style="background:#fff; padding:10px;">
            ${notaHTML}
            <script>
                window.onload = function() {
                    setTimeout(() => { window.print(); window.close(); }, 300);
                }
            <\/script>
        </body>
        </html>
    `);
    popup.document.close();
}

function tutupPopup() {
    const overlay = document.getElementById('print-overlay');
    if (overlay) overlay.remove();
}

function closeNota() {
    if (window.history.length > 1) {
        window.history.back();
    } else {
        window.close();
    }
}