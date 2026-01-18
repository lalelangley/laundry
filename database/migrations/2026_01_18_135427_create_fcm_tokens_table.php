,<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fcm_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pelanggan_id'); // relasi ke tabel pelanggan
            $table->string('token')->unique();
            $table->string('device_name')->nullable(); // opsional, misal: Android, iOS
            $table->timestamps();

            $table->foreign('pelanggan_id')
                  ->references('id_pelanggan')
                  ->on('pelanggan')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fcm_tokens');
    }
};
