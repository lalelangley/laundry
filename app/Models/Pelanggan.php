<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Pelanggan extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'pelanggan';
    protected $primaryKey = 'id_pelanggan';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'gambar',
        'nama_pelanggan',
        'no_hp',
        'alamat',
        'jk',
        'gambar',
        'email',
        'role',
        'password'
    ];

    protected $hidden = [
        'password',
        'email',
    ];

    public function fcmTokens()
{
    return $this->hasMany(FcmToken::class, 'pelanggan_id', 'id_pelanggan');
}
}
