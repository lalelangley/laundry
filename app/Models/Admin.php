<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Notifiable;
    protected $table = 'admin';      // nama tabel
    protected $primaryKey = 'id_admin'; // primary key sesuaikan database
    public $incrementing = true;
    protected $keyType = 'int';

        protected $fillable = [
        'nama',
        'email',
        'password',
        'role_id',
        'status'
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
