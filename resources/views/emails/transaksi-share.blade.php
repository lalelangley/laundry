<!DOCTYPE html>
<!-- FE-DOC: Template frontend untuk resources/views/emails/transaksi-share.blade.php. Tambahan komentar di file ini dipakai sebagai penjelas struktur HTML, CSS, dan JavaScript tanpa mengubah behavior. -->
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ringkasan Pembayaran</title>
</head>
<body style="margin:0;padding:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#1f2937;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:640px;background:#ffffff;border-radius:24px;overflow:hidden;box-shadow:0 12px 40px rgba(15,23,42,0.10);">
                    <tr>
                        <td style="background:linear-gradient(135deg,#facc15,#f59e0b);padding:28px 32px;color:#111827;">
                            <div style="font-size:13px;font-weight:700;letter-spacing:1.2px;text-transform:uppercase;opacity:.8;">
                                {{ config('app.name', 'Laundry App') }}
                            </div>
                            <h1 style="margin:10px 0 8px;font-size:28px;line-height:1.2;">
                                Pembayaran Berhasil Dicatat
                            </h1>
                            <p style="margin:0;font-size:15px;line-height:1.6;">
                                Halo {{ $transaksi->nama_pelanggan }}, berikut ringkasan transaksi laundry Anda.
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:28px 32px 20px;">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0 12px;">
                                <tr>
                                    <td style="width:50%;background:#f9fafb;border:1px solid #e5e7eb;border-radius:16px;padding:16px;">
                                        <div style="font-size:12px;color:#6b7280;text-transform:uppercase;font-weight:700;letter-spacing:.8px;">ID Transaksi</div>
                                        <div style="margin-top:6px;font-size:24px;font-weight:700;color:#111827;">#{{ $transaksi->id_transaksi }}</div>
                                    </td>
                                    <td style="width:50%;padding-left:12px;">
                                        <div style="background:#ecfdf5;border:1px solid #bbf7d0;border-radius:16px;padding:16px;">
                                            <div style="font-size:12px;color:#047857;text-transform:uppercase;font-weight:700;letter-spacing:.8px;">Status Bayar</div>
                                            <div style="margin-top:6px;font-size:20px;font-weight:700;color:#065f46;">{{ strtoupper((string) $transaksi->status_bayar) }}</div>
                                        </div>
                                    </td>
                                </tr>
                            </table>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="margin-top:10px;background:#fff7ed;border:1px solid #fed7aa;border-radius:20px;padding:8px 0;">
                                <tr>
                                    <td style="padding:14px 20px;border-bottom:1px solid #ffedd5;">
                                        <span style="font-size:14px;color:#9a3412;">Tanggal Transaksi</span>
                                        <div style="margin-top:4px;font-size:16px;font-weight:700;color:#7c2d12;">{{ \Carbon\Carbon::parse($transaksi->tgl_transaksi)->format('d/m/Y H:i') }}</div>
                                    </td>
                                </tr>
                                @if(!empty($transaksi->tgl_estimasi))
                                <tr>
                                    <td style="padding:14px 20px;">
                                        <span style="font-size:14px;color:#9a3412;">Estimasi Selesai</span>
                                        <div style="margin-top:4px;font-size:16px;font-weight:700;color:#7c2d12;">{{ \Carbon\Carbon::parse($transaksi->tgl_estimasi)->format('d/m/Y H:i') }}</div>
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 24px;">
                            <h2 style="margin:0 0 14px;font-size:18px;color:#111827;">Ringkasan Pembayaran</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:20px;padding:10px 0;">
                                <tr>
                                    <td style="padding:12px 20px;font-size:14px;color:#6b7280;">Total Harga</td>
                                    <td align="right" style="padding:12px 20px;font-size:15px;font-weight:700;color:#111827;">Rp{{ number_format((float) $transaksi->total_harga, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;font-size:14px;color:#6b7280;">Diskon</td>
                                    <td align="right" style="padding:12px 20px;font-size:15px;font-weight:700;color:#111827;">Rp{{ number_format((float) $transaksi->diskon, 0, ',', '.') }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:12px 20px;font-size:14px;color:#6b7280;">Total Bayar</td>
                                    <td align="right" style="padding:12px 20px;font-size:18px;font-weight:800;color:#16a34a;">Rp{{ number_format((float) $transaksi->total_bayar, 0, ',', '.') }}</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 24px;">
                            <h2 style="margin:0 0 14px;font-size:18px;color:#111827;">Detail Layanan</h2>
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:separate;border-spacing:0 10px;">
                                @foreach($transaksi->detail as $item)
                                    @php
                                        $subtotal = (float) $item->harga * (float) $item->qty;
                                        $layanan = $item->layanan?->nama_layanan ?? 'Layanan';
                                        $jenis = $item->jenis?->nama_jenis ? ' (' . $item->jenis->nama_jenis . ')' : '';
                                    @endphp
                                    <tr>
                                        <td style="background:#ffffff;border:1px solid #e5e7eb;border-radius:18px;padding:16px 18px;">
                                            <div style="font-size:16px;font-weight:700;color:#111827;">{{ $layanan }}{{ $jenis }}</div>
                                            <div style="margin-top:6px;font-size:14px;color:#6b7280;">{{ $item->qty }} x Rp{{ number_format((float) $item->harga, 0, ',', '.') }}</div>
                                            <div style="margin-top:8px;font-size:15px;font-weight:700;color:#0f172a;">Subtotal: Rp{{ number_format($subtotal, 0, ',', '.') }}</div>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:0 32px 32px;">
                            <div style="background:#111827;border-radius:20px;padding:18px 20px;color:#ffffff;">
                                <div style="font-size:16px;font-weight:700;">Terima kasih sudah menggunakan layanan kami.</div>
                                <div style="margin-top:6px;font-size:14px;line-height:1.6;color:#d1d5db;">
                                    Simpan email ini sebagai ringkasan pembayaran transaksi laundry Anda.
                                </div>
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
