<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Transaksi extends Model
{
    protected $table = "transaksi";
    protected $primaryKey = "id_transaksi";
    public $incrementing = true;
    protected $keyType = "int";

protected $fillable = [
    'id_pelanggan',
    'id_kasir',
    'id_metode_bayar',
    'nama_pelanggan',
    'no_hp',
    'status_transaksi',
    'status_bayar',
    'total_harga',
    'total_bayar',
    'dp',
    'diskon',
    'tipe_diskon',
    'tgl_transaksi',
    'tgl_estimasi',
    'tgl_lunas',
    'keterangan',
];


protected $casts = [
    'total_harga' => 'double',
    'total_bayar' => 'double',
    'dp'          => 'double',
    'diskon'      => 'double',
];



    // ===========================
    // RELASI DETAIL TRANSAKSI
    // ===========================
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    // ===========================
    // RELASI DELIVERY
    // ===========================
    public function delivery()
    {
        return $this->hasOne(Delivery::class, 'id_transaksi');
    }

    // ===========================
    // RELASI PELANGGAN
    // ===========================
    public function pelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
    }
}
