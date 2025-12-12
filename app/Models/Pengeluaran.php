<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengeluaran extends Model
{
    protected $table = 'pengeluaran';
    protected $primaryKey = 'id_pengeluaran';
    public $incrementing = true;
    protected $keyType = 'int';
    protected $fillable = [
        'id_kasir',
        'nama_pengeluaran',
        'catatan',
        'nominal',
        'tanggal_pengeluaran',
    ];

    // relasi ke kasir (opsional kalau kamu ada tabel user/kasir)
    public function kasir()
    {
        return $this->belongsTo(User::class, 'id_kasir', 'id');
    }
}
