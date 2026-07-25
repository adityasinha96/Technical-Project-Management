<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationCenterController extends Controller
{
    public function index(
        Request $request
    ): View {
        $user = $request->user();

        $notifications = $user
            ->notifications()
            ->when(
                $request->string('filter')
                    ->toString() === 'unread',

                fn ($query) =>
                    $query->whereNull(
                        'read_at'
                    )
            )
            ->when(
                $request->string('filter')
                    ->toString() === 'read',

                fn ($query) =>
                    $query->whereNotNull(
                        'read_at'
                    )
            )
            ->when(
                $request->filled(
                    'severity'
                ),
                fn ($query) =>
                    $query->where(
                        'data->severity',
                        $request->string(
                            'severity'
                        )
                    )
            )
            ->latest('created_at')
            ->paginate(25)
            ->withQueryString();

        return view(
            'notifications.index',
            [
                'notifications' =>
                    $notifications,

                'unreadCount' =>
                    $user
                        ->unreadNotifications()
                        ->count(),
            ]
        );
    }

    public function open(
        Request $request,
        DatabaseNotification $notification
    ): RedirectResponse {
        $this->ensureOwner(
            $request,
            $notification
        );

        if (!$notification->read_at) {
            $notification->markAsRead();
        }

        $url = data_get(
            $notification->data,
            'url'
        );

        return filled($url)
            ? redirect()->to($url)
            : redirect()->route(
                'notifications.index'
            );
    }

    public function markRead(
        Request $request,
        DatabaseNotification $notification
    ): RedirectResponse {
        $this->ensureOwner(
            $request,
            $notification
        );

        $notification->markAsRead();

        return back()->with(
            'success',
            'Notification marked as read.'
        );
    }

    public function markAllRead(
        Request $request
    ): RedirectResponse {
        $request->user()
            ->unreadNotifications()
            ->update([
                'read_at' => now(),
            ]);

        return back()->with(
            'success',
            'All notifications marked as read.'
        );
    }

    public function destroy(
        Request $request,
        DatabaseNotification $notification
    ): RedirectResponse {
        $this->ensureOwner(
            $request,
            $notification
        );

        $notification->delete();

        return back()->with(
            'success',
            'Notification deleted.'
        );
    }

    public function clearRead(
        Request $request
    ): RedirectResponse {
        $request->user()
            ->readNotifications()
            ->delete();

        return back()->with(
            'success',
            'Read notifications cleared.'
        );
    }

    private function ensureOwner(
        Request $request,
        DatabaseNotification $notification
    ): void {
        abort_unless(
            $notification->notifiable_type
                === $request
                    ->user()
                    ->getMorphClass()
            && (string)
                $notification->notifiable_id
                === (string)
                    $request
                        ->user()
                        ->getKey(),
            404
        );
    }
}