<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('roles')->insert([
            ['id' => 1, 'nama_role' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'nama_role' => 'Admin',       'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'nama_role' => 'Kasir',       'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}