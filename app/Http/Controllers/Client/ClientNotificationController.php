<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class ClientNotificationController extends Controller
{
    public function index(): View
    {
        $notifications =
            auth('client')->user()
                ->notifications()
                ->latest()
                ->paginate(30);

        return view(
            'client.notifications.index',
            compact('notifications')
        );
    }

    public function open(
        DatabaseNotification $notification
    ): RedirectResponse {
        $clientUser =
            auth('client')->user();

        abort_unless(
            $notification->notifiable_type ===
                $clientUser->getMorphClass()
            && (string)
                $notification->notifiable_id ===
                (string)
                $clientUser->id,
            404
        );

        $notification->markAsRead();

        return filled(
            $notification->data['url']
            ?? null
        )
            ? redirect()->to(
                $notification
                    ->data['url']
            )
            : redirect()->route(
                'client.notifications.index'
            );
    }

    public function markAllRead(
        Request $request
    ): RedirectResponse {
        auth('client')->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }
}