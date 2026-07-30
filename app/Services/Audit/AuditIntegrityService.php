<?php

namespace App\Services\Audit;

use App\Models\AuditChainHead;
use App\Models\AuditLog;

class AuditIntegrityService
{
    public function __construct(
        private readonly AuditLogService $auditLogService
    ) {
    }

    public function verify(): array
    {
        $expectedSequence = 1;

        $previousHash =
            str_repeat('0', 64);

        $checked = 0;
        $failure = null;

        /*
         * cursor() keeps memory usage low while preserving strict sequence
         * ordering. It also avoids mixing orderBy('sequence') with an
         * id-based chunk cursor.
         */
        $logs =
            AuditLog::query()
                ->orderBy('sequence')
                ->cursor();

        foreach ($logs as $log) {
            $actualSequence =
                (int) $log->sequence;

            if (
                $actualSequence !==
                $expectedSequence
            ) {
                $failure = [
                    'reason' =>
                        'Sequence gap detected.',

                    'expected_sequence' =>
                        $expectedSequence,

                    'actual_sequence' =>
                        $actualSequence,
                ];

                break;
            }

            $actualPreviousHash =
                (string) $log->previous_hash;

            if (
                !hash_equals(
                    $previousHash,
                    $actualPreviousHash
                )
            ) {
                $failure = [
                    'reason' =>
                        'Previous hash mismatch.',

                    'sequence' =>
                        $actualSequence,

                    'expected_previous_hash' =>
                        $previousHash,

                    'actual_previous_hash' =>
                        $actualPreviousHash,
                ];

                break;
            }

            $payload =
                $this->auditLogService
                    ->payloadFromLog(
                        $log
                    );

            $expectedHash =
                $this->auditLogService
                    ->hashPayload(
                        $payload
                    );

            $actualHash =
                (string) $log->entry_hash;

            if (
                !hash_equals(
                    $expectedHash,
                    $actualHash
                )
            ) {
                $failure = [
                    'reason' =>
                        'Entry hash mismatch.',

                    'sequence' =>
                        $actualSequence,

                    'audit_uuid' =>
                        $log->audit_uuid,

                    'expected_hash' =>
                        $expectedHash,

                    'actual_hash' =>
                        $actualHash,
                ];

                break;
            }

            $previousHash =
                $actualHash;

            $expectedSequence++;
            $checked++;
        }

        if ($failure) {
            return [
                'valid' => false,
                'checked' => $checked,
                'failure' => $failure,
            ];
        }

        $head =
            AuditChainHead::query()
                ->findOrFail(1);

        $headSequence =
            (int) $head->last_sequence;

        $headHash =
            (string) $head->last_hash;

        $headValid =
            $headSequence ===
                $checked
            && hash_equals(
                $headHash,
                $previousHash
            );

        return [
            'valid' => $headValid,
            'checked' => $checked,

            'failure' =>
                $headValid
                    ? null
                    : [
                        'reason' =>
                            'Audit-chain head does not match the final entry.',

                        'expected_sequence' =>
                            $checked,

                        'actual_sequence' =>
                            $headSequence,

                        'expected_hash' =>
                            $previousHash,

                        'actual_hash' =>
                            $headHash,
                    ],
        ];
    }
}

