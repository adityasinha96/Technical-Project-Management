<?php

namespace App\Services\Attachments;

use App\Enums\ActivityVisibility;
use App\Models\Project;
use App\Models\ProjectFile;
use App\Models\User;
use App\Services\Projects\ProjectActivityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class ProjectAttachmentService
{
    public function __construct(
        private readonly ProjectActivityService $activityService
    ) {
    }

    public function storeMany(
        Project $project,
        Model $attachable,
        array $files,
        User $uploadedBy,
        string $category,
        ActivityVisibility $visibility =
            ActivityVisibility::Team,
        ?int $visibleToUserId = null
    ): array {
        $storedFiles = [];
        $storedPaths = [];

        try {
            DB::transaction(
                function () use (
                    $project,
                    $attachable,
                    $files,
                    $uploadedBy,
                    $category,
                    $visibility,
                    $visibleToUserId,
                    &$storedFiles,
                    &$storedPaths
                ): void {
                    foreach ($files as $uploadedFile) {
                        if (!$uploadedFile instanceof UploadedFile) {
                            continue;
                        }

                        $extension = strtolower(
                            $uploadedFile
                                ->getClientOriginalExtension()
                        );

                        $storedName = Str::uuid()
                            . (
                                $extension
                                    ? ".{$extension}"
                                    : ''
                            );

                        $directory = sprintf(
                            'projects/%d/history/%s',
                            $project->id,
                            $category
                        );

                        $path = $uploadedFile
                            ->storeAs(
                                $directory,
                                $storedName,
                                'local'
                            );

                        $storedPaths[] = [
                            'disk' => 'local',
                            'path' => $path,
                        ];

                        $projectFile = $project
                            ->files()
                            ->create([
                                'uploaded_by' =>
                                    $uploadedBy->id,

                                'category' => $category,

                                'original_name' =>
                                    $uploadedFile
                                        ->getClientOriginalName(),

                                'stored_name' =>
                                    $storedName,

                                'path' => $path,
                                'disk' => 'local',
                                'is_private' => true,

                                'mime_type' =>
                                    $uploadedFile
                                        ->getMimeType(),

                                'size' =>
                                    $uploadedFile
                                        ->getSize(),

                                'checksum_sha256' =>
                                    hash_file(
                                        'sha256',
                                        $uploadedFile
                                            ->getRealPath()
                                    ),

                                'description' =>
                                    "Attached to {$category}.",
                            ]);

                        $attachable
                            ->fileLinks()
                            ->create([
                                'project_file_id' =>
                                    $projectFile->id,

                                'created_by' =>
                                    $uploadedBy->id,
                            ]);

                        $this->activityService
                            ->logCustom(
                                project: $project,
                                event:
                                    'attachment_uploaded',

                                title:
                                    "Attachment uploaded: {$projectFile->original_name}",

                                subject: $attachable,

                                metadata: [
                                    'project_file_id' =>
                                        $projectFile->id,

                                    'category' =>
                                        $category,

                                    'mime_type' =>
                                        $projectFile
                                            ->mime_type,

                                    'size' =>
                                        $projectFile->size,
                                ],

                                visibility: $visibility,

                                visibleToUserId:
                                    $visibleToUserId,

                                actorId:
                                    $uploadedBy->id
                            );

                        $storedFiles[] =
                            $projectFile;
                    }
                }
            );
        } catch (Throwable $exception) {
            foreach ($storedPaths as $storedPath) {
                Storage::disk(
                    $storedPath['disk']
                )->delete(
                    $storedPath['path']
                );
            }

            throw $exception;
        }

        return $storedFiles;
    }

    public function delete(
        ProjectFile $projectFile,
        User $deletedBy
    ): void {
        abort_unless(
            in_array(
                $projectFile->category,
                [
                    'general',
                    'note',
                    'work_log',
                    'ticket',
                    'ticket_comment',
                ],
                true
            ),
            403,
            'Protected financial or approval files cannot be deleted here.'
        );

        $projectFile->loadMissing(
            'project',
            'links.fileable'
        );

        $project = $projectFile->project;
        $path = $projectFile->path;
        $disk = $projectFile->disk;

        $this->activityService->logCustom(
            project: $project,
            event: 'attachment_deleted',

            title:
                "Attachment deleted: {$projectFile->original_name}",

            metadata: [
                'category' =>
                    $projectFile->category,

                'size' =>
                    $projectFile->size,
            ],

            visibility:
                ActivityVisibility::Management,

            actorId:
                $deletedBy->id
        );

        DB::transaction(
            function () use ($projectFile): void {
                $projectFile
                    ->links()
                    ->delete();

                $projectFile->delete();
            }
        );

        Storage::disk($disk)->delete($path);
    }

    public function deleteForAttachable(
        Model $attachable,
        User $deletedBy
    ): void {
        $attachable->loadMissing(
            'fileLinks.file'
        );

        foreach (
            $attachable->fileLinks
            as $link
        ) {
            if ($link->file) {
                $this->delete(
                    $link->file,
                    $deletedBy
                );
            }
        }
    }
}