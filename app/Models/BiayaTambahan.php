<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BiayaTambahan extends Model
{
    use HasFactory;

    protected $table = 'biaya_tambahan';
    protected $primaryKey = 'id_biaya_tambahan';

    protected $fillable = [
        'id_transaksi',
        'nama_biaya',
        'nominal',
    ];

    protected $casts = [
        'nama_biaya' => 'string',
        'nominal' => 'double',
    ];

    // Relasi ke Transaksi
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_biaya_tambahan', 'id_biaya_tambahan');
    }
    
}