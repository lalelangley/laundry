<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery', function (Blueprint $table) {
            $table->id('id_delivery');
            $table->integer('id_transaksi')->nullable();
            $table->integer('id_driver')->nullable();
            $table->enum('jenis', ['pickup', 'antar']);
            $table->string('alamat_tujuan', 255)->nullable();
            $table->enum('status', ['menunggu', 'proses', 'selesai'])->default('menunggu');
            $table->dateTime('waktu')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery');
    }
};
