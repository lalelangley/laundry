<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Satuan extends Model
{
    protected $table = 'satuan';
    protected $primaryKey = 'id_satuan';

    // Karena tabel punya created_at dan updated_at
    public $timestamps = true;

    protected $fillable = [
        'nama_satuan'
    ];

    public function jenis()
{
    return $this->hasMany(JenisLayanan::class, 'id_satuan', 'id_satuan');
}

}
