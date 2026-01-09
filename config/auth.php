<?php

return [
    'defaults' => [
        'guard' => 'admin', // ✅ Default guard
        'passwords' => 'admins', // ✅ Ganti ke admins
    ],

    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // ✅ Guard untuk Admin (Super Admin & Admin Biasa)
        'admin' => [
            'driver' => 'session',
            'provider' => 'admins',
        ],

        // ❌ HAPUS INI - Tidak perlu guard admin2 terpisah
        // 'admin2' => [
        //     'driver' => 'session',
        //     'provider' => 'admins',
        // ],

        // ✅ Guard untuk Kasir
        'kasir' => [
            'driver' => 'session',
            'provider' => 'kasirs', // ✅ Ganti ke kasirs (jamak)
        ],
    ],

    'providers' => [
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // ✅ Provider untuk Admin
        'admins' => [
            'driver' => 'eloquent',
            'model' => App\Models\Admin::class,
        ],

        // ✅ Provider untuk Kasir (ganti nama jadi kasirs)
        'kasirs' => [
            'driver' => 'eloquent',
            'model' => App\Models\Kasir::class,
        ],
    ],

    'passwords' => [
        'users' => [
            'provider' => 'users',
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
            'expire' => 60,
            'throttle' => 60,
        ],

        // ✅ Tambahkan untuk admin
        'admins' => [
            'provider' => 'admins',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        // ✅ Tambahkan untuk kasir
        'kasirs' => [
            'provider' => 'kasirs',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),
];