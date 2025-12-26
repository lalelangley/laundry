<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MenuRole extends Model
{
    protected $table = 'menu_role';

   protected $fillable = [
    'role_id',
    'menu_id',
    'can_view',
    'can_add',
    'can_edit',
    'can_delete',
];


    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class, 'menu_id');
    }
}
