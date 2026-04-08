<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ============================================================
 * MIGRATION: MEMBUAT TABEL PELANGGAN
 * Fungsi:
 * - Membentuk struktur tabel pelanggan sesuai kebutuhan sistem
 * - Menyediakan kolom identitas, kontak, alamat, dan autentikasi
 *
 * Konsep:
 * - Class migration
 * - Method up() dan down()
 * - Blueprint schema builder
 * - Arsitektur tabel basis data
 * ============================================================
 */
return new class extends Migration
{
    public function up(): void
    {
        // [METHOD] up() dijalankan saat migration dieksekusi.
        Schema::create('pelanggan', function (Blueprint $table) {
            // Primary key utama tabel pelanggan.
            $table->id('id_pelanggan');

            // Kolom foto/gambar profil pelanggan.
            $table->string('gambar', 225)->nullable();

            // Kolom nama pelanggan.
            $table->string('nama_pelanggan', 100)->nullable();

            // Kolom nomor HP pelanggan.
            $table->string('no_hp', 20)->nullable();
            
            // Kolom email pelanggan.
            $table->string('email', 150)->nullable();

            // Kolom jenis kelamin dengan enum L/P.
            $table->enum('jk', ['L', 'P'])->nullable();

            // Kolom alamat lengkap pelanggan.
            $table->text('alamat')->nullable();

            // Kolom password untuk autentikasi pelanggan.
            $table->string('password', 255);

            // created_at dan updated_at bawaan Laravel.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // [METHOD] down() dipakai untuk rollback migration.
        Schema::dropIfExists('pelanggan');
    }
};
