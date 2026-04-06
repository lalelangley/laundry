<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('menu')->insert([
            // ── Super Admin (role_id 1) ──────────────────────
            ['role_id'=>1,'nama_menu'=>'Dashboard','icon'=>'bi-speedometer2','route'=>'admin.dashboard','parent_id'=>null,'urutan'=>1,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Layanan','icon'=>'bi-basket','route'=>'layanan.index','parent_id'=>null,'urutan'=>2,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Parfum','icon'=>'bi-wind','route'=>'parfum.index','parent_id'=>null,'urutan'=>3,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Satuan','icon'=>'bi-basket3-fill','route'=>'satuan.index','parent_id'=>null,'urutan'=>4,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Pelanggan','icon'=>'bi-people','route'=>'pelanggan.index','parent_id'=>null,'urutan'=>5,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Riwayat','icon'=>'bi-clock-history','route'=>'riwayat.index','parent_id'=>null,'urutan'=>6,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Pengeluaran','icon'=>'bi-cash-coin','route'=>'pengeluaran.index','parent_id'=>null,'urutan'=>7,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Laporan','icon'=>'bi-clipboard-data','route'=>'laporan.index','parent_id'=>null,'urutan'=>8,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'User Manager','icon'=>'bi-person','route'=>'manager.index','parent_id'=>null,'urutan'=>9,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Pengaturan','icon'=>'bi-gear','route'=>'pengaturan.index','parent_id'=>null,'urutan'=>10,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Transaksi','icon'=>'bi-receipt','route'=>'transaksi.create','parent_id'=>null,'urutan'=>11,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>1,'nama_menu'=>'Ganti Password','icon'=>'bi-key','route'=>'change.password','parent_id'=>null,'urutan'=>12,'status'=>1,'created_at'=>null,'updated_at'=>null],
            ['role_id'=>1,'nama_menu'=>'Pesanan Online','icon'=>'bi-cart-check','route'=>'pesanan.online.index','parent_id'=>null,'urutan'=>13,'status'=>1,'created_at'=>now(),'updated_at'=>now()],

            // ── Admin (role_id 2) ────────────────────────────
            ['role_id'=>2,'nama_menu'=>'Dashboard','icon'=>'bi-speedometer2','route'=>'admin2.dashboard','parent_id'=>null,'urutan'=>1,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Layanan','icon'=>'bi-basket','route'=>'admin2.layanan.index','parent_id'=>null,'urutan'=>2,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Parfum','icon'=>'bi-wind','route'=>'admin2.parfum.index','parent_id'=>null,'urutan'=>3,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Satuan','icon'=>'bi-basket3-fill','route'=>'admin2.satuan.index','parent_id'=>null,'urutan'=>4,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Pelanggan','icon'=>'bi-people','route'=>'admin2.pelanggan.index','parent_id'=>null,'urutan'=>5,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Riwayat','icon'=>'bi-clock-history','route'=>'admin2.riwayat.index','parent_id'=>null,'urutan'=>6,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Pengeluaran','icon'=>'bi-cash-coin','route'=>'admin2.pengeluaran.index','parent_id'=>null,'urutan'=>7,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Laporan','icon'=>'bi-clipboard-data','route'=>'admin2.laporan.index','parent_id'=>null,'urutan'=>8,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'User Manager','icon'=>'bi-person','route'=>'admin2.manager.index','parent_id'=>null,'urutan'=>9,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Pengaturan','icon'=>'bi-gear','route'=>'admin2.pengaturan.index','parent_id'=>null,'urutan'=>10,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Transaksi','icon'=>'bi-receipt','route'=>'admin2.transaksi.create','parent_id'=>null,'urutan'=>11,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Ganti Password','icon'=>'bi-key','route'=>'admin2.change.password','parent_id'=>null,'urutan'=>12,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>2,'nama_menu'=>'Pesanan Online','icon'=>'bi-cart-check','route'=>'admin2.pesanan.online.index','parent_id'=>null,'urutan'=>13,'status'=>1,'created_at'=>now(),'updated_at'=>now()],

            // ── Kasir (role_id 3) ────────────────────────────
            ['role_id'=>3,'nama_menu'=>'Dashboard','icon'=>'bi-speedometer2','route'=>'kasir.dashboard','parent_id'=>null,'urutan'=>1,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Layanan','icon'=>'bi-basket','route'=>'kasir.layanan.index','parent_id'=>null,'urutan'=>2,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Parfum','icon'=>'bi-wind','route'=>'kasir.parfum.index','parent_id'=>null,'urutan'=>3,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Satuan','icon'=>'bi-basket3-fill','route'=>'kasir.satuan.index','parent_id'=>null,'urutan'=>4,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Pelanggan','icon'=>'bi-people','route'=>'kasir.pelanggan.index','parent_id'=>null,'urutan'=>5,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Riwayat','icon'=>'bi-clock-history','route'=>'kasir.riwayat.index','parent_id'=>null,'urutan'=>6,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Pengeluaran','icon'=>'bi-cash-coin','route'=>'kasir.pengeluaran.index','parent_id'=>null,'urutan'=>7,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Laporan','icon'=>'bi-clipboard-data','route'=>'kasir.laporan.index','parent_id'=>null,'urutan'=>8,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Pengaturan','icon'=>'bi-gear','route'=>'kasir.pengaturan.index','parent_id'=>null,'urutan'=>10,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Transaksi','icon'=>'bi-receipt','route'=>'kasir.transaksi.create','parent_id'=>null,'urutan'=>11,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Ganti Password','icon'=>'bi-key','route'=>'kasir.change.password','parent_id'=>null,'urutan'=>12,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
            ['role_id'=>3,'nama_menu'=>'Pesanan Online','icon'=>'bi-cart-check','route'=>'kasir.pesanan.online.index','parent_id'=>null,'urutan'=>13,'status'=>1,'created_at'=>now(),'updated_at'=>now()],
        ]);
    }
}