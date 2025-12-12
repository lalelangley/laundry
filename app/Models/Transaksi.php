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
        'gambar',
        'total_harga',
        'total_bayar',
        'diskon',
        'tipe_diskon',
        'status_bayar',
        'status_transaksi',
        'keterangan',
        'tgl_lunas',
        'tgl_estimasi',
        'tgl_transaksi',
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
