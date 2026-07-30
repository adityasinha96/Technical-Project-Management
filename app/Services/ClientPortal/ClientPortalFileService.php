<?php

namespace App\Services\ClientPortal;

use App\Models\ClientUser;
use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClientPortalFileService
{
    public function storeMany(
        Project $project,
        Model $attachable,
        array $files,
        ClientUser $clientUser,
        string $category
    ): Collection {
        return DB::transaction(
            function () use (
                $project,
                $attachable,
                $files,
                $clientUser,
                $category
            ): Collection {
                return collect($files)
                    ->map(
                        function (
                            UploadedFile $uploadedFile
                        ) use (
                            $project,
                            $attachable,
                            $clientUser,
                            $category
                        ) {
                            $storedName =
                                Str::uuid()
                                . '.'
                                . $uploadedFile
                                    ->getClientOriginalExtension();

                            $path =
                                $uploadedFile
                                    ->storeAs(
                                        "projects/{$project->id}/client-portal",
                                        $storedName,
                                        'local'
                                    );

                            $file =
                                ProjectFile::create([
                                    'project_id' =>
                                        $project->id,

                                    'category' =>
                                        $category,

                                    'original_name' =>
                                        $uploadedFile
                                            ->getClientOriginalName(),

                                    'stored_name' =>
                                        $storedName,

                                    'path' =>
                                        $path,

                                    'disk' =>
                                        'local',

                                    'mime_type' =>
                                        $uploadedFile
                                            ->getMimeType(),

                                    'size_bytes' =>
                                        $uploadedFile
                                            ->getSize(),

                                    'uploaded_by' =>
                                        null,

                                    'uploaded_by_client_user_id' =>
                                        $clientUser->id,

                                    'client_visible' =>
                                        true,

                                    'shared_with_client_at' =>
                                        now(),
                                ]);

                            $attachable
                                ->fileLinks()
                                ->create([
                                    'project_file_id' =>
                                        $file->id,
                                ]);

                            return $file;
                        }
                    );
            }
        );
    }
}