<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    protected $fillable = [
        'nama_role',
    ];

    public function admins()
    {
        return $this->hasMany(Admin::class, 'role_id');
    }

    public function menuRoles()
    {
        return $this->hasMany(MenuRole::class, 'role_id');
    }

    public function menus()
{
    return $this->belongsToMany(
        Menu::class,
        'menu_roles',
        'role_id',
        'menu_id'
    )->withPivot([
        'can_view',
        'can_add',
        'can_edit',
        'can_delete'
    ]);
}

}
