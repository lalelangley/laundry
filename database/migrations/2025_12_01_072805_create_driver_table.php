<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('driver', function (Blueprint $table) {
            $table->id('id_driver');
            $table->string('nama_driver', 100);
            $table->string('no_telp', 20)->nullable();
            $table->string('password', 255);
            $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
            $table->string('gambar', 225)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('driver');
    }
};
