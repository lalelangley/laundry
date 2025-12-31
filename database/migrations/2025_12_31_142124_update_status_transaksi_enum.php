<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE transaksi MODIFY COLUMN status_transaksi ENUM(
            'antrian',
            'proses',
            'siap_di_ambil',
            'pick_up',
            'siap_di_antar',
            'selesai',
            'batal',
            'menunggu_konfirmasi',
            'dikonfirmasi',
            'ditolak'
        ) NOT NULL DEFAULT 'antrian'");
    }

    public function down()
    {
        DB::statement("ALTER TABLE transaksi MODIFY COLUMN status_transaksi ENUM(
            'antrian',
            'proses',
            'siap_di_ambil',
            'pick_up',
            'siap_di_antar',
            'selesai',
            'batal'
        ) NOT NULL DEFAULT 'antrian'");
    }
};