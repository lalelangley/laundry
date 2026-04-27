<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #1f2937;">
    <h2 style="margin-bottom: 8px;">Reset Password KasminiLaundry</h2>
    <p>Halo {{ $role === 'admin' ? ($account->nama ?? 'Pengguna') : ($account->nama_kasir ?? 'Pengguna') }},</p>
    <p>Kami menerima permintaan reset password untuk akun Anda.</p>
    <p>Silakan klik tombol di bawah ini untuk membuat password baru:</p>

    <p style="margin: 24px 0;">
        <a href="{{ $resetUrl }}"
           style="background: #f59e0b; color: white; text-decoration: none; padding: 12px 20px; border-radius: 8px; display: inline-block; font-weight: bold;">
            Reset Password
        </a>
    </p>

    <p>Jika tombol tidak bisa diklik, salin link berikut ke browser:</p>
    <p style="word-break: break-all;">{{ $resetUrl }}</p>

    <p>Jika Anda tidak merasa meminta reset password, abaikan email ini.</p>
    <p style="margin-top: 24px;">KasminiLaundry</p>
</div>
