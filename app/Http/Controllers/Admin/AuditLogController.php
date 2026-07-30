<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(
        Request $request
    ): View {
        $validated =
            $request->validate([
                'category' => [
                    'nullable',
                    'string',
                    'max:40',
                ],

                'severity' => [
                    'nullable',
                    'string',
                    'max:30',
                ],

                'event_type' => [
                    'nullable',
                    'string',
                    'max:120',
                ],

                'actor' => [
                    'nullable',
                    'string',
                    'max:255',
                ],

                'date_from' => [
                    'nullable',
                    'date',
                ],

                'date_to' => [
                    'nullable',
                    'date',
                    'after_or_equal:date_from',
                ],
            ]);

        $logs =
            AuditLog::query()
                ->when(
                    $validated['category']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            'category',
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
                    $validated['event_type']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            'event_type',
                            'like',
                            "%{$value}%"
                        )
                )
                ->when(
                    $validated['actor']
                    ?? null,
                    fn ($query, $value) =>
                        $query->where(
                            fn ($query) =>
                                $query
                                    ->where(
                                        'actor_name',
                                        'like',
                                        "%{$value}%"
                                    )
                                    ->orWhere(
                                        'actor_email',
                                        'like',
                                        "%{$value}%"
                                    )
                        )
                )
                ->when(
                    $validated['date_from']
                    ?? null,
                    fn ($query, $value) =>
                        $query->whereDate(
                            'occurred_at',
                            '>=',
                            $value
                        )
                )
                ->when(
                    $validated['date_to']
                    ?? null,
                    fn ($query, $value) =>
                        $query->whereDate(
                            'occurred_at',
                            '<=',
                            $value
                        )
                )
                ->latest('sequence')
                ->paginate(50)
                ->withQueryString();

        return view(
            'admin.security.audit.index',
            compact('logs')
        );
    }

    public function show(
        AuditLog $auditLog
    ): View {
        return view(
            'admin.security.audit.show',
            compact('auditLog')
        );
    }
}