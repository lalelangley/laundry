<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';
    protected $fillable = [
        'nama_layanan',
        'gambar',
        'proses',
    ];

    protected $casts = [
        'proses' => 'string'
    ];

    public function jenis()
    {
        return $this->hasMany(JenisLayanan::class, 'id_layanan');
    }

}
