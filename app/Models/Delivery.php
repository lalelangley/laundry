<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    use HasFactory;

    protected $table = 'delivery';
    protected $primaryKey = 'id_delivery';

    protected $fillable = [
        'id_transaksi',
        'id_driver',
        'jenis',
        'alamat_tujuan',
        'status',
        'catatan',
        'waktu',
    ];

    // ======================
    // RELASI
    // ======================

    // Delivery milik satu transaksi
    public function transaksi()
    {
        return $this->belongsTo(Transaksi::class, 'id_transaksi', 'id_transaksi');
    }

    // Delivery milik satu driver (optional)
    public function driver()
    {
        return $this->belongsTo(Driver::class, 'id_driver', 'id_driver');
    }
}
