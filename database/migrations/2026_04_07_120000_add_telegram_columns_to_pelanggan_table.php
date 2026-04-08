<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRATION: TAMBAH KOLOM TELEGRAM KE TABEL PELANGGAN
 * Fungsi:
 * - Menambahkan integrasi Telegram pada data pelanggan
 * - Menyimpan chat_id dan username Telegram untuk notifikasi bot
 *
 * Konsep:
 * - Class migration
 * - Method up() dan down()
 * - Perubahan struktur tabel yang sudah ada
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        // [METHOD] Tambahkan dua kolom baru ke tabel pelanggan.
        Schema::table('pelanggan', function (Blueprint $table) {
            // chat_id dipakai untuk tujuan pengiriman pesan bot Telegram.
            $table->string('telegram_chat_id')->nullable()->after('email');

            // username dipakai untuk informasi identitas akun Telegram pelanggan.
            $table->string('telegram_username')->nullable()->after('telegram_chat_id');
        });
    }

    public function down(): void
    {
        // [METHOD] Hapus kolom Telegram saat migration di-rollback.
        Schema::table('pelanggan', function (Blueprint $table) {
            $table->dropColumn(['telegram_chat_id', 'telegram_username']);
        });
    }
};
