<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengeluaran', function (Blueprint $table) {
            $table->id('id_pengeluaran');
            $table->integer('id_kasir')->nullable();
            $table->string('nama_pengeluaran', 150)->nullable();
            $table->text('catatan')->nullable();
            $table->double('nominal')->nullable();
            $table->date('tanggal_pengeluaran')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengeluaran');
    }
};
