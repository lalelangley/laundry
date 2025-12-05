<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE VIEW v_laporan_transaksi AS
            SELECT
                id_transaksi,
                no_nota,
                nama_pelanggan,
                no_hp,
                nama_kasir,
                nama_metode_bayar,
                subtotal,
                diskon,
                tipe_diskon,
                total_bayar,
                kembalian,
                tgl_transaksi,
                tgl_estimasi,
                tgl_lunas,
                status_bayar,
                status_transaksi,
                keterangan
            FROM some_table   -- <- ganti dengan tabel sebenarnya
        ");
    }

    public function down(): void
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_transaksi");
    }
};
     