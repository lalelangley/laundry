<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
      DB::table('admin')->updateOrInsert(
            ['email' => 'admin@example.com'],
            [
                'id_admin'  => 3,
                'gambar'    => 'admin/atgsiHqvTjcDnrvlj5czbARJQb5iUKFMNBtXbdFH.png',
                'role_id'   => 1,
                'nama'      => 'Super Admin',
                'email'     => 'admin@example.com',
                'password'  => '$2y$12$QH27/GhnGklJLclSlNkiI.NIeeJwi8LC9mIQjdEjFmQSZzjQk0puW',
                'status'    => 'aktif',
                'is_active' => 1,
            ]
        );
    }
}