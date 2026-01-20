<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Driver extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'driver'; // ← PASTIKAN NAMA TABEL BENAR
    protected $primaryKey = 'id_driver';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'nama_driver',
        'no_telp',
        'password',
        'status',
        'role',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // =============================
    // RELASI
    // =============================

    /**
     * 1 Driver punya banyak Transaksi
     */
    public function transaksi()
    {
        return $this->hasMany(Transaksi::class, 'id_driver', 'id_driver');
    }

    /**
     * 1 Driver bisa punya banyak FCM Tokens
     */
    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class, 'driver_id', 'id_driver');
    }
}