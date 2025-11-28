<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisLayanan extends Model
{
    protected $table = 'jenis_layanan';
    protected $primaryKey = 'id_jenis_layanan';

    protected $fillable = [
        'id_layanan',
        'nama_jenis',
        'satuan',
        'harga',
        'lama',
        'lama_satuan',
        'gambar',
        'keterangan',
    ];

    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan');
    }
    // JenisLayanan.php
public function satuan() {
    return $this->belongsTo(Satuan::class, 'id_satuan');
}

}
