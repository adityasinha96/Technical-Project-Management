<?php

namespace App\Enums;

enum LoginEventType: string
{
    case Login = 'login';
    case Failed = 'failed';
    case Logout = 'logout';
    case Lockout = 'lockout';
    case PasswordReset = 'password_reset';
    case SessionRevoked = 'session_revoked';

    public function label(): string
    {
        return match ($this) {
            self::Login => 'Successful Login',
            self::Failed => 'Failed Login',
            self::Logout => 'Logout',
            self::Lockout => 'Login Lockout',
            self::PasswordReset => 'Password Reset',
            self::SessionRevoked => 'Session Revoked',
        };
    }
}