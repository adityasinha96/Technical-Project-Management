<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\BackupStatus;
use App\Enums\BackupTrigger;
use App\Enums\BackupType;
use App\Enums\BackupVerificationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RunBackupRequest;
use App\Jobs\CreateSystemBackupJob;
use App\Models\BackupRun;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class BackupController extends Controller
{
    public function index(): View
    {
        return view(
            'admin.security.backups.index',
            [
                'backups' =>
                    BackupRun::query()
                        ->with('requestedBy')
                        ->latest()
                        ->paginate(30),

                'backupTypes' =>
                    BackupType::cases(),
            ]
        );
    }

    public function store(
        RunBackupRequest $request
    ): RedirectResponse {
        $backup =
            BackupRun::create([
                'backup_uuid' =>
                    (string) Str::uuid(),

                'backup_type' =>
                    $request->validated(
                        'backup_type'
                    ),

                'trigger' =>
                    BackupTrigger::Manual
                        ->value,

                'status' =>
                    BackupStatus::Queued
                        ->value,

                'verification_status' =>
                    BackupVerificationStatus::Pending
                        ->value,

                'disk' =>
                    config(
                        'system-backup.disk',
                        'backups'
                    ),

                'requested_by' =>
                    $request->user()->id,

                'queued_at' =>
                    now(),

                'retention_until' =>
                    now()->addDays(
                        (int) config(
                            'system-backup.retention.days',
                            30
                        )
                    ),
            ]);

        CreateSystemBackupJob::dispatch(
            $backup->id
        );

        app(
            AuditLogService::class
        )->record(
            eventType:
                'backup.manual_requested',

            category:
                AuditCategory::Backup,

            severity:
                AuditSeverity::High,

            auditable:
                $backup
        );

        return back()->with(
            'success',
            'System backup has been queued.'
        );
    }

    public function download(
        BackupRun $backupRun
    ): StreamedResponse {
        abort_unless(
            $backupRun->status ===
                BackupStatus::Completed
            && filled(
                $backupRun->path
            ),
            404
        );

        abort_unless(
            Storage::disk(
                $backupRun->disk
            )->exists(
                $backupRun->path
            ),
            404
        );

        app(
            AuditLogService::class
        )->record(
            eventType:
                'backup.downloaded',

            category:
                AuditCategory::Backup,

            severity:
                AuditSeverity::Critical,

            auditable:
                $backupRun,

            metadata: [
                'filename' =>
                    $backupRun->filename,
            ]
        );

        return Storage::disk(
            $backupRun->disk
        )->download(
            $backupRun->path,
            $backupRun->filename
        );
    }

    public function destroy(
        BackupRun $backupRun
    ): RedirectResponse {
        abort_unless(
            $backupRun->status !==
                BackupStatus::Running,
            422,
            'A running backup cannot be deleted.'
        );

        if (
            $backupRun->path
            && Storage::disk(
                $backupRun->disk
            )->exists(
                $backupRun->path
            )
        ) {
            Storage::disk(
                $backupRun->disk
            )->delete(
                $backupRun->path
            );
        }

        $backupRun->update([
            'status' =>
                BackupStatus::Deleted
                    ->value,

            'path' => null,
        ]);

        app(
            AuditLogService::class
        )->record(
            eventType:
                'backup.deleted',

            category:
                AuditCategory::Backup,

            severity:
                AuditSeverity::Critical,

            auditable:
                $backupRun
        );

        return back()->with(
            'success',
            'Backup file deleted.'
        );
    }
}