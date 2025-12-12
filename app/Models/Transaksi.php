<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaksi extends Model
{
    protected $table = "transaksi";
    protected $primaryKey = "id_transaksi"; // kalau pakai id_transaksi
    public $incrementing = true;
    protected $keyType = "int";

    protected $fillable = [
    'id_pelanggan',
    'nama_pelanggan',
    'no_hp',
    'total_harga',
    'total_bayar',
    'dp',                 // <--- pastikan ini ada
    'diskon',
    'tipe_diskon',
    'status_bayar',
    'status_transaksi',
    'keterangan',
    'tgl_lunas',
    'tgl_estimasi',
    'tgl_transaksi',
    'id_kasir',
    'id_metode_bayar',
];



    // 🔥 Tambahkan relasi ini
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
    }

        public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id_pelanggan');
    }

    public function parfum()
    {
        return $this->belongsTo(Parfum::class, 'id_parfum', 'id_satuan_parfum');
    }

}
