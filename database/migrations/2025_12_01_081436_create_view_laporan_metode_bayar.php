<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW v_laporan_metode_bayar AS
            SELECT 
                mb.id_metode_bayar,
                mb.nama_metode_bayar,
                COUNT(t.id_transaksi) AS jumlah_digunakan,
                COALESCE(SUM(t.total_bayar), 0) AS total_uang_masuk
            FROM metode_bayar mb
            LEFT JOIN transaksi t ON t.id_metode_bayar = mb.id_metode_bayar
            GROUP BY mb.id_metode_bayar
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_metode_bayar");
    }
};
