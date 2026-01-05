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
        'nama_pelanggan',
        'no_hp',
        'total_harga',
        'total_bayar',
        'dp',
        'diskon',
        'tipe_diskon',
        'status_bayar',
        'status_transaksi',
        'jenis_transaksi',     // 🔥 TAMBAHKAN INI
        'keterangan',
        'tgl_lunas',
        'tgl_estimasi',
        'tgl_transaksi',
        'id_kasir',
        'id_metode_bayar',
        "id_driver",
    ];

    // Relasi
    public function detail()
    {
        return $this->hasMany(DetailTransaksi::class, 'id_transaksi');
    }

    public function detail_transaksi()  // 🔥 ALIAS untuk controller pesanan online
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

    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_kasir');
    }

    public function metodeBayar()
    {
        return $this->belongsTo(MetodeBayar::class, 'id_metode_bayar');
    }
     public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }
    // Transaksi punya satu delivery
public function delivery()
{
    return $this->hasOne(Delivery::class, 'id_transaksi', 'id_transaksi');
}



}