<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jenis_layanan', function (Blueprint $table) {
            $table->id('id_jenis_layanan');
            $table->integer('id_layanan')->nullable();
            $table->integer('id_satuan')->nullable();
            $table->string('nama_jenis', 100);
            $table->integer('harga')->nullable();
            $table->integer('lama')->nullable();
            $table->string('lama_satuan', 50)->nullable();
            $table->text('gambar')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jenis_layanan');
    }
};
