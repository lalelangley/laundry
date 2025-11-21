<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable; // WAJIB
use Laravel\Sanctum\HasApiTokens; // WAJIB

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory; // WAJIB BANGET

    protected $table = 'admin';
    protected $primaryKey = 'id_admin';

    protected $fillable = [
        'nama',
        'email',
        'password',
        'no_telp'
    ];

    protected $hidden = [
        'password'
    ];
}
