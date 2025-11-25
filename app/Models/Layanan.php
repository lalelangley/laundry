<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';

    protected $fillable = [
        // LAYANAN UTAMA
        'nama_layanan',
        'gambar',
        'proses',

        // JENIS LAYANAN (disatukan)
        'nama_jenis',
        'satuan',
        'harga',
        'lama',
        'lama_satuan',
        'keterangan',

        // PENANDA
        'tipe', // 'layanan' atau 'jenis'
    ];

    public $timestamps = true;
}
