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
        Schema::table('menu_role', function (Blueprint $table) {
            // Tambah kolom is_active jika belum ada
            if (!Schema::hasColumn('menu_role', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('menu_id')->comment('ON/OFF Menu');
            }
            
            // Tambah kolom can_cancel jika belum ada
            if (!Schema::hasColumn('menu_role', 'can_cancel')) {
                $table->boolean('can_cancel')->default(false)->after('can_delete')->comment('Dapat batalkan transaksi');
            }
            
            // Tambah kolom can_change_password jika belum ada
            if (!Schema::hasColumn('menu_role', 'can_change_password')) {
                $table->boolean('can_change_password')->default(false)->after('can_cancel')->comment('Dapat ubah password');
            }
            
            // Tambah kolom can_restore_data jika belum ada
            if (!Schema::hasColumn('menu_role', 'can_restore_data')) {
                $table->boolean('can_restore_data')->default(false)->after('can_change_password')->comment('Dapat restore data');
            }
            
            // Tambah kolom show_delete_backup jika belum ada
            if (!Schema::hasColumn('menu_role', 'show_delete_backup')) {
                $table->boolean('show_delete_backup')->default(false)->after('can_restore_data')->comment('Tampilkan hapus backup');
            }
            
            // Tambah kolom show_logout jika belum ada
            if (!Schema::hasColumn('menu_role', 'show_logout')) {
                $table->boolean('show_logout')->default(true)->after('show_delete_backup')->comment('Tampilkan tombol logout');
            }
            
            // Tambah kolom can_access_settings jika belum ada
            if (!Schema::hasColumn('menu_role', 'can_access_settings')) {
                $table->boolean('can_access_settings')->default(false)->after('show_logout')->comment('Dapat akses pengaturan');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_role', function (Blueprint $table) {
            $columns = [
                'is_active',
                'can_cancel',
                'can_change_password',
                'can_restore_data',
                'show_delete_backup',
                'show_logout',
                'can_access_settings'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('menu_role', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};