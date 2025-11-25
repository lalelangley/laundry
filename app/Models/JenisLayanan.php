<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisLayanan extends Model
{
    protected $table = 'jenis_layanan';
    protected $primaryKey = 'id_jenis_layanan';
    protected $fillable = [
        'nama_jenis',
        'gambar',
        'satuan',
        'harga',
        'lama',
        'lama_satuan',
        'keterangan',
    ];
    public $timestamps = true;
}
