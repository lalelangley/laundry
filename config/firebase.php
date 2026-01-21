<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default Firebase project
    |--------------------------------------------------------------------------
    |
    | Project default yang akan dipakai ketika kamu tidak menentukan project lain.
    |
    */
    'default' => env('FIREBASE_PROJECT', 'app'),

    /*
    |--------------------------------------------------------------------------
    | Firebase project configurations
    |--------------------------------------------------------------------------
    */
    'projects' => [

        'app' => [

            /*
            |--------------------------------------------------------------------------
            | Credentials / Service Account
            |--------------------------------------------------------------------------
            */
            'credentials' => env('FIREBASE_CREDENTIALS', env('GOOGLE_APPLICATION_CREDENTIALS')),

            /*
            |--------------------------------------------------------------------------
            | Firebase Auth Component
            |--------------------------------------------------------------------------
            */
            'auth' => [
                'tenant_id' => env('FIREBASE_AUTH_TENANT_ID'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Firestore (opsional)
            |--------------------------------------------------------------------------
            */
            'firestore' => [
                // 'database' => env('FIREBASE_FIRESTORE_DATABASE'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Realtime Database (opsional)
            |--------------------------------------------------------------------------
            */
            'database' => [
                'url' => env('FIREBASE_DATABASE_URL'), // Tambahkan di .env kalau pakai RTDB
                // 'auth_variable_override' => [
                //     'uid' => 'my-service-worker'
                // ],
            ],

            /*
            |--------------------------------------------------------------------------
            | Dynamic Links (opsional)
            |--------------------------------------------------------------------------
            */
            'dynamic_links' => [
                'default_domain' => env('FIREBASE_DYNAMIC_LINKS_DEFAULT_DOMAIN'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Firebase Cloud Storage (opsional)
            |--------------------------------------------------------------------------
            */
            'storage' => [
                'default_bucket' => env('FIREBASE_STORAGE_DEFAULT_BUCKET'),
            ],

            /*
            |--------------------------------------------------------------------------
            | Cache
            |--------------------------------------------------------------------------
            */
            'cache_store' => env('FIREBASE_CACHE_STORE', 'file'),

            /*
            |--------------------------------------------------------------------------
            | Logging
            |--------------------------------------------------------------------------
            */
            'logging' => [
                'http_log_channel' => env('FIREBASE_HTTP_LOG_CHANNEL'),
                'http_debug_log_channel' => env('FIREBASE_HTTP_DEBUG_LOG_CHANNEL'),
            ],

            /*
            |--------------------------------------------------------------------------
            | HTTP Client Options
            |--------------------------------------------------------------------------
            */
            'http_client_options' => [
                'proxy' => env('FIREBASE_HTTP_CLIENT_PROXY'),
                'timeout' => env('FIREBASE_HTTP_CLIENT_TIMEOUT', 10.0),
                'guzzle_middlewares' => [
                    // Contoh: MyInvokableMiddleware::class,
                ],
            ],

        ],

    ],

];
