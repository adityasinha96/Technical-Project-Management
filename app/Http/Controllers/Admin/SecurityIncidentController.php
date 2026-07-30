<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AuditCategory;
use App\Enums\AuditSeverity;
use App\Enums\SecurityIncidentStatus;
use App\Http\Controllers\Controller;
use App\Models\SecurityIncident;
use App\Models\User;
use App\Services\Audit\AuditLogService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SecurityIncidentController extends Controller
{
    public function index(
        Request $request
    ): View {
        $validated =
            $request->validate([
                'status' => [
                    'nullable',
                    Rule::enum(
                        SecurityIncidentStatus::class
                    ),
                ],

                'severity' => [
                    'nullable',
                    Rule::enum(
                        AuditSeverity::class
                    ),
                ],

                'incident_type' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'search' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
            ]);

        $incidents =
            SecurityIncident::query()
                ->when(
                    $validated['status']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            'status',
                            $value
                        )
                )
                ->when(
                    $validated['severity']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            'severity',
                            $value
                        )
                )
                ->when(
                    $validated['incident_type']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            'incident_type',
                            'like',
                            "%{$value}%"
                        )
                )
                ->when(
                    $validated['search']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            function ($query) use (
                                $value
                            ): void {
                                $query
                                    ->where(
                                        'title',
                                        'like',
                                        "%{$value}%"
                                    )
                                    ->orWhere(
                                        'description',
                                        'like',
                                        "%{$value}%"
                                    )
                                    ->orWhere(
                                        'incident_uuid',
                                        'like',
                                        "%{$value}%"
                                    )
                                    ->orWhere(
                                        'fingerprint',
                                        'like',
                                        "%{$value}%"
                                    );
                            }
                        )
                )
                ->orderByRaw(
                    "
                    CASE severity
                        WHEN 'critical' THEN 1
                        WHEN 'high' THEN 2
                        WHEN 'warning' THEN 3
                        WHEN 'info' THEN 4
                        ELSE 5
                    END
                    "
                )
                ->orderByDesc(
                    'last_seen_at'
                )
                ->orderByDesc('id')
                ->paginate(50)
                ->withQueryString();

        return view(
            'admin.security.incidents.index',
            [
                'incidents' =>
                    $incidents,

                'statuses' =>
                    SecurityIncidentStatus::cases(),

                'severities' =>
                    AuditSeverity::cases(),
            ]
        );
    }

    public function show(
        SecurityIncident $securityIncident
    ): View {
        $users =
            User::query()
                ->where(
                    'status',
                    'active'
                )
                ->orderBy('name')
                ->get([
                    'id',
                    'name',
                    'email',
                ]);

        return view(
            'admin.security.incidents.show',
            [
                'securityIncident' =>
                    $securityIncident,

                'statuses' =>
                    SecurityIncidentStatus::cases(),

                'users' =>
                    $users,
            ]
        );
    }

    public function update(
        Request $request,
        SecurityIncident $securityIncident,
        AuditLogService $audit
    ): RedirectResponse {
        $validated =
            $request->validate([
                'status' => [
                    'required',
                    Rule::enum(
                        SecurityIncidentStatus::class
                    ),
                ],

                'assigned_to' => [
                    'nullable',
                    'integer',
                    Rule::exists(
                        'users',
                        'id'
                    ),
                ],

                'resolution_notes' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ]);

        $oldValues =
            $securityIncident
                ->only([
                    'status',
                    'assigned_to',
                    'acknowledged_by',
                    'acknowledged_at',
                    'resolved_by',
                    'resolved_at',
                    'resolution_notes',
                ]);

        $status =
            SecurityIncidentStatus::from(
                $validated['status']
            );

        $administrator =
            $request->user('web');

        abort_unless(
            $administrator instanceof User,
            403
        );

        $securityIncident->status =
            $status->value;

        $securityIncident->assigned_to =
            $validated['assigned_to']
            ?? null;

        $securityIncident->resolution_notes =
            $validated['resolution_notes']
            ?? null;

        if (
            $status ===
            SecurityIncidentStatus::Acknowledged
        ) {
            $securityIncident->acknowledged_by ??=
                $administrator->id;

            $securityIncident->acknowledged_at ??=
                now();
        }

        if (
            in_array(
                $status,
                [
                    SecurityIncidentStatus::Resolved,
                    SecurityIncidentStatus::Dismissed,
                ],
                true
            )
        ) {
            $securityIncident->resolved_by =
                $administrator->id;

            $securityIncident->resolved_at =
                now();
        }

        if (
            $status ===
            SecurityIncidentStatus::Open
        ) {
            $securityIncident->resolved_by =
                null;

            $securityIncident->resolved_at =
                null;
        }

        $securityIncident->save();

        $audit->record(
            eventType:
                'security.incident.updated',

            category:
                AuditCategory::Security,

            severity:
                $securityIncident->severity
                instanceof AuditSeverity
                    ? $securityIncident->severity
                    : AuditSeverity::Info,

            auditable:
                $securityIncident,

            oldValues:
                $oldValues,

            newValues:
                $securityIncident
                    ->fresh()
                    ->only([
                        'status',
                        'assigned_to',
                        'acknowledged_by',
                        'acknowledged_at',
                        'resolved_by',
                        'resolved_at',
                        'resolution_notes',
                    ]),

            metadata: [
                'incident_uuid' =>
                    $securityIncident
                        ->incident_uuid,

                'incident_type' =>
                    $securityIncident
                        ->incident_type,
            ],

            actor:
                $administrator,

            guard:
                'web'
        );

        return redirect()
            ->route(
                'security.incidents.show',
                $securityIncident
            )
            ->with(
                'success',
                'Security incident updated successfully.'
            );
    }
}

