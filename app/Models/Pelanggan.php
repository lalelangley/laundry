<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';

    public $timestamps = true;

    protected $fillable = [
        'gambar',
        'nama_pelanggan',
        'no_hp',
        'email',
        'jk',
        'alamat',
        'password',
    ];
}
