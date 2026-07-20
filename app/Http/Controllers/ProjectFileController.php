<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProjectFileController extends Controller
{
    public function store(
        Request $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validate([
            'category' => [
                'required',
                'string',
                'max:50',
            ],

            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'files' => [
                'required',
                'array',
                'max:10',
            ],

            'files.*' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,csv,txt,png,jpg,jpeg,webp,zip',
                'max:10240',
            ],
        ]);

        foreach ($validated['files'] as $uploadedFile) {
            $extension = strtolower(
                $uploadedFile->getClientOriginalExtension()
            );

            $storedName = Str::uuid()
                . ($extension ? ".{$extension}" : '');

            $path = $uploadedFile->storeAs(
                "projects/{$project->id}/files",
                $storedName,
                'public'
            );

            $project->files()->create([
                'uploaded_by' => $request->user()->id,
                'category' => $validated['category'],
                'original_name' =>
                    $uploadedFile->getClientOriginalName(),
                'stored_name' => $storedName,
                'path' => $path,
                'disk' => 'public',
                'mime_type' => $uploadedFile->getMimeType(),
                'size' => $uploadedFile->getSize(),
                'description' =>
                    $validated['description'] ?? null,
            ]);
        }

        return back()->with(
            'success',
            'Project file(s) uploaded successfully.'
        );
    }

    public function destroy(
        Project $project,
        ProjectFile $projectFile
    ): RedirectResponse {
        abort_unless(
            $projectFile->project_id === $project->id,
            404
        );

        Storage::disk($projectFile->disk)
            ->delete($projectFile->path);

        $projectFile->delete();

        return back()->with(
            'success',
            'Project file deleted successfully.'
        );
    }
}