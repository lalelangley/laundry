<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/privacy/telegram-bot.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy Kasmini Laundry Bot</title>
    <!-- FE-DOC: Blok CSS khusus halaman ini. -->
    <style>
        :root {
            --bg: #fff8e6;
            --card: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --accent: #facc15;
            --accent-dark: #ca8a04;
            --border: #fde68a;
        }

        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: Arial, Helvetica, sans-serif;
            background:
                radial-gradient(circle at top left, #fde68a 0%, transparent 28%),
                linear-gradient(180deg, #fffdf5 0%, var(--bg) 100%);
            color: var(--text);
        }
        .wrap {
            max-width: 860px;
            margin: 0 auto;
            padding: 32px 18px 64px;
        }
        .hero {
            background: linear-gradient(135deg, var(--accent) 0%, #f59e0b 100%);
            border-radius: 28px;
            padding: 32px 28px;
            box-shadow: 0 18px 40px rgba(202, 138, 4, 0.18);
        }
        .eyebrow {
            display: inline-block;
            padding: 8px 14px;
            background: rgba(255,255,255,0.28);
            border-radius: 999px;
            font-size: 12px;
            font-weight: 700;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        h1 {
            margin: 18px 0 10px;
            font-size: 34px;
            line-height: 1.15;
        }
        .hero p {
            margin: 0;
            max-width: 620px;
            font-size: 16px;
            line-height: 1.7;
        }
        .card {
            margin-top: 22px;
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 24px;
            padding: 28px 24px;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.06);
        }
        h2 {
            margin: 0 0 12px;
            font-size: 22px;
        }
        p, li {
            color: var(--muted);
            font-size: 15px;
            line-height: 1.75;
        }
        ul {
            margin: 10px 0 0;
            padding-left: 20px;
        }
        .footer {
            margin-top: 22px;
            text-align: center;
            color: var(--muted);
            font-size: 13px;
        }
        .badge-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 14px;
            margin-top: 20px;
        }
        .badge {
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 18px;
            padding: 16px;
        }
        .badge strong {
            display: block;
            margin-bottom: 6px;
            color: var(--accent-dark);
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="wrap">
        <section class="hero">
            <span class="eyebrow">Kasmini Laundry Bot</span>
            <h1>Privacy Policy</h1>
            <p>
                Halaman ini menjelaskan bagaimana bot Telegram Kasmini Laundry menggunakan informasi
                pelanggan untuk mengirim notifikasi transaksi dan informasi layanan.
            </p>
        </section>

        <section class="card">
            <h2>Penggunaan Bot</h2>
            <p>
                Bot Telegram Kasmini Laundry digunakan untuk mengirim ringkasan pembayaran,
                status transaksi, dan informasi layanan yang berkaitan dengan operasional laundry.
            </p>

            <div class="badge-row">
                <div class="badge">
                    <strong>Data yang digunakan</strong>
                    Nama pelanggan, nomor transaksi, status pembayaran, dan detail layanan.
                </div>
                <div class="badge">
                    <strong>Tujuan penggunaan</strong>
                    Mengirim notifikasi transaksi dan memudahkan komunikasi layanan laundry.
                </div>
                <div class="badge">
                    <strong>Pembagian data</strong>
                    Data tidak dibagikan ke pihak ketiga di luar kebutuhan operasional layanan.
                </div>
            </div>
        </section>

        <section class="card">
            <h2>Kebijakan Privasi</h2>
            <ul>
                <li>Data pelanggan digunakan seperlunya untuk mendukung proses transaksi laundry.</li>
                <li>Bot tidak digunakan untuk mengirim spam atau pesan di luar kebutuhan layanan.</li>
                <li>Informasi pelanggan tidak diperjualbelikan atau dibagikan tanpa kebutuhan operasional yang jelas.</li>
                <li>Pelanggan dapat meminta perubahan atau penghapusan data melalui admin Kasmini Laundry.</li>
            </ul>
        </section>

        <section class="card">
            <h2>Kontak</h2>
            <p>
                Jika ada pertanyaan terkait penggunaan bot atau privasi data, silakan hubungi admin
                Kasmini Laundry melalui kontak resmi yang tersedia pada layanan kami.
            </p>
        </section>

        <div class="footer">
            Terakhir diperbarui: {{ now()->format('d/m/Y') }}
        </div>
    </div>
</body>
</html>
