<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Layanan extends Model
{
    use HasFactory;

    protected $table = 'layanan';
    protected $primaryKey = 'id_layanan';
    protected $fillable = ['nama_layanan', 'proses'];

    // Relasi: Layanan punya banyak jenis layanan
    public function jenisLayanan()
    {
        return $this->hasMany(JenisLayanan::class, 'id_layanan', 'id_layanan');
    }
}
