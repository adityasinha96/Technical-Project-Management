<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProjectMemberController extends Controller
{
    public function store(
        Request $request,
        Project $project
    ): RedirectResponse {
        $validated = $request->validate([
            'user_id' => [
                'required',
                Rule::exists('users', 'id')
                    ->where(
                        fn (Builder $query) =>
                        $query->where('status', 'active')
                    ),
            ],

            'assignment_role' => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        $userId = (int) $validated['user_id'];

        if ($project->team()->whereKey($userId)->exists()) {
            $project->team()->updateExistingPivot(
                $userId,
                [
                    'assignment_role' =>
                        $validated['assignment_role'],

                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ]
            );
        } else {
            $project->team()->attach(
                $userId,
                [
                    'assignment_role' =>
                        $validated['assignment_role'],

                    'assigned_by' => $request->user()->id,
                    'assigned_at' => now(),
                ]
            );
        }

        return back()->with(
            'success',
            'Project team updated successfully.'
        );
    }

    public function destroy(
        Project $project,
        User $user
    ): RedirectResponse {
        if ($project->manager_id === $user->id) {
            return back()->withErrors([
                'member' =>
                    'The project manager cannot be removed from the team. Change the project manager first.',
            ]);
        }

        $project->team()->detach($user->id);

        return back()->with(
            'success',
            'Team member removed successfully.'
        );
    }
}