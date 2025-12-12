<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailTransaksi extends Model
{
    protected $table = 'detail_transaksi';
    protected $primaryKey = 'id_detail_transaksi';
    public $incrementing = true;

    protected $fillable = [
        'id_transaksi',
        'id_layanan',
        'id_jenis_layanan',
        'id_parfum',
        'harga',
        'qty',
        'keterangan',
        'id_satuan',
        'tipe_diskon'
    ];
    public function detail()
{
    return $this->hasMany(DetailTransaksi::class, 'id_transaksi', 'id_transaksi');
}

public function jenis()
{
    return $this->belongsTo(JenisLayanan::class, 'id_jenis_layanan');
}

public function pelanggan()
{
    return $this->belongsTo(Pelanggan::class, 'id_pelanggan');
}
public function layanan()
{
    return $this->belongsTo(Layanan::class, 'id_layanan');

}
public function transaksi()
{
    return $this->belongsTo(Layanan::class, 'id_transaksi');

}
}