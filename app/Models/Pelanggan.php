<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * ============================================================
 * MODEL PELANGGAN
 * Fungsi:
 * - Merepresentasikan tabel `pelanggan`
 * - Menyimpan data identitas pelanggan aplikasi
 * - Menyediakan relasi ke token notifikasi pelanggan
 *
 * Konsep:
 * - Class-object Eloquent
 * - Property konfigurasi model
 * - Array fillable dan hidden
 * - Method relasi hasMany
 * ============================================================
 */
class Pelanggan extends Authenticatable
{
    // Trait bawaan Laravel untuk API token, factory, dan notifikasi.
    use HasApiTokens, HasFactory, Notifiable;

    // Nama tabel yang dipetakan oleh model ini.
    protected $table = 'pelanggan';

    // Primary key tabel pelanggan.
    protected $primaryKey = 'id_pelanggan';

    // Konfigurasi bahwa primary key auto increment dan bertipe integer.
    public $incrementing = true;
    protected $keyType = 'int';

    // Array field yang boleh diisi mass assignment.
    protected $fillable = [
        'gambar',
        'nama_pelanggan',
        'no_hp',
        'alamat',
        'jk',
        'gambar',
        'email',
        'telegram_chat_id',
        'telegram_username',
        'role',
        'password'
    ];

    // Array field sensitif yang disembunyikan saat serialisasi.
    protected $hidden = [
        'password',
        'email',
    ];

    /**
     * Relasi pelanggan ke banyak token FCM.
     * Satu pelanggan dapat login di beberapa device sekaligus.
     */
    public function fcmTokens()
    {
        return $this->hasMany(FcmToken::class, 'pelanggan_id', 'id_pelanggan');
    }
}
