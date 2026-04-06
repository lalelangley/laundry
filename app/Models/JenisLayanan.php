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
        'id_transaksi',
        'id_layanan',
        'id_jenis_layanan',
        'id_parfum',
        'nama_jenis',
        'harga',
        'qty',
        'subtotal',     
        'keterangan',
        'id_satuan',
        'gambar',
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
