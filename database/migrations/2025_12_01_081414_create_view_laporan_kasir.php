<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_kasir");

        DB::statement("
            CREATE VIEW v_laporan_kasir AS
            SELECT 
                k.id_kasir,
                k.nama_kasir,
                k.no_hp,
                COUNT(t.id_transaksi) AS jumlah_transaksi,
                COALESCE(SUM(t.total_bayar), 0) AS total_uang_masuk
            FROM kasir k
            LEFT JOIN transaksi t ON t.id_kasir = k.id_kasir
            GROUP BY k.id_kasir, k.nama_kasir, k.no_hp
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_kasir");
    }
};
