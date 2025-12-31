<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $table = 'menu';

    protected $fillable = [
        'nama_menu',
        'icon',
        'route',
        'parent_id',
        'urutan',
        'status',
    ];

    public function menuRoles()
    {
        return $this->hasMany(MenuRole::class, 'menu_id');
    }

    // 🔥 TAMBAHKAN INI
    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'menu_role',
            'menu_id',
            'role_id'
        )->withPivot('can_view', 'can_add', 'can_edit', 'can_delete');
    }

    public function parent()
    {
        return $this->belongsTo(Menu::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Menu::class, 'parent_id');
    }
}