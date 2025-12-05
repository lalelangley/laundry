<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup', function (Blueprint $table) {
            $table->id('id_pickup');
            $table->integer('id_transaksi')->nullable();
            $table->integer('id_driver')->nullable();
            $table->text('alamat_pickup')->nullable();
            $table->dateTime('tanggal_pickup')->nullable();
            $table->enum('status_pickup', ['pending', 'dijemput', 'selesai', 'batal'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup');
    }
};
