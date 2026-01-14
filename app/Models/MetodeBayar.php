<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MetodeBayar extends Model
{
    protected $table = 'metode_bayar';
    protected $primaryKey = 'id_metode_bayar';

    protected $fillable = [
        'nama_metode_bayar'
    ];

    public function pembayarans()
    {
        return $this->hasMany(Pembayaran::class, 'id_metode_bayar', 'id_metode_bayar');
    }
}
