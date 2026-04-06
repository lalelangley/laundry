<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
{
    if (!Schema::hasTable('detail_transaksi')) {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id('id_detail_transaksi');
            $table->integer('id_transaksi')->nullable();
            $table->integer('id_layanan')->nullable();
            $table->integer('id_jenis')->nullable();
            $table->integer('id_jenis_layanan')->nullable(); // ✅ tambah
            $table->integer('id_parfum')->nullable();
            $table->integer('id_satuan')->nullable(); // ✅ tambah
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
            $table->double('subtotal')->nullable(); // ✅ rename dari total_harga
            $table->tinyInteger('status_transaksi')->nullable();
            $table->date('tgl_transaksi')->nullable();
            $table->timestamps();
        });
    } else {
        Schema::table('detail_transaksi', function (Blueprint $table) {
            if (!Schema::hasColumn('detail_transaksi', 'id_jenis_layanan')) {
                $table->integer('id_jenis_layanan')->nullable()->after('id_jenis');
            }
            if (!Schema::hasColumn('detail_transaksi', 'id_satuan')) {
                $table->integer('id_satuan')->nullable()->after('id_parfum');
            }
        });
    }
}

    public function down(): void
    {
        Schema::dropIfExists('detail_transaksi');
    }
};
