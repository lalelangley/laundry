<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Parfum extends Model
{
    protected $table = 'parfum';
    protected $primaryKey = 'id_parfum';

    // Karena tabel punya created_at dan updated_at
    public $timestamps = true;

    protected $fillable = [
        'nama_parfum'
    ];
}
