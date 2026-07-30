<?php

namespace App\Console\Commands;

use App\Enums\AuditSeverity;
use App\Enums\SecurityIncidentType;
use App\Services\Audit\AuditIntegrityService;
use App\Services\Security\SecurityIncidentService;
use Illuminate\Console\Command;

class VerifyAuditLogIntegrity extends Command
{
    protected $signature =
        'audit:verify-integrity';

    protected $description =
        'Verify the cryptographic audit-log chain';

    public function handle(
        AuditIntegrityService $integrity,
        SecurityIncidentService $incidents
    ): int {
        $result =
            $integrity->verify();

        if ($result['valid']) {
            $this->info(
                "{$result['checked']} audit entries verified."
            );

            return self::SUCCESS;
        }

        $this->error(
            'Audit log integrity verification failed.'
        );

        $incidents->raise(
            type:
                SecurityIncidentType::AuditIntegrityFailure,

            severity:
                AuditSeverity::Critical,

            title:
                'Audit log integrity failure',

            description:
                json_encode(
                    $result['failure'],
                    JSON_UNESCAPED_SLASHES
                ),

            fingerprintSource:
                'audit-integrity-failure',

            metadata:
                $result
        );

        return self::FAILURE;
    }
}