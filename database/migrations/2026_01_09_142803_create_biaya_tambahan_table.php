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
        Schema::create('biaya_tambahan', function (Blueprint $table) {
            $table->id('id_ongkir');
            $table->unsignedBigInteger('id_transaksi');
            $table->double('nominal')->default(0);
            $table->timestamps();

            // Index
            $table->index('id_transaksi', 'idx_biaya_tambahan_transaksi');

            // Foreign key
            $table->foreign('id_transaksi', 'fk_biaya_tambahan_transaksi')
                  ->references('id_transaksi')
                  ->on('transaksi')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('biaya_tambahan');
    }
};