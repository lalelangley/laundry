<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail_transaksi');
            $table->integer('id_transaksi')->nullable();
            $table->integer('id_layanan')->nullable();
            $table->integer('id_jenis')->nullable();
            $table->integer('id_parfum')->nullable();
            $table->text('gambar')->nullable();
            $table->string('nama_jenis', 100)->nullable();
            $table->string('nama_parfum', 100)->nullable();
            $table->string('nama_layanan', 100)->nullable();
            $table->integer('lama_hari')->nullable();
            $table->integer('lama_jam')->nullable();
            $table->string('proses', 50)->nullable();
            $table->double('harga')->nullable();
            $table->string('satuan', 50)->nullable();
            $table->double('qty')->nullable();
            $table->double('diskon')->nullable();
            $table->enum('tipe_diskon', ['percent', 'nominal'])->nullable();
            $table->double('total_harga')->nullable();
            $table->tinyInteger('status_transaksi')->nullable();
            $table->date('tgl_transaksi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
