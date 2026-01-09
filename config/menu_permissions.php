<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Menu Permissions Configuration
    |--------------------------------------------------------------------------
    |
    | Konfigurasi permission yang tersedia untuk setiap menu
    |
    */

    'menus' => [
        'layanan' => [
            'name' => 'Layanan',
            'icon' => 'box-seam',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_add' => 'Tambah Data',
                'can_edit' => 'Edit Data',
                'can_delete' => 'Hapus Data',
            ],
        ],

        'satuan' => [
            'name' => 'Satuan',
            'icon' => 'rulers',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_add' => 'Tambah Data',
                'can_edit' => 'Edit Data',
                'can_delete' => 'Hapus Data',
            ],
        ],

        'parfum' => [
            'name' => 'Parfum',
            'icon' => 'droplet',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_add' => 'Tambah Data',
                'can_edit' => 'Edit Data',
                'can_delete' => 'Hapus Data',
            ],
        ],

        'pelanggan' => [
            'name' => 'Pelanggan',
            'icon' => 'people',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_add' => 'Tambah Data',
                'can_edit' => 'Edit Data',
                'can_delete' => 'Hapus Data',
            ],
        ],

        'pengeluaran' => [
            'name' => 'Pengeluaran',
            'icon' => 'wallet2',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_add' => 'Tambah Data',
                'can_edit' => 'Edit Data',
                'can_delete' => 'Hapus Data',
            ],
        ],

        'transaksi' => [
            'name' => 'Transaksi',
            'icon' => 'receipt',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_cancel' => 'Batalkan Transaksi',
                'can_edit' => 'Edit Transaksi',
                'can_delete' => 'Hapus Transaksi',
            ],
        ],

        'metode-bayar' => [
            'name' => 'Metode Bayar',
            'icon' => 'credit-card',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Data',
                'can_add' => 'Tambah Metode',
                'can_edit' => 'Edit Metode',
                'can_delete' => 'Hapus Metode',
            ],
        ],

        'laporan' => [
            'name' => 'Laporan',
            'icon' => 'bar-chart',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Laporan',
            ],
        ],

        'pengaturan' => [
            'name' => 'Pengaturan / Data',
            'icon' => 'gear',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Akses Pengaturan',
                'can_change_password' => 'Ubah Password',
                'can_restore_data' => 'Restore Data',
                'show_delete_backup' => 'Tampilkan Hapus Backup',
                'show_logout' => 'Tampilkan Logout',
            ],
        ],

        'pesanan-online' => [
            'name' => 'Pesanan Online',
            'icon' => 'cart',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Pesanan',
                'can_edit' => 'Kelola Pesanan',
                'can_delete' => 'Hapus Pesanan',
            ],
        ],

        'riwayat' => [
            'name' => 'Riwayat',
            'icon' => 'clock-history',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat Riwayat',
                'can_edit' => 'Edit Riwayat',
                'can_delete' => 'Hapus Riwayat',
            ],
        ],

        'user-manager' => [
            'name' => 'User Manager',
            'icon' => 'people-fill',
            'permissions' => [
                'is_active' => 'ON/OFF Menu',
                'can_view' => 'Lihat User',
                'can_add' => 'Tambah User',
                'can_edit' => 'Edit User',
                'can_delete' => 'Hapus User',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    |
    | Group permission untuk kemudahan manage
    |
    */
    'groups' => [
        'crud_basic' => ['is_active', 'can_view', 'can_add', 'can_edit', 'can_delete'],
        'transaksi' => ['is_active', 'can_view', 'can_cancel', 'can_edit', 'can_delete'],
        'laporan' => ['is_active', 'can_view'],
        'pengaturan' => ['is_active', 'can_view', 'can_change_password', 'can_restore_data', 'show_delete_backup', 'show_logout'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Labels
    |--------------------------------------------------------------------------
    |
    | Label untuk setiap permission
    |
    */
    'labels' => [
        'is_active' => 'ON/OFF Menu',
        'can_view' => 'Lihat/View',
        'can_add' => 'Tambah/Add',
        'can_edit' => 'Edit',
        'can_delete' => 'Hapus/Delete',
        'can_cancel' => 'Batalkan',
        'can_change_password' => 'Ubah Password',
        'can_restore_data' => 'Restore Data',
        'show_delete_backup' => 'Hapus Backup',
        'show_logout' => 'Logout',
        'can_access_settings' => 'Akses Pengaturan',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permission Icons
    |--------------------------------------------------------------------------
    |
    | Icon Bootstrap untuk setiap permission
    |
    */
    'icons' => [
        'is_active' => 'toggle-on',
        'can_view' => 'eye',
        'can_add' => 'plus-circle',
        'can_edit' => 'pencil',
        'can_delete' => 'trash',
        'can_cancel' => 'x-circle',
        'can_change_password' => 'key',
        'can_restore_data' => 'arrow-counterclockwise',
        'show_delete_backup' => 'trash',
        'show_logout' => 'box-arrow-right',
        'can_access_settings' => 'gear',
    ],
];