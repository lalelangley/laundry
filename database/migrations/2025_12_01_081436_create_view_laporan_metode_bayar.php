<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
public function up()
{
    DB::statement("DROP VIEW IF EXISTS v_laporan_pelanggan");

    DB::statement("
        CREATE VIEW v_laporan_pelanggan AS
        SELECT 
            p.id_pelanggan,
            p.nama_pelanggan,
            p.no_hp,
            COUNT(t.id_transaksi) AS jumlah_transaksi,
            COALESCE(SUM(t.total_bayar), 0) AS total_uang_masuk
        FROM pelanggan p
        LEFT JOIN transaksi t ON t.id_pelanggan = p.id_pelanggan
        GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_hp
    ");
}

public function down()
{
    DB::statement("DROP VIEW IF EXISTS v_laporan_pelanggan");
}

};
