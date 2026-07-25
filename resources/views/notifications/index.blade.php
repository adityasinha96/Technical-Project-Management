@extends('layouts.admin')

@section('title', 'Notifications')
@section('page-title', 'Notification Centre')

@section('content')
    <div class="space-y-6">
        <section class="flex flex-col gap-4 rounded-3xl bg-slate-950 p-6 text-white shadow-xl sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.2em] text-indigo-300">
                    Notification Centre
                </p>

                <h1 class="mt-2 text-3xl font-black">
                    Your Notifications
                </h1>

                <p class="mt-2 text-sm text-slate-300">
                    {{ number_format($unreadCount) }}
                    unread notification(s)
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <form
                    method="POST"
                    action="{{ route(
                        'notifications.read-all'
                    ) }}"
                >
                    @csrf
                    @method('PUT')

                    <button class="min-h-11 rounded-2xl bg-white px-4 text-sm font-bold text-slate-950">
                        Mark All Read
                    </button>
                </form>

                <a
                    href="{{ route(
                        'notification-settings.edit'
                    ) }}"
                    class="inline-flex min-h-11 items-center rounded-2xl border border-white/20 px-4 text-sm font-bold text-white"
                >
                    Preferences
                </a>
            </div>
        </section>

        <section class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm">
            <form
                method="GET"
                class="flex flex-col gap-3 sm:flex-row"
            >
                <select
                    name="filter"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All notifications
                    </option>

                    <option
                        value="unread"
                        @selected(
                            request('filter') ===
                            'unread'
                        )
                    >
                        Unread only
                    </option>

                    <option
                        value="read"
                        @selected(
                            request('filter') ===
                            'read'
                        )
                    >
                        Read only
                    </option>
                </select>

                <select
                    name="severity"
                    class="min-h-12 rounded-2xl border border-slate-200 px-4 text-sm"
                >
                    <option value="">
                        All priorities
                    </option>

                    @foreach ([
                        'info' => 'Information',
                        'success' => 'Success',
                        'warning' => 'Warning',
                        'danger' => 'Urgent',
                        'critical' => 'Critical',
                    ] as $value => $label)
                        <option
                            value="{{ $value }}"
                            @selected(
                                request('severity') ===
                                $value
                            )
                        >
                            {{ $label }}
                        </option>
                    @endforeach
                </select>

                <button class="min-h-12 rounded-2xl bg-indigo-600 px-6 text-sm font-bold text-white">
                    Apply Filter
                </button>
            </form>
        </section>

        <section class="space-y-4">
            @forelse ($notifications as $notification)
                @php
                    $data = $notification->data;

                    $severity =
                        \App\Enums\NotificationSeverity::tryFrom(
                            $data['severity'] ?? 'info'
                        )
                        ?? \App\Enums\NotificationSeverity::Info;
                @endphp

                <article class="rounded-3xl border p-5 shadow-sm sm:p-6
                    {{
                        $notification->read_at
                            ? 'border-slate-200 bg-white'
                            : 'border-indigo-200 bg-indigo-50/40'
                    }}"
                >
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start sm:justify-between">
                        <div class="min-w-0 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="rounded-full px-3 py-1 text-xs font-bold ring-1 ring-inset {{ $severity->badgeClasses() }}">
                                    {{ $severity->label() }}
                                </span>

                                @unless ($notification->read_at)
                                    <span class="rounded-full bg-indigo-600 px-3 py-1 text-xs font-bold text-white">
                                        New
                                    </span>
                                @endunless
                            </div>

                            <h2 class="mt-4 text-lg font-black text-slate-950">
                                {{ $data['title']
                                    ?? 'Notification' }}
                            </h2>

                            <p class="mt-2 text-sm leading-6 text-slate-600">
                                {{ $data['message']
                                    ?? '' }}
                            </p>

                            <p class="mt-4 text-xs text-slate-400">
                                {{ $notification
                                    ->created_at
                                    ->format(
                                        'd M Y, h:i A'
                                    ) }}
                                ·
                                {{ $notification
                                    ->created_at
                                    ->diffForHumans() }}
                            </p>
                        </div>

                        <div class="flex shrink-0 flex-wrap gap-2">
                            <a
                                href="{{ route(
                                    'notifications.open',
                                    $notification
                                ) }}"
                                class="inline-flex min-h-10 items-center rounded-xl bg-slate-950 px-4 text-xs font-bold text-white"
                            >
                                Open
                            </a>

                            @unless ($notification->read_at)
                                <form
                                    method="POST"
                                    action="{{ route(
                                        'notifications.read',
                                        $notification
                                    ) }}"
                                >
                                    @csrf
                                    @method('PUT')

                                    <button class="min-h-10 rounded-xl border border-slate-200 bg-white px-4 text-xs font-bold text-slate-700">
                                        Mark Read
                                    </button>
                                </form>
                            @endunless

                            <form
                                method="POST"
                                action="{{ route(
                                    'notifications.destroy',
                                    $notification
                                ) }}"
                                onsubmit="return confirm('Delete this notification?')"
                            >
                                @csrf
                                @method('DELETE')

                                <button class="min-h-10 rounded-xl bg-red-50 px-4 text-xs font-bold text-red-600">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-14 text-center">
                    <p class="font-black text-slate-900">
                        No notifications found
                    </p>
                </div>
            @endforelse
        </section>

        {{ $notifications->links() }}

        @if (
            auth()
                ->user()
                ->readNotifications()
                ->exists()
        )
            <form
                method="POST"
                action="{{ route(
                    'notifications.clear-read'
                ) }}"
                onsubmit="return confirm('Delete all read notifications?')"
            >
                @csrf
                @method('DELETE')

                <button class="text-sm font-bold text-red-600">
                    Clear All Read Notifications
                </button>
            </form>
        @endif
    </div>
@endsection