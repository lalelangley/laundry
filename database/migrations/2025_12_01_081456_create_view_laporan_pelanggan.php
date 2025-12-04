<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW v_laporan_pelanggan AS
            SELECT 
                p.id_pelanggan,
                p.nama_pelanggan,
                p.no_hp,
                p.email,
                p.jk,
                p.alamat,
                COUNT(t.id_transaksi) AS total_transaksi,
                COALESCE(SUM(t.total_bayar), 0) AS total_pengeluaran
            FROM pelanggan p
            LEFT JOIN transaksi t ON t.id_pelanggan = p.id_pelanggan
            GROUP BY p.id_pelanggan, p.nama_pelanggan, p.no_hp, p.email, p.jk, p.alamat
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_pelanggan");
    }
};
