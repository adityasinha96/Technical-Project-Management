<?php

return [

    'disk' => env(
        'SYSTEM_BACKUP_DISK',
        'backups'
    ),

    'temporary_directory' =>
        storage_path(
            'app/backup-temp'
        ),

    'database' => [
        'binary' => env(
            'MYSQLDUMP_BINARY',
            'mysqldump'
        ),

        'timeout_seconds' =>
            (int) env(
                'BACKUP_DATABASE_TIMEOUT',
                900
            ),
    ],

    'files' => [
        'include' => [
            storage_path('app'),
        ],

        'exclude' => [
            storage_path('app/backups'),
            storage_path('app/backup-temp'),
        ],
    ],

    'encryption' => [
        'enabled' => env(
            'BACKUP_ENCRYPTION_ENABLED',
            true
        ),

        'openssl_binary' => env(
            'OPENSSL_BINARY',
            'openssl'
        ),

        'key' => env(
            'BACKUP_ENCRYPTION_KEY'
        ),

        'iterations' => 200000,
    ],

    'retention' => [
        'days' => (int) env(
            'BACKUP_RETENTION_DAYS',
            30
        ),
    ],
];