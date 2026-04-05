<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB; // ← tambah ini
return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up(): void
{
    Schema::create('pengaturan', function (Blueprint $table) {
        $table->id();
        $table->string('key')->unique();
        $table->text('value')->nullable();
        $table->timestamps();
    });

    DB::table('pengaturan')->insert([
        ['key' => 'nama_outlet', 'value' => 'Laundry Ku', 'created_at' => now(), 'updated_at' => now()],
        ['key' => 'alamat_outlet', 'value' => '', 'created_at' => now(), 'updated_at' => now()],
        ['key' => 'foto_outlet', 'value' => null, 'created_at' => now(), 'updated_at' => now()],
        ['key' => 'hitung_omzet_dari', 'value' => 'lunas', 'created_at' => now(), 'updated_at' => now()],
    ]);
}

public function down(): void
{
    Schema::dropIfExists('pengaturan');
}
};
