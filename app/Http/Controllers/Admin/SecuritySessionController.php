<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SecuritySession;
use App\Models\User;
use App\Services\Security\SecuritySessionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SecuritySessionController extends Controller
{
    public function index(
        Request $request
    ): View {
        $validated =
            $request->validate([
                'status' => [
                    'nullable',
                    Rule::in([
                        'active',
                        'revoked',
                        'all',
                    ]),
                ],

                'guard' => [
                    'nullable',
                    'string',
                    'max:40',
                ],
            ]);

        $status =
            $validated['status']
            ?? 'active';

        $guard =
            $validated['guard']
            ?? null;

        $securitySessions =
            SecuritySession::query()
                ->when(
                    $status === 'active',
                    fn ($query) =>
                        $query->whereNull(
                            'revoked_at'
                        )
                )
                ->when(
                    $status === 'revoked',
                    fn ($query) =>
                        $query->whereNotNull(
                            'revoked_at'
                        )
                )
                ->when(
                    $guard,
                    fn ($query, $value) =>
                        $query->where(
                            'guard',
                            $value
                        )
                )
                ->orderByDesc(
                    'last_seen_at'
                )
                ->orderByDesc('id')
                ->paginate(50)
                ->withQueryString();

        return view(
            'admin.security.sessions.index',
            compact(
                'securitySessions',
                'status',
                'guard'
            )
        );
    }

    public function destroy(
        Request $request,
        SecuritySession $securitySession,
        SecuritySessionService $securitySessionService
    ): RedirectResponse {
        $validated =
            $request->validate([
                'reason' => [
                    'nullable',
                    'string',
                    'max:2000',
                ],
            ]);

        if ($securitySession->revoked_at) {
            return back()->with(
                'success',
                'This security session has already been revoked.'
            );
        }

        $administrator =
            $request->user('web');

        if (!$administrator instanceof User) {
            abort(403);
        }

        $reason =
            trim(
                (string) (
                    $validated['reason']
                    ?? ''
                )
            );

        $securitySessionService->revoke(
            securitySession:
                $securitySession,

            revokedBy:
                $administrator,

            reason:
                $reason !== ''
                    ? $reason
                    : 'Revoked by administrator'
        );

        return back()->with(
            'success',
            'Security session revoked successfully.'
        );
    }
}

