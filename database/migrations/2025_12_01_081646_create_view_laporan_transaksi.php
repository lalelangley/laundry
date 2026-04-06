<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_transaksi");

        DB::statement("
            CREATE VIEW v_laporan_transaksi AS
            SELECT
                t.id_transaksi,
                t.nama_pelanggan,
                t.no_hp,
                k.nama_kasir,
                mb.nama_metode_bayar,
                t.total_harga,
                t.diskon,
                t.total_bayar,
                t.status_bayar,
                t.status_transaksi,
                t.keterangan,
                t.tgl_transaksi,
                t.tgl_estimasi,
                t.tgl_lunas
            FROM transaksi t
            LEFT JOIN kasir k ON k.id_kasir = t.id_kasir
            LEFT JOIN metode_bayar mb ON mb.id_metode_bayar = t.id_metode_bayar
        ");
    }

    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS v_laporan_transaksi");
    }
};
     
