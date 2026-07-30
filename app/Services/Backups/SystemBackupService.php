<?php

namespace App\Services\Backups;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\BackupStatus;
use App\Enums\BackupType;
use App\Enums\BackupVerificationStatus;
use App\Enums\SecurityIncidentType;
use App\Models\BackupRun;
use App\Services\Audit\AuditLogService;
use App\Services\Security\SecurityIncidentService;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;
use ZipArchive;

class SystemBackupService
{
    public function __construct(
        private readonly AuditLogService $audit,
        private readonly SecurityIncidentService $incidents
    ) {
    }

    public function execute(
        BackupRun $backup
    ): BackupRun {
        $temporaryRoot =
            rtrim(
                config(
                    'system-backup.temporary_directory'
                ),
                DIRECTORY_SEPARATOR
            );

        $workingDirectory =
            $temporaryRoot
            . DIRECTORY_SEPARATOR
            . $backup->backup_uuid;

        File::ensureDirectoryExists(
            $workingDirectory,
            0700,
            true
        );

        $backup->update([
            'status' =>
                BackupStatus::Running->value,

            'started_at' => now(),
            'error_message' => null,
        ]);

        try {
            $archivePath =
                $workingDirectory
                . DIRECTORY_SEPARATOR
                . 'backup.zip';

            $manifest = [
                'backup_uuid' =>
                    $backup->backup_uuid,

                'application' =>
                    config('app.name'),

                'environment' =>
                    app()->environment(),

                'backup_type' =>
                    $backup
                        ->backup_type
                        ->value,

                'created_at' =>
                    now()->toIso8601String(),

                'database' => null,
                'files' => [],
            ];

            $zip = new ZipArchive();

            $openResult =
                $zip->open(
                    $archivePath,
                    ZipArchive::CREATE
                    | ZipArchive::OVERWRITE
                );

            if ($openResult !== true) {
                throw new RuntimeException(
                    "Unable to create ZIP archive. Code: {$openResult}"
                );
            }

            if (
                in_array(
                    $backup->backup_type,
                    [
                        BackupType::Database,
                        BackupType::Full,
                    ],
                    true
                )
            ) {
                $databaseDump =
                    $this->createDatabaseDump(
                        $workingDirectory
                    );

                $zip->addFile(
                    $databaseDump,
                    'database/database.sql'
                );

                $manifest['database'] = [
                    'driver' =>
                        config(
                            'database.default'
                        ),

                    'database' =>
                        config(
                            'database.connections.'
                            . config(
                                'database.default'
                            )
                            . '.database'
                        ),

                    'sha256' =>
                        hash_file(
                            'sha256',
                            $databaseDump
                        ),
                ];
            }

            if (
                in_array(
                    $backup->backup_type,
                    [
                        BackupType::Files,
                        BackupType::Full,
                    ],
                    true
                )
            ) {
                $manifest['files'] =
                    $this->addFilesToArchive(
                        $zip
                    );
            }

            $manifestPath =
                $workingDirectory
                . DIRECTORY_SEPARATOR
                . 'manifest.json';

            File::put(
                $manifestPath,
                json_encode(
                    $manifest,
                    JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES
                    | JSON_THROW_ON_ERROR
                )
            );

            $zip->addFile(
                $manifestPath,
                'manifest.json'
            );

            if (!$zip->close()) {
                throw new RuntimeException(
                    'Unable to finalise ZIP archive.'
                );
            }

            $finalPath =
                $archivePath;

            $isEncrypted = false;
            $encryptionMethod = null;

            if (
                config(
                    'system-backup.encryption.enabled',
                    true
                )
            ) {
                $finalPath =
                    $this->encryptArchive(
                        $archivePath
                    );

                $isEncrypted = true;

                $encryptionMethod =
                    'AES-256-CBC/PBKDF2';
            }

            $filename =
                sprintf(
                    '%s-%s-%s.%s',
                    str(
                        config(
                            'app.name',
                            'uipro-pms'
                        )
                    )->slug(),

                    $backup
                        ->backup_type
                        ->value,

                    now()->format(
                        'Y-m-d-His'
                    ),

                    $isEncrypted
                        ? 'zip.enc'
                        : 'zip'
                );

            $storagePath =
                now()->format('Y/m')
                . '/'
                . $filename;

            $stream =
                fopen(
                    $finalPath,
                    'rb'
                );

            if ($stream === false) {
                throw new RuntimeException(
                    'Unable to open backup archive.'
                );
            }

            try {
                Storage::disk(
                    $backup->disk
                )->put(
                    $storagePath,
                    $stream
                );
            } finally {
                fclose($stream);
            }

            $checksum =
                hash_file(
                    'sha256',
                    $finalPath
                );

            $size =
                filesize(
                    $finalPath
                );

            if (
                $size === false
                || $size < 1
            ) {
                throw new RuntimeException(
                    'Created backup file is empty.'
                );
            }

            $backup->update([
                'status' =>
                    BackupStatus::Completed
                        ->value,

                'verification_status' =>
                    BackupVerificationStatus::Valid
                        ->value,

                'path' =>
                    $storagePath,

                'filename' =>
                    $filename,

                'size_bytes' =>
                    $size,

                'checksum_sha256' =>
                    $checksum,

                'is_encrypted' =>
                    $isEncrypted,

                'encryption_method' =>
                    $encryptionMethod,

                'completed_at' =>
                    now(),

                'verified_at' =>
                    now(),

                'verification_message' =>
                    'Archive created, uploaded and checksum recorded.',

                'manifest' =>
                    $manifest,
            ]);

            $this->audit->record(
                eventType:
                    'backup.completed',

                category:
                    AuditCategory::Backup,

                severity:
                    AuditSeverity::Notice,

                auditable:
                    $backup,

                metadata: [
                    'filename' =>
                        $filename,

                    'size_bytes' =>
                        $size,

                    'checksum_sha256' =>
                        $checksum,

                    'encrypted' =>
                        $isEncrypted,
                ]
            );

            return $backup->refresh();
        } catch (Throwable $exception) {
            $backup->update([
                'status' =>
                    BackupStatus::Failed
                        ->value,

                'verification_status' =>
                    BackupVerificationStatus::Invalid
                        ->value,

                'failed_at' =>
                    now(),

                'error_message' =>
                    str(
                        $exception
                            ->getMessage()
                    )->limit(10000),
            ]);

            $this->audit->record(
                eventType:
                    'backup.failed',

                category:
                    AuditCategory::Backup,

                severity:
                    AuditSeverity::Critical,

                auditable:
                    $backup,

                metadata: [
                    'error' =>
                        $exception
                            ->getMessage(),
                ]
            );

            $this->incidents->raise(
                type:
                    SecurityIncidentType::BackupFailure,

                severity:
                    AuditSeverity::Critical,

                title:
                    'System backup failed',

                description:
                    $exception->getMessage(),

                fingerprintSource:
                    now()->format(
                        'Y-m-d-H'
                    ),

                subject:
                    $backup
            );

            report($exception);

            throw $exception;
        } finally {
            File::deleteDirectory(
                $workingDirectory
            );
        }
    }

    private function createDatabaseDump(
        string $workingDirectory
    ): string {
        $connectionName =
            config(
                'database.default'
            );

        $connection =
            config(
                "database.connections.{$connectionName}"
            );

        if (
            ($connection['driver'] ?? null)
            !== 'mysql'
        ) {
            throw new RuntimeException(
                'Phase 11 backup currently supports MySQL databases.'
            );
        }

        $dumpPath =
            $workingDirectory
            . DIRECTORY_SEPARATOR
            . 'database.sql';

        $binary =
            (string) config(
                'system-backup.database.binary',
                'mysqldump'
            );

        $arguments = [
            escapeshellcmd($binary),

            '--host='
            . escapeshellarg(
                (string)
                $connection['host']
            ),

            '--port='
            . escapeshellarg(
                (string)
                $connection['port']
            ),

            '--user='
            . escapeshellarg(
                (string)
                $connection['username']
            ),

            '--single-transaction',
            '--quick',
            '--skip-lock-tables',
            '--routines',
            '--triggers',
            '--events',
            '--hex-blob',
            '--default-character-set=utf8mb4',

            '--result-file='
            . escapeshellarg(
                $dumpPath
            ),

            escapeshellarg(
                (string)
                $connection['database']
            ),
        ];

        $result =
            Process::timeout(
                (int) config(
                    'system-backup.database.timeout_seconds',
                    900
                )
            )
                ->env([
                    'MYSQL_PWD' =>
                        (string)
                        $connection['password'],
                ])
                ->run(
                    implode(
                        ' ',
                        $arguments
                    )
                );

        if ($result->failed()) {
            throw new RuntimeException(
                'mysqldump failed: '
                . str(
                    $result
                        ->errorOutput()
                )->limit(5000)
            );
        }

        if (
            !File::exists(
                $dumpPath
            )
            || File::size(
                $dumpPath
            ) < 1
        ) {
            throw new RuntimeException(
                'mysqldump did not create a valid file.'
            );
        }

        return $dumpPath;
    }

    private function addFilesToArchive(
        ZipArchive $zip
    ): array {
        $manifest = [];

        $includePaths =
            config(
                'system-backup.files.include',
                []
            );

        $excludePaths =
            collect(
                config(
                    'system-backup.files.exclude',
                    []
                )
            )
                ->map(
                    fn (string $path) =>
                        realpath($path)
                        ?: $path
                )
                ->all();

        foreach ($includePaths as $includePath) {
            if (!File::isDirectory($includePath)) {
                continue;
            }

            $basePath =
                realpath($includePath);

            if ($basePath === false) {
                continue;
            }

            foreach (
                File::allFiles(
                    $basePath,
                    true
                )
                as $file
            ) {
                $realPath =
                    $file->getRealPath();

                if ($realPath === false) {
                    continue;
                }

                $excluded =
                    collect(
                        $excludePaths
                    )->contains(
                        fn (string $excludedPath) =>
                            str_starts_with(
                                $realPath,
                                $excludedPath
                            )
                    );

                if ($excluded) {
                    continue;
                }

                $relativePath =
                    ltrim(
                        str_replace(
                            base_path(),
                            '',
                            $realPath
                        ),
                        DIRECTORY_SEPARATOR
                    );

                $archiveName =
                    'files/'
                    . str_replace(
                        DIRECTORY_SEPARATOR,
                        '/',
                        $relativePath
                    );

                $zip->addFile(
                    $realPath,
                    $archiveName
                );

                $manifest[] = [
                    'path' =>
                        $relativePath,

                    'size' =>
                        $file->getSize(),

                    'sha256' =>
                        hash_file(
                            'sha256',
                            $realPath
                        ),
                ];
            }
        }

        return $manifest;
    }

    private function encryptArchive(
        string $archivePath
    ): string {
        $key =
            (string) config(
                'system-backup.encryption.key'
            );

        if ($key === '') {
            throw new RuntimeException(
                'BACKUP_ENCRYPTION_KEY is not configured.'
            );
        }

        $encryptedPath =
            $archivePath . '.enc';

        $binary =
            (string) config(
                'system-backup.encryption.openssl_binary',
                'openssl'
            );

        $iterations =
            (int) config(
                'system-backup.encryption.iterations',
                200000
            );

        $command = implode(' ', [
            escapeshellcmd($binary),
            'enc',
            '-aes-256-cbc',
            '-salt',
            '-pbkdf2',
            '-iter',
            escapeshellarg(
                (string)
                $iterations
            ),
            '-in',
            escapeshellarg(
                $archivePath
            ),
            '-out',
            escapeshellarg(
                $encryptedPath
            ),
            '-pass',
            'env:BACKUP_PASSPHRASE',
        ]);

        $result =
            Process::timeout(900)
                ->env([
                    'BACKUP_PASSPHRASE' =>
                        $key,
                ])
                ->run($command);

        if (
            $result->failed()
            || !File::exists(
                $encryptedPath
            )
        ) {
            throw new RuntimeException(
                'Backup encryption failed: '
                . str(
                    $result
                        ->errorOutput()
                )->limit(5000)
            );
        }

        return $encryptedPath;
    }
}