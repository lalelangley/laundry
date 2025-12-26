<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JenisLayanan extends Model
{
    use HasFactory;

    protected $table = 'jenis_layanan';
    protected $primaryKey = 'id_jenis_layanan';

    protected $fillable = [
        'id_layanan',
        'nama_jenis',
        'id_satuan',     
        'harga',
        'lama',
        'lama_satuan',
        'gambar',
        'keterangan',
    ];

    // Relasi balik ke Layanan
    public function layanan()
    {
        return $this->belongsTo(Layanan::class, 'id_layanan', 'id_layanan');
    }
    // Relasi balik ke Satuan
    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'id_satuan', 'id_satuan');
    }
}
