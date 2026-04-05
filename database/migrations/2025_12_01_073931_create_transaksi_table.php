<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('transaksi')) {
            Schema::create('transaksi', function (Blueprint $table) {
                $table->id('id_transaksi');
                $table->unsignedBigInteger('id_pelanggan')->nullable()->index();
                $table->unsignedBigInteger('id_kasir')->nullable()->index();
                $table->unsignedBigInteger('id_metode_bayar')->nullable()->index();
                $table->string('nama_pelanggan', 100)->nullable();
                $table->string('no_hp', 20)->nullable();
                $table->text('gambar')->nullable();
                $table->double('total_harga')->nullable();
                $table->double('total_bayar')->nullable();
                $table->double('dp')->nullable();
                $table->double('diskon')->nullable();
                $table->string('nama_kasir', 100)->nullable();
                $table->enum('status_bayar', ['lunas', 'DP', 'belum_lunas'])->nullable();
                $table->enum('status_transaksi', ['antrian', 'proses', 'selesai_dicuci', 'siap_di_ambil', 'siap_di_antar', 'pick_up', 'selesai', 'batal'])->nullable();
                $table->enum('jenis_transaksi', ['offline', 'online'])->default('offline');
                $table->text('keterangan')->nullable();
                $table->string('foto_bukti')->nullable();
                $table->string('foto_bukti_bayar')->nullable();
                $table->text('keterangan_bayar')->nullable();
                $table->date('tgl_lunas')->nullable();
                $table->date('tgl_estimasi')->nullable();
                $table->date('tgl_transaksi')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};