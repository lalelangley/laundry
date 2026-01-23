<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Insert menu Transaksi untuk Role 1 (Admin) - Langsung ke create
        DB::table('menu')->insert([
            'role_id' => 1,
            'nama_menu' => 'Transaksi',
            'icon' => 'bi-receipt',
            'route' => 'transaksi.create',
            'parent_id' => null,
            'urutan' => 11,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert menu Transaksi untuk Role 2 (Admin2) - Langsung ke create
        DB::table('menu')->insert([
            'role_id' => 2,
            'nama_menu' => 'Transaksi',
            'icon' => 'bi-receipt',
            'route' => 'admin2.transaksi.create',
            'parent_id' => null,
            'urutan' => 11,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert menu Transaksi untuk Role 3 (Kasir) - Langsung ke create
        DB::table('menu')->insert([
            'role_id' => 3,
            'nama_menu' => 'Transaksi',
            'icon' => 'bi-receipt',
            'route' => 'kasir.transaksi.create',
            'parent_id' => null,
            'urutan' => 11,
            'status' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Hapus menu Transaksi untuk semua role
        DB::table('menu')->where('nama_menu', 'Transaksi')
            ->whereIn('role_id', [1, 2, 3])
            ->delete();
    }
};