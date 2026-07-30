<?php

namespace App\Services\Backups;

use App\Enums\BackupStatus;
use App\Enums\BackupVerificationStatus;
use App\Models\BackupRun;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class BackupVerificationService
{
    public function verify(
        BackupRun $backup
    ): bool {
        if (
            $backup->status !==
                BackupStatus::Completed
            || !$backup->path
        ) {
            return false;
        }

        $disk =
            Storage::disk(
                $backup->disk
            );

        if (
            !$disk->exists(
                $backup->path
            )
        ) {
            $this->invalidate(
                $backup,
                'Backup file does not exist.'
            );

            return false;
        }

        $stream =
            $disk->readStream(
                $backup->path
            );

        if ($stream === false) {
            $this->invalidate(
                $backup,
                'Unable to read backup file.'
            );

            return false;
        }

        try {
            $context =
                hash_init('sha256');

            hash_update_stream(
                $context,
                $stream
            );

            $checksum =
                hash_final(
                    $context
                );
        } finally {
            fclose($stream);
        }

        if (
            !hash_equals(
                (string)
                $backup
                    ->checksum_sha256,
                $checksum
            )
        ) {
            $this->invalidate(
                $backup,
                'Backup checksum mismatch.'
            );

            return false;
        }

        $backup->update([
            'verification_status' =>
                BackupVerificationStatus::Valid
                    ->value,

            'verified_at' =>
                now(),

            'verification_message' =>
                'Backup exists and SHA-256 checksum matches.',
        ]);

        return true;
    }

    private function invalidate(
        BackupRun $backup,
        string $message
    ): void {
        $backup->update([
            'verification_status' =>
                BackupVerificationStatus::Invalid
                    ->value,

            'verified_at' =>
                now(),

            'verification_message' =>
                $message,
        ]);
    }
}