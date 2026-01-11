<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id('id_pembayaran');
            $table->unsignedBigInteger('id_transaksi');
            $table->enum('tipe_pembayaran', ['dp', 'lunas', 'cicilan'])->default('dp');
            $table->decimal('nominal', 15, 2);
            $table->string('foto_bukti')->nullable();
            $table->text('keterangan')->nullable();
            $table->date('tanggal_bayar');
            $table->timestamps();

            $table->foreign('id_transaksi')
                  ->references('id_transaksi')
                  ->on('transaksi')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};