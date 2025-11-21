<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Kasir extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $table = 'kasir';
    protected $primaryKey = 'id_kasir';
   protected $fillable = [
    'nama_kasir',
    'no_hp',
    'password',
    'gambar',
];

protected $hidden = [
    'password',
];

}
