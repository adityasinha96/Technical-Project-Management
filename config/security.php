<?php

return [

    'audit' => [
        /*
         * Use a different key from APP_KEY.
         * Do not change it after logs are created.
         */
        'hmac_key' => env(
            'AUDIT_LOG_HMAC_KEY'
        ),

        'sensitive_fields' => [
            'password',
            'password_confirmation',
            'current_password',
            'remember_token',
            'token',
            'token_hash',
            'api_token',
            'secret',
            'client_secret',
            'private_key',
            'access_token',
            'refresh_token',
            'mail_password',
            'db_password',
            'backup_encryption_key',
        ],

        'ignored_fields' => [
            'updated_at',
            'last_seen_at',
            'last_login_at',
            'last_login_ip',
            'remember_token',
        ],
    ],

    'monitoring' => [
        'failed_login_window_minutes' => 15,
        'failed_login_threshold' => 5,

        'access_denied_window_minutes' => 10,
        'access_denied_threshold' => 10,

        'backup_maximum_age_hours' => 30,

        'session_idle_minutes' => 120,

        'security_confirmation_seconds' => 900,
    ],

    'retention' => [
        /*
         * Audit logs are not automatically deleted.
         */
        'login_events_days' => 365,
        'resolved_incidents_days' => 730,
        'expired_sessions_days' => 90,
    ],
];