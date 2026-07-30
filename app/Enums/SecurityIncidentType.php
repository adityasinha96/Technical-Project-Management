<?php

namespace App\Enums;

enum SecurityIncidentType: string
{
    case RepeatedLoginFailure = 'repeated_login_failure';
    case LoginLockout = 'login_lockout';
    case NewLoginLocation = 'new_login_location';
    case PermissionEscalation = 'permission_escalation';
    case AccessDeniedBurst = 'access_denied_burst';
    case AuditIntegrityFailure = 'audit_integrity_failure';
    case BackupFailure = 'backup_failure';
    case BackupOverdue = 'backup_overdue';
    case SuspiciousSession = 'suspicious_session';
    case SensitiveDataChange = 'sensitive_data_change';

    public function label(): string
    {
        return match ($this) {
            self::RepeatedLoginFailure =>
                'Repeated Login Failure',

            self::LoginLockout =>
                'Login Lockout',

            self::NewLoginLocation =>
                'Login from New Address',

            self::PermissionEscalation =>
                'Permission Escalation',

            self::AccessDeniedBurst =>
                'Repeated Access Denial',

            self::AuditIntegrityFailure =>
                'Audit Integrity Failure',

            self::BackupFailure =>
                'Backup Failure',

            self::BackupOverdue =>
                'Backup Overdue',

            self::SuspiciousSession =>
                'Suspicious Session',

            self::SensitiveDataChange =>
                'Sensitive Data Change',
        };
    }
}