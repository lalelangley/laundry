<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaksi extends Model
{
    protected $table = 'transaksi';
    protected $primaryKey = 'id_transaksi';

    protected $fillable = [
        'id_pelanggan',
        'id_kasir',
        'id_metode_bayar',
        'total_harga',
        'total_bayar',
        'diskon',
        'tipe_diskon',
        'status_bayar',
        'status_transaksi'
    ];

    public $timestamps = true;
}
