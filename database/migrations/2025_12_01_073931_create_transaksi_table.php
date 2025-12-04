<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
            $table->double('diskon')->nullable();
            $table->enum('tipe_diskon', ['percent', 'nominal'])->nullable();

            $table->tinyInteger('status_bayar')->nullable();
            $table->tinyInteger('status_transaksi')->nullable();
            $table->text('keterangan')->nullable();

            $table->date('tgl_lunas')->nullable();
            $table->date('tgl_estimasi')->nullable();
            $table->date('tgl_transaksi')->nullable();

            $table->timestamps(); // created_at & updated_at

            // Optional: jika ingin foreign key, bisa ditambahkan nanti
            // $table->foreign('id_pelanggan')->references('id_pelanggan')->on('pelanggan')->onDelete('set null');
            // $table->foreign('id_kasir')->references('id_pegawai')->on('pegawai')->onDelete('set null');
            // $table->foreign('id_metode_bayar')->references('id_metode')->on('metode_bayar')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transaksi');
    }
};
