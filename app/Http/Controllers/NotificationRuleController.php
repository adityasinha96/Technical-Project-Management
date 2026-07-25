<?php

namespace App\Http\Controllers;

use App\Enums\NotificationRecipientStrategy;
use App\Enums\NotificationSeverity;
use App\Http\Requests\UpdateNotificationRuleRequest;
use App\Models\NotificationRule;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class NotificationRuleController extends Controller
{
    public function index(): View
    {
        return view(
            'notification-rules.index',
            [
                'rules' =>
                    NotificationRule::query()
                        ->orderBy('name')
                        ->get(),

                'severities' =>
                    NotificationSeverity::cases(),

                'recipientStrategies' =>
                    NotificationRecipientStrategy::cases(),
            ]
        );
    }

    public function update(
        UpdateNotificationRuleRequest $request,
        NotificationRule $notificationRule
    ): RedirectResponse {
        $notificationRule->update(
            $request->validated()
        );

        return back()->with(
            'success',
            "{$notificationRule->name} updated."
        );
    }
}